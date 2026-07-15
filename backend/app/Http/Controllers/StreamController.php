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
                'assigned_until' => $now->copy()->addSeconds(30),
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
}
