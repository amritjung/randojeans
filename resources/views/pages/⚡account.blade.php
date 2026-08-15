<?php

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Your Account — Rando Jeans')] class extends Component {
    public function logout(): void
    {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        $this->redirect(route('home'), navigate: false);
    }
};

?>

{{-- Widened from max-w-2xl to max-w-3xl to give a 3-column grid enough
     room to breathe — the old width was sized for a single-column
     stacked list, not a grid. --}}
<div class="mx-auto max-w-3xl px-6 py-16">
    <h1 class="font-display text-4xl mb-2">Your Account</h1>
    <p class="text-neutral-500 text-sm mb-10">{{ auth()->user()->name }}</p>

    {{-- grid-cols-1 on mobile (cards stay readable stacked on a narrow
         screen), sm:grid-cols-3 once there's room for three across.
         With 5 cards total, the last row naturally has 2 items filling
         from the left — normal, expected grid behavior. --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <a href="{{ route('profile') }}" class="block border border-neutral-800 hover:border-copper p-6 transition">
            <p class="font-display text-lg">Profile</p>
            <p class="text-neutral-500 text-sm mt-1">View or edit your personal details</p>
        </a>

        <a href="{{ route('addresses') }}" class="block border border-neutral-800 hover:border-copper p-6 transition">
            <p class="font-display text-lg">Addresses</p>
            <p class="text-neutral-500 text-sm mt-1">Manage your delivery address</p>
        </a>

        <a href="{{ route('orders') }}" class="block border border-neutral-800 hover:border-copper p-6 transition">
            <p class="font-display text-lg">Order History</p>
            <p class="text-neutral-500 text-sm mt-1">View all your past orders</p>
        </a>

        <a href="{{ route('security') }}" class="block border border-neutral-800 hover:border-copper p-6 transition">
            <p class="font-display text-lg">Login & Security</p>
            <p class="text-neutral-500 text-sm mt-1">Manage your account password</p>
        </a>

        <a href="{{ route('account.delete') }}" class="block border border-neutral-800 hover:border-red-900 p-6 transition">
            <p class="font-display text-lg text-neutral-400">Delete Account</p>
            <p class="text-neutral-600 text-sm mt-1">See how to permanently delete your account</p>
        </a>
    </div>

    <button wire:click="logout" class="mt-10 font-mono text-xs uppercase tracking-widest text-neutral-500 hover:text-bone">
        Log out
    </button>
</div>