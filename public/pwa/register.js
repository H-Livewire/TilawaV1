(() => {
    if (!('serviceWorker' in navigator) || !window.isSecureContext) return;
    navigator.serviceWorker.register('/sw.js', { scope: '/', updateViaCache: 'none' })
        .catch(() => { /* Normal online reading remains available if registration fails. */ });
    let installPrompt;
    const showButtons = () => document.querySelectorAll('[data-install-tilawa]').forEach(button => {
        button.hidden = !installPrompt;
    });
    window.addEventListener('beforeinstallprompt', event => {
        event.preventDefault();
        installPrompt = event;
        showButtons();
    });
    window.addEventListener('appinstalled', () => { installPrompt = null; showButtons(); });
    document.addEventListener('livewire:navigated', showButtons);
    document.addEventListener('click', async event => {
        const button = event.target.closest('[data-install-tilawa]');
        if (!button || !installPrompt) return;
        const prompt = installPrompt;
        installPrompt = null;
        showButtons();
        await prompt.prompt();
        await prompt.userChoice;
    });
})();
