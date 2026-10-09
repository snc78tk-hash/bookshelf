<?php

namespace Tests\Unit;

use App\Models\Book;
use App\Models\ReadingPlan;
use App\Models\User;
use App\Notifications\ReadingPlanReminderNotification;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationServiceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 3日後のリーディングプランを持つユーザーに通知が作成されることを確認
     */
    public function test_send_reminders_creates_notifications_for_users_with_upcoming_plans(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        $readingPlan = ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => now()->addDays(3),
        ]);

        $notificationService = new NotificationService;
        $notificationService->sendReminders();

        // 通知が作成されたことを確認
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $user->id,
            'type' => ReadingPlanReminderNotification::class,
        ]);
    }

    /**
     * 重複して通知が作成されないことを確認
     */
    public function test_send_reminders_does_not_create_duplicate_notifications(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        ReadingPlan::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'target_date' => now()->addDays(3),
        ]);

        $service = new NotificationService;

        $service->sendReminders();
        $service->sendReminders();

        $this->assertDatabaseCount('notifications', 1);
    }
}
