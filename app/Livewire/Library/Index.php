<?php

namespace App\Livewire\Library;

use App\Services\LibraryService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app', ['title' => 'Tilawa — Maktaba ya Kiislamu (Islamic Library)'])]
class Index extends Component
{
    public string $activeCategory = 'all';

    public string $search = '';

    public function setCategory(string $category): void
    {
        $this->activeCategory = $category;
    }

    #[Computed]
    public function categories(): array
    {
        return app(LibraryService::class)->categories();
    }

    #[Computed]
    public function books(): array
    {
        $catalog = collect(app(LibraryService::class)->catalog());

        if ($this->activeCategory !== 'all') {
            $catalog = $catalog->where('categorySlug', $this->activeCategory);
        }

        if (trim($this->search) !== '') {
            $needle = mb_strtolower(trim($this->search));
            $catalog = $catalog->filter(function (array $book) use ($needle): bool {
                return str_contains(mb_strtolower($book['title']), $needle)
                    || str_contains(mb_strtolower($book['swahiliTitle']), $needle)
                    || str_contains(mb_strtolower($book['author']), $needle)
                    || str_contains(mb_strtolower($book['description']), $needle)
                    || str_contains($book['arabicTitle'], trim($this->search));
            });
        }

        return $catalog->values()->all();
    }

    public function render()
    {
        return view('livewire.library.index');
    }
}
