<?php

namespace Tests\Unit;

use App\Models\Book;
use App\Models\Genre;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_book_belongs_to_many_genres()
    {
        $book = Book::factory()->create();
        $genre1 = Genre::factory()->create();
        $genre2 = Genre::factory()->create();

        $book->genres()->attach([$genre1->id, $genre2->id]);

        $this->assertCount(2, $book->genres);
        $this->assertTrue($book->genres->contains($genre1));
        $this->assertTrue($book->genres->contains($genre2));
    }

    public function test_book_belongs_to_creator()
    {
        $user = User::factory()->create();
        $book = Book::factory()->create([
            'created_by' => $user->id,
        ]);

        $this->assertInstanceOf(User::class, $book->creator);
        $this->assertEquals($user->id, $book->creator->id);
    }

    public function test_book_has_many_reviews()
    {
        $book = Book::factory()->create();

        Review::factory()->count(3)->create([
            'book_id' => $book->id,
        ]);

        $this->assertCount(3, $book->reviews);
    }

    // public function test_book_belongs_to_many_genres()
    // {
    //     $book = Book::factory()->create();
    //     $genre1 = Genre::factory()->create();
    //     $genre2 = Genre::factory()->create();

    //     $book->genres()->attach([$genre1->id, $genre2->id]);

    //     $this->assertCount(2, $book->genres);
    //     $this->assertTrue($book->genres->contains($genre1));
    //     $this->assertTrue($book->genres->contains($genre2));
    // }

    public function test_book_belongs_to_many_favorited_users()
    {
        $book = Book::factory()->create();
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $book->favoritedUsers()->attach([$user1->id, $user2->id]);

        $this->assertCount(2, $book->favoritedUsers);
        $this->assertTrue($book->favoritedUsers->contains($user1));
        $this->assertTrue($book->favoritedUsers->contains($user2));
    }
}
