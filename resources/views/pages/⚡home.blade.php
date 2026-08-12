<?php

use App\Models\Product;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Rando Jeans — Premium Black Denim')] class extends Component {
    public Product $featured;

    public function mount(): void
    {
        $this->featured = Product::where('status', 'active')->firstOrFail();
    }
};

?>

<div>
    {{-- Hero --}}
    <div class="border-b border-neutral-800">
        <div class="mx-auto max-w-5xl px-6 py-24 text-center">
            <p class="font-mono text-xs tracking-widest text-copper-light uppercase mb-4">One jean. Done properly.</p>
            <h1 class="font-display text-5xl md:text-6xl leading-tight">The Original Black Jean</h1>
            <p class="mt-6 text-neutral-400 max-w-xl mx-auto">
                Cut and sewn in Vietnam from heavyweight denim built to hold its colour —
                designed in Australia for people who wear one pair of jeans until they've earned their fade.
            </p>
            <a href="{{ route('shop') }}"
               class="inline-block mt-10 bg-copper hover:bg-copper-light text-ink font-medium px-10 py-4 transition">
                Shop the jean
            </a>
        </div>
    </div>

    {{-- Story teaser --}}
    <div class="mx-auto max-w-5xl px-6 py-20 grid md:grid-cols-2 gap-16 items-center">
        <div class="aspect-square bg-neutral-900 border border-neutral-800 flex items-center justify-center">
            <span class="font-mono text-xs text-neutral-600 tracking-widest">BRAND IMAGE</span>
        </div>
        <div>
            <h2 class="font-display text-3xl">Why we only make one jean</h2>
            <p class="mt-4 text-neutral-400 leading-relaxed">
                Most denim brands chase trends across dozens of fits and washes.
                We picked one fabric, one silhouette, and one colour — and spent the time
                that would've gone into variety on getting the one thing right instead.
            </p>
            <a href="{{ route('craft') }}" class="inline-block mt-6 font-mono text-xs uppercase tracking-widest text-copper-light hover:text-bone">
                Read about our craft →
            </a>
        </div>
    </div>
</div>