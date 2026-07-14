<?php

namespace App\Http\Controllers;

use App\Models\BottleAssignment;
use App\Models\BottleMessage;
use App\Models\BottlePickup;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MessageController extends Controller
{
    /**
     * ボトル回収
     *
     * ユーザーがボトルをクリックして「ひろう」を押した際に呼ばれる。
     * 回収記録(BottlePickup)を作成し、message / assignment を picked にする。
     */
    public function pickup(Request $request, BottleMessage $message): JsonResponse
    {
        $validated = $request->validate([
            'assignment_id' => ['required', 'uuid', 'exists:bottle_assignments,id'],
            'receiver_session_id' => ['required', 'uuid', 'exists:client_sessions,id'],
        ]);

        $body = DB::transaction(function () use ($validated, $message) {
            /** @var BottleMessage $lockedMessage */
            $lockedMessage = BottleMessage::query()
                ->whereKey($message->getKey())
                ->lockForUpdate()
                ->first();

            /** @var BottleAssignment|null $assignment */
            $assignment = BottleAssignment::query()
                ->whereKey($validated['assignment_id'])
                ->lockForUpdate()
                ->first();

            // 割り当てが対象メッセージ・回収者に紐づくか
            if ($assignment->bottle_message_id !== $lockedMessage->id
                || $assignment->assigned_session_id !== $validated['receiver_session_id']) {
                throw ValidationException::withMessages([
                    'assignment_id' => 'この割り当ては指定されたメッセージ・セッションに紐づいていません。',
                ]);
            }

            // すでに回収済み、または有効な割り当てでない場合は回収不可
            if ($lockedMessage->status === BottleMessage::STATUS_PICKED
                || $assignment->status !== BottleAssignment::STATUS_ACTIVE) {
                throw ValidationException::withMessages([
                    'assignment_id' => 'このボトルはすでに回収済みか、回収できる状態ではありません。',
                ]);
            }

            $now = now();

            BottlePickup::create([
                'bottle_message_id' => $lockedMessage->id,
                'receiver_session_id' => $validated['receiver_session_id'],
                'assignment_id' => $assignment->id,
                'picked_at' => $now,
            ]);

            $lockedMessage->update([
                'status' => BottleMessage::STATUS_PICKED,
                'picked_at' => $now,
            ]);

            $assignment->update([
                'status' => BottleAssignment::STATUS_PICKED,
            ]);

            return $lockedMessage->body;
        });

        return response()->json([
            'message_id' => $message->id,
            'body' => $body,
        ]);
    }
}
