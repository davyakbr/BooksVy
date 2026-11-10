<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    protected $table = 'banners';
    public $timestamps = false;

    protected $fillable = [
        'title',
        'subtitle',
        'badge_text',
        'btn1_label',
        'btn1_url',
        'btn2_label',
        'btn2_url',
        'bg_class',
        'image',
        'image_2',
        'image_3',
        'sort_order',
        'is_active',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'is_active'  => 'boolean',
            'sort_order' => 'integer',
        ];
    }
}
