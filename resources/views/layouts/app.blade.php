<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

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
                        window.Pusher=function(pkey,opts){ return new Orig(pkey||key, opts); };
                        for(var k in Orig){ try{ window.Pusher[k]=Orig[k]; }catch(e){} }
                    }
                }catch(e){}
            })();
        </script>
        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-background text-foreground">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-background border-b border-border shadow-sm">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main class="bg-background">
                {{ $slot }}
            </main>
        </div>
    </body>
</html>
