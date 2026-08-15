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
        {{-- x-data holds whether the mobile menu is open. Alpine.js handles
     this purely client-side — no server round-trip needed just to
     toggle a menu open/closed. --}}
<header x-data="{ mobileMenuOpen: false }" class="border-b border-neutral-800 relative">
    <div class="mx-auto max-w-5xl px-6 py-5 flex items-center justify-between">
        <a href="{{ route('home') }}" class="font-display text-lg tracking-wide">Rando Jeans</a>

        {{-- Desktop nav: hidden below the 'md' breakpoint (~768px),
             shown as a normal horizontal row above it. Exactly the
             nav we had before, just now explicitly desktop-only. --}}
             <nav class="hidden md:flex items-center gap-6 font-mono text-xs uppercase tracking-widest text-neutral-400">
                <a href="{{ route('home') }}" class="hover:text-bone">Home</a>
                <a href="{{ route('shop') }}" class="hover:text-bone">Shop</a>
                <a href="{{ route('craft') }}" class="hover:text-bone">The Craft of Denim</a>
            
                @auth
                    
                    <a href="{{ route('account') }}" class="hover:text-bone" aria-label="Account">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.963 0a9 9 0 10-11.963 0m11.963 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </a>
                    <livewire:wishlist-badge />
                @else
                    <a href="{{ route('login') }}" class="hover:text-bone" aria-label="Login">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l3 3m0 0l-3 3m3-3H3" />
                        </svg>
                    </a>
                @endauth
            
                <livewire:cart-badge />
            </nav>

        {{-- Mobile-only controls: cart stays visible at all times
             (people expect quick access to it even with the menu
             collapsed), plus a hamburger button that toggles the
             dropdown panel below. md:hidden means this whole block
             disappears once the screen is wide enough for the full nav. --}}
        <div class="flex md:hidden items-center gap-4">
            <livewire:cart-badge />
            <button
                @click="mobileMenuOpen = !mobileMenuOpen"
                class="text-neutral-400 hover:text-bone"
                aria-label="Toggle menu"
            >
                {{-- Simple hamburger/close icon swap using Alpine's
                     x-show, rather than pulling in an icon library for
                     two glyphs. --}}
                <span x-show="!mobileMenuOpen" class="text-2xl leading-none">☰</span>
                <span x-show="mobileMenuOpen" x-cloak class="text-2xl leading-none">✕</span>
            </button>
        </div>
    </div>

    {{-- Mobile dropdown panel: positioned absolutely so it overlays
         content below rather than pushing the page down, full-width,
         only rendered when mobileMenuOpen is true. Each link closes
         the menu on click (@click="mobileMenuOpen = false") so it
         doesn't stay open after navigating. --}}
    <nav
        x-show="mobileMenuOpen"
        x-cloak
        @click.outside="mobileMenuOpen = false"
        class="md:hidden absolute top-full left-0 right-0 bg-ink border-b border-neutral-800 flex flex-col font-mono text-xs uppercase tracking-widest text-neutral-400 z-40"
    >
        <a href="{{ route('home') }}" @click="mobileMenuOpen = false" class="px-6 py-4 border-b border-neutral-800 hover:text-bone hover:bg-neutral-900">Home</a>
        <a href="{{ route('shop') }}" @click="mobileMenuOpen = false" class="px-6 py-4 border-b border-neutral-800 hover:text-bone hover:bg-neutral-900">Shop</a>
        <a href="{{ route('craft') }}" @click="mobileMenuOpen = false" class="px-6 py-4 border-b border-neutral-800 hover:text-bone hover:bg-neutral-900">The Craft of Denim</a>
        @auth
            <a href="{{ route('wishlist') }}" @click="mobileMenuOpen = false" class="px-6 py-4 border-b border-neutral-800 hover:text-bone hover:bg-neutral-900">Wishlist</a>
            <a href="{{ route('account') }}" @click="mobileMenuOpen = false" class="px-6 py-4 hover:text-bone hover:bg-neutral-900">Account</a>
        @else
            <a href="{{ route('login') }}" @click="mobileMenuOpen = false" class="px-6 py-4 hover:text-bone hover:bg-neutral-900">Login</a>
        @endauth
    </nav>
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
        {{-- Global toast handler — lives once in the layout rather than being
            duplicated per-page. Entirely client-side: x-data holds local
            state (message text + visible flag), @toast.window listens for
            the event dispatched from any Livewire component anywhere on the
            page, and the whole show/timer/hide cycle runs in the browser
            with no further Livewire round-trips needed once triggered. --}}
        <div
        x-data="{ message: '', visible: false }"
        x-on:toast.window="message = $event.detail.message; visible = true; setTimeout(() => visible = false, 3000)"
        x-show="visible"
        x-cloak
        x-transition
        class="fixed bottom-6 left-1/2 -translate-x-1/2 bg-copper text-ink font-mono text-xs uppercase tracking-widest pl-5 pr-3 py-3 z-50 flex items-center gap-4 shadow-lg"
        >
        <span x-text="message"></span>
        <button @click="visible = false" class="text-ink/70 hover:text-ink text-base leading-none">✕</button>
        </div>
    </body>
</html>