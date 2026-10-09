<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 認証済みユーザーはレポートページにアクセスできる
     */
    public function test_authenticated_user_can_access_report_page(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get('/reports');

        $response->assertStatus(200);
    }

    /**
     * ゲストユーザーはレポートページにアクセスできず、ログインページにリダイレクトされる
     */
    public function test_guest_user_cannot_access_report_page(): void
    {
        $response = $this->get('/reports');
        $response->assertRedirect('/login');
    }

    /**
     * ユーザーのレビュー数と平均評価が正しく表示される
     */
    public function test_user_review_count_is_displayed(): void
    {
        $user = User::factory()->create();
        $book1 = Book::factory()->create();
        $book2 = Book::factory()->create();
        $user->reviews()->createMany([
            ['book_id' => $book1->id, 'rating' => 5, 'comment' => 'Great book!'],
            ['book_id' => $book2->id, 'rating' => 4, 'comment' => 'Good read.'],
        ]);

        $response = $this->actingAs($user)->get('/reports');
        $response->assertStatus(200);
        $response->assertSee('2');
        $response->assertSee('4.5');
    }

    /**
     * ユーザーがレビューを投稿していない場合、レビュー数が0、平均評価が-として表示される
     */
    public function test_user_with_no_reviews_shows_zero(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get('/reports');
        $response->assertStatus(200);
        $response->assertSee('0');
        $response->assertSee('-');
    }
}
