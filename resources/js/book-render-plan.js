export function pageRenderPlan(current, total, pageWidth, pixelRatio, narrow) {
    const visibleWidth = Math.min(narrow ? 1400 : 1800, Math.ceil(pageWidth * Math.min(pixelRatio || 1, 2)));
    return new Map([current, current + 1, current - 1, current + 2, current - 2, current + 3, current - 3, current + 4]
        .filter(index => index >= 0 && index < total)
        .map(index => [index, index === current || (!narrow && index === current + 1) ? visibleWidth : Math.min(640, visibleWidth)]));
}
