<?php

namespace Tests\Feature;

use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LikeControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 認証済みユーザーはレビューに対していいねを追加できる
     */
    public function test_authenticated_user_can_like_review(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $review = Review::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        $response = $this
            ->actingAs($user)
            ->from(route('books.show', $review->book_id))
            ->post(route('reviews.like', $review));

        $response->assertRedirect(route('books.show', $review->book_id));

        $this->assertDatabaseHas('likes', [
            'user_id' => $user->id,
            'review_id' => $review->id,
        ]);
    }

    /**
     * 認証済みユーザーはレビューに対していいねを解除できる
     */
    public function test_authenticated_user_can_unlike_review(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $review = Review::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        $user->likedReviews()->attach($review->id);

        $response = $this
            ->actingAs($user)
            ->from(route('books.show', $review->book_id))
            ->post(route('reviews.like', $review));

        $response->assertRedirect(route('books.show', $review->book_id));

        $this->assertDatabaseMissing('likes', [
            'user_id' => $user->id,
            'review_id' => $review->id,
        ]);
    }

    /**
     * 未認証ユーザーはレビューに対していいねを追加できず、ログインページにリダイレクトされる
     */
    public function test_guest_cannot_like_review(): void
    {
        $review = Review::factory()->create();

        $response = $this->post(route('reviews.like', $review));

        $response->assertRedirect(route('login'));

        $this->assertDatabaseMissing('likes', [
            'review_id' => $review->id,
        ]);
    }

    /**
     * 連続でいいねを追加→削除→追加しても正しく反映される
     */
    public function test_like_toggle_multiple_times(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $review = Review::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        // 1回目のいいね
        $response = $this->actingAs($user)
            ->from(route('books.show', $review->book_id))
            ->post(route('reviews.like', $review));

        $response->assertRedirect(route('books.show', $review->book_id));

        $this->assertDatabaseHas('likes', [
            'user_id' => $user->id,
            'review_id' => $review->id,
        ]);

        // 2回目のいいね（削除）
        $response = $this->actingAs($user)
            ->from(route('books.show', $review->book_id))
            ->post(route('reviews.like', $review));

        $response->assertRedirect(route('books.show', $review->book_id));
        $this->assertDatabaseMissing('likes', [
            'user_id' => $user->id,
            'review_id' => $review->id,
        ]);

        // 3回目のいいね（再追加）
        $response = $this->actingAs($user)
            ->from(route('books.show', $review->book_id))
            ->post(route('reviews.like', $review));

        $response->assertRedirect(route('books.show', $review->book_id));
        $this->assertDatabaseHas('likes', [
            'user_id' => $user->id,
            'review_id' => $review->id,
        ]);
    }
}
