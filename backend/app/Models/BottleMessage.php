<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BottleMessage extends Model
{
    use HasFactory, HasUuids;

    public const STATUS_WAITING = 'waiting';
    public const STATUS_ASSIGNED = 'assigned';
    public const STATUS_PICKED = 'picked';
    public const STATUS_EXPIRED = 'expired';

    protected $fillable = [
        'sender_session_id',
        'body',
        'status',
        'picked_at',
    ];

    protected function casts(): array
    {
        return [
            'picked_at' => 'datetime',
        ];
    }

    public function senderSession(): BelongsTo
    {
        return $this->belongsTo(ClientSession::class, 'sender_session_id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(BottleAssignment::class);
    }
}
