<?php

namespace App\Livewire\Library;

use App\Services\LibraryService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;

#[Layout('layouts.app', ['title' => 'Tilawa — Hisn al-Muslim (Ngome ya Muislamu)'])]
class Reader extends Component
{
    #[Locked]
    public string $slug = 'hisn-al-muslim';

    public int $page = 1;

    /** @var 'spread'|'single' */
    public string $viewMode = 'spread';

    public string $search = '';

    public bool $tocOpen = false;

    public bool $searchOpen = false;

    public bool $soundMuted = false;

    public function mount(string $slug = 'hisn-al-muslim'): void
    {
        $this->slug = $slug;

        $targetPage = request()->query('page', 1);
        if (is_numeric($targetPage) && (int) $targetPage >= 1) {
            $this->page = min((int) $targetPage, $this->totalPages());
        }
    }

    public function nextPage(): void
    {
        $step = $this->viewMode === 'spread' && $this->page > 1 ? 2 : 1;
        $target = $this->page + $step;

        if ($target <= $this->totalPages()) {
            $this->page = $target;
        } elseif ($this->page < $this->totalPages()) {
            $this->page = $this->totalPages();
        }
    }

    public function previousPage(): void
    {
        $step = $this->viewMode === 'spread' && $this->page > 2 ? 2 : 1;
        $target = max(1, $this->page - $step);

        $this->page = $target;
    }

    public function goToPage(int $pageNumber): void
    {
        if ($pageNumber >= 1 && $pageNumber <= $this->totalPages()) {
            // In spread mode, align to odd page after cover
            if ($this->viewMode === 'spread' && $pageNumber > 1 && $pageNumber % 2 === 1 && $pageNumber > 2) {
                $this->page = $pageNumber - 1;
            } else {
                $this->page = $pageNumber;
            }
            $this->tocOpen = false;
            $this->searchOpen = false;
        }
    }

    public function goToChapter(int $chapterId): void
    {
        foreach ($this->pages as $page) {
            if (isset($page['chapter']) && (int) $page['chapter']['id'] === $chapterId) {
                $this->goToPage($page['pageNumber']);

                return;
            }
        }
    }

    public function setViewMode(string $mode): void
    {
        $this->viewMode = in_array($mode, ['spread', 'single'], true) ? $mode : 'spread';
    }

    public function toggleSound(): void
    {
        $this->soundMuted = ! $this->soundMuted;
    }

    #[Computed]
    public function book(): array
    {
        $book = app(LibraryService::class)->getBook($this->slug);

        abort_unless($book !== null, 404);

        return $book;
    }

    #[Computed]
    public function pages(): array
    {
        return $this->book['pages'] ?? [];
    }

    public function totalPages(): int
    {
        return count($this->pages);
    }

    #[Computed]
    public function currentPageData(): ?array
    {
        return $this->pages[$this->page - 1] ?? null;
    }

    /** In dual-spread mode, returns the right-hand companion page */
    #[Computed]
    public function companionPageData(): ?array
    {
        if ($this->viewMode !== 'spread' || $this->page === 1 || $this->page >= $this->totalPages()) {
            return null;
        }

        return $this->pages[$this->page] ?? null;
    }

    #[Computed]
    public function searchResults(): array
    {
        if (trim($this->search) === '') {
            return [];
        }

        return app(LibraryService::class)->searchBook($this->slug, $this->search);
    }

    #[Computed]
    public function chaptersWithPages(): array
    {
        $map = [];

        foreach ($this->pages as $page) {
            if (isset($page['chapter']) && ! isset($map[$page['chapter']['id']])) {
                $map[$page['chapter']['id']] = [
                    'id' => $page['chapter']['id'],
                    'number' => $page['chapter']['number'],
                    'title' => $page['chapter']['title'],
                    'arabicTitle' => $page['chapter']['arabicTitle'],
                    'pageNumber' => $page['pageNumber'],
                ];
            }
        }

        return array_values($map);
    }

    public function render()
    {
        return view('livewire.library.reader')->layout('layouts.app', [
            'title' => "Tilawa — {$this->book['title']} ({$this->book['swahiliTitle']})",
        ]);
    }
}
