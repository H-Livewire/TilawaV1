(() => {
    if (!('serviceWorker' in navigator) || !window.isSecureContext) return;
    navigator.serviceWorker.register('/sw.js', { scope: '/', updateViaCache: 'none' })
        .catch(() => { /* Online reading remains available if registration fails. */ });
    let installPrompt;
    let hideTimer;
    let frame;
    let dismissedUntil = 0;
    try { dismissedUntil = Number(localStorage.getItem('tilawa-install-dismissed-until')) || 0; } catch {}
    const standalone = () => window.matchMedia('(display-mode: standalone)').matches || navigator.standalone === true;
    const hideBanner = () => {
        cancelAnimationFrame(frame);
        const banner = document.getElementById('tilawa-install');
        if (!banner) return;
        banner.classList.remove('is-visible');
        clearTimeout(hideTimer);
        hideTimer = setTimeout(() => { banner.hidden = true; }, 400);
    };
    const refresh = () => {
        const available = !!installPrompt && !standalone();
        document.querySelectorAll('[data-install-tilawa]').forEach(button => { button.hidden = !available; });
        const banner = document.getElementById('tilawa-install');
        if (!banner) return;
        if (!available || Date.now() < dismissedUntil) { hideBanner(); return; }
        clearTimeout(hideTimer);
        cancelAnimationFrame(frame);
        banner.hidden = false;
        frame = requestAnimationFrame(() => {
            frame = requestAnimationFrame(() => banner.classList.add('is-visible'));
        });
    };
    const dismiss = () => {
        dismissedUntil = Date.now() + 7 * 24 * 60 * 60 * 1000;
        try { localStorage.setItem('tilawa-install-dismissed-until', String(dismissedUntil)); } catch {}
        hideBanner();
    };
    window.addEventListener('beforeinstallprompt', event => {
        event.preventDefault();
        installPrompt = event;
        refresh();
    });
    window.addEventListener('appinstalled', () => { installPrompt = null; refresh(); });
    document.addEventListener('livewire:navigated', refresh);
    document.addEventListener('click', async event => {
        if (!(event.target instanceof Element)) return;
        if (event.target.closest('[data-dismiss-tilawa]')) { dismiss(); return; }
        if (!event.target.closest('[data-install-tilawa]') || !installPrompt) return;
        const prompt = installPrompt;
        installPrompt = null;
        refresh();
        try {
            await prompt.prompt();
            const choice = await prompt.userChoice;
            if (choice.outcome === 'dismissed') dismiss();
        } catch { /* The browser may invalidate an install prompt; wait for a new one. */ }
    });
})();
