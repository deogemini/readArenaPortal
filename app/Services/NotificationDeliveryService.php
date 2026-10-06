<?php

namespace App\Services;

use App\Models\NotificationChannelSetting;
use App\Models\PushDeviceToken;
use App\Models\SmsGatewaySetting;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotificationDeliveryService
{
    public function __construct(private readonly FlexSmsGatewayService $sms)
    {
    }

    public function deliver(User $user, string $type, string $title, string $body, array $data = [], ?int $notificationId = null): void
    {
        $settings = NotificationChannelSetting::query()->latest('id')->first();

        if ($settings?->email_enabled && $user->email) {
            $this->sendEmail($settings, $user, $title, $body);
        }

        $smsSettings = SmsGatewaySetting::query()->latest('id')->first();
        if ($smsSettings?->is_enabled && $user->phone_number) {
            try {
                $result = $this->sms->send((string) $user->phone_number, $title.': '.$body);
                if (! ($result['ok'] ?? false)) {
                    Log::warning('Notification SMS delivery failed.', [
                        'user_id' => $user->id,
                        'status' => $result['status'] ?? null,
                    ]);
                }
            } catch (\Throwable $exception) {
                Log::warning('Notification SMS delivery failed.', ['user_id' => $user->id]);
            }
        }

        if ($settings?->push_enabled && $settings->firebase_service_account_json) {
            $this->sendPush($settings, $user, $type, $title, $body, $data, $notificationId);
        }
    }

    private function sendEmail(NotificationChannelSetting $settings, User $user, string $title, string $body): void
    {
        $host = $settings->smtp_host ?: config('mail.mailers.smtp.host');
        $fromAddress = $settings->mail_from_address ?: config('mail.from.address');
        if (! $host || ! $fromAddress) {
            Log::warning('Notification email delivery skipped because SMTP is incomplete.', ['user_id' => $user->id]);

            return;
        }

        $mailerName = 'read_arena_notifications';
        Config::set("mail.mailers.{$mailerName}", [
            'transport' => 'smtp',
            'scheme' => $settings->smtp_encryption === 'ssl' ? 'smtps' : 'smtp',
            'host' => $host,
            'port' => $settings->smtp_port ?: config('mail.mailers.smtp.port', 587),
            'username' => $settings->smtp_username ?: config('mail.mailers.smtp.username'),
            'password' => $settings->smtp_password ?: config('mail.mailers.smtp.password'),
            'timeout' => 12,
            'local_domain' => parse_url((string) config('app.url'), PHP_URL_HOST),
        ]);

        try {
            Mail::purge($mailerName);
            Mail::mailer($mailerName)->raw($body, function ($message) use ($user, $title, $settings, $fromAddress): void {
                $message->to($user->email, $user->name)
                    ->subject($title)
                    ->from($fromAddress, $settings->mail_from_name ?: config('mail.from.name'));
            });
        } catch (\Throwable $exception) {
            Log::warning('Notification email delivery failed.', ['user_id' => $user->id]);
        } finally {
            Mail::purge($mailerName);
            Config::set("mail.mailers.{$mailerName}", null);
        }
    }

    private function sendPush(
        NotificationChannelSetting $settings,
        User $user,
        string $type,
        string $title,
        string $body,
        array $data,
        ?int $notificationId,
    ): void {
        $tokens = PushDeviceToken::query()->where('user_id', $user->id)->get();
        if ($tokens->isEmpty()) {
            return;
        }

        $serviceAccount = json_decode((string) $settings->firebase_service_account_json, true);
        if (! is_array($serviceAccount) || empty($serviceAccount['client_email']) || empty($serviceAccount['private_key'])) {
            Log::warning('Push notification delivery skipped because Firebase service account settings are invalid.');

            return;
        }

        $projectId = $settings->firebase_project_id ?: ($serviceAccount['project_id'] ?? null);
        if (! is_string($projectId) || $projectId === '') {
            Log::warning('Push notification delivery skipped because Firebase project ID is missing.');

            return;
        }

        try {
            $accessToken = $this->firebaseAccessToken($serviceAccount, $projectId);
        } catch (\Throwable $exception) {
            Log::warning('Push notification authorization failed.');

            return;
        }

        $dataPayload = [
            'type' => $type,
            'notification_id' => $notificationId === null ? '' : (string) $notificationId,
        ];
        foreach ($data as $key => $value) {
            if (is_scalar($value) || $value === null) {
                $dataPayload[(string) $key] = (string) $value;
            } else {
                $dataPayload[(string) $key] = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '';
            }
        }

        foreach ($tokens as $device) {
            try {
                $response = Http::withToken($accessToken)
                    ->acceptJson()
                    ->asJson()
                    ->timeout(12)
                    ->post("https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send", [
                        'message' => [
                            'token' => $device->token,
                            'notification' => ['title' => $title, 'body' => $body],
                            'data' => $dataPayload,
                            'android' => ['priority' => 'high'],
                        ],
                    ]);

                $errorCode = data_get($response->json(), 'error.details.0.errorCode');
                if ($errorCode === 'UNREGISTERED') {
                    $device->delete();
                } elseif (! $response->successful()) {
                    Log::warning('Push notification delivery failed.', [
                        'user_id' => $user->id,
                        'status' => $response->status(),
                    ]);
                } else {
                    $device->forceFill(['last_used_at' => now()])->save();
                }
            } catch (\Throwable $exception) {
                Log::warning('Push notification delivery failed.', ['user_id' => $user->id]);
            }
        }
    }

    private function firebaseAccessToken(array $serviceAccount, string $projectId): string
    {
        $cacheKey = 'read-arena-fcm-token:'.sha1($projectId.'|'.$serviceAccount['client_email'].'|'.$serviceAccount['private_key']);

        return Cache::remember($cacheKey, now()->addMinutes(50), function () use ($serviceAccount): string {
            $issuedAt = time();
            $header = $this->base64UrlEncode(json_encode(['alg' => 'RS256', 'typ' => 'JWT'], JSON_THROW_ON_ERROR));
            $claims = $this->base64UrlEncode(json_encode([
                'iss' => $serviceAccount['client_email'],
                'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
                'aud' => 'https://oauth2.googleapis.com/token',
                'iat' => $issuedAt,
                'exp' => $issuedAt + 3600,
            ], JSON_THROW_ON_ERROR));
            $unsignedToken = $header.'.'.$claims;

            if (! openssl_sign($unsignedToken, $signature, $serviceAccount['private_key'], OPENSSL_ALGO_SHA256)) {
                throw new \RuntimeException('Unable to sign Firebase authorization assertion.');
            }

            $response = Http::asForm()->acceptJson()->timeout(12)->post('https://oauth2.googleapis.com/token', [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $unsignedToken.'.'.$this->base64UrlEncode($signature),
            ]);

            $response->throw();
            $token = $response->json('access_token');
            if (! is_string($token) || $token === '') {
                throw new \RuntimeException('Firebase authorization did not return an access token.');
            }

            return $token;
        });
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}
