<?php

namespace App\Livewire;

use App\Exceptions\QuranApiException;
use App\Services\AyahOfTheDay;
use App\Services\ReadingState;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app', ['title' => 'Tilawa — Ayah of the Day'])]
class DailyAyah extends Component
{
    /**
     * Today's ayah in the reader's translation language. The notification
     * preferences (on/off, time, content) live on the device — see
     * public/pwa/daily-ayah-core.js — so this component only renders the verse.
     *
     * @return array<string, mixed>|null null when the Qur'an API is unreachable
     */
    #[Computed]
    public function ayah(): ?array
    {
        try {
            return app(AyahOfTheDay::class)->forDate(null, app(ReadingState::class)->language());
        } catch (QuranApiException) {
            return null;
        }
    }

    public function render()
    {
        return view('livewire.daily-ayah');
    }
}
