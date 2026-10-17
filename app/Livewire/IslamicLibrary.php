<?php

namespace App\Livewire;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class IslamicLibrary extends Component
{
    public function render(): View
    {
        return view('livewire.islamic-library')->layout('layouts.app', [
            'title' => 'Islamic library — Tilawa',
        ]);
    }
}
