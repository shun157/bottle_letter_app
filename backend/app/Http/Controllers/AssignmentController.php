<?php

namespace App\Http\Controllers;

use App\Models\BottleAssignment;
use App\Models\BottleMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class AssignmentController extends Controller
{
    /**
     * 割り当て期限切れ（再放流）
     *
     * 表示されていたボトルが画面から消えた際に呼ばれ、
     * assignment を expired、対応する message を waiting に戻す。
     */
    public function expire(BottleAssignment $assignment): JsonResponse
    {
        $result = DB::transaction(function () use ($assignment) {
            /** @var BottleAssignment $locked */
            $locked = BottleAssignment::query()
                ->whereKey($assignment->getKey())
                ->lockForUpdate()
                ->first();

            // すでに expired / picked 済みなら何もしない（冪等）
            if ($locked->status !== BottleAssignment::STATUS_ACTIVE) {
                return $locked->load('message');
            }

            $locked->update([
                'status' => BottleAssignment::STATUS_EXPIRED,
            ]);

            // 拾われていない（assigned のまま）場合のみ待機中に戻す
            $message = $locked->message()->lockForUpdate()->first();
            if ($message !== null && $message->status === BottleMessage::STATUS_ASSIGNED) {
                $message->update([
                    'status' => BottleMessage::STATUS_WAITING,
                ]);
            }

            return $locked->load('message');
        });

        return response()->json([
            'assignment_id' => $result->id,
            'status' => $result->status,
            'message_status' => $result->message?->status,
        ]);
    }
}
