<?php

namespace App\Services;

use App\Models\PushSubscription;
use Illuminate\Support\Collection;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;

/** Thin wrapper over minishlink/web-push so the rest of the app (and tests) never touch the library. */
class PushSender
{
    public function isConfigured(): bool
    {
        return filled(config('services.webpush.public_key')) && filled(config('services.webpush.private_key'));
    }

    /**
     * Send one payload to many subscriptions.
     *
     * @param  Collection<int, PushSubscription>  $subscriptions
     * @param  array<string, mixed>  $payload
     * @return list<string> endpoints the push service says are gone and should be deleted
     */
    public function send(Collection $subscriptions, array $payload): array
    {
        if (! $this->isConfigured() || $subscriptions->isEmpty()) {
            return [];
        }

        $webPush = new WebPush(['VAPID' => [
            'subject' => config('services.webpush.subject'),
            'publicKey' => config('services.webpush.public_key'),
            'privateKey' => config('services.webpush.private_key'),
        ]]);

        foreach ($subscriptions as $subscription) {
            $webPush->queueNotification(Subscription::create([
                'endpoint' => $subscription->endpoint,
                'publicKey' => $subscription->public_key,
                'authToken' => $subscription->auth_token,
                'contentEncoding' => $subscription->content_encoding,
            ]), json_encode($payload, JSON_UNESCAPED_UNICODE), ['TTL' => 6 * 60 * 60, 'urgency' => 'normal']);
        }

        $gone = [];
        foreach ($webPush->flush() as $report) {
            if ($report->isSubscriptionExpired()) {
                $gone[] = $report->getEndpoint();
            }
        }

        return $gone;
    }
}
