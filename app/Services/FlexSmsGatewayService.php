<?php

namespace App\Services;

use App\Models\SmsGatewaySetting;
use Illuminate\Support\Facades\Http;

class FlexSmsGatewayService
{
    public function send(string $recipient, string $contents, ?string $schedule = null, string $scheduleType = 'once'): array
    {
        $settings = SmsGatewaySetting::query()->latest('id')->first();

        $baseUrl = rtrim((string) ($settings?->base_url ?: config('services.flex_sms.base_url')), '/');
        $clientId = (string) ($settings?->client_id ?: config('services.flex_sms.client_id'));
        $clientSecret = (string) ($settings?->client_secret ?: config('services.flex_sms.client_secret'));
        $senderId = (string) ($settings?->sender_id ?: config('services.flex_sms.sender_id', 'FLEX'));

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
