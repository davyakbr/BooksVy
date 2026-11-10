<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $table = 'categories';
    public $timestamps = false;

    protected $fillable = [
        'name',
        'description',
        'created_at',
    ];

    public function books()
    {
        return $this->hasMany(Book::class, 'category_id');
    }
}
