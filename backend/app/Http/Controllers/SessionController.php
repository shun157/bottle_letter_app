<?php

namespace App\Http\Controllers;

use App\Models\ClientSession;
use Illuminate\Http\JsonResponse;
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
}
