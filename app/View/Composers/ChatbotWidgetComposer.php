<?php

namespace App\View\Composers;

use App\Enums\NotificationAudience;
use App\Models\Announcement;
use App\Models\FaqEntry;
use Illuminate\View\View;

class ChatbotWidgetComposer
{
    public function compose(View $view): void
    {
        $user = request()->user();

        $faqs = FaqEntry::query()
            ->active()
            ->orderBy('id')
            ->limit(8)
            ->get(['id', 'question_topic', 'answer']);

        $announcementsQuery = Announcement::query()
            ->currentlyVisible()
            ->orderByDesc('id')
            ->limit(6);

        if ($user === null) {
            $announcementsQuery->where('audience', NotificationAudience::All);
        }

        $announcements = $announcementsQuery->get(['id', 'title', 'body', 'created_at']);

        $view->with([
            'chatbotFaqs' => $faqs,
            'chatbotAnnouncements' => $announcements,
            'chatbotUserFirstName' => $user ? explode(' ', trim($user->name))[0] : null,
        ]);
    }
}
