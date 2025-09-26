<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @auth
    <meta name="user-role" content="{{ auth()->user()->role }}">
    <meta name="user-id" content="{{ auth()->id() }}">
    <meta name="user-name" content="{{ auth()->user()->name }}">
    @endauth
    <title>Audios IBRPM - @yield('title', 'Dashboard')</title>
    <!-- Theme preflight: apply saved mode before CSS to avoid FOUC -->
    <script>
        (function () {
            try {
                var params = new URLSearchParams(window.location.search);
                var themeKey = 'audios-color-theme';
                var darkKey = 'audios-dark-mode';
                var theme = 'spotify';
                document.documentElement.setAttribute('data-theme', theme);
                try { localStorage.setItem(themeKey, theme); } catch (error) {}

                var darkParam = params.get('dark');
                var storedDark = localStorage.getItem(darkKey);
                var prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
                var dark = false;
                if (darkParam === '1') dark = true;
                else if (darkParam === '0') dark = false;
                else if (storedDark === '1') dark = true;
                else if (storedDark === '0') dark = false;
                else if (prefersDark) dark = true;
                document.documentElement.classList.toggle('dark', dark);
                localStorage.setItem(darkKey, dark ? '1' : '0');
            } catch (e) {}
        })();
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/player.js', 'resources/js/public-audios.js'])
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="font-sans antialiased bg-background">
    <main data-content-main>
        @yield('content')
    </main>
    <script>
        lucide.createIcons();
    </script>
    <x-player.sticky />
    @stack('scripts')
</body>
</html>
