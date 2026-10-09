<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 認証済みユーザーはレビューを作成できる
     */
    public function test_authenticated_user_can_create_review(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create([
            'created_by' => $user->id,
        ]);

        $response = $this
            ->actingAs($user)
            ->post(route('reviews.store', $book), [
                'rating' => 4,
                'comment' => 'とても面白かったです。',
            ]);

        $response->assertRedirect(route('books.show', $book));

        $this->assertDatabaseHas('reviews', [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 4,
            'comment' => 'とても面白かったです。',
        ]);
    }

    /**
     * バリデーションエラーが発生した場合、エラーメッセージが表示される
     */
    public function test_review_creation_validation_errors(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create([
            'created_by' => $user->id,
        ]);

        $response = $this
            ->actingAs($user)
            ->post(route('reviews.store', $book), [
                'rating' => '',
                'comment' => '',
            ]);

        $response->assertSessionHasErrors(['rating']);
    }

    /**
     * ユーザーは自分のレビューを作成できるが、同じ書籍に対して複数のレビューを作成できない
     */
    public function test_user_cannot_create_multiple_reviews_for_same_book(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create([
            'created_by' => $user->id,
        ]);

        // 最初のレビューを作成
        $review = Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        // 2回目のレビューを作成しようとする
        $response = $this
            ->actingAs($user)
            ->post(route('reviews.store', $book), [
                'rating' => 4,
                'comment' => 'とても面白かったです。',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('error', '既にレビューを投稿しています。');

        // データベースに2つ目のレビューが存在しないことを確認
        $this->assertDatabaseMissing('reviews', [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 4,
            'comment' => 'とても面白かったです。',
        ]);
    }

    /**
     * 未認証ユーザーはレビューを作成できず、ログインページにリダイレクトされる
     */
    public function test_guest_cannot_create_review(): void
    {
        $book = Book::factory()->create();

        $response = $this->post(route('reviews.store', $book), [
            'book_id' => $book->id,
            'rating' => 5,
            'comment' => 'とても面白かったです。',
        ]);

        $response->assertRedirect(route('login'));

        $this->assertDatabaseMissing('reviews', [
            'book_id' => $book->id,
            'rating' => 5,
            'comment' => 'とても面白かったです。',
        ]);
    }

    /**
     * 投稿者は自分のレビューの編集画面にアクセスできる
     */
    public function test_user_can_access_edit_review_page(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create([
            'created_by' => $user->id,
        ]);
        $review = Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 4,
            'comment' => '面白かったです。',
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('reviews.edit', $review));

        $response->assertStatus(200);
    }

    /**
     * 投稿者以外のユーザーはレビューの編集画面にアクセスできない
     */
    public function test_user_cannot_access_edit_review_page_of_others(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $book = Book::factory()->create([
            'created_by' => $user->id,
        ]);
        $review = Review::factory()->create([
            'user_id' => $otherUser->id,
            'book_id' => $book->id,
            'rating' => 4,
            'comment' => '面白かったです。',
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('reviews.edit', $review));

        $response->assertForbidden();
    }

    /**
     * 投稿者は自分のレビューを編集できて、完了後は書籍詳細ページにリダイレクトされる
     */
    public function test_user_can_edit_own_review(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create([
            'created_by' => $user->id,
        ]);
        $review = Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 4,
            'comment' => '面白かったです。',
        ]);

        $response = $this
            ->actingAs($user)
            ->put(route('reviews.update', $review), [
                'rating' => 5,
                'comment' => 'とても面白かったです。',
            ]);

        $response->assertSessionHasNoErrors();

        $response->assertRedirect(route('books.show', $book));
        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'rating' => 5,
            'comment' => 'とても面白かったです。',
        ]);
    }

    /**
     * バリデーションエラーが発生した場合、エラーメッセージが表示される
     */
    public function test_review_update_validation_errors(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create([
            'created_by' => $user->id,
        ]);
        $review = Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 4,
            'comment' => '面白かったです。',
        ]);

        $response = $this
            ->actingAs($user)
            ->put(route('reviews.update', $review), [
                'rating' => '',
                'comment' => '',
            ]);

        $response->assertSessionHasErrors(['rating']);
    }

    /**
     * 投稿者以外のユーザーはレビューを編集できず、403エラーが返る
     */
    public function test_user_cannot_edit_review_of_others(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $book = Book::factory()->create([
            'created_by' => $user->id,
        ]);
        $review = Review::factory()->create([
            'user_id' => $otherUser->id,
            'book_id' => $book->id,
            'rating' => 4,
            'comment' => '面白かったです。',
        ]);

        $response = $this
            ->actingAs($user)
            ->put(route('reviews.update', $review), [
                'rating' => 5,
                'comment' => 'とても面白かったです。',
            ]);

        $response->assertForbidden();
        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
            'rating' => 4,
            'comment' => '面白かったです。',
        ]);
    }

    /**
     * ユーザーは自分のレビューを削除できる
     */
    public function test_user_can_delete_own_review(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create([
            'created_by' => $user->id,
        ]);
        $review = Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 4,
            'comment' => '面白かったです。',
        ]);

        $response = $this
            ->actingAs($user)
            ->delete(route('reviews.destroy', $review));

        $response->assertRedirect(route('books.show', $book));

        $this->assertDatabaseMissing('reviews', [
            'id' => $review->id,
        ]);
    }

    /**
     * 関連するlikesの紐付けも削除される
     */
    public function test_related_likes_are_deleted_with_review(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create([
            'created_by' => $user->id,
        ]);
        $review = Review::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'rating' => 4,
            'comment' => '面白かったです。',
        ]);

        $liker = User::factory()->create();
        $review->likedByUsers()->attach($liker->id);

        $response = $this
            ->actingAs($user)
            ->delete(route('reviews.destroy', $review));

        $response->assertRedirect(route('books.show', $book));

        $this->assertDatabaseMissing('reviews', [
            'id' => $review->id,
        ]);
        $this->assertDatabaseMissing('likes', [
            'review_id' => $review->id,
            'user_id' => $liker->id,
        ]);
    }

    /**
     * 投稿者以外のユーザーはレビューを削除できず、403エラーが返る
     */
    public function test_user_cannot_delete_review_of_others(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $book = Book::factory()->create([
            'created_by' => $user->id,
        ]);
        $review = Review::factory()->create([
            'user_id' => $otherUser->id,
            'book_id' => $book->id,
            'rating' => 4,
            'comment' => '面白かったです。',
        ]);

        $response = $this
            ->actingAs($user)
            ->delete(route('reviews.destroy', $review));

        $response->assertForbidden();
        $this->assertDatabaseHas('reviews', [
            'id' => $review->id,
        ]);
    }
}
