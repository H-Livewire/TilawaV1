import { cp, mkdir } from 'node:fs/promises';

// PDF.js needs its image decoders for scanned books (including JBIG2 pages).
// Keep these files from the installed package in sync on every build/dev start.
const target = new URL('../public/vendor/pdfjs/wasm/', import.meta.url);
await mkdir(target, { recursive: true });
await cp(new URL('../node_modules/pdfjs-dist/wasm/', import.meta.url), target, { recursive: true });
