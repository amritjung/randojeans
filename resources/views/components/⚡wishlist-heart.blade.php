<?php

use App\Models\Product;
use App\Models\WishlistItem;
use Livewire\Attributes\Reactive;
use Livewire\Component;

new class extends Component {
    public Product $product;

    // #[Reactive] means: if the parent page passes a new value for this
    // (e.g. the product page's $selectedVariantId changes when someone
    // clicks a different size), this component picks up the new value
    // automatically without needing a full page reload — so the heart
    // always reflects whichever size is currently selected right now.
    #[Reactive]
    public ?int $productVariantId = null;

    public bool $isWishlisted = false;

    public function mount(Product $product, ?int $productVariantId = null): void
    {
        $this->product = $product;
        $this->productVariantId = $productVariantId;
        $this->isWishlisted = $product->isWishlistedBy(auth()->id());
    }

    public function toggle(): void
    {
        if (! auth()->check()) {
            $this->redirect(route('login'), navigate: false);
            return;
        }

        if ($this->isWishlisted) {
            WishlistItem::where('user_id', auth()->id())
                ->where('product_id', $this->product->id)
                ->delete();
            $this->isWishlisted = false;
        } else {
            WishlistItem::firstOrCreate(
                ['user_id' => auth()->id(), 'product_id' => $this->product->id],
                ['product_variant_id' => $this->productVariantId]
            );
            $this->isWishlisted = true;
        }

        $this->dispatch('wishlist-updated');
    }
};

?>

<button
    wire:click="toggle"
    class="w-9 h-9 flex items-center justify-center border transition
        {{ $isWishlisted ? 'border-copper text-copper-light bg-copper/10' : 'border-neutral-700 text-neutral-400 hover:border-neutral-500' }}"
    aria-label="{{ $isWishlisted ? 'Remove from wishlist' : 'Add to wishlist' }}"
>
    <span class="text-base leading-none">{{ $isWishlisted ? '♥' : '♡' }}</span>
</button>