<?php

namespace App\View\Composers;

use App\Enums\NotificationAudience;
use App\Models\Announcement;
use Illuminate\Support\Collection;
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

        /** @var Collection<int, Announcement> $announcements */
        $announcements = $announcementsQuery->get();

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

        $announcementItems = $announcements->map(fn (Announcement $announcement): array => [
            'id' => 'a-'.$announcement->id,
            'source_id' => $announcement->id,
            'kind' => 'announcement',
            'title' => $announcement->title,
            'body' => $announcement->body,
            'read_at' => null,
            'created_at' => $announcement->created_at?->format('d.m.Y H:i'),
            'created_ts' => $announcement->created_at?->getTimestamp() ?? 0,
        ]);

        $notificationItems = $headerNotifications->map(fn ($notification): array => [
            'id' => (string) $notification->id,
            'source_id' => $notification->id,
            'kind' => 'notification',
            'title' => $notification->title,
            'body' => $notification->body,
            'read_at' => $notification->read_at?->toIso8601String(),
            'created_at' => $notification->created_at?->format('d.m.Y H:i'),
            'created_ts' => $notification->created_at?->getTimestamp() ?? 0,
        ]);

        $headerBellItems = $notificationItems
            ->concat($announcementItems)
            ->sortByDesc('created_ts')
            ->values()
            ->map(function (array $item): array {
                unset($item['created_ts']);

                return $item;
            })
            ->all();

        $view->with([
            'headerAnnouncements' => $announcements,
            'headerNotifications' => $headerNotifications,
            'headerUnreadCount' => $headerUnreadCount,
            'headerBellItems' => $headerBellItems,
        ]);
    }
}
