<?php

namespace App\Console\Commands;

use App\Services\NotificationService;
use Illuminate\Console\Command;

class SendReadingPlanReminderCommand extends Command
{
    protected $signature = 'reading-plans:send-reminders';

    protected $description = '読書計画のリマインダー通知を送信する';

    public function handle(NotificationService $service): int
    {
        $service->sendReminders();

        $this->info('読書計画の通知を送信しました。');

        return self::SUCCESS;
    }
}
