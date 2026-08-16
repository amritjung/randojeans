<?php

use App\Models\CartItem;
use App\Models\Product;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('The Original Black Jean')] class extends Component {
    public Product $product;
    public ?int $selectedVariantId = null;
    public int $quantity = 1;
    public bool $justAdded = false;

    public bool $canReview = false;
    public ?int $reviewableOrderItemId = null;
    public int $reviewRating = 5;
    public string $reviewTitle = '';
    public string $reviewBody = '';
    public bool $reviewSubmitted = false;

    public \App\Models\SiteSetting $settings;

    // Separate from $justAdded — this tracks "does the cart have anything
    // in it at all right now," which should stay true even after switching
    // sizes, unlike $justAdded which is meant to reset (it's specifically
    // about "was THIS size just added").
    public bool $cartHasItems = false;

    public function mount(Product $product): void
    {
        $this->product = $product->load('variants');
        $this->selectedVariantId = $this->product->variants->first(fn ($v) => $v->inStock())?->id;

        $reviewableItem = $this->product->reviewableOrderItemFor(auth()->id());
        $this->canReview = $reviewableItem !== null;
        $this->reviewableOrderItemId = $reviewableItem?->id;

        $this->settings = \App\Models\SiteSetting::current();
    }

    #[Computed]
    public function selectedVariant()
    {
        return $this->product->variants->find($this->selectedVariantId);
    }

    public function addToCart(): void
    {
        if (! $this->selectedVariant || ! $this->selectedVariant->inStock()) {
            return;
        }

        $item = CartItem::firstOrNew([
            'session_id' => session()->getId(),
            'product_variant_id' => $this->selectedVariant->id,
        ]);

        $item->quantity = ($item->exists ? $item->quantity : 0) + $this->quantity;
        $item->save();

        $this->justAdded = true;
        $this->cartHasItems = true; // stays true from here on, regardless of size switching
        $this->dispatch('cart-updated');
    }
    public function updatedSelectedVariantId(): void
    {
            $this->justAdded = false;
    }

        public function submitReview(): void
    {
        $this->validate([
            'reviewRating' => 'required|integer|min:1|max:5',
            'reviewTitle' => 'nullable|string|max:255',
            'reviewBody' => 'required|string|min:10|max:2000',
        ]);

        \App\Models\Review::create([
            'product_id' => $this->product->id,
            'user_id' => auth()->id(),
            'order_item_id' => $this->reviewableOrderItemId,
            'rating' => $this->reviewRating,
            'title' => $this->reviewTitle,
            'body' => $this->reviewBody,
            'status' => 'pending',
        ]);

        $this->canReview = false;
        $this->reviewSubmitted = true;

        // Notify admins there's a review awaiting moderation, same pattern
        // as new return requests.
        $adminEmails = \App\Models\User::where('is_admin', true)->pluck('email');
        \Illuminate\Support\Facades\Mail::to($adminEmails)->send(new \App\Mail\NewReview($this->product, auth()->user()));
    }
};

?>

<div class="mx-auto max-w-5xl px-6 py-16 grid md:grid-cols-2 gap-16">

        <div
    x-data="{
        items: @js($product->images->map(fn ($i) => ['url' => $i->url(), 'alt' => $i->alt_text, 'type' => $i->type])),
        active: 0,
        lightbox: false,
        zoomed: false,
        next() { this.active = (this.active + 1) % this.items.length; this.zoomed = false },
        prev() { this.active = (this.active - 1 + this.items.length) % this.items.length; this.zoomed = false },
    }"
>
    @if ($product->images->isEmpty())
        <div class="aspect-[3/4] bg-neutral-900 border border-neutral-800 flex items-center justify-center">
            <span class="font-mono text-xs text-neutral-600 tracking-widest">PRODUCT IMAGE</span>
        </div>
    @else
        {{-- Main viewer --}}
        <div class="aspect-[3/4] bg-neutral-900 border border-neutral-800 relative overflow-hidden cursor-zoom-in"
             @click="lightbox = true">
            <template x-for="(item, index) in items" :key="index">
                <div x-show="active === index" class="w-full h-full">
                    <img x-show="item.type === 'image'" :src="item.url" :alt="item.alt" class="w-full h-full object-cover">
                    <video x-show="item.type === 'video'" :src="item.url" class="w-full h-full object-cover" muted loop autoplay playsinline></video>
                </div>
            </template>

            <button x-show="items.length > 1" @click.stop="prev()" class="absolute left-2 top-1/2 -translate-y-1/2 bg-ink/70 border border-neutral-700 w-9 h-9 flex items-center justify-center hover:border-copper">‹</button>
            <button x-show="items.length > 1" @click.stop="next()" class="absolute right-2 top-1/2 -translate-y-1/2 bg-ink/70 border border-neutral-700 w-9 h-9 flex items-center justify-center hover:border-copper">›</button>
        </div>

        {{-- Thumbnails --}}
        @if ($product->images->count() > 1)
            <div class="grid grid-cols-4 gap-2 mt-2">
                <template x-for="(item, index) in items" :key="index">
                    <button @click="active = index"
                            class="aspect-square bg-neutral-900 border overflow-hidden relative"
                            :class="active === index ? 'border-copper' : 'border-neutral-800'">
                        <img x-show="item.type === 'image'" :src="item.url" :alt="item.alt" class="w-full h-full object-cover">
                        <video x-show="item.type === 'video'" :src="item.url" class="w-full h-full object-cover" muted></video>
                        <span x-show="item.type === 'video'" class="absolute inset-0 flex items-center justify-center text-bone text-lg">▶</span>
                    </button>
                </template>
            </div>
        @endif

        {{-- Lightbox --}}
        <div x-show="lightbox" x-cloak
             @keydown.escape.window="lightbox = false"
             @keydown.arrow-right.window="next()"
             @keydown.arrow-left.window="prev()"
             class="fixed inset-0 bg-ink/95 z-50 flex items-center justify-center p-6"
             @click="lightbox = false">
            <template x-for="(item, index) in items" :key="index">
                <div x-show="active === index" @click.stop class="max-w-4xl max-h-full">
                    <img x-show="item.type === 'image'" :src="item.url" :alt="item.alt"
                         class="max-w-full max-h-[85vh] object-contain transition-transform duration-300 cursor-zoom-in"
                         :class="zoomed ? 'scale-150' : 'scale-100'"
                         @click.stop="zoomed = !zoomed">
                    <video x-show="item.type === 'video'" :src="item.url" class="max-w-full max-h-[85vh]" controls autoplay></video>
                </div>
            </template>

            <button @click.stop="prev()" class="absolute left-6 top-1/2 -translate-y-1/2 text-bone text-3xl hover:text-copper-light">‹</button>
            <button @click.stop="next()" class="absolute right-6 top-1/2 -translate-y-1/2 text-bone text-3xl hover:text-copper-light">›</button>
            <button @click.stop="lightbox = false" class="absolute top-6 right-6 text-bone text-2xl hover:text-copper-light">✕</button>
        </div>
    @endif
</div>

        <div>
            <h1 class="font-display text-4xl leading-tight">{{ $product->name }}</h1>
            <p class="mt-3 text-neutral-400">{{ $product->description }}</p>

            <p class="mt-6 font-mono text-2xl">
                @if ($product->isOnSale())
                    <span class="text-neutral-600 line-through text-lg mr-2">${{ number_format($product->base_price, 2) }}</span>
                    <span class="text-copper-light">${{ number_format($this->selectedVariant?->price() ?? $product->currentPrice(), 2) }} AUD</span>
                @else
                    <span class="text-copper-light">${{ number_format($this->selectedVariant?->price() ?? $product->base_price, 2) }} AUD</span>
                @endif
            </p>
            <div class="mt-4">
                <livewire:wishlist-heart :product="$product" :product-variant-id="$selectedVariantId" :key="'pdp-heart-'.$product->id" />
            </div>

            <div class="my-8 border-t border-dashed border-neutral-700"></div>

            <div>
                <p class="font-mono text-xs tracking-widest text-neutral-500 uppercase mb-3">Size</p>
                <div class="flex flex-wrap gap-2">
                    @foreach ($product->variants as $variant)
                        <button
                            wire:click="$set('selectedVariantId', {{ $variant->id }})"
                            @disabled(! $variant->inStock())
                            class="w-14 h-11 border font-mono text-sm transition
                                {{ $selectedVariantId === $variant->id
                                    ? 'border-copper text-copper-light'
                                    : 'border-neutral-700 text-neutral-400 hover:border-neutral-500' }}
                                {{ ! $variant->inStock() ? 'opacity-30 cursor-not-allowed line-through' : '' }}"
                        >
                            {{ $variant->size }}
                        </button>
                    @endforeach
                </div>
            </div>

            <div class="mt-8 flex gap-3">
                <button
                    wire:click="addToCart"
                    wire:loading.attr="disabled"
                    wire:target="addToCart"
                    class="flex-1 bg-copper hover:bg-copper-light text-ink font-medium py-4 transition disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <span wire:loading.remove wire:target="addToCart">
                        @if ($justAdded) Added ✓ @else Add to cart @endif
                    </span>
                    <span wire:loading wire:target="addToCart">Adding...</span>
                </button>
            
                {{-- Only appears once something's actually been added — skips
                     straight to checkout for someone who knows they want this and
                     doesn't need to review their cart first. Since it's a plain
                     link (not a Livewire action), it costs nothing extra to render
                     and just relies on the session cart already having the item. --}}
                     @if ($cartHasItems)
                     <a href="{{ route('cart') }}"
                        class="flex-1 flex items-center justify-center border border-copper text-copper-light hover:bg-copper hover:text-ink font-medium py-4 transition text-center">
                         View Cart →
                     </a>
                 @endif
            </div>

            <div class="mt-10 border border-neutral-800 p-5 font-mono text-xs text-neutral-500 leading-relaxed">
                {{ $product->story }}
            </div>
            
            <div class="mt-6 border border-neutral-800 p-5">
                <p class="font-mono text-xs tracking-widest text-neutral-500 uppercase mb-4">Fabric & Construction</p>
                <dl class="grid grid-cols-2 gap-y-3 text-sm">
                    <dt class="text-neutral-500">Fit</dt>
                    <dd class="text-bone">{{ $product->fit }}</dd>
            
                    <dt class="text-neutral-500">Composition</dt>
                    <dd class="text-bone">{{ $product->composition }}</dd>
            
                    <dt class="text-neutral-500">Weight</dt>
                    <dd class="text-bone">{{ $product->weight_oz }}oz</dd>
            
                    <dt class="text-neutral-500">Weave</dt>
                    <dd class="text-bone">{{ $product->weave }}</dd>
            
                    <dt class="text-neutral-500">Origin</dt>
                    <dd class="text-bone">{{ $product->origin }}</dd>
                </dl>
            </div>
            <div id="reviews" class="mt-16 pt-16 border-t border-neutral-800">
                <h2 class="font-display text-2xl mb-6">Reviews</h2>
            
                @if ($product->reviewCount() > 0)
                    <div class="flex items-center gap-3 mb-8">
                        <span class="font-mono text-2xl text-copper-light">{{ $product->averageRating() }}</span>
                        <span class="text-neutral-500 text-sm">out of 5 — {{ $product->reviewCount() }} {{ Str::plural('review', $product->reviewCount()) }}</span>
                    </div>
                @else
                    <p class="text-neutral-500 text-sm mb-8">No reviews yet.</p>
                @endif
            
                @if ($canReview)
                    <form wire:submit="submitReview" class="border border-neutral-800 p-6 mb-8 space-y-4">
                        <p class="font-mono text-xs uppercase tracking-widest text-neutral-500">Write a Review</p>
            
                        <div>
                            <label class="text-xs text-neutral-500">Rating</label>
                            <select wire:model="reviewRating" class="w-full mt-2 bg-ink border border-neutral-700 px-3 py-2 text-sm">
                                @for ($i = 5; $i >= 1; $i--)
                                    <option value="{{ $i }}">{{ $i }} star{{ $i > 1 ? 's' : '' }}</option>
                                @endfor
                            </select>
                        </div>
            
                        <div>
                            <label class="text-xs text-neutral-500">Title (optional)</label>
                            <input type="text" wire:model="reviewTitle" class="w-full mt-2 bg-transparent border border-neutral-700 px-3 py-2 text-sm outline-none">
                        </div>
            
                        <div>
                            <label class="text-xs text-neutral-500">Your review</label>
                            <textarea wire:model="reviewBody" rows="4" class="w-full mt-2 bg-transparent border border-neutral-700 px-3 py-2 text-sm outline-none"></textarea>
                            @error('reviewBody') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
            
                        <button type="submit" class="bg-copper hover:bg-copper-light text-ink text-sm font-medium px-6 py-2 transition">
                            Submit Review
                        </button>
                    </form>
                @elseif ($reviewSubmitted)
                    <p class="text-copper-light text-sm mb-8">Thanks — your review is awaiting approval.</p>
                @endif
            
                <div class="space-y-6">
                    @forelse ($product->approvedReviews as $review)
                        <div class="border-b border-neutral-800 pb-6">
                            <div class="flex items-center justify-between">
                                <span class="font-mono text-sm text-copper-light">{{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}</span>
                                <span class="font-mono text-xs text-neutral-500">{{ $review->created_at->format('d M Y') }}</span>
                            </div>
                            @if ($review->title)
                                <p class="mt-2 font-medium">{{ $review->title }}</p>
                            @endif
                            <p class="mt-2 text-neutral-400 text-sm">{{ $review->body }}</p>
                            <p class="mt-2 font-mono text-xs text-neutral-600">{{ $review->user->name }} — Verified Purchase</p>
                        </div>
                    @empty
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>