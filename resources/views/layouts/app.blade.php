<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Tilawa' }}</title>
    <script>
        (function () {
            try {
                var stored = localStorage.getItem('tilawa-theme');
                var resolved = (stored === 'light' || stored === 'dark')
                    ? stored
                    : (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
                if (resolved === 'dark') {
                    document.documentElement.classList.add('dark');
                }
            } catch (e) {}
        })();
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <x-ui.pwa-head />
</head>
<body class="min-h-screen bg-[#F4FAFA] font-sans text-tilawa-ink antialiased dark:bg-[#101c20]">
    {{ $slot }}
    @guest
        <livewire:auth.login-drawer />
    @endguest
    @livewireScripts
</body>
</html>
