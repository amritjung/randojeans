<?php

use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Checkout Cancelled — Rando Jeans')] class extends Component {
};

?>

<div class="mx-auto max-w-2xl px-6 py-24 text-center">
    <h1 class="font-display text-4xl mb-4">Checkout cancelled</h1>
    <p class="text-neutral-400">No payment was taken. Your cart is still saved.</p>
    <a href="{{ route('cart') }}" class="inline-block mt-8 font-mono text-xs uppercase tracking-widest text-copper-light hover:text-bone">
        Back to cart →
    </a>
</div>