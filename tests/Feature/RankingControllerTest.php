<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RankingControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * ユーザー・ゲストは/rankingでランキングページを表示できる
     */
    public function test_ranking_index_returns_successful_response(): void
    {
        $response = $this->get('/ranking');
        $response->assertOk();
        $response->assertSee('ランキング');
    }

    /**
     * レビューがない書籍はランキングページに表示されない
     */
    public function test_books_without_reviews_are_not_displayed_in_ranking(): void
    {
        $bookWithoutReview = Book::factory()->create();

        $response = $this->get('/ranking');

        $response->assertDontSee($bookWithoutReview->title);
    }

    /**
     * レビューがある書籍はランキングページに表示される
     */
    public function test_books_with_reviews_are_displayed_in_ranking(): void
    {
        $bookWithReview = Book::factory()->create();
        $bookWithReview->reviews()->create([
            'user_id' => User::factory()->create()->id,
            'rating' => 5,
            'comment' => '素晴らしい本でした',
        ]);

        $response = $this->get('/ranking');
        $response->assertSee($bookWithReview->title);
    }

    /**
     * 平均評価が高い順に書籍を表示する
     */
    public function test_books_are_displayed_in_order_of_average_rating(): void
    {
        $book1 = Book::factory()->create();
        $book1->reviews()->createMany([
            ['user_id' => User::factory()->create()->id, 'rating' => 5, 'comment' => '素晴らしい本でした'],
            ['user_id' => User::factory()->create()->id, 'rating' => 4, 'comment' => '良い本でした'],
        ]);

        $book2 = Book::factory()->create();
        $book2->reviews()->createMany([
            ['user_id' => User::factory()->create()->id, 'rating' => 3, 'comment' => '普通の本でした'],
            ['user_id' => User::factory()->create()->id, 'rating' => 2, 'comment' => 'あまり面白くなかったです'],
        ]);

        $response = $this->get('/ranking');
        $response->assertSeeInOrder([$book1->title, $book2->title]);
    }
}
