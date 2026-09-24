<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\ReadingPlan;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReadingPlanSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('name', '山田太郎')->first();
        $user2 = User::where('name', '鈴木花子')->first();

        $book1 = Book::where('title', '吾輩は猫である')->first();
        $book2 = Book::where('title', '人を動かす')->first();
        $book3 = Book::where('title', 'リーダブルコード')->first();
        $book4 = Book::where('title', '7つの習慣')->first();
        $book5 = Book::where('title', '嫌われる勇気')->first();
        $book6 = Book::where('title', 'サピエンス全史')->first();
        $book7 = Book::where('title', 'コンテナ物語')->first();

        // 5日前、3日前、1日前、当日、1日後、3日後、5日後のリーディングプランを作成
        $readingPlan1 = ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $book1->id,
            'target_date' => now()->subDays(5),
        ]);
        $readingPlan2 = ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $book2->id,
            'target_date' => now()->subDays(3),
        ]);
        $readingPlan3 = ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $book3->id,
            'target_date' => now()->subDays(1),
        ]);
        $readingPlan4 = ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $book4->id,
            'target_date' => now(),

        ]);
        $readingPlan5 = ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $book5->id,
            'target_date' => now()->addDays(1),
        ]);
        $readingPlan6 = ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $book6->id,
            'target_date' => now()->addDays(3),
        ]);
        $readingPlan7 = ReadingPlan::create([
            'user_id' => $user->id,
            'book_id' => $book7->id,
            'target_date' => now()->addDays(5),
        ]);

        // 3日後のリーディングプランを持つ別のユーザーのリーディングプランを作成
        $readingPlan8 = ReadingPlan::create([
            'user_id' => $user2->id,
            'book_id' => $book1->id,
            'target_date' => now()->addDays(3),
        ]);
    }
}
