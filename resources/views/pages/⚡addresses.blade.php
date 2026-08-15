<?php

use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Addresses — Rando Jeans')] class extends Component {
};

?>

<div class="mx-auto max-w-md px-6 py-16">
    <a href="{{ route('account') }}" class="font-mono text-xs uppercase tracking-widest text-neutral-500 hover:text-bone">
        ← Back to Account
    </a>

    <div class="flex items-center justify-between mt-6 mb-8">
        <h1 class="font-display text-3xl">Addresses</h1>
        <a href="{{ route('addresses.edit') }}" class="font-mono text-xs uppercase tracking-widest text-copper-light hover:text-bone">
            Edit →
        </a>
    </div>

    @if (auth()->user()->address_street)
        <p class="text-bone">
            {{ auth()->user()->address_street }}<br>
            {{ auth()->user()->address_suburb }} {{ auth()->user()->address_state }} {{ auth()->user()->address_postcode }}
        </p>
    @else
        <p class="text-neutral-500">No address saved yet.</p>
    @endif
</div>