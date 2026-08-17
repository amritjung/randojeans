<?php

use App\Models\Product;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Shop — Rando Jeans')] class extends Component {
    use WithPagination;

    public \App\Models\SiteSetting $settings;

    public function mount(): void
    {
        $this->settings = \App\Models\SiteSetting::current();
    }

    public function with(): array
    {
        return [
            'products' => Product::where('status', 'active')
                ->orderBy('name')
                ->paginate(9),
        ];
    }
};

?>

<div class="relative min-h-screen overflow-hidden">
    @if ($settings->productPageBgUrl())
        <img src="{{ $settings->productPageBgUrl() }}" alt="" class="absolute inset-0 w-full h-full object-cover">
        <div class="absolute inset-0 bg-ink/70"></div>
    @endif

    <div class="relative z-10 mx-auto max-w-6xl px-6 py-16">
    {{-- <div class="max-w-xl mb-16">
        <h1 class="font-display text-4xl mb-4">Shop</h1>
        <p class="text-neutral-400 leading-relaxed">
            Every style we make, all built the same way: heavyweight denim, black, made to last.
            No seasonal drops, no seven washes of the same jean — just the styles worth keeping.
        </p>
    </div> --}}

    {{-- Tall, portrait-oriented cards rather than a boxy square grid —
         gives each product room to actually show a full-length garment
         shot once real photography exists, and reads as more premium/
         editorial than a small thumbnail grid. --}}
    {{-- Masonry-style abstract layout using CSS columns rather than a
     uniform grid — each item's aspect ratio cycles through a varied
     set, so heights differ organically rather than lining up in
     neat rows. break-inside-avoid stops an item from ever splitting
     awkwardly across two columns. --}}
<div class="columns-2 md:columns-3 gap-4 md:gap-6">
    @php
        // Cycled per item, not truly random — genuine randomness would
        // reshuffle on every page load/reload, which reads as broken
        // rather than intentional. This gives a stable, varied rhythm
        // instead: tall, then square-ish, then short-wide, then very
        // tall, repeating.
        $aspects = ['aspect-[2/3]', 'aspect-square', 'aspect-[4/3]', 'aspect-[9/16]', 'aspect-[3/4]'];
    @endphp

    @foreach ($products as $product)
        <a href="{{ route('product.show', $product) }}" class="group block break-inside-avoid mb-4 md:mb-6">
            <div class="{{ $aspects[$loop->index % count($aspects)] }} bg-neutral-900 border border-neutral-800 flex items-center justify-center overflow-hidden">
                @if ($product->primaryImage())
                    <img src="{{ $product->primaryImage()->url() }}" alt="{{ $product->primaryImage()->alt_text }}" class="w-full h-full object-cover group-hover:opacity-80 transition duration-300">
                @else
                    <span class="font-mono text-xs text-neutral-600 tracking-widest">PRODUCT IMAGE</span>
                @endif
            </div>
            <h2 class="font-display text-lg mt-4 group-hover:text-copper-light transition">{{ $product->name }}</h2>
            <p class="font-mono text-sm mt-1">
                @if ($product->isOnSale())
                    <span class="line-through text-neutral-600">${{ number_format($product->base_price, 2) }}</span>
                    <span class="text-copper-light ml-2">${{ number_format($product->sale_price, 2) }}</span>
                @else
                    <span class="text-neutral-500">${{ number_format($product->base_price, 2) }} AUD</span>
                @endif
            </p>
        </a>
    @endforeach
</div>

    @if($products->hasPages())
    <div class="mt-12 flex justify-center gap-2 font-mono text-xs">
        @if($products->onFirstPage())
            <span class="px-4 py-2 border border-neutral-800 text-neutral-700">← Prev</span>
        @else
            <button wire:click="previousPage" class="px-4 py-2 border border-neutral-700 text-neutral-400 hover:border-neutral-500 hover:text-bone transition">← Prev</button>
        @endif

        <span class="px-4 py-2 text-neutral-500">
            {{ $products->currentPage() }} / {{ $products->lastPage() }}
        </span>

        @if($products->hasMorePages())
            <button wire:click="nextPage" class="px-4 py-2 border border-neutral-700 text-neutral-400 hover:border-neutral-500 hover:text-bone transition">Next →</button>
        @else
            <span class="px-4 py-2 border border-neutral-800 text-neutral-700">Next →</span>
        @endif
    </div>
    @endif
</div>
</div>