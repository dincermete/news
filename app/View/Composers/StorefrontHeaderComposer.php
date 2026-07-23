<?php

namespace App\View\Composers;

use App\Enums\NotificationAudience;
use App\Models\Announcement;
use Illuminate\View\View;

class StorefrontHeaderComposer
{
    public function compose(View $view): void
    {
        $user = request()->user();

        $announcementsQuery = Announcement::query()
            ->currentlyVisible()
            ->orderByDesc('id')
            ->limit(5);

        if ($user === null) {
            $announcementsQuery->where('audience', NotificationAudience::All);
        }

        $headerNotifications = collect();
        $headerUnreadCount = 0;

        if ($user !== null) {
            $headerNotifications = $user->notificationsInbox()
                ->latest('id')
                ->limit(8)
                ->get();

            $headerUnreadCount = $user->notificationsInbox()
                ->whereNull('read_at')
                ->count();
        }

        $view->with([
            'headerAnnouncements' => $announcementsQuery->get(),
            'headerNotifications' => $headerNotifications,
            'headerUnreadCount' => $headerUnreadCount,
        ]);
    }
}
