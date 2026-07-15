<?php

namespace Tests\Feature;

use App\Models\BottleMessage;
use App\Models\ClientSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class MessageStoreApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_creates_a_waiting_bottle(): void
    {
        $session = ClientSession::create(['session_token' => (string) Str::uuid()]);

        $response = $this->postJson('/api/messages', [
            'body' => 'こんにちは。',
            'sender_session_id' => $session->id,
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('body', 'こんにちは。')
            ->assertJsonPath('status', BottleMessage::STATUS_WAITING)
            ->assertJsonStructure(['id', 'body', 'status', 'created_at']);

        $this->assertDatabaseHas('bottle_messages', [
            'sender_session_id' => $session->id,
            'body' => 'こんにちは。',
            'status' => BottleMessage::STATUS_WAITING,
        ]);
    }

    public function test_store_requires_a_non_empty_body(): void
    {
        $session = ClientSession::create(['session_token' => (string) Str::uuid()]);

        $response = $this->postJson('/api/messages', [
            'body' => '',
            'sender_session_id' => $session->id,
        ]);

        $response->assertUnprocessable()->assertJsonValidationErrors('body');
    }

    public function test_store_requires_an_existing_sender_session(): void
    {
        $response = $this->postJson('/api/messages', [
            'body' => 'こんにちは。',
            'sender_session_id' => (string) Str::uuid(),
        ]);

        $response->assertUnprocessable()->assertJsonValidationErrors('sender_session_id');
    }
}
