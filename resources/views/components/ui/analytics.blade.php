@props(['livewire' => true])
@production
{{-- Google Analytics 4. Included once in each full-page layout's <head>. --}}
<script async src="https://www.googletagmanager.com/gtag/js?id=G-ZQGBYDME0X" data-navigate-once></script>
<script data-navigate-once>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    // page_view is sent manually so each page is counted exactly once.
    gtag('config', 'G-ZQGBYDME0X', { send_page_view: false });
    function sendPageView() {
        gtag('event', 'page_view', {
            page_title: document.title,
            page_location: window.location.href,
            page_path: window.location.pathname + window.location.search,
        });
    }
    @if ($livewire)
    // livewire:navigated fires on first load and on every wire:navigate visit.
    document.addEventListener('livewire:navigated', sendPageView);
    @else
    window.addEventListener('DOMContentLoaded', sendPageView);
    @endif
</script>
@endproduction
