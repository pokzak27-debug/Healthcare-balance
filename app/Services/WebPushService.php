<?php

namespace App\Services;

use App\Models\PushSubscription;
use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;

class WebPushService
{
    public function isConfigured(): bool
    {
        return ! empty(config('webpush.vapid_public')) && ! empty(config('webpush.vapid_private'));
    }

    /**
     * @param  array{endpoint: string, keys: array{p256dh: string, auth: string}}  $subscription
     */
    public function sendToSubscription(array $subscription, string $title, string $body, ?string $url = null): bool
    {
        if (! $this->isConfigured()) {
            return false;
        }

        $pushSubscription = Subscription::create([
            'endpoint' => $subscription['endpoint'],
            'keys' => [
                'p256dh' => $subscription['keys']['p256dh'],
                'auth' => $subscription['keys']['auth'],
            ],
        ]);

        $payload = json_encode([
            'title' => $title,
            'body' => $body,
            'url' => $url ?? '/dashboard',
        ]);

        try {
            $webPush = new WebPush([
                'VAPID' => [
                    'subject' => config('app.url'),
                    'publicKey' => config('webpush.vapid_public'),
                    'privateKey' => config('webpush.vapid_private'),
                ],
            ]);
            $report = $webPush->sendOneNotification($pushSubscription, $payload);
            return $report->isSuccess();
        } catch (\Throwable) {
            return false;
        }
    }

    public function sendToUser(int $userId, string $title, string $body, ?string $url = null): int
    {
        $sent = 0;
        $subs = PushSubscription::where('user_id', $userId)->get();

        foreach ($subs as $sub) {
            $arr = [
                'endpoint' => $sub->endpoint,
                'keys' => [
                    'p256dh' => $sub->public_key,
                    'auth' => $sub->auth_token,
                ],
            ];
            if ($this->sendToSubscription($arr, $title, $body, $url)) {
                $sent++;
            }
        }

        return $sent;
    }

    public function sendToAllStaff(string $title, string $body, ?string $url = null): int
    {
        $sent = 0;
        $subs = PushSubscription::whereHas('user', fn ($q) => $q->where('is_active', true))->get();

        foreach ($subs as $sub) {
            $arr = [
                'endpoint' => $sub->endpoint,
                'keys' => [
                    'p256dh' => $sub->public_key,
                    'auth' => $sub->auth_token,
                ],
            ];
            if ($this->sendToSubscription($arr, $title, $body, $url)) {
                $sent++;
            }
        }

        return $sent;
    }
}
