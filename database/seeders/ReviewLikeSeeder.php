<?php

namespace Database\Seeders;

use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReviewLikeSeeder extends Seeder
{
    public function run(): void
    {
        foreach (Review::all() as $review) {
            $likerIds = User::where('id', '!=', $review->user_id)->inRandomOrder()->take(rand(1, 3))->pluck('id');
            $review->likedByUsers()->syncWithoutDetaching($likerIds);
        }
    }
}
