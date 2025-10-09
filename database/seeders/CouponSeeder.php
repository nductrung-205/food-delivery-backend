<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Coupon;

class CouponSeeder extends Seeder
{
    public function run(): void
    {
        Coupon::create([
            'code' => 'DISCOUNT10',
            'discount' => 10,
            'type' => 'percent',
            'expires_at' => now()->addDays(30),
        ]);
    }
}
