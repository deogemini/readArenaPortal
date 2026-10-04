<?php

namespace App\Services;

use App\Models\ReaderNotification;
use App\Models\ReaderNotificationPreference;
use App\Models\User;

class ReaderNotificationService
{
    public function send(User $user, string $type, string $title, string $body, array $data = []): ?ReaderNotification
    {
        $enabled = ReaderNotificationPreference::query()
            ->where('user_id', $user->id)
            ->where('type', $type)
            ->value('in_app_enabled');

        if ($enabled === false || $enabled === 0 || $enabled === '0') {
            return null;
        }

        return ReaderNotification::create([
            'user_id' => $user->id,
            'type' => $type,
            'title' => $title,
            'body' => $body,
            'data' => $data ?: null,
        ]);
    }
}
