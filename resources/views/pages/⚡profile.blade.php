<?php

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Profile — Rando Jeans')] class extends Component {
};

?>

<div class="mx-auto max-w-md px-6 py-16">
    <a href="{{ route('account') }}" class="font-mono text-xs uppercase tracking-widest text-neutral-500 hover:text-bone">
        ← Back to Account
    </a>

    <div class="flex items-center justify-between mt-6 mb-8">
        <h1 class="font-display text-3xl">Profile</h1>
        <a href="{{ route('profile.edit') }}" class="font-mono text-xs uppercase tracking-widest text-copper-light hover:text-bone">
            Edit →
        </a>
    </div>

    <div class="space-y-6">
        <div>
            <p class="font-mono text-xs uppercase tracking-widest text-neutral-500">Full name</p>
            <p class="mt-1 text-bone">{{ auth()->user()->name }}</p>
        </div>

        <div>
            <p class="font-mono text-xs uppercase tracking-widest text-neutral-500">Email</p>
            <p class="mt-1 text-bone">{{ auth()->user()->email }}</p>
        </div>

        <div>
            <p class="font-mono text-xs uppercase tracking-widest text-neutral-500">Phone</p>
            <p class="mt-1 text-bone">{{ auth()->user()->phone ?: 'Not set' }}</p>
        </div>
    </div>
</div>