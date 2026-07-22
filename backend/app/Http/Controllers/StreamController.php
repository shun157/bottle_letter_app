<?php

namespace App\Http\Controllers;

use App\Models\BottleAssignment;
use App\Models\BottleMessage;
use App\Models\ClientSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StreamController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'session_id' => ['required', 'uuid', 'exists:client_sessions,id'],
        ]);

        // 期限切れの割り当てを海へ戻してから配布する（拾われず放置されたボトルの回収）
        $this->reclaimExpiredAssignments();

        $assignment = DB::transaction(function () use ($validated) {
            $message = BottleMessage::query()
                ->where('status', BottleMessage::STATUS_WAITING)
                ->where('sender_session_id', '!=', $validated['session_id'])
                ->inRandomOrder()
                ->lockForUpdate()
                ->first();

            if ($message === null) {
                return null;
            }

            $now = now();

            $assignment = BottleAssignment::create([
                'bottle_message_id' => $message->id,
                'assigned_session_id' => $validated['session_id'],
                'status' => BottleAssignment::STATUS_ACTIVE,
                'assigned_at' => $now,
                'assigned_until' => $now->copy()->addSeconds(BottleAssignment::ACTIVE_SECONDS),
            ]);

            $message->update([
                'status' => BottleMessage::STATUS_ASSIGNED,
            ]);

            return $assignment->load('message');
        });

        if ($assignment === null) {
            return response()->json([
                'message' => null,
            ]);
        }

        return response()->json([
            'assignment_id' => $assignment->id,
            'message' => [
                'id' => $assignment->message->id,
                'body' => $assignment->message->body,
            ],
            'assigned_until' => $assignment->assigned_until->toJSON(),
        ]);
    }

    /**
     * assigned_until を過ぎても active のままの割り当てを expired にし、
     * 対応するメッセージを waiting に戻す（サーバ側の再放流）。
     */
    private function reclaimExpiredAssignments(): void
    {
        DB::transaction(function () {
            $expired = BottleAssignment::query()
                ->where('status', BottleAssignment::STATUS_ACTIVE)
                ->where('assigned_until', '<', now())
                ->lockForUpdate()
                ->get();

            foreach ($expired as $assignment) {
                $assignment->update(['status' => BottleAssignment::STATUS_EXPIRED]);

                BottleMessage::query()
                    ->whereKey($assignment->bottle_message_id)
                    ->where('status', BottleMessage::STATUS_ASSIGNED)
                    ->update(['status' => BottleMessage::STATUS_WAITING]);
            }
        });
    }
}
