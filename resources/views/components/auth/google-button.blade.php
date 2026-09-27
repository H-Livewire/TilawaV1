@php
    $googleAvailable = filled(config('services.google.client_id')) && filled(config('services.google.client_secret'));
@endphp
<div>
    @if ($googleAvailable)
        <a href="{{ route('auth.google.redirect', ['remember' => 1]) }}"
            x-bind:href="@js(route('auth.google.redirect')) + '?remember=' + ($wire.remember ? '1' : '0')"
            class="flex w-full items-center justify-center gap-3 rounded-xl border border-tilawa-line bg-tilawa-surface px-5 py-3.5 text-sm font-bold text-tilawa-ink transition hover:border-tilawa-teal">
            <x-auth.google-mark /> Continue with Google
        </a>
    @else
        <button type="button" disabled aria-describedby="google-availability" class="flex w-full cursor-not-allowed items-center justify-center gap-3 rounded-xl border border-tilawa-line bg-tilawa-surface px-5 py-3.5 text-sm font-bold text-tilawa-sub">
            <x-auth.google-mark /> Continue with Google
        </button>
        <p id="google-availability" class="mt-2 text-center text-xs leading-5 text-tilawa-sub">Google sign-in will be available soon. Email and guest reading are ready.</p>
    @endif
    @error('google')<p role="alert" class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>@enderror
</div>
