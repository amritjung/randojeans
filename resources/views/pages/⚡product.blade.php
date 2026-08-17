<?php

use App\Models\CartItem;
use App\Models\Product;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('The Original Black Jean')] class extends Component {
    public Product $product;
    public ?int $selectedVariantId = null;
    public ?string $selectedWaist = null;
    public ?string $selectedInseam = null;
    public int $quantity = 1;
    public bool $justAdded = false;

    public bool $canReview = false;
    public ?int $reviewableOrderItemId = null;
    public int $reviewRating = 5;
    public string $reviewTitle = '';
    public string $reviewBody = '';
    public bool $reviewSubmitted = false;
    public int $reviewsPerPage = 5;

    public \App\Models\SiteSetting $settings;
    public bool $cartHasItems = false;
    public $relatedProducts;

    public function mount(Product $product): void
    {
        $this->product = $product->load('variants');
        $firstInStock = $this->product->variants->first(fn ($v) => $v->inStock());
        $this->selectedWaist  = $firstInStock?->size;
        $this->selectedInseam = $firstInStock?->inseam;
        $this->selectedVariantId = $firstInStock?->id;

        $reviewableItem = $this->product->reviewableOrderItemFor(auth()->id());
        $this->canReview = $reviewableItem !== null;
        $this->reviewableOrderItemId = $reviewableItem?->id;

        $this->settings = \App\Models\SiteSetting::current();

        $this->relatedProducts = Product::where('status', 'active')
            ->where('id', '!=', $this->product->id)
            ->limit(3)
            ->get();
    }

    #[Computed]
    public function selectedVariant()
    {
        return $this->product->variants->find($this->selectedVariantId);
    }

    #[Computed]
    public function availableWaists(): array
    {
        return $this->product->variants
            ->groupBy('size')
            ->map(fn ($group) => $group->some(fn ($v) => $v->inStock()))
            ->all();
    }

    #[Computed]
    public function availableInseams(): array
    {
        if (! $this->selectedWaist) return [];
        return $this->product->variants
            ->where('size', $this->selectedWaist)
            ->keyBy('inseam')
            ->map(fn ($v) => $v->inStock())
            ->all();
    }

    public function selectWaist(string $waist): void
    {
        $this->selectedWaist  = $waist;
        $this->selectedInseam = null;
        $this->selectedVariantId = null;
        $this->justAdded = false;
        $first = $this->product->variants
            ->where('size', $waist)
            ->first(fn ($v) => $v->inStock());
        if ($first) {
            $this->selectedInseam    = $first->inseam;
            $this->selectedVariantId = $first->id;
        }
    }

    public function selectInseam(string $inseam): void
    {
        $this->selectedInseam = $inseam;
        $variant = $this->product->variants
            ->where('size', $this->selectedWaist)
            ->where('inseam', $inseam)
            ->first();
        $this->selectedVariantId = $variant?->id;
        $this->justAdded = false;
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
        $this->cartHasItems = true;
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
            'reviewTitle'  => 'nullable|string|max:255',
            'reviewBody'   => 'required|string|min:10|max:2000',
        ]);

        \App\Models\Review::create([
            'product_id'    => $this->product->id,
            'user_id'       => auth()->id(),
            'order_item_id' => $this->reviewableOrderItemId,
            'rating'        => $this->reviewRating,
            'title'         => $this->reviewTitle,
            'body'          => $this->reviewBody,
            'status'        => 'pending',
        ]);

        $this->canReview = false;
        $this->reviewSubmitted = true;

        $adminEmails = \App\Models\User::where('is_admin', true)->pluck('email');
        \Illuminate\Support\Facades\Mail::to($adminEmails)->send(new \App\Mail\NewReview($this->product, auth()->user()));
    }

    public function loadMoreReviews(): void
    {
        $this->reviewsPerPage += 5;
    }
};

?>

@push('head-scripts')
<script>history.scrollRestoration = 'manual'; window.scrollTo(0, 0);</script>
@endpush

<div>

    {{-- ── FULL BLEED HERO GALLERY — no margin, sits behind transparent nav ── --}}
    <div class="relative w-full"
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
            <div class="h-screen bg-neutral-900 flex items-center justify-center">
                <span class="font-mono text-xs text-neutral-600 tracking-widest">PRODUCT IMAGE</span>
            </div>
        @else
            {{-- Main hero image — full viewport height --}}
            <div class="h-screen relative overflow-hidden" @dblclick="lightbox = true">
                <template x-for="(item, index) in items" :key="index">
                    <div x-show="active === index" class="absolute inset-0">
                        <img x-show="item.type === 'image'" :src="item.url" :alt="item.alt"
                             class="w-full h-full object-cover">
                        <video x-show="item.type === 'video'" :src="item.url"
                               class="w-full h-full object-cover" muted loop autoplay playsinline></video>
                    </div>
                </template>

                {{-- Gradient overlay so thumbnails are readable --}}
                <div class="absolute inset-x-0 bottom-0 h-40 bg-gradient-to-t from-ink/80 to-transparent pointer-events-none"></div>

                {{-- Prev/Next arrows --}}
                <button x-show="items.length > 1" @click.stop="prev()"
                        class="absolute left-6 top-1/2 -translate-y-1/2 w-10 h-10 flex items-center justify-center text-2xl text-white/60 hover:text-copper transition">‹</button>
                <button x-show="items.length > 1" @click.stop="next()"
                        class="absolute right-6 top-1/2 -translate-y-1/2 w-10 h-10 flex items-center justify-center text-2xl text-white/60 hover:text-copper transition">›</button>

                {{-- Thumbnail strip split above and below the prev button --}}
                @if ($product->images->count() > 1)
                <div class="absolute left-4 inset-y-0 flex flex-col justify-center gap-0 z-10 pointer-events-none">
                    {{-- Top half: images before the midpoint --}}
                    <div class="flex flex-col gap-2 pointer-events-auto" style="margin-bottom: 3.5rem">
                        <template x-for="(item, index) in items.slice(0, Math.floor(items.length / 2))" :key="index">
                            <button @click.stop="active = index"
                                    class="w-12 h-12 border overflow-hidden relative flex-shrink-0 transition rounded-full"
                                    :class="active === index ? 'border-copper' : 'border-white/20 hover:border-white/60'">
                                <img x-show="item.type === 'image'" :src="item.url" :alt="item.alt"
                                     class="w-full h-full object-cover rounded-full">
                                <video x-show="item.type === 'video'" :src="item.url"
                                       class="w-full h-full object-cover" muted></video>
                                <span x-show="item.type === 'video'"
                                      class="absolute inset-0 flex items-center justify-center text-bone text-sm">▶</span>
                            </button>
                        </template>
                    </div>
                    {{-- Gap for the prev button (40px button + spacing) --}}
                    <div style="height: 3.5rem"></div>
                    {{-- Bottom half: images from midpoint onwards --}}
                    <div class="flex flex-col gap-2 pointer-events-auto" style="margin-top: 0.5rem">
                        <template x-for="(item, index) in items.slice(Math.floor(items.length / 2))" :key="index">
                            <button @click.stop="active = items.indexOf(item)"
                                    class="w-12 h-12 border overflow-hidden relative flex-shrink-0 transition rounded-full"
                                    :class="active === items.indexOf(item) ? 'border-copper' : 'border-white/20 hover:border-white/60'">
                                <img x-show="item.type === 'image'" :src="item.url" :alt="item.alt"
                                     class="w-full h-full object-cover rounded-full">
                                <video x-show="item.type === 'video'" :src="item.url"
                                       class="w-full h-full object-cover" muted></video>
                                <span x-show="item.type === 'video'"
                                      class="absolute inset-0 flex items-center justify-center text-bone text-sm">▶</span>
                            </button>
                        </template>
                    </div>
                </div>
                @endif
            </div>

            {{-- Lightbox with natural pan/zoom --}}
            <div x-show="lightbox" x-cloak
                 @keydown.escape.window="lightbox = false; zoom = 1; tx = 0; ty = 0"
                 @keydown.arrow-right.window="next()"
                 @keydown.arrow-left.window="prev()"
                 x-data="{
                     zoom: 1, tx: 0, ty: 0,
                     dragging: false, startX: 0, startY: 0, lastTx: 0, lastTy: 0,
                     onWheel(e) {
                         e.preventDefault();
                         let delta = e.deltaY > 0 ? -0.15 : 0.15;
                         this.zoom = Math.min(5, Math.max(1, this.zoom + delta));
                         if (this.zoom === 1) { this.tx = 0; this.ty = 0; }
                     },
                     startDrag(e) {
                         if (this.zoom <= 1) return;
                         this.dragging = true;
                         this.startX = e.clientX - this.tx;
                         this.startY = e.clientY - this.ty;
                     },
                     onDrag(e) {
                         if (!this.dragging) return;
                         this.tx = e.clientX - this.startX;
                         this.ty = e.clientY - this.startY;
                     },
                     stopDrag() { this.dragging = false; },
                     clickImg() {
                         if (this.zoom === 1) { this.zoom = 2.5; }
                         else { this.zoom = 1; this.tx = 0; this.ty = 0; }
                     },
                     reset() { this.zoom = 1; this.tx = 0; this.ty = 0; }
                 }"
                 class="fixed inset-0 bg-ink/95 z-50 flex items-center justify-center"
                 @wheel.prevent="onWheel($event)">

                <template x-for="(item, index) in items" :key="index">
                    <div x-show="active === index"
                         @click.stop
                         class="w-full h-full flex items-center justify-center overflow-hidden"
                         @mousedown.stop="startDrag($event)"
                         @mousemove.stop="onDrag($event)"
                         @mouseup.stop="stopDrag()"
                         @mouseleave="stopDrag()">
                        <img x-show="item.type === 'image'"
                             :src="item.url" :alt="item.alt"
                             :style="`transform: scale(${zoom}) translate(${tx/zoom}px, ${ty/zoom}px); transition: ${dragging ? 'none' : 'transform 0.2s ease'}; cursor: ${zoom > 1 ? (dragging ? 'grabbing' : 'grab') : 'zoom-in'}`"
                             class="w-screen h-screen object-cover select-none"
                             @dblclick.stop="clickImg()"
                             @click.stop
                             draggable="false">
                        <video x-show="item.type === 'video'"
                               :src="item.url"
                               class="fixed inset-0 w-full h-full object-contain bg-ink" controls autoplay
                               @click.stop></video>
                    </div>
                </template>

                <button @click.stop="prev(); reset()" class="absolute left-6 top-1/2 -translate-y-1/2 text-white/60 hover:text-copper text-3xl transition">‹</button>
                <button @click.stop="next(); reset()" class="absolute right-6 top-1/2 -translate-y-1/2 text-white/60 hover:text-copper text-3xl transition">›</button>
                <button @click.stop="lightbox = false; reset()" class="absolute top-6 right-6 text-white/60 hover:text-copper text-xl transition">✕</button>
                <span class="absolute top-16 right-6 font-mono text-xs text-white/30 pointer-events-none select-none" x-show="zoom === 1" x-transition.opacity>double-tap to zoom · scroll to zoom · drag to pan</span>
            </div>
        @endif
    </div>

    {{-- ── CONTENT BELOW GALLERY ───────────────────────────────────────── --}}
    <div class="mx-auto max-w-7xl px-6 py-16">

    {{-- ── 2-COL GRID BELOW GALLERY ───────────────────────────────────── --}}
    <div class="grid md:grid-cols-2 gap-16">

        {{-- LEFT COL: name, price, colour, sizes, add to cart ─────────── --}}
        <div>
            <h1 class="font-display text-4xl leading-tight">{{ $product->name }}</h1>

            <p class="mt-4 font-mono text-2xl">
                @if ($product->isOnSale())
                    <span class="text-neutral-600 line-through text-lg mr-2">${{ number_format($product->base_price, 2) }}</span>
                    <span class="text-copper-light">${{ number_format($this->selectedVariant?->price() ?? $product->currentPrice(), 2) }} AUD</span>
                @else
                    <span class="text-copper-light">${{ number_format($this->selectedVariant?->price() ?? $product->base_price, 2) }} AUD</span>
                @endif
            </p>
            <div class="mt-3">
                <livewire:wishlist-heart :product="$product" :product-variant-id="$selectedVariantId" :key="'pdp-heart-'.$product->id" />
            </div>
            

            {{-- Colour --}}
            @if($product->colors && count($product->colors))
            <div class="mt-6">
                <p class="font-mono text-xs tracking-widest text-neutral-500 uppercase mb-3">Colour</p>
                <div class="flex gap-3 flex-wrap">
                    @foreach($product->colors as $color)
                    <div class="flex items-center gap-2">
                        <span class="w-5 h-5 rounded-full border border-neutral-700"
                              style="background-color: {{ $color['hex'] }}"></span>
                        <span class="font-mono text-xs text-neutral-400">{{ $color['name'] }}</span>
                    </div>
                    @endforeach
                </div>
                @if($product->model_note)
                <p class="font-mono text-xs text-neutral-600 mt-2 leading-relaxed">{{ $product->model_note }}</p>
                @endif
            </div>
            @endif

            <div class="my-6 border-t border-dashed border-neutral-700"></div>

            {{-- Waist --}}
            <div>
                <p class="font-mono text-xs tracking-widest text-neutral-500 uppercase mb-3">Waist</p>
                <div class="flex flex-wrap gap-2">
                    @foreach ($this->availableWaists as $waist => $hasStock)
                        <button
                            wire:click="selectWaist('{{ $waist }}')"
                            class="w-14 h-11 border font-mono text-sm transition {{ ! $hasStock ? 'opacity-30 cursor-not-allowed line-through' : '' }}"
                            :class="'{{ $selectedWaist }}' === '{{ $waist }}' ? 'border-copper text-copper-light' : 'border-neutral-700 text-neutral-400 hover:border-neutral-500'"
                            @disabled(! $hasStock)
                        >
                            {{ $waist }}
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- Length --}}
            @if ($selectedWaist && count($this->availableInseams))
            <div class="mt-5">
                <p class="font-mono text-xs tracking-widest text-neutral-500 uppercase mb-3">Length</p>
                <div class="flex flex-wrap gap-2">
                    @foreach ($this->availableInseams as $inseam => $inStock)
                        <button
                            wire:click="selectInseam('{{ $inseam }}')"
                            class="w-14 h-11 border font-mono text-sm transition {{ ! $inStock ? 'opacity-30 cursor-not-allowed line-through' : '' }}"
                            :class="'{{ $selectedInseam }}' === '{{ $inseam }}' ? 'border-copper text-copper-light' : 'border-neutral-700 text-neutral-400 hover:border-neutral-500'"
                            @disabled(! $inStock)
                        >
                            {{ $inseam }}
                        </button>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Add to cart --}}
            <div class="mt-8 flex gap-3">
                <button
                    wire:click="addToCart"
                    wire:loading.attr="disabled"
                    wire:target="addToCart"
                    @disabled(! $selectedVariantId)
                    class="flex-1 bg-copper hover:bg-copper-light text-ink font-medium py-4 transition disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <span wire:loading.remove wire:target="addToCart">
                        @if ($justAdded) Added ✓ @else Add to cart @endif
                    </span>
                    <span wire:loading wire:target="addToCart">Adding...</span>
                </button>

                @if ($cartHasItems)
                    <a href="{{ route('cart') }}"
                       class="flex-1 flex items-center justify-center border border-copper text-copper-light hover:bg-copper hover:text-ink font-medium py-4 transition text-center">
                        View Cart →
                    </a>
                @endif
            </div>

            

            {{-- Shipping & returns --}}
            @if($product->shipping_carrier || $product->returns_days)
            <div class="mt-6 flex gap-6">
                @if($product->shipping_carrier)
                <span class="font-mono text-xs text-neutral-500">{{ $product->shipping_carrier }}@if($product->shipping_note) — {{ $product->shipping_note }}@endif</span>
                @endif
                @if($product->returns_days)
                <span class="font-mono text-xs text-neutral-500">{{ $product->returns_days }} day returns</span>
                @endif
            </div>
            @endif
        </div>

        {{-- RIGHT COL: price, wishlist, size & fit ───────────────────────── --}}
        <div>
            @if($product->size_fit_body || $product->size_fit_chips || $product->size_fit_tag)
            <div>
                <p class="font-mono text-xs tracking-widest text-neutral-500 uppercase mb-4">Size & Fit</p>
                @if($product->size_fit_tag)
                <span class="inline-block font-mono text-xs uppercase tracking-widest border border-neutral-700 px-2 py-1 text-neutral-500 mb-3">{{ $product->size_fit_tag }}</span>
                @endif
                @if($product->size_fit_chips && count($product->size_fit_chips))
                <div class="flex flex-wrap gap-2 mb-4">
                    @foreach($product->size_fit_chips as $chip)
                    <span class="font-mono text-xs border border-neutral-800 px-3 py-1 text-neutral-500">{{ $chip['value'] ?? $chip }}</span>
                    @endforeach
                </div>
                @endif
                @if($product->size_fit_body)
                <div class="font-mono text-xs text-neutral-400 leading-relaxed prose prose-invert prose-sm max-w-none">{!! $product->size_fit_body !!}</div>
                @endif
            </div>
            @endif
        </div>
    </div>

    {{-- ── FULL WIDTH BELOW GRID ───────────────────────────────────────── --}}
    <div class="mt-16 pt-4" x-data="{ open: null }">

        {{-- The Story --}}
        @if($product->story)
        <div class="mb-12">
            <p class="font-mono text-xs tracking-widest text-neutral-500 uppercase mb-3">The Story</p>
            <p class="font-mono text-xs text-neutral-400 leading-relaxed">{{ $product->story }}</p>
        </div>
        @endif

        {{-- Accordions --}}
        <div class="border-t border-neutral-800">

{{-- Description & Care --}}
                @if($product->description_body || $product->description_bullets || $product->washing_instructions)
                <div class="border-b border-neutral-800">
                    <button @click="open = open === 'care' ? null : 'care'"
                            class="w-full flex justify-between items-center py-4 font-mono text-xs tracking-widest text-neutral-400 uppercase hover:text-bone transition">
                        <span>Description & Care</span>
                        <span class="text-lg transition-transform duration-200" :class="open === 'care' ? 'rotate-45' : ''">+</span>
                    </button>
                    <div x-show="open === 'care'" x-collapse class="pb-5 space-y-4">
                        @if($product->description_tag)
                        <span class="inline-block font-mono text-xs uppercase tracking-widest border border-neutral-700 px-2 py-1 text-neutral-500">{{ $product->description_tag }}</span>
                        @endif
                        @if($product->description_body)
                        <div class="font-mono text-xs text-neutral-400 leading-relaxed prose prose-invert prose-sm max-w-none">{!! $product->description_body !!}</div>
                        @endif
                        @if($product->description_bullets && count($product->description_bullets))
                        <ul class="space-y-1">
                            @foreach($product->description_bullets as $bullet)
                            <li class="font-mono text-xs text-neutral-400 flex gap-2"><span class="text-neutral-700">—</span>{{ $bullet['value'] ?? $bullet }}</li>
                            @endforeach
                        </ul>
                        @endif
                        @if($product->washing_instructions && count($product->washing_instructions))
                        @php
                        $svgMap = [
                            'machine_wash_30' => ['label' => 'Machine wash 30°C', 'svg' => '<rect x="4" y="8" width="24" height="18" rx="2" stroke="currentColor" stroke-width="1.5" fill="none"/><path d="M4 14h24" stroke="currentColor" stroke-width="1.5"/><text x="16" y="24" text-anchor="middle" font-size="8" font-weight="500" fill="currentColor" font-family="sans-serif">30°</text>'],
                            'no_bleach'       => ['label' => 'Do not bleach',      'svg' => '<path d="M7 26 L16 8 L25 26 Z" stroke="currentColor" stroke-width="1.5" fill="none" stroke-linejoin="round"/><line x1="9" y1="10" x2="23" y2="24" stroke="currentColor" stroke-width="1.5"/><line x1="23" y1="10" x2="9" y2="24" stroke="currentColor" stroke-width="1.5"/>'],
                            'tumble_dry_low'  => ['label' => 'Tumble dry low',     'svg' => '<circle cx="16" cy="17" r="10" stroke="currentColor" stroke-width="1.5" fill="none"/><circle cx="16" cy="17" r="5" stroke="currentColor" stroke-width="1.5" fill="none"/><circle cx="16" cy="9" r="1.5" fill="currentColor"/>'],
                            'no_steam_iron'   => ['label' => 'No steam iron',      'svg' => '<path d="M5 18 Q8 13 28 13 L28 20 Q20 22 5 20 Z" stroke="currentColor" stroke-width="1.5" fill="none" stroke-linejoin="round"/><line x1="6" y1="11" x2="26" y2="22" stroke="currentColor" stroke-width="1.5"/><line x1="6" y1="22" x2="26" y2="11" stroke="currentColor" stroke-width="1.5"/>'],
                            'dry_clean'       => ['label' => 'Dry clean',          'svg' => '<circle cx="16" cy="16" r="11" stroke="currentColor" stroke-width="1.5" fill="none"/><text x="16" y="21" text-anchor="middle" font-size="11" font-weight="500" fill="currentColor" font-family="sans-serif">P</text>'],
                            'hang_dry'        => ['label' => 'Hang to dry',        'svg' => '<rect x="5" y="8" width="22" height="16" rx="1.5" stroke="currentColor" stroke-width="1.5" fill="none"/><line x1="5" y1="8" x2="27" y2="24" stroke="currentColor" stroke-width="1.5"/><line x1="5" y1="24" x2="27" y2="8" stroke="currentColor" stroke-width="1.5"/>'],
                            'do_not_wash'     => ['label' => 'Do not wash',        'svg' => '<rect x="4" y="8" width="24" height="18" rx="2" stroke="currentColor" stroke-width="1.5" fill="none"/><line x1="7" y1="10" x2="25" y2="24" stroke="currentColor" stroke-width="1.5"/><line x1="7" y1="24" x2="25" y2="10" stroke="currentColor" stroke-width="1.5"/>'],
                            'iron_low'        => ['label' => 'Iron low heat',      'svg' => '<path d="M5 18 Q8 13 28 13 L28 20 Q20 22 5 20 Z" stroke="currentColor" stroke-width="1.5" fill="none" stroke-linejoin="round"/><circle cx="12" cy="23" r="1" fill="currentColor"/>'],
                            'iron_medium'     => ['label' => 'Iron medium heat',   'svg' => '<path d="M5 18 Q8 13 28 13 L28 20 Q20 22 5 20 Z" stroke="currentColor" stroke-width="1.5" fill="none" stroke-linejoin="round"/><circle cx="10" cy="23" r="1" fill="currentColor"/><circle cx="14" cy="23" r="1" fill="currentColor"/>'],
                            'do_not_tumble'   => ['label' => 'Do not tumble dry',  'svg' => '<circle cx="16" cy="17" r="10" stroke="currentColor" stroke-width="1.5" fill="none"/><line x1="9" y1="10" x2="23" y2="24" stroke="currentColor" stroke-width="1.5"/><line x1="23" y1="10" x2="9" y2="24" stroke="currentColor" stroke-width="1.5"/>'],
                        ];
                        @endphp
                        <div class="mt-2">
                            <p class="font-mono text-xs tracking-widest text-neutral-600 uppercase mb-3">Washing instructions</p>
                            <div class="flex gap-4 flex-wrap">
                                @foreach($product->washing_instructions as $item)
                                @php $sym = $svgMap[$item['symbol']] ?? null; @endphp
                                @if($sym)
                                <div class="group relative flex flex-col items-center cursor-default" role="img" aria-label="{{ $item['label'] ?? $sym['label'] }}">
                                    <div class="w-12 h-12 border border-neutral-800 group-hover:border-neutral-600 flex items-center justify-center text-neutral-500 group-hover:text-neutral-300 transition">
                                        <svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">{!! $sym['svg'] !!}</svg>
                                    </div>
                                    <span class="absolute bottom-full mb-2 left-1/2 -translate-x-1/2 bg-neutral-900 border border-neutral-700 text-neutral-300 font-mono text-xs px-2 py-1 whitespace-nowrap opacity-0 group-hover:opacity-100 transition pointer-events-none z-10">{{ $item['label'] ?? $sym['label'] }}</span>
                                </div>
                                @endif
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
                @endif

                {{-- Custom sections --}}
                @if($product->custom_sections && count($product->custom_sections))
                @foreach($product->custom_sections as $i => $section)
                @if(!empty($section['title']) && !empty($section['body']))
                <div class="border-b border-neutral-800">
                    <button @click="open = open === 'custom{{$i}}' ? null : 'custom{{$i}}'"
                            class="w-full flex justify-between items-center py-4 font-mono text-xs tracking-widest text-neutral-400 uppercase hover:text-bone transition">
                        <span>{{ $section['title'] }}</span>
                        <span class="text-lg transition-transform duration-200" :class="open === 'custom{{$i}}' ? 'rotate-45' : ''">+</span>
                    </button>
                    <div x-show="open === 'custom{{$i}}'" x-collapse class="pb-5 space-y-3">
                        @if(!empty($section['tag_label']))
                        <span class="inline-block font-mono text-xs uppercase tracking-widest border border-neutral-700 px-2 py-1 text-neutral-500">{{ $section['tag_label'] }}</span>
                        @endif
                        <div class="font-mono text-xs text-neutral-400 leading-relaxed prose prose-invert prose-sm max-w-none">{!! $section['body'] !!}</div>
                    </div>
                </div>
                @endif
                @endforeach
                @endif

                {{-- Sustainability --}}
                @if($product->sustainability_message || $product->manufacturing_origins)
                <div class="border-b border-neutral-800">
                    <button @click="open = open === 'sustain' ? null : 'sustain'"
                            class="w-full flex justify-between items-center py-4 font-mono text-xs tracking-widest text-neutral-400 uppercase hover:text-bone transition">
                        <span>Sustainability & Traceability</span>
                        <span class="text-lg transition-transform duration-200" :class="open === 'sustain' ? 'rotate-45' : ''">+</span>
                    </button>
                    <div x-show="open === 'sustain'" x-collapse class="pb-5 space-y-3">
                        @if($product->sustainability_tag)
                        <span class="inline-block font-mono text-xs uppercase tracking-widest border border-neutral-700 px-2 py-1 text-neutral-500">{{ $product->sustainability_tag }}</span>
                        @endif
                        @if($product->sustainability_message)
                        <p class="font-mono text-xs text-neutral-400">{{ $product->sustainability_message }}</p>
                        @endif
                        @if($product->manufacturing_origins && count($product->manufacturing_origins))
                        <div class="mt-2 space-y-1">
                            @foreach($product->manufacturing_origins as $origin)
                            <div class="flex justify-between font-mono text-xs border-b border-neutral-800 pb-1">
                                <span class="text-neutral-500">{{ $origin['stage'] }}</span>
                                <span class="text-neutral-400">{{ $origin['location'] }}</span>
                            </div>
                            @endforeach
                        </div>
                        @endif
                    </div>
                </div>
                @endif

        </div>
    </div>

    {{-- ── REVIEWS ─────────────────────────────────────────────────────── --}}
    <div id="reviews" class="mt-16 pt-16">
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
                <button type="submit" class="bg-copper hover:bg-copper-light text-ink text-sm font-medium px-6 py-2 transition">Submit Review</button>
            </form>
        @elseif ($reviewSubmitted)
            <p class="text-copper-light text-sm mb-8">Thanks — your review is awaiting approval.</p>
        @endif

        <div class="space-y-6">
            @php $pagedReviews = $product->approvedReviews()->take($reviewsPerPage)->get(); @endphp
            @forelse ($pagedReviews as $review)
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

            @if($product->reviewCount() > $reviewsPerPage)
            <button wire:click="loadMoreReviews"
                    class="mt-6 w-full border border-neutral-700 text-neutral-400 hover:border-neutral-500 hover:text-bone font-mono text-xs uppercase tracking-widest py-3 transition">
                Load more reviews ({{ $product->reviewCount() - $reviewsPerPage }} remaining)
            </button>
            @endif
        </div>
    </div>

    {{-- ── YOU MAY ALSO LIKE ───────────────────────────────────────────── --}}
    @if ($relatedProducts->isNotEmpty())
        <div class="mt-24 pt-16 border-t border-neutral-800">
            <h2 class="font-display text-2xl text-center mb-10">You May Also Like</h2>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-6">
                @foreach ($relatedProducts as $related)
                    <a href="{{ route('product.show', $related) }}" class="group block">
                        <div class="aspect-[3/3] bg-neutral-900 border border-neutral-800 flex items-center justify-center overflow-hidden">
                            @if ($related->primaryImage())
                                <img src="{{ $related->primaryImage()->url() }}" alt="{{ $related->name }}" class="w-full h-full object-cover group-hover:opacity-80 transition">
                            @else
                                <span class="font-mono text-xs text-neutral-600 tracking-widest">PRODUCT IMAGE</span>
                            @endif
                        </div>
                        <h3 class="font-display text-lg mt-4 group-hover:text-copper-light transition">{{ $related->name }}</h3>
                        <p class="font-mono text-sm text-neutral-500 mt-1">${{ number_format($related->base_price, 2) }} AUD</p>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    </div>

</div>
