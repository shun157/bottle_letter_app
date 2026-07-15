<?php

namespace Tests\Feature;

use App\Models\ClientSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Tests\TestCase;

class SessionActiveApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_updates_last_seen_at(): void
    {
        $session = ClientSession::create([
            'session_token' => (string) Str::uuid(),
            'last_seen_at' => now()->subHour(),
        ]);

        Carbon::setTestNow(now());

        $response = $this->putJson('/api/session/active', [
            'session_id' => $session->id,
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('session_id', $session->id)
            ->assertJsonStructure(['session_id', 'last_seen_at']);

        $this->assertSame(
            now()->format('Y-m-d H:i:s'),
            $session->fresh()->last_seen_at->format('Y-m-d H:i:s'),
            'last_seen_at should be updated to the current time'
        );

        Carbon::setTestNow();
    }

    public function test_active_requires_an_existing_session(): void
    {
        $response = $this->putJson('/api/session/active', [
            'session_id' => (string) Str::uuid(),
        ]);

        $response->assertUnprocessable()->assertJsonValidationErrors('session_id');
    }
}
