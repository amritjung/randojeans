<?php

use App\Models\CartItem;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Your Cart — Rando Jeans')] class extends Component {
    public $items;

    public function mount(): void
    {
        $this->loadItems();
    }

    public function loadItems(): void
    {
        $this->items = CartItem::currentItems();
    }

    public function updateQuantity(int $itemId, int $quantity): void
    {
        if ($quantity < 1) {
            $this->removeItem($itemId);
            return;
        }

        CartItem::where('id', $itemId)
            ->where('session_id', session()->getId())
            ->update(['quantity' => $quantity]);

        $this->loadItems();
        $this->dispatch('cart-updated');
    }

    public function removeItem(int $itemId): void
    {
        CartItem::where('id', $itemId)
            ->where('session_id', session()->getId())
            ->delete();

        $this->loadItems();
        $this->dispatch('cart-updated');
    }

    public function getSubtotalProperty(): float
    {
        return $this->items->sum(fn ($item) => $item->lineTotal());
    }
};

?>

<div class="mx-auto max-w-3xl px-6 py-16">
    <h1 class="font-display text-4xl mb-10">Your Cart</h1>

    @if ($items->isEmpty())
        <div class="border border-neutral-800 p-10 text-center">
            <p class="text-neutral-400">Your cart is empty.</p>
            <a href="{{ route('shop') }}" class="inline-block mt-4 font-mono text-xs uppercase tracking-widest text-copper-light hover:text-bone">
                Continue shopping →
            </a>
        </div>
    @else
        <div class="space-y-6">
            @foreach ($items as $item)
                <div wire:key="cart-item-{{ $item->id }}" class="flex gap-6 border-b border-neutral-800 pb-6">
                    <div class="w-24 h-32 bg-neutral-900 border border-neutral-800 flex-shrink-0 flex items-center justify-center">
                        @if ($item->productVariant->product->images->isNotEmpty())
                            <img src="{{ $item->productVariant->product->primaryImage()->url() }}"
                                 alt="{{ $item->productVariant->product->name }}"
                                 class="w-full h-full object-cover">
                        @else
                            <span class="font-mono text-[10px] text-neutral-600">IMAGE</span>
                        @endif
                    </div>

                    <div class="flex-1">
                        <p class="font-display text-lg">{{ $item->productVariant->product->name }}</p>
                        <p class="font-mono text-xs text-neutral-500 mt-1">{{ $item->productVariant->label() }}</p>

                        <div class="flex items-center gap-3 mt-4">
                            <button wire:click="updateQuantity({{ $item->id }}, {{ $item->quantity - 1 }})"
                                    class="w-8 h-8 border border-neutral-700 hover:border-copper text-sm">−</button>
                            <span class="font-mono text-sm w-6 text-center">{{ $item->quantity }}</span>
                            <button wire:click="updateQuantity({{ $item->id }}, {{ $item->quantity + 1 }})"
                                    class="w-8 h-8 border border-neutral-700 hover:border-copper text-sm">+</button>

                            <button wire:click="removeItem({{ $item->id }})"
                                    class="ml-4 font-mono text-xs text-neutral-500 hover:text-red-400 uppercase tracking-widest">
                                Remove
                            </button>
                        </div>
                    </div>

                    <p class="font-mono text-sm text-copper-light">
                        ${{ number_format($item->lineTotal(), 2) }}
                    </p>
                </div>
            @endforeach
        </div>

        <div class="mt-10 flex items-center justify-between border-t border-dashed border-neutral-700 pt-6">
            <span class="font-mono text-xs uppercase tracking-widest text-neutral-500">Subtotal (GST incl.)</span>
            <span class="font-display text-2xl">${{ number_format($this->subtotal, 2) }} AUD</span>
        </div>

        <a href="#"
           class="block text-center mt-8 bg-copper hover:bg-copper-light text-ink font-medium py-4 transition">
            Checkout
        </a>
    @endif
</div>