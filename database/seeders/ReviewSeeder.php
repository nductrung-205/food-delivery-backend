<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Review;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        Review::create([
            'user_id' => 1,
            'product_id' => 1,
            'rating' => 5,
            'comment' => 'Rất ngon, sẽ ủng hộ tiếp!',
        ]);
    }
}
