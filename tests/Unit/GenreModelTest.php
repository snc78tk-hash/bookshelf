<?php

namespace Tests\Unit;

use App\Models\Book;
use App\Models\Genre;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenreModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_genre_has_many_books()
    {
        $genre = Genre::factory()->create();
        $book1 = Book::factory()->create();
        $book2 = Book::factory()->create();

        $genre->books()->attach([$book1->id, $book2->id]);

        $this->assertCount(2, $genre->books);
        $this->assertTrue($genre->books->contains($book1));
        $this->assertTrue($genre->books->contains($book2));
    }
}
