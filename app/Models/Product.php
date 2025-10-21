<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'price',
        'category_id',
        'description',
        'stock',
        'status',
        'image',
        'cloudinary_public_id'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'stock' => 'integer',
    ];

    // ⚡️Tự động thêm trường image_url vào JSON trả về
    protected $appends = ['image_url'];

    public function getImageUrlAttribute()
    {
        if (!$this->image) return null;

        // Nếu là link Cloudinary
        if (preg_match('/^https?:\/\//', $this->image)) {
            return $this->image;
        }

        // Nếu là ảnh local
        return asset('storage/' . $this->image);
    }



    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
