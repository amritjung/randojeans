<?php

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('My Profile — Rando Jeans')] class extends Component {
    // No form logic here at all — this page only ever displays data,
    // it never writes to the database. All editing happens on the
    // separate profile-edit page, keeping "view" and "change" cleanly
    // separated rather than one page doing both jobs.
};

?>

<div class="mx-auto max-w-md px-6 py-16">
    <div class="flex items-center justify-between mb-8">
        <h1 class="font-display text-3xl">My Profile</h1>
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
            {{-- ?: 'Not set' shows a clear placeholder rather than blank
                 space when the person hasn't filled this field in yet. --}}
            <p class="mt-1 text-bone">{{ auth()->user()->phone ?: 'Not set' }}</p>
        </div>

        <div>
            <p class="font-mono text-xs uppercase tracking-widest text-neutral-500">Delivery address</p>
            @if (auth()->user()->address_street)
                <p class="mt-1 text-bone">
                    {{ auth()->user()->address_street }}<br>
                    {{ auth()->user()->address_suburb }} {{ auth()->user()->address_state }} {{ auth()->user()->address_postcode }}
                </p>
            @else
                <p class="mt-1 text-neutral-500">Not set</p>
            @endif
        </div>
    </div>
</div>