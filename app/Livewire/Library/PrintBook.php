<?php

namespace App\Livewire\Library;

use App\Services\LibraryService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('layouts.guest', ['title' => 'Print / PDF — Hisn al-Muslim'])]
class PrintBook extends Component
{
    #[Locked]
    public string $slug = 'hisn-al-muslim';

    public function mount(string $slug = 'hisn-al-muslim'): void
    {
        $this->slug = $slug;
    }

    #[Computed]
    public function book(): array
    {
        $book = app(LibraryService::class)->getBook($this->slug);

        abort_unless($book !== null, 404);

        return $book;
    }

    public function render()
    {
        return view('livewire.library.print');
    }
}
