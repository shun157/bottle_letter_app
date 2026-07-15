<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;


class CollectionItem extends Model
{
    use HasUuids;

    protected $table = 'collection_items';

    protected $fillable = [
        'user_id',
        'bottle_message_id',
        'collected_at',
    ];

    protected $casts = [
        'collected_at' => 'datetime',
    ];

    /**
     * コレクションしたユーザー
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * コレクションされたボトルメッセージ
     */
    public function bottleMessage()
    {
        return $this->belongsTo(BottleMessage::class);
    }

}
