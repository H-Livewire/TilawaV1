// Keep the page-flip library in its own browsing context so its animation loop is
// released when Livewire removes the reader. Quran state stays in Reading.php.
export function mushafFlip() {
    let events;
    let observer;
    let ready = false;
    let configuration;
    let disposed = false;
    return {
        init() {
            events = new AbortController();
            window.addEventListener('message', async event => {
                if (event.origin !== location.origin || event.source !== this.$refs.frame.contentWindow) return;
                const data = event.data;
                if (data?.type === 'mushaf:ready') {
                    ready = true;
                    this.send({ type: 'mushaf:configure', ...configuration });
                } else if (data?.type === 'mushaf:load') {
                    try {
                        const pages = await this.$wire.loadMushafPages(data.pages);
                        this.send({ type: 'mushaf:loaded', id: data.id, pages });
                    } catch {
                        this.send({ type: 'mushaf:loaded', id: data.id, error: true });
                    }
                } else if (data?.type === 'mushaf:page') {
                    try {
                        const saved = await this.$wire.openMushafPage(data.page);
                        if (saved !== true) throw new Error('Could not save position');
                        this.send({ type: 'mushaf:saved', id: data.id });
                    } catch {
                        this.send({ type: 'mushaf:saved', id: data.id, error: true });
                    }
                } else if (data?.type === 'mushaf:height' && Number.isFinite(data.height)) {
                    this.$refs.frame.style.height = `${Math.min(6000, Math.max(500, data.height))}px`;
                } else if (data?.type === 'mushaf:word') {
                    this.$dispatch('open-word', data.word);
                } else if (data?.type === 'mushaf:tajweed') {
                    this.$dispatch('open-tajweed', data.rule);
                }
            }, { signal: events.signal });
            observer = new MutationObserver(() => this.configure(...configurationArgs(this)));
            observer.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
        },
        configure(page, language, tajweed) {
            configuration = { page, language, tajweed, dark: document.documentElement.classList.contains('dark') };
            if (ready) this.send({ type: 'mushaf:configure', ...configuration });
        },
        send(data) {
            if (!disposed) this.$refs.frame?.contentWindow?.postMessage(data, location.origin);
        },
        destroy() {
            disposed = true;
            events?.abort();
            observer?.disconnect();
            this.$refs.frame?.removeAttribute('src');
        },
    };
}
function configurationArgs(component) {
    return [component.$wire.physicalPage, component.$wire.translationLanguage, component.$wire.tajweedEnabled];
}
document.addEventListener('alpine:init', () => window.Alpine.data('mushafFlip', mushafFlip));
if (document.querySelector('[data-mushaf-frame]')) import('./mushaf-frame');
