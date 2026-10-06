<?php

namespace App\Services;

use App\Models\SmsGatewaySetting;
use Illuminate\Support\Facades\Http;

class FlexSmsGatewayService
{
    public function send(string $recipient, string $contents, ?string $schedule = null, string $scheduleType = 'once'): array
    {
        $settings = SmsGatewaySetting::query()->latest('id')->first();

        if (! $settings?->is_enabled || blank($settings->base_url) || blank($settings->client_id) || blank($settings->client_secret)) {
            return ['ok' => false, 'status' => 0, 'body' => ['message' => 'SMS gateway is disabled or incomplete.']];
        }

        $baseUrl = rtrim((string) $settings->base_url, '/');
        $clientId = (string) $settings->client_id;
        $clientSecret = (string) $settings->client_secret;
        $senderId = (string) $settings->sender_id;

        $payload = [
            'senderId' => $senderId,
            'recipient' => $recipient,
            'contents' => $contents,
            'schedule' => $schedule,
            'schedule_type' => $scheduleType,
        ];

        $response = Http::acceptJson()
            ->asJson()
            ->withHeaders([
                'X-Client-Id' => $clientId,
                'X-Client-Secret' => $clientSecret,
            ])
            ->post($baseUrl.'/api/sms/send', $payload);

        return [
            'ok' => $response->successful(),
            'status' => $response->status(),
            'body' => $response->json() ?? ['raw' => $response->body()],
        ];
    }
}
