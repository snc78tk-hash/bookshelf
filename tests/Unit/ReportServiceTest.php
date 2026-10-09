<?php

namespace Tests\Unit;

use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;
use App\Models\User;
use App\Services\ReportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportServiceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * getUserStatsメソッドが正しいsummaryを返すことを確認するテスト
     */
    public function test_get_user_stats_returns_correct_summary()
    {
        $user = User::factory()->create();
        $book1 = Book::factory()->create(['created_by' => $user->id]);
        $book2 = Book::factory()->create(['created_by' => $user->id]);
        Review::factory()->create(['user_id' => $user->id, 'book_id' => $book1->id, 'rating' => 5]);
        Review::factory()->create(['user_id' => $user->id, 'book_id' => $book2->id, 'rating' => 4]);
        $genre1 = Genre::factory()->create();
        $genre2 = Genre::factory()->create();
        $book1->genres()->attach($genre1->id);
        $book2->genres()->attach($genre2->id);

        $reportService = new ReportService;
        $stats = $reportService->getUserStats($user);

        $this->assertSame(2, $stats['summary']['books_read']);
        $this->assertSame(2, $stats['summary']['total_reviews']);
        $this->assertSame(4.5, $stats['summary']['average_rating']);
    }

    /**
     * getUserStatsメソッドが正しいrating_distributionを返すことを確認するテスト
     */
    public function test_get_user_stats_returns_correct_rating_distribution()
    {
        $user = User::factory()->create();
        $book1 = Book::factory()->create(['created_by' => $user->id]);
        $book2 = Book::factory()->create(['created_by' => $user->id]);
        Review::factory()->create(['user_id' => $user->id, 'book_id' => $book1->id, 'rating' => 5]);
        Review::factory()->create(['user_id' => $user->id, 'book_id' => $book2->id, 'rating' => 4]);

        $reportService = new ReportService;
        $stats = $reportService->getUserStats($user);

        $distribution = $stats['rating_distribution'];
        $this->assertSame(1, $distribution[4]);
        $this->assertSame(1, $distribution[3]);
        $this->assertSame(0, $distribution[2]);
        $this->assertSame(0, $distribution[1]);
        $this->assertSame(0, $distribution[0]);
    }

    /**
     * getUserStatsメソッドが正しいtop_rated_booksを返すことを確認するテスト
     */
    public function test_get_user_stats_returns_correct_top_rated_books()
    {
        $user = User::factory()->create();
        $book1 = Book::factory()->create(['created_by' => $user->id]);
        $book2 = Book::factory()->create(['created_by' => $user->id]);
        Review::factory()->create(['user_id' => $user->id, 'book_id' => $book1->id, 'rating' => 5]);
        Review::factory()->create(['user_id' => $user->id, 'book_id' => $book2->id, 'rating' => 4]);

        $reportService = new ReportService;
        $stats = $reportService->getUserStats($user);

        $this->assertCount(2, $stats['top_rated_books']);
        $this->assertSame($book1->id, $stats['top_rated_books'][0]['id']);
        $this->assertSame($book2->id, $stats['top_rated_books'][1]['id']);
    }
}
