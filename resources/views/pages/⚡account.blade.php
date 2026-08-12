<?php

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Your Account — Rando Jeans')] class extends Component {
    // This page is now just a landing/navigation hub — no data logic of
    // its own. Splitting "Account" (a menu) from "Order History" (a list)
    // and "Profile" (view/edit) keeps each page doing exactly one job,
    // rather than one crowded page trying to do all three at once.
    public function logout(): void
    {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        $this->redirect(route('home'), navigate: false);
    }
};

?>

<div class="mx-auto max-w-md px-6 py-16">
    <h1 class="font-display text-4xl mb-2">Your Account</h1>
    <p class="text-neutral-500 text-sm mb-10">{{ auth()->user()->name }}</p>

    <div class="space-y-4">
        <a href="{{ route('profile') }}" class="block border border-neutral-800 hover:border-copper p-6 transition">
            <p class="font-display text-lg">My Profile</p>
            <p class="text-neutral-500 text-sm mt-1">View and edit your details and delivery address</p>
        </a>

        <a href="{{ route('orders') }}" class="block border border-neutral-800 hover:border-copper p-6 transition">
            <p class="font-display text-lg">Order History</p>
            <p class="text-neutral-500 text-sm mt-1">View all your past orders</p>
        </a>
    </div>

    <button wire:click="logout" class="mt-10 font-mono text-xs uppercase tracking-widest text-neutral-500 hover:text-bone">
        Log out
    </button>
</div>