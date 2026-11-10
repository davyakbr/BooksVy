<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    protected $table = 'books';
    public $timestamps = false;

    protected $fillable = [
        'category_id',
        'title',
        'author',
        'publisher',
        'year',
        'price',
        'stock',
        'description',
        'cover_image',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'float',
            'stock' => 'integer',
            'year'  => 'integer',
        ];
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    // Relasi Many-to-Many: Satu Buku bisa berada di banyak Order melalui tabel pivot order_items
    public function orders()
    {
        return $this->belongsToMany(Order::class, 'order_items', 'book_id', 'order_id')
                    ->withPivot(['quantity', 'price', 'subtotal']);
    }

    public function isOutOfStock(): bool
    {
        return $this->stock <= 0;
    }

    public function getCoverUrlAttribute(): ?string
    {
        if ($this->cover_image && $this->cover_image !== 'default_book.png') {
            return asset('uploads/' . $this->cover_image);
        }
        return null;
    }
}
