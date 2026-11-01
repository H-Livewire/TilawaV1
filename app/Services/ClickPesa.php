<?php

namespace App\Services;

use App\Models\Donation;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Minimal ClickPesa client: token + hosted checkout link.
 * Docs: https://docs.clickpesa.com (generate-token, generate-checkout-url, checksum).
 */
class ClickPesa
{
    public function checkoutLink(Donation $donation): string
    {
        $payload = [
            'totalPrice' => (string) $donation->amount,
            'orderReference' => $donation->reference,
            'orderCurrency' => $donation->currency,
            'description' => 'Donation to Tilawa',
            'callbackUrl' => route('donations.callback'),
        ];

        if ($key = config('services.clickpesa.checksum_key')) {
            $payload['checksum'] = self::checksum($key, $payload);
        }

        $response = $this->request(fn (string $token) => Http::withHeaders(['Authorization' => $token])
            ->acceptJson()->asJson()->timeout(20)
            ->post($this->url('/third-parties/checkout-link/generate-checkout-url'), $payload));

        $link = $response->json('checkoutLink');

        if (! $response->successful() || ! is_string($link) || $link === '') {
            throw new RuntimeException('ClickPesa checkout failed ('.$response->status().'): '.($response->json('message') ?? substr($response->body(), 0, 200)), $response->status());
        }

        return $link;
    }

    /** HMAC-SHA256 over the canonical (recursively key-sorted, compact) JSON payload. */
    public static function checksum(string $key, array $payload): string
    {
        unset($payload['checksum'], $payload['checksumMethod']);

        return hash_hmac('sha256', json_encode(self::canonicalize($payload), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), $key);
    }

    public static function validChecksum(string $key, array $payload, ?string $received): bool
    {
        return is_string($received) && hash_equals(self::checksum($key, $payload), $received);
    }

    private static function canonicalize(mixed $value): mixed
    {
        if (! is_array($value)) {
            return $value;
        }

        if (array_is_list($value)) {
            return array_map([self::class, 'canonicalize'], $value);
        }

        ksort($value);

        return array_map([self::class, 'canonicalize'], $value);
    }

    private function request(callable $send)
    {
        $response = $send($this->token());

        if ($response->status() === 401) {
            Cache::forget('clickpesa.token');
            $response = $send($this->token());
        }

        return $response;
    }

    private function token(): string
    {
        return Cache::remember('clickpesa.token', now()->addMinutes(50), function () {
            $response = Http::withHeaders([
                'client-id' => (string) config('services.clickpesa.client_id'),
                'api-key' => (string) config('services.clickpesa.api_key'),
            ])->acceptJson()->timeout(15)->post($this->url('/third-parties/generate-token'));

            $token = $response->json('token');

            if (! $response->successful() || ! is_string($token) || $token === '') {
                throw new RuntimeException('ClickPesa token failed ('.$response->status().'): '.($response->json('message') ?? substr($response->body(), 0, 200)), $response->status());
            }

            return $token; // already includes the "Bearer " prefix
        });
    }

    private function url(string $path): string
    {
        return rtrim((string) config('services.clickpesa.base_url'), '/').$path;
    }
}
