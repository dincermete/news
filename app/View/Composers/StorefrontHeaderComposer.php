<?php

namespace App\View\Composers;

use App\Enums\NotificationAudience;
use App\Enums\SiteStatus;
use App\Models\Announcement;
use App\Models\BlogPost;
use App\Models\Site;
use App\Models\SiteCategory;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
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
            'megaMenu' => $this->megaMenuHighlights(),
        ]);
    }

    /**
     * Precomputed, cached content for the header's full-width mega menus: popular
     * categories with real listing counts, catalogue-wide stats, and the latest blog
     * post. Cached because the header renders on every single page.
     *
     * @return array{topCategories: list<array{label: string, url: string, count: int}>, activeSiteCount: int, categoryCount: int, latestPost: null|array{title: string, excerpt: ?string, image: ?string, url: string}}
     */
    private function megaMenuHighlights(): array
    {
        return Cache::remember('header.mega_menu_highlights', now()->addMinutes(15), function (): array {
            $topCategories = SiteCategory::query()
                ->withCount(['sites' => fn ($query) => $query->where('status', SiteStatus::Active)])
                ->orderByDesc('sites_count')
                ->limit(8)
                ->get(['name', 'slug'])
                ->filter(fn (SiteCategory $category): bool => $category->sites_count > 0)
                ->map(fn (SiteCategory $category): array => [
                    'label' => $category->name,
                    'slug' => $category->slug,
                    'url' => Route::has('sites.category') ? route('sites.category', ['kategori' => $category->slug]) : '#',
                    'count' => $category->sites_count,
                ])
                ->values()
                ->all();

            $latestPost = BlogPost::query()->published()->latest('published_at')->first();

            return [
                'topCategories' => $topCategories,
                'activeSiteCount' => Site::query()->where('status', SiteStatus::Active)->count(),
                'categoryCount' => SiteCategory::query()->count(),
                'latestPost' => $latestPost ? [
                    'title' => $latestPost->title,
                    'excerpt' => $latestPost->excerpt,
                    'image' => $latestPost->featuredImageUrl(),
                    'url' => $latestPost->url(),
                ] : null,
            ];
        });
    }
}
