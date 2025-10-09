<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Món Chính', 'description' => 'Các món ăn chính'],
            ['name' => 'Món Phụ', 'description' => 'Các món ăn phụ và khai vị'],
            ['name' => 'Đồ Uống', 'description' => 'Nước ngọt, trà, cà phê'],
            ['name' => 'Tráng Miệng', 'description' => 'Bánh ngọt và tráng miệng'],
            ['name' => 'Món Lẩu', 'description' => 'Các loại lẩu'],
            ['name' => 'Món Nướng', 'description' => 'Các món nướng BBQ'],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
