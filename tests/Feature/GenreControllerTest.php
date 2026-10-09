<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Genre;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenreControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 認証済みユーザーはジャンル一覧ページを表示できる
     */
    public function test_authenticated_user_can_access_genre_index_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('genres.index'));

        $response->assertOk();
    }

    /**
     * 未認証ユーザーはジャンル一覧ページを表示できず、ログインページにリダイレクトされる
     */
    public function test_unauthenticated_user_cannot_access_genre_index_page(): void
    {
        $response = $this->get(route('genres.index'));

        $response->assertRedirect(route('login'));
    }

    /**
     * 認証済みユーザーはジャンル詳細ページを表示できる
     */
    public function test_authenticated_user_can_access_genre_show_page(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $response = $this->actingAs($user)->get(route('genres.show', $genre));

        $response->assertOk();
        $response->assertSee($genre->name);
    }

    /**
     * 未認証ユーザーはジャンル詳細ページを表示できず、ログインページにリダイレクトされる
     */
    public function test_unauthenticated_user_cannot_access_genre_show_page(): void
    {
        $genre = Genre::factory()->create();

        $response = $this->get(route('genres.show', $genre));

        $response->assertRedirect(route('login'));
    }

    /**
     * 認証済みユーザーはジャンル登録ページを表示できる
     */
    public function test_authenticated_user_can_access_genre_create_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('genres.create'));

        $response->assertOk();
    }

    /**
     * 未認証ユーザーはジャンル登録ページを表示できず、ログインページにリダイレクトされる
     */
    public function test_unauthenticated_user_cannot_access_genre_create_page(): void
    {
        $response = $this->get(route('genres.create'));

        $response->assertRedirect(route('login'));
    }

    /**
     * 認証済みユーザーはジャンルを登録できる
     */
    public function test_authenticated_user_can_store_genre(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->post(route('genres.store'), [
            'name' => '新しいジャンル',
        ]);

        $response->assertRedirect(route('genres.index'));
        $this->assertDatabaseHas('genres', [
            'name' => '新しいジャンル',
        ]);
    }

    /**
     * バリデーションエラーが発生する場合、ジャンル登録ページにリダイレクトされ、エラーメッセージが表示される
     */
    public function test_genre_store_validation_error(): void
    {
        $user = User::factory()->create();
        $response = $this
            ->actingAs($user)
            ->from(route('genres.create'))
            ->post(route('genres.store'), [
                'name' => '',
            ]);

        $response->assertSessionHasErrors('name');
        $response->assertRedirect(route('genres.create'));
        $this->assertDatabaseMissing('genres', [
            'name' => '',
        ]);
    }

    /**
     * 未認証ユーザーはジャンルを登録できず、ログインページにリダイレクトされる
     */
    public function test_unauthenticated_user_cannot_store_genre(): void
    {
        $response = $this->post(route('genres.store'), [
            'name' => '新しいジャンル',
        ]);

        $response->assertRedirect(route('login'));
        $this->assertDatabaseMissing('genres', [
            'name' => '新しいジャンル',
        ]);
    }

    /**
     * 認証済みユーザーはジャンルを更新できる
     */
    public function test_authenticated_user_can_update_genre(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from(route('genres.edit', $genre))
            ->put(route('genres.update', $genre), [
                'name' => '更新されたジャンル',
            ]);

        $response->assertRedirect(route('genres.index'));
        $this->assertDatabaseHas('genres', [
            'id' => $genre->id,
            'name' => '更新されたジャンル',
        ]);
    }

    /**
     * バリデーションエラーが発生する場合、ジャンル編集ページにリダイレクトされ、エラーメッセージが表示される
     */
    public function test_genre_update_validation_error(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from(route('genres.edit', $genre))
            ->put(route('genres.update', $genre), [
                'name' => '',
            ]);

        $response->assertSessionHasErrors('name');
        $response->assertRedirect(route('genres.edit', $genre));
        $this->assertDatabaseHas('genres', [
            'id' => $genre->id,
            'name' => $genre->name,
        ]);
    }

    /**
     * 未認証ユーザーはジャンルを更新できず、ログインページにリダイレクトされる
     */
    public function test_unauthenticated_user_cannot_update_genre(): void
    {
        $genre = Genre::factory()->create();

        $response = $this->put(route('genres.update', $genre), [
            'name' => '更新されたジャンル',
        ]);

        $response->assertRedirect(route('login'));
        $this->assertDatabaseHas('genres', [
            'id' => $genre->id,
            'name' => $genre->name,
        ]);
    }

    /**
     * 認証済みユーザーはジャンルを削除できる
     */
    public function test_authenticated_user_can_delete_genre(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();

        $response = $this->actingAs($user)->delete(route('genres.destroy', $genre));

        $response->assertRedirect(route('genres.index'));
        $this->assertDatabaseMissing('genres', [
            'id' => $genre->id,
        ]);
    }

    /**
     * 未認証ユーザーはジャンルを削除できず、ログインページにリダイレクトされる
     */
    public function test_unauthenticated_user_cannot_delete_genre(): void
    {
        $genre = Genre::factory()->create();

        $response = $this->delete(route('genres.destroy', $genre));

        $response->assertRedirect(route('login'));
        $this->assertDatabaseHas('genres', [
            'id' => $genre->id,
            'name' => $genre->name,
        ]);
    }

    /**
     * 認証済みユーザーは書籍に関連付けられているジャンルを削除できず、エラーメッセージが表示される
     */
    public function test_authenticated_user_cannot_delete_genre_with_books(): void
    {
        $user = User::factory()->create();
        $genre = Genre::factory()->create();
        $book = Book::factory()->create();
        $book->genres()->attach($genre->id);

        $response = $this
            ->actingAs($user)
            ->from(route('genres.index'))
            ->delete(route('genres.destroy', $genre));

        $response->assertSessionHas('error');
        $response->assertRedirect(route('genres.index'));
        $this->assertDatabaseHas('genres', [
            'id' => $genre->id,
            'name' => $genre->name,
        ]);
    }
}
