<?php

namespace Tests\Feature;

use App\Models\BottleAssignment;
use App\Models\BottleMessage;
use App\Models\BottlePickup;
use App\Models\ClientSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CollectionApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_collection_returns_picked_and_sent_messages_for_the_session(): void
    {
        $me = ClientSession::create(['session_token' => (string) Str::uuid()]);
        $other = ClientSession::create(['session_token' => (string) Str::uuid()]);

        // 自分が送信したメッセージ
        $sent = BottleMessage::create([
            'sender_session_id' => $me->id,
            'body' => '自分が流したメッセージ',
            'status' => BottleMessage::STATUS_WAITING,
        ]);

        // 他人が送信し、自分が拾ったメッセージ
        $othersMessage = BottleMessage::create([
            'sender_session_id' => $other->id,
            'body' => '誰かが流したメッセージ',
            'status' => BottleMessage::STATUS_PICKED,
            'picked_at' => now(),
        ]);
        $assignment = BottleAssignment::create([
            'bottle_message_id' => $othersMessage->id,
            'assigned_session_id' => $me->id,
            'status' => BottleAssignment::STATUS_PICKED,
            'assigned_at' => now(),
            'assigned_until' => now()->addSeconds(30),
        ]);
        $pickup = BottlePickup::create([
            'bottle_message_id' => $othersMessage->id,
            'receiver_session_id' => $me->id,
            'assignment_id' => $assignment->id,
            'picked_at' => now(),
        ]);

        $response = $this->getJson('/api/collection?session_id='.$me->id);

        $response
            ->assertOk()
            ->assertJsonCount(1, 'picked_messages')
            ->assertJsonCount(1, 'sent_messages')
            ->assertJsonPath('picked_messages.0.pickup_id', $pickup->id)
            ->assertJsonPath('picked_messages.0.message_id', $othersMessage->id)
            ->assertJsonPath('picked_messages.0.body', '誰かが流したメッセージ')
            ->assertJsonPath('sent_messages.0.message_id', $sent->id)
            ->assertJsonPath('sent_messages.0.body', '自分が流したメッセージ')
            ->assertJsonPath('sent_messages.0.status', BottleMessage::STATUS_WAITING);
    }

    public function test_collection_is_empty_for_a_session_with_no_history(): void
    {
        $session = ClientSession::create(['session_token' => (string) Str::uuid()]);

        $response = $this->getJson('/api/collection?session_id='.$session->id);

        $response
            ->assertOk()
            ->assertExactJson([
                'picked_messages' => [],
                'sent_messages' => [],
            ]);
    }

    public function test_collection_does_not_leak_other_sessions_history(): void
    {
        $me = ClientSession::create(['session_token' => (string) Str::uuid()]);
        $other = ClientSession::create(['session_token' => (string) Str::uuid()]);

        BottleMessage::create([
            'sender_session_id' => $other->id,
            'body' => '他人の送信メッセージ',
            'status' => BottleMessage::STATUS_WAITING,
        ]);

        $response = $this->getJson('/api/collection?session_id='.$me->id);

        $response
            ->assertOk()
            ->assertJsonCount(0, 'sent_messages')
            ->assertJsonCount(0, 'picked_messages');
    }

    public function test_collection_requires_an_existing_session(): void
    {
        $response = $this->getJson('/api/collection?session_id='.Str::uuid());

        $response->assertUnprocessable();
    }
}
