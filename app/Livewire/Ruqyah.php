<?php

namespace App\Livewire;

use App\Exceptions\QuranApiException;
use App\Services\QuranService;
use Livewire\Component;

class Ruqyah extends Component
{
    public function render()
    {
        $passages = [
            ['surah' => 1, 'ayah' => 1, 'title' => 'Al-Fatihah', 'range' => '1:1–7 · Whole surah', 'source' => 5736],
            ['surah' => 2, 'ayah' => 255, 'title' => 'Ayat al-Kursi', 'range' => '2:255 · Selected ayah', 'source' => 2311],
            ['surah' => 2, 'ayah' => 285, 'title' => 'The closing ayahs of Al-Baqarah', 'range' => '2:285–286 · Selected ayahs', 'source' => 5009],
            ['surah' => 112, 'ayah' => 1, 'title' => 'Al-Ikhlas', 'range' => '112:1–4 · Whole surah', 'source' => 5017],
            ['surah' => 113, 'ayah' => 1, 'title' => 'Al-Falaq', 'range' => '113:1–5 · Whole surah', 'source' => 5017],
            ['surah' => 114, 'ayah' => 1, 'title' => 'An-Nas', 'range' => '114:1–6 · Whole surah', 'source' => 5017],
        ];

        $apiUnavailable = false;
        try {
            $surahs = collect(app(QuranService::class)->getSurahList())->keyBy('number');
        } catch (QuranApiException) {
            $surahs = collect();
            $apiUnavailable = true;
        }

        foreach ($passages as &$passage) {
            $passage['name'] = $surahs->get($passage['surah'])['name'] ?? null;
        }
        unset($passage);

        return view('livewire.ruqyah', compact('passages', 'apiUnavailable'))
            ->layout('layouts.app', ['title' => 'Tilawa — Ruqyah reading']);
    }
}
