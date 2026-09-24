<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\User;
use Illuminate\Database\Seeder;

class FavoriteSeeder extends Seeder
{
    public function run(): void
    {
        $favoriteMap = [
            '山田太郎' => [
                '吾輩は猫である',
                'リーダブルコード',
                '嫌われる勇気',
                'Clean Code',
            ],
            '鈴木花子' => [
                '吾輩は猫である',
                '7つの習慣',
                'サピエンス全史',
                '坊っちゃん',
                '火花',
            ],
            '田中一郎' => [
                '人を動かす',
                '嫌われる勇気',
                'サピエンス全史',
                'コンテナ物語',
            ],
            '佐藤美咲' => [
                'リーダブルコード',
                '7つの習慣',
                'FACTFULNESS',
            ],
            '高橋健太' => [
                'リーダブルコード',
                '坊っちゃん',
                'サピエンス全史',
                'コンテナ物語',
            ],
        ];

        foreach ($favoriteMap as $userName => $bookTitles) {
            $user = User::where('name', $userName)->first();
            $bookIds = Book::whereIn('title', $bookTitles)->pluck('id')->toArray();

            $user->favoriteBooks()->syncWithoutDetaching($bookIds);
        }
    }
}
