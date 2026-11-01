<?php

namespace App\Http\Controllers;

use App\Models\Donation;
use App\Services\ClickPesa;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DonationCallbackController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $payload = $request->all();

        if ($key = config('services.clickpesa.checksum_key')) {
            $received = $payload['checksum'] ?? $request->header('X-ClickPesa-Signature');

            if (! ClickPesa::validChecksum($key, $payload, is_string($received) ? $received : null)) {
                return response()->json(['message' => 'Invalid signature'], 403);
            }
        }

        $data = is_array($payload['data'] ?? null) ? $payload['data'] : $payload;
        $donation = Donation::where('reference', (string) ($data['orderReference'] ?? ''))->first();

        if (! $donation) {
            return response()->json(['message' => 'Unknown order'], 404);
        }

        // Never downgrade a donation that is already confirmed.
        if ($donation->status !== 'paid') {
            $status = match (strtoupper((string) ($data['status'] ?? ''))) {
                'SUCCESS', 'SETTLED', 'COMPLETED' => 'paid',
                'FAILED' => 'failed',
                'CANCELED', 'CANCELLED' => 'canceled',
                default => 'pending',
            };

            $donation->forceFill([
                'status' => $status,
                'payment_reference' => $data['paymentReference'] ?? $donation->payment_reference,
                'paid_at' => $status === 'paid' ? now() : null,
            ])->save();
        }

        return response()->json(['ok' => true]);
    }
}
