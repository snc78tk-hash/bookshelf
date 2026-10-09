<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\ReadingPlan;
use App\Models\User;
use App\Notifications\ReadingPlanReminderNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SendReminderTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 期限が3日後のリーディングプランを持つユーザーに通知が送信される
     */
    public function test_send_reminder_command_sends_notifications(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => now()->addDays(3),
        ]);

        $this->artisan('reading-plans:send-reminders')
            ->expectsOutput('読書計画の通知を送信しました。')
            ->assertExitCode(0);
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $user->id,
            'type' => ReadingPlanReminderNotification::class,
        ]);
    }

    /**
     * 期限が3日後のリーディングプランを持たないユーザーには通知が送信されない
     */
    public function test_send_reminder_command_does_not_send_notifications_for_users_without_upcoming_plans(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => now()->addDays(10),
        ]);

        $this->artisan('reading-plans:send-reminders')
            ->expectsOutput('読書計画の通知を送信しました。')
            ->assertExitCode(0);

        $this->assertDatabaseMissing('notifications', [
            'notifiable_id' => $user->id,
            'type' => ReadingPlanReminderNotification::class,
        ]);
    }

    /**
     * 重複して通知が送信されないことを確認するテスト
     */
    public function test_send_reminder_command_does_not_send_duplicate_notifications(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => now()->addDays(3),
        ]);

        // 1回目の通知送信
        $this->artisan('reading-plans:send-reminders')
            ->expectsOutput('読書計画の通知を送信しました。')
            ->assertExitCode(0);

        // 2回目の通知送信（重複して送信されないことを確認）
        $this->artisan('reading-plans:send-reminders')
            ->expectsOutput('読書計画の通知を送信しました。')
            ->assertExitCode(0);

        // 通知が1件だけ送信されていることを確認
        $this->assertDatabaseCount('notifications', 1);
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $user->id,
            'type' => ReadingPlanReminderNotification::class,
        ]);
    }

    /**
     * 3日前・当日・3日後のリーディングプランに対して通知が送信されることを確認するテスト
     */
    public function test_send_reminder_command_sends_notifications_for_various_timings(): void
    {
        $user = User::factory()->create();
        $book1 = Book::factory()->create();
        $book2 = Book::factory()->create();
        $book3 = Book::factory()->create();

        ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book1->id,
            'target_date' => now()->addDays(3),
        ]);

        ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book2->id,
            'target_date' => now(),
        ]);

        ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book3->id,
            'target_date' => now()->subDays(3),
        ]);

        $this->artisan('reading-plans:send-reminders')
            ->expectsOutput('読書計画の通知を送信しました。')
            ->assertExitCode(0);

        $this->assertDatabaseCount('notifications', 3);
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $user->id,
            'type' => ReadingPlanReminderNotification::class,
        ]);
    }
}
