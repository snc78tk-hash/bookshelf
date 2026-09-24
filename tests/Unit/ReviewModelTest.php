<?php

namespace Tests\Unit;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_review_belongs_to_user()
    {
        $user = User::factory()->create();
        $review = Review::factory()->create([
            'user_id' => $user->id,
        ]);

        $this->assertInstanceOf(User::class, $review->user);
        $this->assertEquals($user->id, $review->user->id);
    }

    public function test_review_belongs_to_book()
    {
        $book = Book::factory()->create();
        $review = Review::factory()->create([
            'book_id' => $book->id,
        ]);

        $this->assertInstanceOf(Book::class, $review->book);
        $this->assertEquals($book->id, $review->book->id);
    }

    public function test_review_has_many_likes()
    {
        $review = Review::factory()->create();
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $review->likedByUsers()->attach([$user1->id, $user2->id]);

        $this->assertCount(2, $review->likedByUsers);
        $this->assertTrue($review->likedByUsers->contains($user1));
        $this->assertTrue($review->likedByUsers->contains($user2));
    }
}
