<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $title ?? config('app.name', 'Laravel') }}</title>

        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="bg-ink text-bone font-sans antialiased">
        <header class="border-b border-neutral-800">
            <div class="mx-auto max-w-5xl px-6 py-5 flex items-center justify-between">
                <a href="{{ route('home') }}" class="font-display text-lg tracking-wide">Rando Jeans</a>
                <nav class="flex gap-6 font-mono text-xs uppercase tracking-widest text-neutral-400">
                    <a href="{{ route('home') }}" class="hover:text-bone">Home</a>
                    <a href="{{ route('shop') }}" class="hover:text-bone">Shop</a>
                    <a href="{{ route('craft') }}" class="hover:text-bone">The Craft of Denim</a>
                    <livewire:cart-badge />
                    {{-- Shows "Account" if logged in, "Login" if not — auth()->check()
                         is Laravel's standard way to ask "is someone currently logged in?" --}}
                    @auth
                        <a href="{{ route('account') }}" class="hover:text-bone">Account</a>
                    @else
                        <a href="{{ route('login') }}" class="hover:text-bone">Login</a>
                    @endauth
                </nav>
            </div>
        </header>

        <main>
            {{ $slot }}
        </main>

        <footer class="border-t border-neutral-800 mt-24">
            <div class="mx-auto max-w-5xl px-6 py-10 font-mono text-xs text-neutral-500">
                © {{ date('Y') }} Rando Jeans. Made in Vietnam. Built in Australia.
            </div>
        </footer>

        @livewireScripts
    </body>
</html>