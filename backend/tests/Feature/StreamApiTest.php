<?php

namespace Tests\Feature;

use App\Models\BottleAssignment;
use App\Models\BottleMessage;
use App\Models\ClientSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class StreamApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_stream_assigns_a_waiting_message_to_the_request_session(): void
    {
        $sender = ClientSession::create([
            'session_token' => (string) Str::uuid(),
        ]);
        $receiver = ClientSession::create([
            'session_token' => (string) Str::uuid(),
        ]);
        $message = BottleMessage::create([
            'sender_session_id' => $sender->id,
            'body' => 'hello from the sea',
            'status' => BottleMessage::STATUS_WAITING,
        ]);

        $response = $this->getJson('/api/stream?session_id='.$receiver->id);

        $response
            ->assertOk()
            ->assertJsonPath('message.id', $message->id)
            ->assertJsonPath('message.body', 'hello from the sea')
            ->assertJsonStructure([
                'assignment_id',
                'message' => ['id', 'body'],
                'assigned_until',
            ]);

        $this->assertDatabaseHas('bottle_messages', [
            'id' => $message->id,
            'status' => BottleMessage::STATUS_ASSIGNED,
        ]);
        $this->assertDatabaseHas('bottle_assignments', [
            'bottle_message_id' => $message->id,
            'assigned_session_id' => $receiver->id,
            'status' => BottleAssignment::STATUS_ACTIVE,
        ]);
    }

    public function test_stream_returns_null_when_no_waiting_message_exists(): void
    {
        $session = ClientSession::create([
            'session_token' => (string) Str::uuid(),
        ]);

        $response = $this->getJson('/api/stream?session_id='.$session->id);

        $response
            ->assertOk()
            ->assertExactJson([
                'message' => null,
            ]);
    }

    public function test_stream_does_not_assign_the_request_sessions_own_message(): void
    {
        $session = ClientSession::create([
            'session_token' => (string) Str::uuid(),
        ]);
        BottleMessage::create([
            'sender_session_id' => $session->id,
            'body' => 'my own bottle',
            'status' => BottleMessage::STATUS_WAITING,
        ]);

        $response = $this->getJson('/api/stream?session_id='.$session->id);

        $response
            ->assertOk()
            ->assertExactJson([
                'message' => null,
            ]);
    }

    public function test_stream_requires_an_existing_session(): void
    {
        $response = $this->getJson('/api/stream?session_id='.Str::uuid());

        $response->assertUnprocessable();
    }

    public function test_stream_reclaims_expired_assignments_and_reassigns(): void
    {
        $sender = ClientSession::create(['session_token' => (string) Str::uuid()]);
        $receiver = ClientSession::create(['session_token' => (string) Str::uuid()]);

        // 誰かが受け取ったまま放置され、期限切れになったボトル
        $message = BottleMessage::create([
            'sender_session_id' => $sender->id,
            'body' => 'stuck bottle',
            'status' => BottleMessage::STATUS_ASSIGNED,
        ]);
        $stale = BottleAssignment::create([
            'bottle_message_id' => $message->id,
            'assigned_session_id' => $sender->id,
            'status' => BottleAssignment::STATUS_ACTIVE,
            'assigned_at' => now()->subMinutes(5),
            'assigned_until' => now()->subMinutes(4),
        ]);

        $response = $this->getJson('/api/stream?session_id='.$receiver->id);

        // 回収され、改めて receiver に配布される
        $response
            ->assertOk()
            ->assertJsonPath('message.id', $message->id);

        $this->assertDatabaseHas('bottle_assignments', [
            'id' => $stale->id,
            'status' => BottleAssignment::STATUS_EXPIRED,
        ]);
        $this->assertDatabaseHas('bottle_assignments', [
            'bottle_message_id' => $message->id,
            'assigned_session_id' => $receiver->id,
            'status' => BottleAssignment::STATUS_ACTIVE,
        ]);
    }

    public function test_stream_does_not_reclaim_assignments_before_expiry(): void
    {
        $sender = ClientSession::create(['session_token' => (string) Str::uuid()]);
        $receiver = ClientSession::create(['session_token' => (string) Str::uuid()]);

        $message = BottleMessage::create([
            'sender_session_id' => $sender->id,
            'body' => 'still active',
            'status' => BottleMessage::STATUS_ASSIGNED,
        ]);
        BottleAssignment::create([
            'bottle_message_id' => $message->id,
            'assigned_session_id' => $sender->id,
            'status' => BottleAssignment::STATUS_ACTIVE,
            'assigned_at' => now(),
            'assigned_until' => now()->addSeconds(BottleAssignment::ACTIVE_SECONDS),
        ]);

        // まだ期限内なので回収されず、配れるボトルは無い
        $response = $this->getJson('/api/stream?session_id='.$receiver->id);

        $response->assertOk()->assertExactJson(['message' => null]);
        $this->assertDatabaseHas('bottle_messages', [
            'id' => $message->id,
            'status' => BottleMessage::STATUS_ASSIGNED,
        ]);
    }
}
