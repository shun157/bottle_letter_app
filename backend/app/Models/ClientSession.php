<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClientSession extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'session_token',
        'notification_enabled',
        'last_seen_at',
    ];

    protected function casts(): array
    {
        return [
            'notification_enabled' => 'boolean',
            'last_seen_at' => 'datetime',
        ];
    }

    public function sentMessages(): HasMany
    {
        return $this->hasMany(BottleMessage::class, 'sender_session_id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(BottleAssignment::class, 'assigned_session_id');
    }
}
