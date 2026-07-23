<?php

namespace App\Observers;

use App\Models\UserNotification;

class UserNotificationObserver
{
    public function updating(UserNotification $notification): bool
    {
        return array_keys($notification->getDirty()) === ['read_at'];
    }

    public function deleting(UserNotification $notification): bool
    {
        return false;
    }
}
