<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BottlePickup extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'bottle_message_id',
        'receiver_session_id',
        'assignment_id',
        'picked_at',
    ];

    protected function casts(): array
    {
        return [
            'picked_at' => 'datetime',
        ];
    }

    public function message(): BelongsTo
    {
        return $this->belongsTo(BottleMessage::class, 'bottle_message_id');
    }

    public function receiverSession(): BelongsTo
    {
        return $this->belongsTo(ClientSession::class, 'receiver_session_id');
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(BottleAssignment::class, 'assignment_id');
    }
}
