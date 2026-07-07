<?php

namespace Tests\Feature;

use App\Models\BottleAssignment;
use App\Models\BottleMessage;
use App\Models\ClientSession;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class MessagePickupApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array{BottleMessage, BottleAssignment, ClientSession}
     */
    private function makeAssignedBottle(string $assignmentStatus = BottleAssignment::STATUS_ACTIVE): array
    {
        $sender = ClientSession::create(['session_token' => (string) Str::uuid()]);
        $receiver = ClientSession::create(['session_token' => (string) Str::uuid()]);

        $message = BottleMessage::create([
            'sender_session_id' => $sender->id,
            'body' => 'こんにちは。どこかの誰かへ。',
            'status' => BottleMessage::STATUS_ASSIGNED,
        ]);
        $assignment = BottleAssignment::create([
            'bottle_message_id' => $message->id,
            'assigned_session_id' => $receiver->id,
            'status' => $assignmentStatus,
            'assigned_at' => now(),
            'assigned_until' => now()->addSeconds(30),
        ]);

        return [$message, $assignment, $receiver];
    }

    public function test_pickup_records_the_bottle_and_returns_the_body(): void
    {
        [$message, $assignment, $receiver] = $this->makeAssignedBottle();

        $response = $this->postJson('/api/messages/'.$message->id.'/pickup', [
            'assignment_id' => $assignment->id,
            'receiver_session_id' => $receiver->id,
        ]);

        $response
            ->assertOk()
            ->assertExactJson([
                'message_id' => $message->id,
                'body' => 'こんにちは。どこかの誰かへ。',
            ]);

        $this->assertDatabaseHas('bottle_pickups', [
            'bottle_message_id' => $message->id,
            'receiver_session_id' => $receiver->id,
            'assignment_id' => $assignment->id,
        ]);
        $this->assertDatabaseHas('bottle_messages', [
            'id' => $message->id,
            'status' => BottleMessage::STATUS_PICKED,
        ]);
        $this->assertDatabaseHas('bottle_assignments', [
            'id' => $assignment->id,
            'status' => BottleAssignment::STATUS_PICKED,
        ]);
        $this->assertNotNull($message->fresh()->picked_at);
    }

    public function test_pickup_rejects_an_assignment_belonging_to_another_session(): void
    {
        [$message, $assignment] = $this->makeAssignedBottle();
        $stranger = ClientSession::create(['session_token' => (string) Str::uuid()]);

        $response = $this->postJson('/api/messages/'.$message->id.'/pickup', [
            'assignment_id' => $assignment->id,
            'receiver_session_id' => $stranger->id,
        ]);

        $response->assertUnprocessable();
        $this->assertDatabaseCount('bottle_pickups', 0);
    }

    public function test_pickup_prevents_double_pickup(): void
    {
        [$message, $assignment, $receiver] = $this->makeAssignedBottle();

        $this->postJson('/api/messages/'.$message->id.'/pickup', [
            'assignment_id' => $assignment->id,
            'receiver_session_id' => $receiver->id,
        ])->assertOk();

        $second = $this->postJson('/api/messages/'.$message->id.'/pickup', [
            'assignment_id' => $assignment->id,
            'receiver_session_id' => $receiver->id,
        ]);

        $second->assertUnprocessable();
        $this->assertDatabaseCount('bottle_pickups', 1);
    }

    public function test_pickup_rejects_an_expired_assignment(): void
    {
        [$message, $assignment, $receiver] = $this->makeAssignedBottle(BottleAssignment::STATUS_EXPIRED);

        $response = $this->postJson('/api/messages/'.$message->id.'/pickup', [
            'assignment_id' => $assignment->id,
            'receiver_session_id' => $receiver->id,
        ]);

        $response->assertUnprocessable();
        $this->assertDatabaseCount('bottle_pickups', 0);
    }

    public function test_pickup_returns_404_for_unknown_message(): void
    {
        $receiver = ClientSession::create(['session_token' => (string) Str::uuid()]);
        $assignment = BottleAssignment::create([
            'bottle_message_id' => (string) Str::uuid(),
            'assigned_session_id' => $receiver->id,
            'status' => BottleAssignment::STATUS_ACTIVE,
            'assigned_at' => now(),
            'assigned_until' => now()->addSeconds(30),
        ]);

        $response = $this->postJson('/api/messages/'.Str::uuid().'/pickup', [
            'assignment_id' => $assignment->id,
            'receiver_session_id' => $receiver->id,
        ]);

        $response->assertNotFound();
    }
}
