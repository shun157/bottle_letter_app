<?php

namespace App\Http\Controllers;

use App\Models\BottleMessage;
use App\Models\BottlePickup;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CollectionController extends Controller
{
    /**
     * コレクション・履歴取得
     *
     * 自分が拾ったメッセージと、自分が送信したメッセージの一覧を返す。
     */
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'session_id' => ['required', 'uuid', 'exists:client_sessions,id'],
        ]);

        $sessionId = $validated['session_id'];

        $pickedMessages = BottlePickup::query()
            ->with('message')
            ->where('receiver_session_id', $sessionId)
            ->orderByDesc('picked_at')
            ->get()
            ->map(fn (BottlePickup $pickup) => [
                'pickup_id' => $pickup->id,
                'message_id' => $pickup->bottle_message_id,
                'body' => $pickup->message?->body,
                'picked_at' => optional($pickup->picked_at)->toJSON(),
            ]);

        $sentMessages = BottleMessage::query()
            ->where('sender_session_id', $sessionId)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (BottleMessage $message) => [
                'message_id' => $message->id,
                'body' => $message->body,
                'status' => $message->status,
                'created_at' => optional($message->created_at)->toJSON(),
                'picked_at' => optional($message->picked_at)->toJSON(),
            ]);

        return response()->json([
            'picked_messages' => $pickedMessages,
            'sent_messages' => $sentMessages,
        ]);
    }
}
