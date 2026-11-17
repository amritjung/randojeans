<?php

use App\Models\Product;
use App\Models\ProductVariant;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Shop — Rando Jeans')] class extends Component {
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $size = '';

    #[Url]
    public string $priceRange = '';

    #[Url]
    public string $sortBy = 'name';

    public \App\Models\SiteSetting $settings;

    public function mount(): void
    {
        $this->settings = \App\Models\SiteSetting::current();
    }

    public function updatedSearch(): void { $this->resetPage(); }
    public function updatedSize(): void { $this->resetPage(); }
    public function updatedPriceRange(): void { $this->resetPage(); }
    public function updatedSortBy(): void { $this->resetPage(); }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->size = '';
        $this->priceRange = '';
        $this->sortBy = 'name';
        $this->resetPage();
    }

    public function with(): array
    {
        $query = Product::where('status', 'active')
            ->with(['variants', 'images']);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->size) {
            $query->whereHas('variants', function ($q) {
                $q->where('size', $this->size)->where('stock_quantity', '>', 0);
            });
        }

        if ($this->priceRange) {
            [$min, $max] = explode('-', $this->priceRange);
            $query->where('base_price', '>=', $min);
            if ($max !== '+') {
                $query->where('base_price', '<=', $max);
            }
        }

        match ($this->sortBy) {
            'price_asc'  => $query->orderBy('base_price', 'asc'),
            'price_desc' => $query->orderBy('base_price', 'desc'),
            'newest'     => $query->orderBy('created_at', 'desc'),
            default      => $query->orderBy('name', 'asc'),
        };

        $availableSizes = ProductVariant::whereHas('product', fn ($q) => $q->where('status', 'active'))
            ->where('stock_quantity', '>', 0)
            ->whereNotNull('size')
            ->distinct()
            ->orderBy('size')
            ->pluck('size');

        return [
            'products'       => $query->paginate(9),
            'availableSizes' => $availableSizes,
        ];
    }
};

?>

<div class="relative min-h-screen overflow-hidden"
     x-data="{ filterOpen: false }">

    @if ($settings->productPageBgUrl())
        <img src="{{ $settings->productPageBgUrl() }}" alt="" class="absolute inset-0 w-full h-full object-cover">
        <div class="absolute inset-0 bg-ink/70"></div>
    @endif

    {{-- ── SLIDE-IN FILTER PANEL ──────────────────────────────────────── --}}
    <div class="fixed inset-y-0 right-0 z-50 w-80 bg-ink border-l border-neutral-800 flex flex-col"
         style="transform: translateX(100%); transition: transform 0.3s ease;"
         :style="filterOpen ? 'transform: translateX(0)' : 'transform: translateX(100%)'"
         :class="filterOpen ? '' : 'pointer-events-none'">

        {{-- Panel header --}}
        <div class="flex items-center justify-between px-6 py-5 border-b border-neutral-800">
            <span class="font-mono text-xs uppercase tracking-widest text-neutral-400">Filters & Sort</span>
            <button @click="filterOpen = false" class="text-neutral-500 hover:text-bone transition">
                <svg width="18" height="18" viewBox="0 0 18 18" fill="none"><path d="M3 3l12 12M15 3L3 15" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
            </button>
        </div>

        {{-- Panel body --}}
        <div class="flex-1 overflow-y-auto px-6 py-6 space-y-8">

            {{-- Search --}}
            <div>
                <p class="font-mono text-xs uppercase tracking-widest text-neutral-600 mb-3">Search</p>
                <div class="relative">
                    <input
                        wire:model.live.debounce.300ms="search"
                        type="text"
                        placeholder="Search jeans..."
                        class="w-full bg-transparent border border-neutral-700 font-mono text-sm text-bone placeholder-neutral-600 px-4 py-2 pr-8 outline-none focus:border-neutral-500 transition"
                    />
                    @if($search)
                    <button wire:click="$set('search', '')" class="absolute right-3 top-1/2 -translate-y-1/2 text-neutral-500 hover:text-bone text-xs">✕</button>
                    @endif
                </div>
            </div>

            {{-- Waist size --}}
            <div>
                <p class="font-mono text-xs uppercase tracking-widest text-neutral-600 mb-3">Waist size</p>
                <div class="flex flex-wrap gap-2">
                    <button wire:click="$set('size', '')"
                            class="px-3 py-1.5 border font-mono text-xs transition
                                {{ !$size ? 'border-copper text-copper-light' : 'border-neutral-700 text-neutral-400 hover:border-neutral-500' }}">
                        All
                    </button>
                    @foreach($availableSizes as $s)
                    <button wire:click="$set('size', '{{ $s }}')"
                            class="px-3 py-1.5 border font-mono text-xs transition
                                {{ $size === $s ? 'border-copper text-copper-light' : 'border-neutral-700 text-neutral-400 hover:border-neutral-500' }}">
                        {{ $s }}
                    </button>
                    @endforeach
                </div>
            </div>

            {{-- Price range --}}
            <div>
                <p class="font-mono text-xs uppercase tracking-widest text-neutral-600 mb-3">Price</p>
                <div class="space-y-2">
                    @foreach([
                        '' => 'All prices',
                        '0-100' => 'Under $100',
                        '100-200' => '$100 – $200',
                        '200-300' => '$200 – $300',
                        '300-500' => '$300 – $500',
                        '500-+' => '$500+',
                    ] as $val => $label)
                    <button wire:click="$set('priceRange', '{{ $val }}')"
                            class="w-full text-left px-3 py-2 border font-mono text-xs transition
                                {{ $priceRange === $val ? 'border-copper text-copper-light bg-copper/5' : 'border-neutral-800 text-neutral-400 hover:border-neutral-600' }}">
                        {{ $label }}
                    </button>
                    @endforeach
                </div>
            </div>

            {{-- Sort --}}
            <div>
                <p class="font-mono text-xs uppercase tracking-widest text-neutral-600 mb-3">Sort by</p>
                <div class="space-y-2">
                    @foreach([
                        'name'       => 'A – Z',
                        'price_asc'  => 'Lowest price',
                        'price_desc' => 'Highest price',
                        'newest'     => 'Newest',
                    ] as $val => $label)
                    <button wire:click="$set('sortBy', '{{ $val }}')"
                            class="w-full text-left px-3 py-2 border font-mono text-xs transition
                                {{ $sortBy === $val ? 'border-copper text-copper-light bg-copper/5' : 'border-neutral-800 text-neutral-400 hover:border-neutral-600' }}">
                        {{ $label }}
                    </button>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Panel footer --}}
        @if($search || $size || $priceRange || $sortBy !== 'name')
        <div class="px-6 py-4 border-t border-neutral-800">
            <button wire:click="clearFilters"
                    class="w-full font-mono text-xs text-neutral-500 hover:text-copper-light transition underline underline-offset-2">
                Clear all filters
            </button>
        </div>
        @endif
    </div>

    {{-- Backdrop --}}
    <div x-show="filterOpen"
         x-cloak
         x-transition:enter="transition duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-ink/60 z-40"
         @click="filterOpen = false">
    </div>

    {{-- ── MAIN CONTENT ─────────────────────────────────────────────────── --}}
    <div class="relative z-10 mx-auto max-w-6xl px-6 py-16">

        {{-- Top bar --}}
        <div class="flex items-center justify-between mb-10">
            <span class="font-mono text-xs text-neutral-600">
                {{ $products->total() }} {{ Str::plural('product', $products->total()) }}
            </span>

            <div class="flex items-center gap-3 flex-wrap justify-end">

                {{-- Active filter chips --}}
                @if($search)
                <span class="font-mono text-xs border border-neutral-700 text-neutral-400 px-3 py-1 flex items-center gap-2">
                    "{{ $search }}"
                    <button wire:click="$set('search', '')" class="text-neutral-600 hover:text-bone">✕</button>
                </span>
                @endif
                @if($size)
                <span class="font-mono text-xs border border-neutral-700 text-neutral-400 px-3 py-1 flex items-center gap-2">
                    Waist {{ $size }}
                    <button wire:click="$set('size', '')" class="text-neutral-600 hover:text-bone">✕</button>
                </span>
                @endif
                @if($priceRange)
                <span class="font-mono text-xs border border-neutral-700 text-neutral-400 px-3 py-1 flex items-center gap-2">
                    {{ ['0-100' => 'Under $100', '100-200' => '$100–$200', '200-300' => '$200–$300', '300-500' => '$300–$500', '500-+' => '$500+'][$priceRange] ?? '' }}
                    <button wire:click="$set('priceRange', '')" class="text-neutral-600 hover:text-bone">✕</button>
                </span>
                @endif

                {{-- Filter & Sort button --}}
                <button @click="filterOpen = true"
                        class="flex items-center gap-2 border border-neutral-700 font-mono text-xs text-neutral-400 hover:border-neutral-500 hover:text-bone px-4 py-2.5 transition">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true">
                        <path d="M2 4h12M4 8h8M6 12h4" stroke="currentColor" stroke-width="1.2" stroke-linecap="round"/>
                    </svg>
                    Filter & Sort
                    @if($search || $size || $priceRange || $sortBy !== 'name')
                    <span class="w-1.5 h-1.5 rounded-full bg-copper inline-block"></span>
                    @endif
                </button>

            </div>
        </div>

        {{-- Product grid --}}
        @if($products->isEmpty())
            <div class="py-24 text-center">
                <p class="font-mono text-sm text-neutral-500">No products found.</p>
                <button wire:click="clearFilters" class="mt-4 font-mono text-xs text-copper-light hover:underline">Clear filters</button>
            </div>
        @else
            @php
                $aspects = ['aspect-[2/3]', 'aspect-square', 'aspect-[4/3]', 'aspect-[9/16]', 'aspect-[3/4]'];
            @endphp
            <div class="columns-2 md:columns-3 gap-4 md:gap-6">
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
                <span class="px-4 py-2 text-neutral-500">{{ $products->currentPage() }} / {{ $products->lastPage() }}</span>
                @if($products->hasMorePages())
                    <button wire:click="nextPage" class="px-4 py-2 border border-neutral-700 text-neutral-400 hover:border-neutral-500 hover:text-bone transition">Next →</button>
                @else
                    <span class="px-4 py-2 border border-neutral-800 text-neutral-700">Next →</span>
                @endif
            </div>
            @endif
        @endif

    </div>
</div>