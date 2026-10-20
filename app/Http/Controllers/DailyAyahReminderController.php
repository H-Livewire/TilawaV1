<?php

namespace App\Http\Controllers;

use App\Models\PushSubscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DailyAyahReminderController extends Controller
{
    /** Save the signed-in user's reminder choices and (optionally) register this device for push. */
    public function update(Request $request): JsonResponse
    {
        $data = $request->validate([
            'enabled' => ['required', 'boolean'],
            'time' => ['required', 'regex:/^([01]\d|2[0-3]):[0-5]\d$/'],
            'arabic' => ['required', 'boolean'],
            'translation' => ['required', 'boolean'],
            'reference' => ['required', 'boolean'],
            'simple' => ['required', 'boolean'],
            'timezone' => ['required', 'timezone:all'],
            'subscription' => ['nullable', 'array'],
            'subscription.endpoint' => ['required_with:subscription', 'url:https', 'max:2000'],
            'subscription.keys.p256dh' => ['required_with:subscription', 'string', 'max:255'],
            'subscription.keys.auth' => ['required_with:subscription', 'string', 'max:255'],
            'subscription.contentEncoding' => ['nullable', 'in:aes128gcm,aesgcm'],
        ]);

        foreach (['enabled', 'arabic', 'translation', 'reference', 'simple'] as $flag) {
            $data[$flag] = (bool) $data[$flag];
        }

        $user = $request->user();
        $localNow = Carbon::now($data['timezone']);
        $slotPassed = $localNow->copy()->setTimeFromTimeString($data['time'])->lessThanOrEqualTo($localNow);

        // Only a real change (turned on, or a new time/timezone) restarts today's send; a device re-syncing must not.
        $schedule = [$user->daily_ayah_enabled, $user->daily_ayah_time, $user->timezone];
        $scheduleChanged = $schedule !== [$data['enabled'], $data['time'], $data['timezone']];

        $user->forceFill([
            'daily_ayah_enabled' => $data['enabled'],
            'daily_ayah_time' => $data['time'],
            'daily_ayah_arabic' => $data['arabic'],
            'daily_ayah_translation' => $data['translation'],
            'daily_ayah_reference' => $data['reference'],
            'daily_ayah_simple' => $data['simple'],
            'timezone' => $data['timezone'],
        ]);

        if ($scheduleChanged) {
            // Turning it on (or moving the time) after today's slot has passed waits for tomorrow instead of firing at once.
            $user->forceFill(['daily_ayah_last_sent_on' => $slotPassed ? $localNow->toDateString() : null]);
        }

        $user->save();

        if (! empty($data['subscription'])) {
            $subscription = $data['subscription'];

            // A device that moves between accounts keeps only its latest owner.
            PushSubscription::updateOrCreate(
                ['endpoint_hash' => PushSubscription::hashFor($subscription['endpoint'])],
                [
                    'user_id' => $user->id,
                    'endpoint' => $subscription['endpoint'],
                    'public_key' => $subscription['keys']['p256dh'],
                    'auth_token' => $subscription['keys']['auth'],
                    'content_encoding' => $subscription['contentEncoding'] ?? 'aes128gcm',
                ],
            );
        }

        return response()->json(['saved' => true]);
    }

    /** Forget this device (the user turned the reminder off here, or the browser dropped its subscription). */
    public function destroy(Request $request): JsonResponse
    {
        $data = $request->validate(['endpoint' => ['required', 'url:https', 'max:2000']]);

        $request->user()->pushSubscriptions()
            ->where('endpoint_hash', PushSubscription::hashFor($data['endpoint']))
            ->delete();

        return response()->json(['removed' => true]);
    }
}
