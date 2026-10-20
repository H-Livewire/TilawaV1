<?php

namespace App\View\Components;

use App\Exceptions\QuranApiException;
use App\Services\AyahOfTheDay;
use App\Services\PushSender;
use App\Services\ReadingState;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\Component;
use Illuminate\View\View;

/**
 * The "Allow notifications" card for the Ayah of the Day reminder. Reminders
 * are on by default (7:00 AM, Arabic + translation + reference); the only
 * action a reader needs is allowing notifications.
 */
class DailyReminder extends Component
{
    /** @var array<string, mixed>|null */
    public ?array $ayah = null;

    public function __construct(public bool $compact = false)
    {
        try {
            $this->ayah = app(AyahOfTheDay::class)->forDate(null, app(ReadingState::class)->language());
        } catch (\Throwable $e) {
            report($e);
        }
    }

    /** @return array<string, mixed> */
    public function reminder(): array
    {
        $user = Auth::user();

        return [
            'authenticated' => $user !== null,
            'saved' => $user?->dailyAyahPreferences(),
            'vapidPublicKey' => $user && app(PushSender::class)->isConfigured() ? config('services.webpush.public_key') : null,
            'testEveryMinute' => (bool) config('services.webpush.test_every_minute'),
            'csrf' => csrf_token(),
            'updateUrl' => route('daily-ayah.reminder.update'),
            'removeUrl' => route('daily-ayah.reminder.destroy'),
        ];
    }

    public function render(): View
    {
        return view('components.ui.daily-reminder', ['reminder' => $this->reminder()]);
    }
}
