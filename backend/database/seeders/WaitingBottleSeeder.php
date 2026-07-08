<?php

namespace Database\Seeders;

use App\Models\BottleMessage;
use App\Models\ClientSession;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class WaitingBottleSeeder extends Seeder
{
    /**
     * 動作確認用に waiting 状態のボトルを投入する。
     * 送信者は専用のシステムセッション1つにまとめる。
     */
    public function run(): void
    {
        $sender = ClientSession::firstOrCreate(
            ['session_token' => 'seed-system-sender'],
            ['last_seen_at' => now()]
        );

        $bodies = [
            'こんにちは。どこかの誰かへ。',
            '今日も一日おつかれさま。',
            '海の向こうから、こんにちは。',
            'いいことがありますように。',
            'このボトルが誰かに届きますように。',
        ];

        foreach ($bodies as $body) {
            BottleMessage::create([
                'id' => (string) Str::uuid(),
                'sender_session_id' => $sender->id,
                'body' => $body,
                'status' => BottleMessage::STATUS_WAITING,
            ]);
        }
    }
}
