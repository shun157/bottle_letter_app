<?php

namespace Tests\Feature;

use App\Models\BottleAssignment;
use App\Models\BottleMessage;
use App\Models\ClientSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class AssignmentExpireApiTest extends TestCase
{
    use RefreshDatabase;

    private function makeActiveAssignment(): array
    {
        $sender = ClientSession::create([
            'session_token' => (string) Str::uuid(),
        ]);
        $receiver = ClientSession::create([
            'session_token' => (string) Str::uuid(),
        ]);
        $message = BottleMessage::create([
            'sender_session_id' => $sender->id,
            'body' => 'drifting bottle',
            'status' => BottleMessage::STATUS_ASSIGNED,
        ]);
        $assignment = BottleAssignment::create([
            'bottle_message_id' => $message->id,
            'assigned_session_id' => $receiver->id,
            'status' => BottleAssignment::STATUS_ACTIVE,
            'assigned_at' => now(),
            'assigned_until' => now()->addSeconds(30),
        ]);

        return [$assignment, $message];
    }

    public function test_expire_returns_the_message_to_the_waiting_pool(): void
    {
        [$assignment, $message] = $this->makeActiveAssignment();

        $response = $this->patchJson('/api/assignments/'.$assignment->id.'/expire');

        $response
            ->assertOk()
            ->assertExactJson([
                'assignment_id' => $assignment->id,
                'status' => BottleAssignment::STATUS_EXPIRED,
                'message_status' => BottleMessage::STATUS_WAITING,
            ]);

        $this->assertDatabaseHas('bottle_assignments', [
            'id' => $assignment->id,
            'status' => BottleAssignment::STATUS_EXPIRED,
        ]);
        $this->assertDatabaseHas('bottle_messages', [
            'id' => $message->id,
            'status' => BottleMessage::STATUS_WAITING,
        ]);
    }

    public function test_expire_is_idempotent_when_already_expired(): void
    {
        [$assignment, $message] = $this->makeActiveAssignment();
        $assignment->update(['status' => BottleAssignment::STATUS_EXPIRED]);
        $message->update(['status' => BottleMessage::STATUS_WAITING]);

        $response = $this->patchJson('/api/assignments/'.$assignment->id.'/expire');

        $response
            ->assertOk()
            ->assertJsonPath('status', BottleAssignment::STATUS_EXPIRED)
            ->assertJsonPath('message_status', BottleMessage::STATUS_WAITING);
    }

    public function test_expire_does_not_revert_an_already_picked_message(): void
    {
        [$assignment, $message] = $this->makeActiveAssignment();
        // ボトルが拾われた後に期限切れリクエストが届いたケース
        $assignment->update(['status' => BottleAssignment::STATUS_PICKED]);
        $message->update(['status' => BottleMessage::STATUS_PICKED]);

        $response = $this->patchJson('/api/assignments/'.$assignment->id.'/expire');

        $response
            ->assertOk()
            ->assertJsonPath('status', BottleAssignment::STATUS_PICKED)
            ->assertJsonPath('message_status', BottleMessage::STATUS_PICKED);

        $this->assertDatabaseHas('bottle_messages', [
            'id' => $message->id,
            'status' => BottleMessage::STATUS_PICKED,
        ]);
    }

    public function test_expire_returns_404_for_unknown_assignment(): void
    {
        $response = $this->patchJson('/api/assignments/'.Str::uuid().'/expire');

        $response->assertNotFound();
    }
}
