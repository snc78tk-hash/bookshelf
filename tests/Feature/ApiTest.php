<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * /api/v1/booksが表示される
     */
    public function test_guest_can_access_api_books(): void
    {
        $this->withoutExceptionHandling();
        $response = $this->get('/api/v1/books');
        $response->assertOk();
    }

    /**
     * /api/v1/booksでJSON形式の書籍一覧が返され、ジャンル・平均評価・レビュー数が含まれる
     */
    public function test_api_books_returns_json(): void
    {
        $this->withoutExceptionHandling();
        $response = $this->get('/api/v1/books');
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'title',
                    'author',
                    'isbn',
                    'published_at',
                    'created_at',
                    'updated_at',
                    'genres' => [
                        '*' => [
                            'name',
                        ],
                    ],
                    'average_rating',
                    'reviews_count',
                ],
            ],
        ]);
    }

    /**
     * 検索・絞り込み・ページネーションに対応していることを確認する
     */
    public function test_api_books_supports_search_filter_and_pagination(): void
    {
        $this->withoutExceptionHandling();
        $response = $this->get('/api/v1/books?search=example&genre=fiction&page=2');
        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'title',
                    'author',
                    'isbn',
                    'published_at',
                    'created_at',
                    'updated_at',
                    'genres' => [
                        '*' => [
                            'name',
                        ],
                    ],
                    'average_rating',
                    'reviews_count',
                ],
            ],
        ]);

        $response->assertJsonFragment([ // ページネーション情報の確認
            'current_page' => 2,
            'per_page' => 10,
        ]);

    }

    /**
     * 書籍詳細ページがJSON形式で返され、ジャンル・レビューが含まれる
     */
    public function test_api_book_detail_returns_json(): void
    {

        $book = Book::factory()->create();
        $response = $this->getJson("/api/v1/books/{$book->id}");
        $response->assertJsonStructure([
            'data' => [
                'title',
                'author',
                'isbn',
                'published_at',
                'created_at',
                'updated_at',
                'genres' => [
                    '*' => [
                        'name',
                    ],
                ],
                'reviews_count',
                'average_rating',
            ],
        ]);
    }

    /**
     * 存在しない書籍IDは/api/v1/books/{id}にアクセスすると404エラーになる
     */
    public function test_api_book_detail_with_nonexistent_id(): void
    {
        $response = $this->get('/api/v1/books/9999'); // 存在しない書籍ID
        $response->assertStatus(404);
    }

    /**
     * ユーザーは/api/v1/booksにPOSTリクエストを送信できる
     */
    public function test_authenticated_user_can_post_to_api_books(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();
        $response = $this->actingAs($user)->postJson('/api/v1/books', [
            'title' => 'New Book',
            'author' => 'Author Name',
            'isbn' => '1234567890123',
            'published_at' => '2023-01-01',
            'description' => 'Book description',
            'image_url' => 'https://example.com/image.jpg',
            'genres' => [$genre->id],
            'created_by' => $user->id,
        ]);
        $response->assertStatus(201);
        $this->assertDatabaseHas('books', [
            'title' => 'New Book',
            'author' => 'Author Name',
            'isbn' => '1234567890123',
            'published_at' => '2023-01-01 00:00:00',
            'description' => 'Book description',
            'image_url' => 'https://example.com/image.jpg',
            'created_by' => $user->id,
        ]);
    }

    /**
     * バリデーションエラー時は/api/v1/booksにPOSTリクエストを送信できず、エラーが返る
     */
    public function test_post_to_api_books_fails_with_validation_errors(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();
        $response = $this->actingAs($user)->postJson('/api/v1/books', [
            'title' => '',
            'author' => '',
            'isbn' => '',
            'published_at' => '',
            'created_by' => $user->id,
        ]);
        $response->assertStatus(422); // バリデーションエラー
        $response->assertJsonValidationErrors(['title', 'author', 'isbn', 'published_at', 'genres']);
        $this->assertDatabaseMissing('books', [
            'title' => '',
            'author' => '',
            'isbn' => '',
            'published_at' => '',
            'created_by' => $user->id,
        ]);
    }

    /**
     * ISBNが重複している場合は/api/v1/booksにPOSTリクエストを送信できず、エラーが返る
     */
    public function test_post_to_api_books_fails_with_duplicate_isbn(): void
    {
        $existingBook = Book::factory()->create(['isbn' => '1234567890123']);
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/v1/books', [
            'title' => 'New Book',
            'author' => 'Author Name',
            'isbn' => '1234567890123', // 重複するISBN
            'published_at' => '2023-01-01',
            'genres' => [$genre->id],
            'created_by' => $user->id,
        ]);

        $response->assertStatus(422); // バリデーションエラー
        $response->assertJsonValidationErrors(['isbn']);
    }

    /**
     * 存在しないユーザーIDは/api/v1/booksにPOSTリクエストを送信するとエラーになる
     */
    public function test_post_to_api_books_with_nonexistent_user_id_fails(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();
        $response = $this->postJson('/api/v1/books', [
            'title' => 'New Book',
            'author' => 'Author Name',
            'isbn' => '1234567890123',
            'published_at' => '2023-01-01',
            'genres' => [$genre->id],
            'created_by' => 9999, // 存在しないユーザーID
        ]);
        $response->assertStatus(401);
    }

    /**
     * 書籍データ作成者はPUTリクエストで書籍情報を更新できる
     */
    public function test_book_creator_can_update_book(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['created_by' => $user->id]);
        $genre = Genre::factory()->create();

        $response = $this->actingAs($user)->putJson("/api/v1/books/{$book->id}", [
            'title' => 'Updated Title',
            'author' => 'Updated Author',
            'isbn' => '1234567890123',
            'published_at' => '2023-02-01',
            'genres' => [$genre->id],
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => 'Updated Title',
            'author' => 'Updated Author',
            'isbn' => '1234567890123',
            'published_at' => '2023-02-01 00:00:00',
        ]);
    }

    /**
     * バリデーションエラー時はPUTリクエストで書籍情報を更新できず、422エラーが返る
     */
    public function test_book_update_fails_with_validation_errors(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['created_by' => $user->id]);
        $genre = Genre::factory()->create();

        $response = $this->actingAs($user)->putJson("/api/v1/books/{$book->id}", [
            'title' => '',
            'author' => '',
            'isbn' => '',
            'published_at' => '',
            'genres' => [],
            'created_by' => $user->id,
        ]);

        $response->assertStatus(422); // バリデーションエラー
        $response->assertJsonValidationErrors(['title', 'author', 'isbn', 'published_at', 'genres']);
    }

    /**
     * ISBNが重複している場合はPUTリクエストで書籍情報を更新できず、422エラーが返る。ただし、更新対象の書籍自身のISBNは重複とみなさない
     */
    public function test_book_update_fails_with_duplicate_isbn(): void
    {
        $user = User::factory()->create();
        $book1 = Book::factory()->create(['created_by' => $user->id, 'isbn' => '1234567890123']);
        $book2 = Book::factory()->create(['created_by' => $user->id, 'isbn' => '0987654321098']);
        $genre = Genre::factory()->create();

        $response = $this->actingAs($user)->putJson("/api/v1/books/{$book2->id}", [
            'title' => 'Updated Title',
            'author' => 'Updated Author',
            'isbn' => '1234567890123', // book1と重複するISBN
            'published_at' => '2023-02-01',
            'genres' => [$genre->id],
            'created_by' => $user->id,
        ]);

        $response->assertStatus(422); // バリデーションエラー
        $response->assertJsonValidationErrors(['isbn']);
    }

    /**
     * 書籍データ作成者以外はPUTリクエストで書籍情報を更新できず403エラーが返る
     */
    public function test_non_creator_cannot_update_book(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $book = Book::factory()->create(['created_by' => $otherUser->id]); // 別のユーザーが作成した書籍
        $genre = Genre::factory()->create();

        $response = $this->actingAs($user)->putJson("/api/v1/books/{$book->id}", [
            'title' => 'Updated Title',
            'author' => 'Updated Author',
            'isbn' => '1234567890123',
            'published_at' => '2023-02-01',
            'genres' => [$genre->id],
            'created_by' => $user->id,
        ]);

        $response->assertStatus(403); // 権限エラー
        $this->assertDatabaseMissing('books', [
            'id' => $book->id,
            'title' => 'Updated Title',
            'author' => 'Updated Author',
            'isbn' => '1234567890123',
            'published_at' => '2023-02-01',
            'created_by' => $user->id,
        ]);
    }

    /**
     * 書籍データ作成者はDELETEリクエストで書籍情報を削除できて、関連するレビューとお気に入りも削除される
     */
    public function test_book_creator_can_delete_book(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['created_by' => $user->id]);
        $genre = Genre::factory()->create();
        $book->genres()->attach($genre->id);

        $response = $this->actingAs($user)->deleteJson("/api/v1/books/{$book->id}");

        $response->assertStatus(204); // No Content
        $this->assertDatabaseMissing('books', [
            'id' => $book->id,
        ]);
        $this->assertDatabaseMissing('reviews', [
            'book_id' => $book->id,
        ]);
        $this->assertDatabaseMissing('favorites', [
            'book_id' => $book->id,
        ]);
    }

    /**
     * 存在しない書籍IDはDELETEリクエストで書籍情報を削除できず404エラーが返る
     */
    public function test_delete_book_with_nonexistent_id(): void
    {
        $user = User::factory()->create();
        $nonExistentBookId = 9999; // 存在しない書籍ID

        $response = $this->actingAs($user)->deleteJson("/api/v1/books/{$nonExistentBookId}");

        $response->assertStatus(404); // Not Found
    }

    /**
     * 書籍データ作成者以外はDELETEリクエストで書籍情報を削除できず403エラーが返る
     */
    public function test_non_creator_cannot_delete_book(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $book = Book::factory()->create(['created_by' => $otherUser->id]); // 別のユーザーが作成した書籍
        $genre = Genre::factory()->create();
        $book->genres()->attach($genre->id);

        $response = $this->actingAs($user)->deleteJson("/api/v1/books/{$book->id}");

        $response->assertStatus(403); // Forbidden
        $this->assertDatabaseHas('books', [
            'id' => $book->id,
        ]);
    }
}
