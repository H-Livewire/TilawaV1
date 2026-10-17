<?php

namespace App\Support;

class LibraryCatalogue
{
    /**
     * Every catalogue book with the URLs of its reader and PDF.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function all(): array
    {
        $books = [];

        foreach (config('library.books') as $edition => $book) {
            $hasNamedRoutes = isset($book['reader_route'], $book['pdf_route']);

            $books[$edition] = $book + [
                'edition' => $edition,
                'reader_url' => $hasNamedRoutes ? route($book['reader_route']) : route('library.book.reader', $edition),
                'pdf_url' => $hasNamedRoutes ? route($book['pdf_route']) : route('library.book.pdf', $edition),
            ];
        }

        return $books;
    }

    /**
     * @return array<string, mixed>
     */
    public static function find(string $edition): array
    {
        $books = self::all();
        abort_unless(array_key_exists($edition, $books), 404);

        return $books[$edition];
    }
}
