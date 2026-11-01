<?php

namespace App\Livewire;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app', ['title' => 'Thank you — Tilawa'])]
class DonateThanks extends Component
{
    public function render()
    {
        return view('livewire.donate-thanks');
    }
}
