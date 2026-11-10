<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactMessage extends Model
{
    protected $table = 'messages';
    public $timestamps = false;

    protected $fillable = [
        'ticket_number',
        'user_id',
        'topic',
        'order_number',
        'name',
        'email',
        'subject',
        'message',
        'status',
        'reply',
        'replied_at',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'replied_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
