<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Banner;

class BannerSeeder extends Seeder
{
    public function run(): void
    {
        Banner::create([
            'title' => 'Khuyến mãi Pizza 50%',
            'image' => 'banner1.jpg',
            'is_active' => true,
        ]);
    }
}
