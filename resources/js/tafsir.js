// Tafsir choice + loader. The selected tafsir is shared by the toolbar menu and
// every ayah card through Alpine.store('tafsir'); text comes from Quran.com.
const API = 'https://api.quran.com/api/v4/tafsirs';
const STORE_KEY = 'tilawa.tafsir';
const TAFSIRS = [
    { id: 169, name: 'Ibn Kathir (Abridged)', lang: 'en', author: 'Hafiz Ibn Kathir' },
    { id: 168, name: "Ma'arif Al-Qur'an", lang: 'en', author: 'Mufti Muhammad Shafi' },
];
// Swahili is listed so readers can see it is planned, but no Swahili tafsir text is available yet.
const LANGUAGES = [{ code: 'en', label: 'English', ready: true }, { code: 'sw', label: 'Kiswahili', ready: false }];
const RTL = [];
const ALLOWED = new Set(['P', 'BR', 'B', 'STRONG', 'I', 'EM', 'U', 'SPAN', 'H1', 'H2', 'H3', 'H4', 'UL', 'OL', 'LI', 'BLOCKQUOTE', 'SUP', 'SUB']);
const cache = new Map();

// The text is third-party HTML: keep a small set of text tags and drop every attribute.
function clean(html) {
    const doc = new DOMParser().parseFromString(html, 'text/html');
    const out = document.createElement('div');
    const walk = (node, into) => {
        for (const child of node.childNodes) {
            if (child.nodeType === Node.TEXT_NODE) {
                into.append(document.createTextNode(child.textContent));
            } else if (child.nodeType === Node.ELEMENT_NODE) {
                if (['SCRIPT', 'STYLE', 'IFRAME', 'OBJECT', 'EMBED'].includes(child.tagName)) continue;
                if (ALLOWED.has(child.tagName)) {
                    const copy = document.createElement(child.tagName);
                    walk(child, copy);
                    into.append(copy);
                } else {
                    walk(child, into);
                }
            }
        }
    };
    walk(doc.body, out);
    return out.innerHTML;
}

document.addEventListener('alpine:init', () => {
    window.Alpine.store('tafsir', {
        id: 169,
        languages: LANGUAGES,
        tafsirs: TAFSIRS,

        init() {
            try {
                const saved = Number(localStorage.getItem(STORE_KEY));
                if (TAFSIRS.some(t => t.id === saved)) this.id = saved;
            } catch { /* storage unavailable: default applies */ }
        },
        get current() {
            return TAFSIRS.find(t => t.id === this.id) ?? TAFSIRS[0];
        },
        get lang() {
            return this.current.lang;
        },
        get rtl() {
            return RTL.includes(this.lang);
        },
        get forLanguage() {
            return TAFSIRS.filter(t => t.lang === this.lang);
        },
        select(id) {
            this.id = id;
            try { localStorage.setItem(STORE_KEY, String(id)); } catch { /* ignore */ }
            window.dispatchEvent(new CustomEvent('tafsir-changed', { detail: { id } }));
        },
        setLanguage(code) {
            const first = TAFSIRS.find(t => t.lang === code);
            if (first) this.select(first.id);
        },
        get credit() {
            return `${this.current.name} by ${this.current.author}, via Quran.com`;
        },
        async fetchAyah(surah, ayah) {
            const key = `${this.id}:${surah}:${ayah}`;
            if (cache.has(key)) return cache.get(key);
            const response = await fetch(`${API}/${this.id}/by_ayah/${surah}:${ayah}`);
            if (!response.ok) throw new Error('Tafsir unavailable');
            const tafsir = (await response.json()).tafsir;
            if (!tafsir?.text) throw new Error('Tafsir unavailable');
            const keys = Object.keys(tafsir.verses ?? {});
            const numbers = keys.map(k => Number(k.split(':')[1])).filter(Number.isFinite);
            const result = {
                html: clean(tafsir.text),
                note: numbers.length > 1 ? `You are reading a tafsir for the group of verses ${keys[0]} to ${keys[keys.length - 1]}` : '',
            };
            cache.set(key, result);
            return result;
        },
    });
});
