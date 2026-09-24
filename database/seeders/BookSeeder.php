<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Database\Seeder;

class BookSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        $books = [
            [
                'title' => '吾輩は猫である',
                'author' => '夏目漱石',
                'isbn' => '9784101010014',
                'published_at' => '1905-01-01',
                'genres' => ['小説'],
                'description' => '吾輩は猫であるは、夏目漱石の代表作の一つである。',
                'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=1',
            ],
            [
                'title' => '人を動かす',
                'author' => 'D・カーネギー',
                'isbn' => '9784422100524',
                'published_at' => '1936-10-01',
                'genres' => ['ビジネス', '自己啓発'],
                'description' => '人を動かすは、人間関係を築くための基本的な原則を説明した本である。',
                'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=2',
            ],
            [
                'title' => 'リーダブルコード',
                'author' => 'Dustin Boswell',
                'isbn' => '9784873115658',
                'published_at' => '2012-06-23',
                'genres' => ['技術書'],
                'description' => 'リーダブルコードは、コードの可読性を高めるための原則と技法を説明した本である。',
                'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=3',
            ],
            [
                'title' => '7つの習慣',
                'author' => 'スティーブン・R・コヴィー',
                'isbn' => '9784863940246',
                'published_at' => '2013-08-30',
                'genres' => ['ビジネス', '自己啓発'],
                'description' => '7つの習慣は、個人の成功と幸福を築くための原則を説明した本である。',
                'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=4',
            ],
            [
                'title' => '坊っちゃん',
                'author' => '夏目漱石',
                'isbn' => '9784101010021',
                'published_at' => '1906-04-01',
                'genres' => ['小説'],
                'description' => '坊っちゃんは、夏目漱石の代表作の一つである。',
                'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=5',
            ],
            [
                'title' => 'サピエンス全史',
                'author' => 'ユヴァル・ノア・ハラリ',
                'isbn' => '9784309226712',
                'published_at' => '2016-09-08',
                'genres' => ['歴史', '科学'],
                'description' => 'サピエンス全史は、人類の歴史を俯瞰的に解説した本である。',
                'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=6',
            ],
            [
                'title' => 'Clean Code',
                'author' => 'Robert C. Martin',
                'isbn' => '9784048930598',
                'published_at' => '2017-12-18',
                'genres' => ['技術書'],
                'description' => 'Clean Codeは、ソフトウェア開発におけるコードの品質を高めるための原則と技法を説明した本である。',
                'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=7',
            ],
            [
                'title' => '嫌われる勇気',
                'author' => '岸見一郎・古賀史健',
                'isbn' => '9784478025819',
                'published_at' => '2013-12-13',
                'genres' => ['自己啓発'],
                'description' => '嫌われる勇気は、自己肯定感を高めるための原則と技法を説明した本である。',
                'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=8',
            ],
            [
                'title' => '火花',
                'author' => '又吉直樹',
                'isbn' => '9784163902302',
                'published_at' => '2015-03-11',
                'genres' => ['小説'],
                'description' => '火花は、又吉直樹の代表作の一つである。',
                'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=9',
            ],
            [
                'title' => 'FACTFULNESS',
                'author' => 'ハンス・ロスリング',
                'isbn' => '9784822289607',
                'published_at' => '2019-01-11',
                'genres' => ['ビジネス', '科学'],
                'description' => 'FACTFULNESSは、世界の現状を客観的に理解するための原則と技法を説明した本である。',
                'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=10',
            ],
            [
                'title' => 'コンテナ物語',
                'author' => 'マルク・レビンソン',
                'isbn' => '9784822251468',
                'published_at' => '2007-01-18',
                'genres' => ['ビジネス', '歴史'],
                'description' => 'コンテナ物語は、現代社会の変化を考察した本である。',
                'image_url' => 'https://placehold.co/200x300/e2e8f0/475569?text=11',
            ],
        ];

        foreach ($books as $book) {
            Book::FirstOrCreate(
                ['isbn' => $book['isbn']],
                [
                    'title' => $book['title'],
                    'author' => $book['author'],
                    'published_at' => $book['published_at'],
                    'description' => $book['description'],
                    'image_url' => $book['image_url'],
                    'created_by' => $users->random()->id,
                ]
            )->genres()->sync(
                Genre::whereIn('name', $book['genres'])->pluck('id')
            );
        }
    }
}
