// Opening /surah/N?ayah=M#ayah-M (from the daily-ayah notification) must land on that ayah. The browser jumps to
// the anchor before the Arabic font and the cards above it have settled, so the page ends up short of it.
// Re-align a few times while the page settles, and stop as soon as the reader scrolls themselves.
function align() {
    const hash = window.location.hash;
    if (!/^#ayah-\d+$/.test(hash)) return;

    let cancelled = false;
    const stop = () => { cancelled = true; };
    ['wheel', 'touchstart', 'keydown', 'mousedown'].forEach(name => window.addEventListener(name, stop, { once: true, passive: true }));

    const go = () => {
        if (cancelled) return;
        document.querySelector(hash)?.scrollIntoView({ block: 'start' });
    };

    go();
    document.fonts?.ready.then(go);
    window.addEventListener('load', go, { once: true });
    [300, 800, 1600].forEach(delay => setTimeout(go, delay));
}

document.addEventListener('DOMContentLoaded', align);
document.addEventListener('livewire:navigated', align);
