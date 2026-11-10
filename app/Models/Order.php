<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $table = 'orders';
    public $timestamps = false;

    protected $fillable = [
        'order_number',
        'user_id',
        'recipient_name',
        'recipient_phone',
        'shipping_address',
        'payment_method',
        'payment_status',
        'order_status',
        'total_amount',
        'notes',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'total_amount' => 'float',
            'created_at'   => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }

    // Relasi Many-to-Many: Satu Order berisi banyak Buku melalui tabel pivot order_items
    public function books()
    {
        return $this->belongsToMany(Book::class, 'order_items', 'order_id', 'book_id')
                    ->withPivot(['quantity', 'price', 'subtotal']);
    }
}
