<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\OrderItem;

class OrderItemSeeder extends Seeder
{
    public function run(): void
    {
        OrderItem::create([
            'order_id' => 1,
            'product_id' => 1,
            'quantity' => 1,
            'price' => 9.99,
        ]);
    }
}
