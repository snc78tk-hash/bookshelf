<?php

namespace App\Services;

use App\Enums\ReadingPlanStatus;
use App\Models\ReadingPlan;
use App\Models\User;
use App\Notifications\ReadingPlanReminderNotification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class NotificationService
{
    /**
     * ユーザーの通知一覧を取得
     */
    public function getUserNotifications(User $user): Collection
    {
        return $user->notifications()
            ->latest()
            ->get();
    }

    /**
     * 通知を既読にする
     */
    public function markAsRead(User $user, string $notificationId): void
    {
        $notification = $user
            ->notifications()
            ->findOrFail($notificationId);

        $notification->markAsRead();
    }

    /**
     * リマインド通知を送信する
     */
    public function sendReminders(): void
    {
        $this->sendReminderFor('three_days_before');
        $this->sendReminderFor('on_due_date');
        $this->sendReminderFor('three_days_after');
    }

    private function sendReminderFor(string $timing): void
    {
        $dueDate = $this->targetDateFor($timing);

        $readingPlans = ReadingPlan::query()
            ->with('user', 'book')
            ->where('status', ReadingPlanStatus::Planned)
            ->whereDate('target_date', $dueDate)
            ->get();
        $readingPlans->each(function ($plan) use ($timing) {
            if ($this->alreadySent($plan, $timing)) {
                return;
            }
            $plan->user->notify(
                new ReadingPlanReminderNotification($plan, $timing)
            );
        });

    }

    private function targetDateFor(string $timing): Carbon
    {
        return match ($timing) {
            'three_days_before' => now()->addDays(3),
            'on_due_date' => now(),
            'three_days_after' => now()->subDays(3),
        };
    }

    private function alreadySent(ReadingPlan $plan, string $timing): bool
    {
        return $plan->user->notifications()
            ->where('type', ReadingPlanReminderNotification::class)
            ->where('data->reading_plan_id', $plan->id)
            ->where('data->timing', $timing)
            ->exists();
    }
}
