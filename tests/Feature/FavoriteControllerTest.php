<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FavoriteControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 認証済みユーザーはお気に入りを追加できる
     */
    public function test_authenticated_user_can_add_favorite(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create([
            'created_by' => $user->id,
        ]);

        $response = $this
            ->actingAs($user)
            ->from(route('books.show', $book))
            ->post(route('favorites.toggle', $book));

        $response->assertRedirect(route('books.show', $book));

        $this->assertDatabaseHas('favorites', [
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);
    }

    /**
     * 認証済みユーザーはお気に入りを削除できる
     */
    public function test_authenticated_user_can_remove_favorite(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create([
            'created_by' => $user->id,
        ]);

        $user->favoriteBooks()->attach($book->id);

        $response = $this
            ->actingAs($user)
            ->from(route('books.show', $book))
            ->post(route('favorites.toggle', $book));

        $response->assertRedirect(route('books.show', $book));

        $this->assertDatabaseMissing('favorites', [
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);
    }

    /**
     * ゲストユーザーはお気に入りを追加しようとするとログインページにリダイレクトされる
     */
    public function test_guest_user_cannot_add_favorite(): void
    {
        $book = Book::factory()->create();

        $response = $this->post(route('favorites.toggle', $book));

        $response->assertRedirect(route('login'));

        $this->assertDatabaseMissing('favorites', [
            'book_id' => $book->id,
        ]);
    }

    /**
     * 認証済みユーザーはお気に入り一覧ページにアクセスできる
     */
    public function test_authenticated_user_can_access_favorites_index(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create([
            'created_by' => $user->id,
        ]);

        $user->favoriteBooks()->attach($book->id);

        $response = $this
            ->actingAs($user)
            ->get(route('favorites.index'));

        $response->assertStatus(200);
        $response->assertSee($book->title);
    }

    /**
     * ゲストユーザーはお気に入り一覧ページにアクセスできず、ログインページにリダイレクトされる
     */
    public function test_guest_user_cannot_access_favorites_index(): void
    {
        $response = $this->get(route('favorites.index'));

        $response->assertRedirect(route('login'));
    }

    /**
     * 連続でお気に入りを追加→削除→追加しても正しく反映される
     */
    public function test_toggle_favorite_multiple_times(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create([
            'created_by' => $user->id,
        ]);

        $response = $this
            ->actingAs($user)
            ->from(route('books.show', $book))
            ->post(route('favorites.toggle', $book));

        $response->assertRedirect(route('books.show', $book));

        $this->assertDatabaseHas('favorites', [
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        $response = $this
            ->actingAs($user)
            ->from(route('books.show', $book))
            ->post(route('favorites.toggle', $book));

        $response->assertRedirect(route('books.show', $book));

        $this->assertDatabaseMissing('favorites', [
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        $response = $this
            ->actingAs($user)
            ->from(route('books.show', $book))
            ->post(route('favorites.toggle', $book));

        $response->assertRedirect(route('books.show', $book));

        $this->assertDatabaseHas('favorites', [
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);
    }
}
