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
}
