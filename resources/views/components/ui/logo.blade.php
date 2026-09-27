@props(['inverse' => false])

<span {{ $attributes->class(['inline-flex shrink-0 items-center']) }}>
    @if ($inverse)
        <img src="{{ asset('images/tilawa-logo.png') }}" alt="Tilawa" width="863" height="323" class="h-10 w-auto object-contain sm:h-12">
    @else
        <img src="{{ asset('images/tilawa-logo-dark.png') }}" alt="Tilawa" width="863" height="323" class="h-10 w-auto object-contain sm:h-12 dark:hidden">
        <img src="{{ asset('images/tilawa-logo.png') }}" alt="Tilawa" width="863" height="323" class="hidden h-10 w-auto object-contain sm:h-12 dark:block">
    @endif
</span>
