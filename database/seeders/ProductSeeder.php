<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            // Món Chính
            [
                'name' => 'Phở Bò Tái',
                'category' => 'Món Chính',
                'description' => 'Phở bò truyền thống Hà Nội với thịt bò tái mềm, nước dùng thanh ngọt từ xương ống hầm 12 tiếng',
                'price' => 45000,
                'stock' => 50,
                'status' => 'available',
            ],
            [
                'name' => 'Bún Bò Huế',
                'category' => 'Món Chính',
                'description' => 'Bún bò Huế chuẩn vị cay nồng, nước lèo đậm đà với sả và mắm ruốc',
                'price' => 50000,
                'stock' => 40,
                'status' => 'available',
            ],
            [
                'name' => 'Cơm Tấm Sườn Bì Chả',
                'category' => 'Món Chính',
                'description' => 'Cơm tấm sườn nướng thơm phức, bì giòn và chả trứng đặc biệt',
                'price' => 40000,
                'stock' => 60,
                'status' => 'available',
            ],
            [
                'name' => 'Mì Quảng',
                'category' => 'Món Chính',
                'description' => 'Mì Quảng Đà Nẵng với tôm, thịt, trứng cút và nước lèo đậm đà',
                'price' => 48000,
                'stock' => 35,
                'status' => 'available',
            ],
            [
                'name' => 'Bánh Xèo Miền Tây',
                'category' => 'Món Chính',
                'description' => 'Bánh xèo giòn rụm với tôm tươi, thịt ba chỉ và giá đỗ',
                'price' => 55000,
                'stock' => 30,
                'status' => 'available',
            ],
            [
                'name' => 'Bún Chả Hà Nội',
                'category' => 'Món Chính',
                'description' => 'Bún chả nướng than hoa thơm lừng, chả viên đặc biệt và nước mắm chua ngọt',
                'price' => 52000,
                'stock' => 45,
                'status' => 'available',
            ],
            
            // Món Phụ
            [
                'name' => 'Gỏi Cuốn Tôm Thịt',
                'category' => 'Món Phụ',
                'description' => 'Gỏi cuốn tươi mát với tôm, thịt luộc và rau thơm',
                'price' => 35000,
                'stock' => 50,
                'status' => 'available',
            ],
            [
                'name' => 'Chả Giò Rế',
                'category' => 'Món Phụ',
                'description' => 'Chả giò chiên giòn tan với nhân thịt và mộc nhĩ',
                'price' => 38000,
                'stock' => 40,
                'status' => 'available',
            ],
            [
                'name' => 'Nem Nướng Nha Trang',
                'category' => 'Món Phụ',
                'description' => 'Nem nướng thơm nức mũi, ăn kèm bánh tráng và rau sống',
                'price' => 42000,
                'stock' => 35,
                'status' => 'available',
            ],
            [
                'name' => 'Bò Lá Lốt',
                'category' => 'Món Phụ',
                'description' => 'Thịt bò bọc lá lốt nướng than, thơm ngon đậm đà',
                'price' => 65000,
                'stock' => 25,
                'status' => 'available',
            ],

            // Món Lẩu
            [
                'name' => 'Lẩu Thái Hải Sản',
                'category' => 'Món Lẩu',
                'description' => 'Lẩu Thái chua cay với tôm, mực, cá và rau củ tươi ngon',
                'price' => 280000,
                'stock' => 20,
                'status' => 'available',
            ],
            [
                'name' => 'Lẩu Gà Lá É',
                'category' => 'Món Lẩu',
                'description' => 'Lẩu gà đặc sản miền Tây với lá é thơm đặc trưng',
                'price' => 250000,
                'stock' => 15,
                'status' => 'available',
            ],

            // Món Nướng
            [
                'name' => 'Sườn Nướng BBQ',
                'category' => 'Món Nướng',
                'description' => 'Sườn heo nướng sốt BBQ Hàn Quốc, mềm ngọt thơm lừng',
                'price' => 120000,
                'stock' => 30,
                'status' => 'available',
            ],
            [
                'name' => 'Gà Nướng Muối Ớt',
                'category' => 'Món Nướng',
                'description' => 'Gà ta nướng muối ớt thơm phức, da giòn thịt mềm',
                'price' => 150000,
                'stock' => 25,
                'status' => 'available',
            ],

            // Đồ Uống
            [
                'name' => 'Trà Đá',
                'category' => 'Đồ Uống',
                'description' => 'Trà đá truyền thống, giải khát tuyệt vời',
                'price' => 5000,
                'stock' => 100,
                'status' => 'available',
            ],
            [
                'name' => 'Cà Phê Sữa Đá',
                'category' => 'Đồ Uống',
                'description' => 'Cà phê phin truyền thống pha với sữa đặc, đá lạnh',
                'price' => 25000,
                'stock' => 80,
                'status' => 'available',
            ],
            [
                'name' => 'Nước Chanh Dây',
                'category' => 'Đồ Uống',
                'description' => 'Nước chanh dây tươi mát, chua ngọt vừa phải',
                'price' => 20000,
                'stock' => 70,
                'status' => 'available',
            ],
            [
                'name' => 'Sinh Tố Bơ',
                'category' => 'Đồ Uống',
                'description' => 'Sinh tố bơ béo ngậy, thơm mát từ bơ tươi',
                'price' => 30000,
                'stock' => 50,
                'status' => 'available',
            ],

            // Tráng Miệng
            [
                'name' => 'Chè Ba Màu',
                'category' => 'Tráng Miệng',
                'description' => 'Chè ba màu đậu đỏ, đậu xanh và thạch dai ngon',
                'price' => 18000,
                'stock' => 60,
                'status' => 'available',
            ],
            [
                'name' => 'Bánh Flan Caramen',
                'category' => 'Tráng Miệng',
                'description' => 'Bánh flan mềm mịn với lớp caramen đắng nhẹ',
                'price' => 15000,
                'stock' => 45,
                'status' => 'available',
            ],
        ];

        foreach ($products as $productData) {
            $category = Category::where('name', $productData['category'])->first();
            
            if ($category) {
                Product::create([
                    'name' => $productData['name'],
                    'category_id' => $category->id,
                    'description' => $productData['description'],
                    'price' => $productData['price'],
                    'stock' => $productData['stock'],
                    'status' => $productData['status'],
                    'image' => null, // Bạn có thể thêm ảnh sau
                ]);
            }
        }
    }
}