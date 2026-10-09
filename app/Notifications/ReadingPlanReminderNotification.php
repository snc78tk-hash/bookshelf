<?php

namespace App\Notifications;

use App\Models\ReadingPlan;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ReadingPlanReminderNotification extends Notification
{
    use Queueable;

    public function __construct(
        private ReadingPlan $plan,
        private string $timing,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'title' => '読書計画のお知らせ',

            'body' => match ($this->timing) {
                'three_days_before' => "『{$this->plan->book->title}』の読了予定日まであと3日です。",
                'on_due_date' => "『{$this->plan->book->title}』の読了予定日が今日です。",
                'three_days_after' => "『{$this->plan->book->title}』の読了予定日を3日過ぎています。",
                default => "『{$this->plan->book->title}』の読書計画をご確認ください。",
            },

            'timing' => $this->timing,

            'reading_plan_id' => $this->plan->id,
        ];
    }
}
