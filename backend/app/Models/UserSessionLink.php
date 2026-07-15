<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class UserSessionLink extends Model
{
    use HasUuids;

    protected $table = 'user_session_links';

    protected $fillable = [
        'user_id',
        'client_session_id',
        'linked_at',
    ];

    protected $casts = [
        'linked_at' => 'datetime',
    ];

    /**
     * ユーザーとのリレーション
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * セッションとのリレーション
     */
    public function clientSession()
    {
        return $this->belongsTo(ClientSession::class);
    }
}
