<?php

namespace App\Livewire;

use App\Support\LibraryCatalogue;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class IslamicLibrary extends Component
{
    public function render(): View
    {
        return view('livewire.islamic-library', ['books' => LibraryCatalogue::all()])->layout('layouts.app', [
            'title' => 'Islamic library — Tilawa',
        ]);
    }
}
