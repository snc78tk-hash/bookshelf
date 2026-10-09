<?php

namespace App\Http\Controllers;

use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(NotificationService $service): View
    {
        $notifications = $service->getUserNotifications(auth()->user());

        return view('notifications.index', compact('notifications'));
    }

    public function markAsRead(
        string $notificationId,
        NotificationService $service
    ): RedirectResponse {
        $service->markAsRead(
            auth()->user(),
            $notificationId
        );

        return redirect()
            ->route('notifications.index')
            ->with('success', '通知を既読にしました。');
    }
}
