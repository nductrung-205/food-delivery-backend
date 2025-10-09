<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Comment;

class CommentSeeder extends Seeder
{
    public function run(): void
    {
        Comment::create([
            'user_id' => 1,
            'product_id' => 1,
            'content' => 'Pizza ngon quá!',
        ]);
    }
}
