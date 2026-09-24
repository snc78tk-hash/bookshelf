<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\ReadingPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReadingPlanControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 認証済みユーザーは読書計画ページにアクセスできる
     */
    public function test_authenticated_user_can_access_reading_plan_page(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get(route('reading-plans.index'));

        $response->assertStatus(200);
    }

    /**
     * ゲストユーザーは読書計画ページにアクセスできず、ログインページにリダイレクトされる
     */
    public function test_guest_user_cannot_access_reading_plan_page(): void
    {
        $response = $this->get(route('reading-plans.index'));
        $response->assertRedirect(route('login'));
    }

    /**
     * 認証済みユーザーは読書計画作成ページにアクセスできる
     */
    public function test_authenticated_user_can_access_create_reading_plan_page(): void
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user)->get(route('reading-plans.create'));

        $response->assertStatus(200);
    }

    /**
     * ゲストユーザーは読書計画作成ページにアクセスできず、ログインページにリダイレクトされる
     */
    public function test_guest_user_cannot_access_create_reading_plan_page(): void
    {
        $response = $this->get(route('reading-plans.create'));
        $response->assertRedirect(route('login'));
    }

    /**
     * 認証済みユーザーは読書計画を作成できる
     */
    public function test_authenticated_user_can_create_reading_plan(): void
    {
        // $this->withoutExceptionHandling();
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $response = $this->actingAs($user)->post(route('reading-plans.store'), [
            'book_id' => $book->id,
            'target_date' => now()->addWeek()->toDateString(),
        ]);

        $response->assertRedirect(route('reading-plans.index'));
        $this->assertDatabaseHas('reading_plans', [
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);
    }

    /**
     * バリデーションエラーが発生した場合、エラーメッセージが表示される
     */
    public function test_validation_error_on_create_reading_plan(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->from(route('reading-plans.create'))->post(route('reading-plans.store'), [
            'book_id' => null,
            'target_date' => null,
        ]);

        $response->assertRedirect(route('reading-plans.create'));
        $response->assertSessionHasErrors(['book_id', 'target_date']);

        $this->assertDatabaseMissing('reading_plans', [
            'user_id' => $user->id,
        ]);

    }

    /**
     * target_dateが過去日付の場合はエラーになる
     */
    public function test_target_date_cannot_be_in_the_past(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $response = $this->actingAs($user)->from(route('reading-plans.create'))->post(route('reading-plans.store'), [
            'book_id' => $book->id,
            'target_date' => now()->subDay()->toDateString(),
        ]);

        $response->assertRedirect(route('reading-plans.create'));
        $response->assertSessionHasErrors(['target_date']);

        $this->assertDatabaseMissing('reading_plans', [
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);
    }

    /**
     * 存在しない書籍の場合はエラーになる
     */
    public function test_book_id_must_exist(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->from(route('reading-plans.create'))->post(route('reading-plans.store'), [
            'book_id' => 9999, // 存在しない書籍ID
            'target_date' => now()->addWeek()->toDateString(),
        ]);

        $response->assertRedirect(route('reading-plans.create'));
        $response->assertSessionHasErrors(['book_id']);

        $this->assertDatabaseMissing('reading_plans', [
            'user_id' => $user->id,
            'book_id' => 9999,
        ]);
    }

    /**
     * ゲストユーザーは読書計画を作成できず、ログインページにリダイレクトされる
     */
    public function test_guest_user_cannot_create_reading_plan(): void
    {
        $book = Book::factory()->create();

        $response = $this->post(route('reading-plans.store'), [
            'book_id' => $book->id,
            'target_date' => now()->addWeek()->toDateString(),
        ]);

        $response->assertRedirect(route('login'));
        $this->assertDatabaseMissing('reading_plans', [
            'book_id' => $book->id,
        ]);
    }

    /**
     * 認証済みユーザーは読書計画の編集ページにアクセスできる
     */
    public function test_authenticated_user_can_access_edit_reading_plan_page(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        $response = $this->actingAs($user)->get(route('reading-plans.edit', $readingPlan));

        $response->assertStatus(200);
    }

    /**
     * ゲストユーザーは読書計画の編集ページにアクセスできず、ログインページにリダイレクトされる
     */
    public function test_guest_user_cannot_access_edit_reading_plan_page(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        $response = $this->get(route('reading-plans.edit', $readingPlan));

        $response->assertRedirect(route('login'));
    }

    /**
     * 認証済みユーザーは自分の読書計画を更新できる
     */
    public function test_authenticated_user_can_update_own_reading_plan(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        $response = $this->actingAs($user)->put(route('reading-plans.update', $readingPlan), [
            'target_date' => now()->addWeek()->toDateString(),
        ]);

        $response->assertRedirect(route('reading-plans.index'));
        $readingPlan->refresh();

        $this->assertEquals(
            now()->addWeek()->toDateString(),
            $readingPlan->target_date->toDateString()
        );
    }

    /**
     * バリデーションエラーが発生した場合、エラーが返り、読書計画は更新されない
     */
    public function test_validation_error_on_update_reading_plan(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);
        $originalDate = $readingPlan->target_date->toDateString();

        $response = $this->actingAs($user)->from(route('reading-plans.edit', $readingPlan))->put(route('reading-plans.update', $readingPlan), [
            'target_date' => null,
        ]);

        $response->assertRedirect(route('reading-plans.edit', $readingPlan));
        $response->assertSessionHasErrors(['target_date']);

        $readingPlan->refresh();
        $this->assertEquals($originalDate, $readingPlan->target_date->toDateString());
    }

    /**
     * 認証済みユーザーは他人の読書計画を更新できず、403エラーが返る
     */
    public function test_other_user_cannot_update_others_reading_plan(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $book = Book::factory()->create();
        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $otherUser->id,
            'book_id' => $book->id,
        ]);

        $response = $this->actingAs($owner)->put(route('reading-plans.update', $readingPlan), [
            'target_date' => now()->addWeek()->toDateString(),
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseMissing('reading_plans', [
            'id' => $readingPlan->id,
            'target_date' => now()->addWeek()->toDateString(),
        ]);
    }

    /**
     * ゲストユーザーは読書計画を更新できず、ログインページにリダイレクトされる
     */
    public function test_guest_user_cannot_update_reading_plan(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        $response = $this->put(route('reading-plans.update', $readingPlan), [
            'target_date' => now()->addWeek()->toDateString(),
        ]);

        $response->assertRedirect(route('login'));
    }

    /**
     * 認証済みユーザーは読書計画を削除できる
     */
    public function test_authenticated_user_can_delete_reading_plan(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        $response = $this->actingAs($user)->delete(route('reading-plans.destroy', $readingPlan));

        $response->assertRedirect(route('reading-plans.index'));
        $this->assertDatabaseMissing('reading_plans', [
            'id' => $readingPlan->id,
        ]);
    }

    /**
     * 認証済みユーザーは他人の読書計画を削除できず、403エラーが返る
     */
    public function test_other_user_cannot_delete_others_reading_plan(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $book = Book::factory()->create();
        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $otherUser->id,
            'book_id' => $book->id,
        ]);

        $response = $this->actingAs($owner)->delete(route('reading-plans.destroy', $readingPlan));

        $response->assertStatus(403);
        $this->assertDatabaseHas('reading_plans', [
            'id' => $readingPlan->id,
        ]);
    }

    /**
     * ゲストユーザーは読書計画を削除できず、ログインページにリダイレクトされる
     */
    public function test_guest_user_cannot_delete_reading_plan(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        $response = $this->delete(route('reading-plans.destroy', $readingPlan));

        $response->assertRedirect(route('login'));
    }

    /**
     * 認証済みユーザーは読書計画を完了にできる
     */
    public function test_authenticated_user_can_complete_reading_plan(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'status' => 'planned',
        ]);

        $response = $this->actingAs($user)->post(route('reading-plans.complete', $readingPlan));

        $response->assertRedirect(route('reading-plans.index'));
        $readingPlan->refresh();
        $this->assertDatabaseHas('reading_plans', [
            'id' => $readingPlan->id,
            'status' => 'completed',
        ]);
        $this->assertNotNull($readingPlan->completed_at);
    }

    /**
     * 認証済みユーザーは他人の読書計画を完了にできず、403エラーが返る
     */
    public function test_other_user_cannot_complete_others_reading_plan(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $book = Book::factory()->create();
        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $otherUser->id,
            'book_id' => $book->id,
            'status' => 'planned',
        ]);

        $response = $this->actingAs($owner)->post(route('reading-plans.complete', $readingPlan));

        $response->assertForbidden();
        $readingPlan->refresh();
        $this->assertDatabaseHas('reading_plans', [
            'id' => $readingPlan->id,
            'status' => 'planned',
        ]);
        $this->assertNull($readingPlan->completed_at);
    }
}
