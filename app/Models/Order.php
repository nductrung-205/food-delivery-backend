<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'total_price',
        'status',
        'payment_method',
        'ordered_at',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

     public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

}
