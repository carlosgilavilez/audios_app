<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @auth
    <meta name="user-role" content="{{ auth()->user()->role }}">
    <meta name="user-id" content="{{ auth()->id() }}">
    @endauth
    <title>Audios IBRPM - @yield('title', 'Dashboard')</title>
    <!-- Theme preflight: apply saved mode before CSS to avoid FOUC -->
    <script>
        try {
            var t = localStorage.getItem('theme');
            if (t === 'dark') document.documentElement.classList.add('dark');
            if (t === 'light') document.documentElement.classList.remove('dark');
        } catch (e) {}
    </script>
    <!-- Pusher/Echo runtime pre-init (before Vite) -->
    <meta name="pusher-key" content="{{ config('broadcasting.connections.pusher.key') }}">
    <meta name="pusher-cluster" content="{{ config('broadcasting.connections.pusher.options.cluster') }}">
    <script src="https://js.pusher.com/8.4/pusher.min.js"></script>
    <script>
        (function(){
            try{
                var key=document.querySelector('meta[name="pusher-key"]').getAttribute('content');
                var cluster=document.querySelector('meta[name="pusher-cluster"]').getAttribute('content');
                if(!key||!cluster) return;
                var Orig=window.Pusher;
                if(typeof Orig==='function'){
                    // shim to inject key if missing (for early Echo init in Vite bundle)
                    window.Pusher=function(pkey,opts){ return new Orig(pkey||key, opts); };
                    for(var k in Orig){ try{ window.Pusher[k]=Orig[k]; }catch(e){} }
                }
            }catch(e){}
        })();
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/player.js'])
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="font-sans antialiased">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <aside class="border-r border-sidebar-border bg-sidebar text-sidebar-foreground w-48 flex-shrink-0">
            <div class="px-4 py-6">
                <h1 class="text-2xl font-bold text-sidebar-primary">Audios IBRPM</h1>
                <p class="text-muted-foreground">Sistema de Gestión</p>
            </div>

            <nav class="mt-6">
                <!-- Administración Group -->
                <div class="mb-4 px-2">
                    <h3 class="px-4 text-xs font-semibold uppercase text-muted-foreground mb-2">Administración</h3>
                    <ul>
                        <li>
                            @if(auth()->user()->role == 'admin')
                                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 py-3 px-4 rounded-lg {{ request()->routeIs('admin.dashboard') ? 'bg-sidebar-accent text-sidebar-primary font-medium' : 'hover:bg-sidebar-accent/50' }}">
                            @else
                                <a href="{{ route('editor.dashboard') }}" class="flex items-center gap-3 py-3 px-4 rounded-lg {{ request()->routeIs('editor.dashboard') ? 'bg-sidebar-accent text-sidebar-primary font-medium' : 'hover:bg-sidebar-accent/50' }}">
                            @endif
                                <i data-lucide="layout-dashboard" class="h-5 w-5"></i>
                                <span class="text-sm font-medium">Dashboard</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route(auth()->user()->role . '.autores.index') }}" class="flex items-center gap-3 py-3 px-4 rounded-lg {{ request()->routeIs(auth()->user()->role . '.autores.index') ? 'bg-sidebar-accent text-sidebar-primary font-medium' : 'hover:bg-sidebar-accent/50' }}">
                                <i data-lucide="user" class="h-5 w-5"></i>
                                <span class="text-sm font-medium">Autores</span>
                            </a>
                        </li>
                        <li>
                            <a href="{{ route(auth()->user()->role . '.series.index') }}" class="flex items-center gap-3 py-3 px-4 rounded-lg {{ request()->routeIs(auth()->user()->role . '.series.index') ? 'bg-sidebar-accent text-sidebar-primary font-medium' : 'hover:bg-sidebar-accent/50' }}">
                                <i data-lucide="music" class="h-5 w-5"></i>
                                <span class="text-sm font-medium">Series</span>
                            </a>
                        </li>
                        <li>
                            <li>
                                <a href="{{ route(auth()->user()->role . '.audios.index') }}" class="flex items-center gap-3 py-3 px-4 rounded-lg {{ request()->routeIs(auth()->user()->role . '.audios.index') ? 'bg-sidebar-accent text-sidebar-primary font-medium' : 'hover:bg-sidebar-accent/50' }}">
                                    <i data-lucide="headphones" class="h-5 w-5"></i>
                                    <span class="text-sm font-medium">Audios</span>
                                </a>
                            </li>
                        </li>
                    </ul>
                </div>

                <!-- Sistema Group -->
                @if(auth()->user()->role == 'admin')
                <div class="mb-4 px-2">
                    <h3 class="px-4 text-xs font-semibold uppercase text-muted-foreground mb-2">Sistema</h3>
                    <ul>
                        <li>
                            <a href="{{ route('admin.logs') }}" class="flex items-center gap-3 py-3 px-4 rounded-lg {{ request()->routeIs('admin.logs') ? 'bg-sidebar-accent text-sidebar-primary font-medium' : 'hover:bg-sidebar-accent/50' }}">
                                <i data-lucide="file-text" class="h-5 w-5"></i>
                                <span class="text-sm font-medium">Logs</span>
                            </a>
                        </li>
                        {{-- New User Management Link --}}
                        <li>
                            <a href="{{ route('admin.users.index') }}" class="flex items-center gap-3 py-3 px-4 rounded-lg {{ request()->routeIs('admin.users.*') ? 'bg-sidebar-accent text-sidebar-primary font-medium' : 'hover:bg-sidebar-accent/50' }}">
                                <i data-lucide="users" class="h-5 w-5"></i>
                                <span class="text-sm font-medium">Usuarios</span>
                            </a>
                        </li>
                    </ul>
                </div>
                @endif

                <!-- Público Group -->
                <div class="px-2">
                    <h3 class="px-4 text-xs font-semibold uppercase text-muted-foreground mb-2">Público</h3>
                    <ul>
                        <li>
                            <a href="{{ route('public.audios') }}" class="flex items-center gap-3 py-3 px-4 rounded-lg hover:bg-sidebar-accent/50">
                                <i data-lucide="book" class="h-5 w-5"></i>
                                <span class="text-sm font-medium">Biblioteca de audios</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>
        </aside>

        <!-- Main Content Area -->
        <div class="flex-1 min-w-0 flex flex-col">
            <header class="h-14 border-b border-border bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60 flex items-center justify-between px-3 sm:px-4 md:px-6 gap-1 sm:gap-2 md:gap-3">
                <!-- Sidebar Toggle (Placeholder for now) -->
                <button onclick="alert('Sidebar toggle functionality to be implemented')" class="p-2 rounded-md hover:bg-accent">
                    <i data-lucide="menu"></i>
                </button>
                <div class="flex items-center space-x-4 flex-1 min-w-0 overflow-hidden">
                    <h1 class="font-semibold text-foreground truncate">@yield('title', 'Dashboard')</h1>
                </div>
                <!-- Actions: Theme + Logout -->
                <div class="flex items-center gap-2 shrink-0 {{ request()->routeIs('admin.audios.*') ? 'mr-4 sm:mr-4 md:mr-0' : '' }}">
                    <div class="hidden md:flex items-center gap-2 mr-2 text-sm text-muted-foreground">
                        <i data-lucide="user" class="h-4 w-4"></i>
                        <span class="truncate max-w-[220px]">{{ auth()->user()->name }}</span>
                    </div>
                    <div class="hidden md:block"><x-theme-toggle /></div>
                    <a href="{{ route('profile.edit') }}" class="inline-flex items-center rounded-md px-2 md:px-3 py-2 text-sm font-medium border border-border bg-secondary text-secondary-foreground hover:bg-muted/70 transition" title="Mi perfil">
                        <i data-lucide="settings-2" class="h-5 w-5 mr-2"></i>
                        <span class="hidden xl:inline">Perfil</span>
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="inline-flex items-center rounded-md mr-1 px-2 md:px-3 py-2 text-sm font-medium border border-border bg-secondary text-secondary-foreground hover:bg-muted/70 transition" title="Cerrar sesión">
                            <i data-lucide="log-out" class="h-5 w-5 mr-2"></i>
                            <span class="hidden 2xl:inline">Salir</span>
                        </button>
                    </form>
                </div>
            </header>

            <main class="flex-1 min-w-0 bg-background px-3 sm:px-4 md:px-6 py-6 pb-24">
                @yield('content')
            </main>
        </div>
    </div>
    <script>
        lucide.createIcons();
    </script>
    <x-player.sticky />
    @stack('scripts')
</body>
</html>
