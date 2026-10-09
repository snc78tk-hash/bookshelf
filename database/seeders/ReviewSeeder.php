<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {

        $mainUser = User::where('name', '山田太郎')->first();
        $users = User::where('id', '!=', $mainUser->id)->get();

        $books = Book::all();
        foreach ($books as $book) {
            Review::factory()->create([
                'user_id' => $mainUser->id,
                'book_id' => $book->id,
            ]);
        }

        // 他ユーザーのレビュー
        foreach ($books as $book) {
            $users->where('id', '!=', $mainUser->id)
                ->random(rand(1, 3))
                ->each(function ($user) use ($book) {
                    Review::factory()->create([
                        'user_id' => $user->id,
                        'book_id' => $book->id,
                    ]);
                });
        }
    }
}
