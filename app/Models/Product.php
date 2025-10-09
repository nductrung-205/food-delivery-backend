<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name',
        'category_id',
        'description',
        'price',
        'stock',
        'status',
        'image',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}