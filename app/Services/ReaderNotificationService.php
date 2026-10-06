<?php

namespace App\Services;

use App\Models\ReaderNotification;
use App\Models\ReaderNotificationPreference;
use App\Models\User;
use Illuminate\Support\Facades\Lang;

class ReaderNotificationService
{
    public function __construct(private readonly NotificationDeliveryService $delivery)
    {
    }

    public function send(User $user, string $type, string $title, string $body, array $data = []): ?ReaderNotification
    {
        $inAppEnabled = ReaderNotificationPreference::query()
            ->where('user_id', $user->id)
            ->where('type', $type)
            ->value('in_app_enabled');

        $notification = null;
        if ($inAppEnabled !== false && $inAppEnabled !== 0 && $inAppEnabled !== '0') {
            $notification = ReaderNotification::create([
                'user_id' => $user->id,
                'type' => $type,
                'title' => $title,
                'body' => $body,
                'data' => $data ?: null,
            ]);
        }

        $this->delivery->deliver($user, $type, $title, $body, $data, $notification?->id);

        return $notification;
    }

    public function sendTranslated(
        User $user,
        string $type,
        string $titleKey,
        string $bodyKey,
        array $replacements = [],
        array $data = [],
    ): ?ReaderNotification {
        $locale = in_array($user->locale, ['en', 'sw'], true) ? $user->locale : 'en';

        return $this->send(
            $user,
            $type,
            Lang::get($titleKey, $replacements, $locale),
            Lang::get($bodyKey, $replacements, $locale),
            $data,
        );
    }
}
