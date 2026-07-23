<?php

namespace App\Observers;

use App\Enums\SiteSubmissionStatus;
use App\Models\SiteSubmission;
use App\Models\UserNotification;

class SiteSubmissionObserver
{
    public function updated(SiteSubmission $submission): void
    {
        if (! $submission->wasChanged('status')) {
            return;
        }

        $status = $submission->status;

        if ($status === SiteSubmissionStatus::Approved) {
            UserNotification::query()->create([
                'user_id' => $submission->user_id,
                'title' => 'Site başvurunuz onaylandı',
                'body' => $submission->url.' başvurusu onaylandı.'.(
                    filled($submission->admin_note) ? ' Not: '.$submission->admin_note : ''
                ),
            ]);

            return;
        }

        if ($status === SiteSubmissionStatus::Rejected) {
            UserNotification::query()->create([
                'user_id' => $submission->user_id,
                'title' => 'Site başvurunuz reddedildi',
                'body' => $submission->url.' başvurusu reddedildi.'.(
                    filled($submission->admin_note) ? ' Sebep: '.$submission->admin_note : ''
                ),
            ]);
        }
    }
}
