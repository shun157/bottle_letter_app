<?php

namespace App\Http\Controllers;

use App\Models\ClientSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SessionController extends Controller
{
    /**
     * クライアントセッション発行
     *
     * ログインなしユーザーを識別するためのセッションを新規作成する。
     * フロントは起動時にこれを呼び、session_id を localStorage 等に保存する。
     */
    public function store(): JsonResponse
    {
        $session = ClientSession::create([
            'session_token' => (string) Str::uuid(),
            'last_seen_at' => now(),
        ]);

        return response()->json([
            'session_id' => $session->id,
        ], 201);
    }

    /**
     * セッション更新（オンライン維持）
     *
     * サイトを開いている間、last_seen_at を現在時刻に更新する。
     * 現在サイトを開いている人数の集計などに使う。
     */
    public function active(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'session_id' => ['required', 'uuid', 'exists:client_sessions,id'],
        ]);

        $session = ClientSession::query()->findOrFail($validated['session_id']);
        $session->update(['last_seen_at' => now()]);

        return response()->json([
            'session_id' => $session->id,
            'last_seen_at' => optional($session->last_seen_at)->toJSON(),
        ]);
    }
}
