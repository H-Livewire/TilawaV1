# Islamic library reader

The catalogue in `config/library.php` lists the English (193 pages) and Kiswahili
(224 pages) Hisnul Muslim editions. Original PDFs live in
`storage/app/private/library/`; the controller serves only catalogue entries,
with byte-range support. Both editions use the same PDF.js/StPageFlip reader.

Run `npm ci` and `npm run build` when deploying. The build automatically copies
the installed PDF.js image decoders to `public/vendor/pdfjs/wasm/`. Deploy that
directory **alongside** `public/build/` and the private library PDFs. These
decoders are required for the scanned Kiswahili PDF; omitting them can produce
blank pages. `npm run dev` also synchronizes them. No new dependency is needed.

PDF pages are rendered progressively; the original files and their crop boxes
are preserved. Test `/library` by opening each edition, turning a page, jumping
to the last page, zooming, and closing the reader before switching editions.

Automated checks:

```sh
php artisan test --compact tests/Feature/IslamicLibraryTest.php tests/Feature/ReadingTest.php
node --test tests/js/book-render-plan.test.mjs tests/js/mushaf-pagination.test.mjs
```
