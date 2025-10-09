<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'image',
        'parent_id',
    ];

    // Định nghĩa quan hệ với các danh mục con
    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    // Định nghĩa quan hệ với danh mục cha
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    // Quan hệ với Products (một Category có nhiều Product)
    public function products()
    {
        return $this->hasMany(Product::class);
    }
}