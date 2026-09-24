<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\ReadingPlan;
use App\Models\User;
use App\Notifications\ReadingPlanReminderNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 認証済みユーザーは通知一覧ページにアクセスできる
     */
    public function test_authenticated_user_can_access_notifications_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('notifications.index'));

        $response->assertStatus(200);
        $response->assertSee('通知一覧');
    }

    /**
     * ゲストユーザーは通知一覧ページにアクセスできず、ログインページにリダイレクトされる
     */
    public function test_guest_user_cannot_access_notifications_page(): void
    {
        $response = $this->get(route('notifications.index'));

        $response->assertRedirect(route('login'));
    }

    /**
     * 認証済みユーザーは通知を既読にできる
     */
    public function test_authenticated_user_can_mark_notification_as_read(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);
        $notification = new ReadingPlanReminderNotification($readingPlan, 'upcoming');
        $user->notify($notification);

        $dbNotification = $user->notifications()->first();

        $response = $this->actingAs($user)->post(route('notifications.read', $dbNotification->id));

        $response->assertRedirect(route('notifications.index'));
        $dbNotification->refresh();
        $this->assertNotNull($dbNotification->read_at);
    }

    /**
     * 他人の通知は既読にできない
     */
    public function test_user_cannot_mark_others_notification_as_read(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $book = Book::factory()->create();
        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);
        $notification = new ReadingPlanReminderNotification($readingPlan, 'upcoming');
        $user->notify($notification);

        $dbNotification = $user->notifications()->first();

        $response = $this->actingAs($otherUser)->post(route('notifications.read', $dbNotification->id));

        $response->assertNotFound();
        $this->assertNull($dbNotification->refresh()->read_at);
    }
}
