export const PAGE_COUNT = 604;
export const spreadStart = page => page % 2 ? page : page - 1;
export const bookIndex = page => PAGE_COUNT - page;
export const pageAtIndex = (index, spread) => PAGE_COUNT - index - (spread ? 1 : 0);
export function nearbyPages(page) {
    const start = spreadStart(page);
    return Array.from({ length: 6 }, (_, i) => start - 2 + i).filter(n => n >= 1 && n <= PAGE_COUNT);
}
