<?php

namespace App\Console\Commands;

use App\Exceptions\QuranApiException;
use App\Models\PushSubscription;
use App\Models\User;
use App\Services\AyahOfTheDay;
use App\Services\PushSender;
use App\Support\DailyAyahMessage;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class SendDailyAyah extends Command
{
    protected $signature = 'ayah:send-daily';

    protected $description = 'Send the Ayah of the Day push notification to every signed-in user whose reminder time has arrived';

    /** A reminder still goes out if the scheduler was down briefly, but never hours later. */
    private const CATCH_UP_MINUTES = 120;

    public function handle(AyahOfTheDay $ayahOfTheDay, PushSender $sender): int
    {
        if (! $sender->isConfigured()) {
            $this->warn('Web push is not configured (VAPID keys missing); nothing sent.');

            return self::SUCCESS;
        }

        $sent = 0;

        User::query()
            ->where('daily_ayah_enabled', true)
            ->whereHas('pushSubscriptions')
            ->with('pushSubscriptions')
            ->chunkById(100, function ($users) use ($ayahOfTheDay, $sender, &$sent) {
                foreach ($users as $user) {
                    $localNow = $this->localNow($user);
                    $testing = (bool) config('services.webpush.test_every_minute');
                    if (! $testing && ! $this->isDue($user, $localNow)) {
                        continue;
                    }

                    // Mark first: a slow or failing push service must never cause a second send.
                    $user->forceFill(['daily_ayah_last_sent_on' => $localNow->toDateString()])->save();

                    try {
                        $ayah = $ayahOfTheDay->forDate($localNow->toDateString(), $user->translation_language ?? 'en');
                    } catch (QuranApiException) {
                        $ayah = null;
                    }

                    $message = DailyAyahMessage::compose($ayah, $user->dailyAyahPreferences());
                    $gone = $sender->send($user->pushSubscriptions, $message);

                    if ($gone !== []) {
                        PushSubscription::whereIn('endpoint_hash', array_map(PushSubscription::hashFor(...), $gone))->delete();
                    }
                    $sent++;
                }
            });

        $this->info("Daily ayah sent to {$sent} user(s).");

        return self::SUCCESS;
    }

    private function localNow(User $user): Carbon
    {
        try {
            return now($user->timezone ?: 'UTC');
        } catch (\Throwable) {
            return now('UTC');
        }
    }

    private function isDue(User $user, Carbon $localNow): bool
    {
        if ($user->daily_ayah_last_sent_on?->toDateString() === $localNow->toDateString()) {
            return false;
        }

        $at = $localNow->copy()->setTimeFromTimeString($user->daily_ayah_time ?: '07:00');
        $minutesSince = $at->diffInMinutes($localNow, false);

        return $minutesSince >= 0 && $minutesSince <= self::CATCH_UP_MINUTES;
    }
}
