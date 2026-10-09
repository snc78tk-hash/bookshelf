<?php

namespace Tests\Unit;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_has_many_books()
    {
        $user = User::factory()->create();
        $book1 = Book::factory()->create(['created_by' => $user->id]);
        $book2 = Book::factory()->create(['created_by' => $user->id]);

        $this->assertCount(2, $user->books);
        $this->assertTrue($user->books->contains($book1));
        $this->assertTrue($user->books->contains($book2));
    }

    public function test_user_has_many_reviews()
    {
        $user = User::factory()->create();
        $review1 = Review::factory()->create(['user_id' => $user->id]);
        $review2 = Review::factory()->create(['user_id' => $user->id]);

        $this->assertCount(2, $user->reviews);
        $this->assertTrue($user->reviews->contains($review1));
        $this->assertTrue($user->reviews->contains($review2));
    }

    public function test_user_has_many_favorite_books()
    {
        $user = User::factory()->create();
        $book1 = Book::factory()->create();
        $book2 = Book::factory()->create();

        $user->favoriteBooks()->attach([$book1->id, $book2->id]);

        $this->assertCount(2, $user->favoriteBooks);
        $this->assertTrue($user->favoriteBooks->contains($book1));
        $this->assertTrue($user->favoriteBooks->contains($book2));
    }

    public function test_user_has_many_liked_reviews()
    {
        $user = User::factory()->create();
        $review1 = Review::factory()->create();
        $review2 = Review::factory()->create();

        $user->likedReviews()->attach([$review1->id, $review2->id]);

        $this->assertCount(2, $user->likedReviews);
        $this->assertTrue($user->likedReviews->contains($review1));
        $this->assertTrue($user->likedReviews->contains($review2));
    }
}
