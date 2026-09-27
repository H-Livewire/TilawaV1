<?php

namespace App\Livewire;

use Livewire\Component;

class Landing extends Component
{
    public function render()
    {
        return view('livewire.landing', [
            'featuredSurahs' => [
                ['number' => 1, 'englishName' => 'Al-Fatihah', 'name' => 'الفاتحة', 'translation' => 'The Opening', 'ayahs' => 7],
                ['number' => 2, 'englishName' => 'Al-Baqarah', 'name' => 'البقرة', 'translation' => 'The Cow', 'ayahs' => 286],
                ['number' => 18, 'englishName' => 'Al-Kahf', 'name' => 'الكهف', 'translation' => 'The Cave', 'ayahs' => 110],
                ['number' => 36, 'englishName' => 'Ya-Sin', 'name' => 'يس', 'translation' => 'Ya-Sin', 'ayahs' => 83],
                ['number' => 55, 'englishName' => 'Ar-Rahman', 'name' => 'الرحمن', 'translation' => 'The Most Merciful', 'ayahs' => 78],
                ['number' => 67, 'englishName' => 'Al-Mulk', 'name' => 'الملك', 'translation' => 'The Sovereignty', 'ayahs' => 30],
            ],
        ])->layout('layouts.app', [
            'title' => 'Tilawa — Your daily companion for the Qur\'an',
        ]);
    }
}
