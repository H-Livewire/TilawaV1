<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class LibraryPdfController extends Controller
{
    public function reader(string $edition = 'english'): View
    {
        $book = $this->book($edition);

        return view('library.reader', compact('book'));
    }

    private function book(string $edition): array
    {
        $books = config('library.books');
        abort_unless(array_key_exists($edition, $books), 404);

        return $books[$edition];
    }

    public function __invoke(string $edition = 'english'): BinaryFileResponse
    {
        $book = $this->book($edition);
        $path = storage_path('app/private/library/'.$book['file']);

        abort_unless(is_file($path), 404, 'This book is temporarily unavailable.');

        return response()->file($path, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$book['download_name'].'"',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, max-age=3600',
        ]);
    }
}
