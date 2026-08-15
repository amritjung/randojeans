<?php

use App\Models\WishlistItem;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;
use App\Models\CartItem;

new #[Title('Your Wishlist — Rando Jeans')] class extends Component {
    public $items;


    public function mount(): void
    {
        $this->loadItems();
    }

    // Listens for the same 'wishlist-updated' event the heart button
    // dispatches — so removing an item from this page's own remove
    // button refreshes the list without a full page reload.
    #[On('wishlist-updated')]
    public function loadItems(): void
    {
        $this->items = WishlistItem::where('user_id', auth()->id())
            ->with('product.variants', 'productVariant')
            ->latest()
            ->get();
    }

    public function remove(int $productId): void
    {
        WishlistItem::where('user_id', auth()->id())
            ->where('product_id', $productId)
            ->delete();

        $this->loadItems();
        $this->dispatch('wishlist-updated');
    }

    public function updateVariant(int $wishlistItemId, int $variantId): void
    {
        WishlistItem::where('id', $wishlistItemId)
            ->where('user_id', auth()->id())
            ->update(['product_variant_id' => $variantId]);

        $this->loadItems();
    }

    public function addToCart(int $wishlistItemId): void
    {
        $wishlistItem = $this->items->firstWhere('id', $wishlistItemId);

        if (! $wishlistItem || ! $wishlistItem->product_variant_id) {
            return;
        }

        $variant = $wishlistItem->productVariant;

        if (! $variant || ! $variant->inStock()) {
            return;
        }

        $item = CartItem::firstOrNew([
            'session_id' => session()->getId(),
            'product_variant_id' => $variant->id,
        ]);

        $item->quantity = ($item->exists ? $item->quantity : 0) + 1;
        $item->save();

        $wishlistItem->delete();

        $this->dispatch('cart-updated');
        $this->dispatch('wishlist-updated');
        $this->loadItems();

        // Dispatches a plain browser event with the message as a payload —
        // Alpine (in the layout, see below) picks this up and handles the
        // entire show/auto-hide/dismiss cycle itself, with zero further
        // server round-trips needed for the toast's timing.
        $this->dispatch('toast', message: 'Item moved to cart');
    }
};

?>

<div class="mx-auto max-w-5xl px-6 py-16">
    
    <h1 class="font-display text-4xl mb-10">Your Wishlist</h1>

    @if ($items->isEmpty())
        <div class="border border-neutral-800 p-10 text-center">
            <p class="text-neutral-400">Nothing saved yet.</p>
            <a href="{{ route('shop') }}" class="inline-block mt-4 font-mono text-xs uppercase tracking-widest text-copper-light hover:text-bone">
                Browse the shop →
            </a>
        </div>
    @else
        <div class="grid sm:grid-cols-2 md:grid-cols-3 gap-8">
            @foreach ($items as $item)
                <div wire:key="wishlist-{{ $item->id }}">
                    <a href="{{ route('product.show', $item->product) }}" class="group block">
                        <div class="aspect-[3/4] bg-neutral-900 border border-neutral-800 flex items-center justify-center overflow-hidden">
                            @if ($item->product->primaryImage())
                                <img src="{{ $item->product->primaryImage()->url() }}" alt="{{ $item->product->name }}" class="w-full h-full object-cover group-hover:opacity-80 transition">
                            @else
                                <span class="font-mono text-xs text-neutral-600 tracking-widest">PRODUCT IMAGE</span>
                            @endif
                        </div>
                        <h2 class="font-display text-lg mt-4 group-hover:text-copper-light transition">{{ $item->product->name }}</h2>
                        <p class="font-mono text-sm text-neutral-500 mt-1">${{ number_format($item->product->base_price, 2) }} AUD</p>
                    </a>
                    <div class="mt-2">
                        <select wire:change="updateVariant({{ $item->id }}, $event.target.value)"
                                class="w-full bg-ink border border-neutral-700 text-xs px-2 py-1.5">
                            <option value="">No size selected</option>
                            @foreach ($item->product->variants as $variant)
                                <option value="{{ $variant->id }}" {{ $item->product_variant_id === $variant->id ? 'selected' : '' }}>
                                    {{ $variant->label() }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mt-2 flex gap-2">
                        @if ($item->product_variant_id)
                        <button
                        wire:click="addToCart({{ $item->id }})"
                        class="flex-1 bg-copper hover:bg-copper-light text-ink text-xs font-medium py-2 transition"
                    >
                        Add to Cart
                    </button>
                        @else
                            <p class="flex-1 text-center text-xs text-neutral-600 py-2 border border-neutral-800">
                                Select a size to add
                            </p>
                        @endif
                    
                        <button wire:click="remove({{ $item->product->id }})" class="font-mono text-xs text-neutral-500 hover:text-red-400 uppercase tracking-widest px-2">
                            Remove
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>