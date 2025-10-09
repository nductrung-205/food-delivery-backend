<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\User;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        Order::create([
            'user_id' => 2,
            'total_price' => 15.98,
            'status' => 'confirmed',
            'payment_method' => 'cash',
            'ordered_at' => now(),
        ]);
    }
}
