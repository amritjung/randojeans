<?php

use App\Models\Product;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Shop — Rando Jeans')] class extends Component {
    public $products;

    public function mount(): void
    {
        $this->products = Product::where('status', 'active')->orderBy('name')->get();
    }
};

?>

<div class="mx-auto max-w-5xl px-6 py-16">
    <h1 class="font-display text-4xl mb-2">Shop</h1>
    <p class="text-neutral-400 mb-12">Every style we make, all built the same way: heavyweight denim, black, made to last.</p>

    <div class="grid sm:grid-cols-2 md:grid-cols-3 gap-8">
        @foreach ($products as $product)
        <div class="relative">
            <a href="{{ route('product.show', $product) }}" class="group block">
                <div class="aspect-[3/4] bg-neutral-900 border border-neutral-800 flex items-center justify-center overflow-hidden">
                    @if ($product->primaryImage())
                        <img src="{{ $product->primaryImage()->url() }}" alt="{{ $product->primaryImage()->alt_text }}" class="w-full h-full object-cover group-hover:opacity-80 transition">
                    @else
                        <span class="font-mono text-xs text-neutral-600 tracking-widest">PRODUCT IMAGE</span>
                    @endif
                </div>
                <h2 class="font-display text-lg mt-4 group-hover:text-copper-light transition">{{ $product->name }}</h2>
                <p class="font-mono text-sm text-neutral-500 mt-1">${{ number_format($product->base_price, 2) }} AUD</p>
            </a>
        </a>
        <div class="absolute top-3 right-3">
            <livewire:wishlist-heart :product="$product" :key="'shop-heart-'.$product->id" />
        </div>
    </div>
        @endforeach
    </div>
</div>