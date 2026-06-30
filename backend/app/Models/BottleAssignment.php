<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BottleAssignment extends Model
{
    use HasFactory, HasUuids;

    public const STATUS_ACTIVE = 'active';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_PICKED = 'picked';

    protected $fillable = [
        'bottle_message_id',
        'assigned_session_id',
        'status',
        'assigned_at',
        'assigned_until',
    ];

    protected function casts(): array
    {
        return [
            'assigned_at' => 'datetime',
            'assigned_until' => 'datetime',
        ];
    }

    public function message(): BelongsTo
    {
        return $this->belongsTo(BottleMessage::class, 'bottle_message_id');
    }

    public function assignedSession(): BelongsTo
    {
        return $this->belongsTo(ClientSession::class, 'assigned_session_id');
    }
}
