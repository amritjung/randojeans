<?php

use App\Models\Product;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Rando Jeans — Premium Black Denim')] class extends Component {
    public Product $featured;
    public $shopProducts;
    public \App\Models\SiteSetting $settings;

    public function mount(): void
    {
        $this->featured = Product::where('status', 'active')->firstOrFail();
        $this->shopProducts = Product::where('status', 'active')->get();
        $this->settings = \App\Models\SiteSetting::current();
    }
};

?>

<div>
    {{-- Full-bleed hero: large image area (ready for real lifestyle
         photography), big display headline overlaid, single clear CTA.
         Taller aspect ratio on mobile, matching how Nudie's hero scales
         portrait-oriented on small screens. --}}
    <div class="relative h-[85vh] min-h-[500px] bg-neutral-900 border-b border-neutral-800 flex items-end">
        {{-- Subtle dark gradient at the very top, behind the now-transparent
     nav — insurance so nav text/icons stay readable even if the top
     portion of the hero photo turns out to be light-coloured. Doesn't
     add a visible "bar," just enough of a gradient to protect legibility. --}}
        <div class="absolute inset-0 flex items-center justify-center overflow-hidden">
            @if ($settings->heroImageUrl())
                <img src="{{ $settings->heroImageUrl() }}" alt="" class="w-full h-full object-cover">
            @else
                <span class="font-mono text-xs text-neutral-700 tracking-widest">HERO LIFESTYLE IMAGE</span>
            @endif
        </div>
        <div class="relative z-10 w-full bg-gradient-to-t from-ink via-ink/60 to-transparent pt-32 pb-16 px-6 text-center">
            <p class="font-mono text-xs tracking-widest text-copper-light uppercase mb-4">One jean. Done properly.</p>
            <h1 class="font-display text-5xl md:text-7xl leading-tight">Create Tomorrow's<br>Wardrobe.</h1>
            <a href="{{ route('product.show', $featured) }}"
               class="inline-block mt-8 bg-copper hover:bg-copper-light text-ink font-medium px-10 py-4 transition">
                Shop the Jean
            </a>
        </div>
    </div>

    {{-- Manifesto strip: a short, confident brand statement as a visual
         breather between image sections — same role as Nudie's
         typewriter-style tagline banner. --}}
         <div class="relative border-b border-neutral-800 py-20 text-center overflow-hidden">
            @if ($settings->manifesto1ImageUrl())
                <img src="{{ $settings->manifesto1ImageUrl() }}" alt="" class="absolute inset-0 w-full h-full object-cover">
                {{-- Dark overlay for text legibility, same principle used
                     everywhere else an image sits behind text on this page. --}}
                <div class="absolute inset-0 bg-ink/60"></div>
            @endif
            <p class="relative z-10 font-display text-2xl md:text-3xl text-bone px-6">
                "Built to be worn, not replaced."
            </p>
        </div>

    {{-- Large feature block: full-bleed image + story, alternating
         layout direction from the hero above for visual rhythm. --}}
    <div class="grid md:grid-cols-2 border-b border-neutral-800">
        <div class="aspect-square md:aspect-auto bg-neutral-900 flex items-center justify-center border-b md:border-b-0 md:border-r border-neutral-800 overflow-hidden">
            @if ($settings->featureImageUrl())
                <img src="{{ $settings->featureImageUrl() }}" alt="" class="w-full h-full object-cover">
            @else
                <span class="font-mono text-xs text-neutral-700 tracking-widest">PRODUCT DETAIL IMAGE</span>
            @endif
        </div>
        <div class="flex items-center p-10 md:p-16">
            <div>
                <p class="font-mono text-xs tracking-widest text-copper-light uppercase mb-4">The Fabric</p>
                <h2 class="font-display text-3xl md:text-4xl mb-4">Heavyweight. Honest. Built to Last.</h2>
                <p class="text-neutral-400 leading-relaxed">
                    {{ $featured->weight_oz }}oz {{ $featured->composition }} denim, woven with a
                    {{ strtolower($featured->weave) }}. This isn't fast fashion denim — it's built to
                    hold its shape and fade honestly, the way real jeans should.
                </p>
                <a href="{{ route('craft') }}" class="inline-block mt-6 font-mono text-xs uppercase tracking-widest text-copper-light hover:text-bone">
                    Read about our craft →
                </a>
            </div>
        </div>
    </div>

    {{-- Second manifesto-style strip, different message --}}
    <div class="relative border-b border-neutral-800 py-20 text-center overflow-hidden">
        @if ($settings->manifesto2ImageUrl())
            <img src="{{ $settings->manifesto2ImageUrl() }}" alt="" class="absolute inset-0 w-full h-full object-cover">
            <div class="absolute inset-0 bg-ink/60"></div>
        @endif
        <p class="relative z-10 font-display text-2xl md:text-3xl text-bone px-6">
            Designed in Australia. Made to travel anywhere.
        </p>
    </div>
    {{-- Full-bleed video section with text overlaid on top — only renders
     if a video's actually been uploaded, so the homepage doesn't show
     a broken/empty block if this hasn't been set up yet. --}}
@if ($settings->featureVideoUrl())
<div class="relative h-[70vh] min-h-[400px] border-b border-neutral-800 overflow-hidden flex items-center justify-center">
    {{-- autoplay + muted + loop + playsinline is the standard
         recipe for ambient background video — muted is required
         for autoplay to work in every browser, playsinline stops
         it forcing fullscreen on mobile. No controls, since this
         is decorative background, not content to interact with. --}}
    <video
        src="{{ $settings->featureVideoUrl() }}"
        autoplay
        muted
        loop
        playsinline
        class="absolute inset-0 w-full h-full object-cover"
    ></video>

    {{-- Dark overlay so text stays readable regardless of what's
         happening in the video underneath it — same legibility
         principle as the header's protective gradient. --}}
    <div class="absolute inset-0 bg-ink/50"></div>

    <div class="relative z-10 text-center px-6 max-w-xl">
        @if ($settings->video_heading)
            <h2 class="font-display text-4xl md:text-5xl mb-4">{{ $settings->video_heading }}</h2>
        @endif
        @if ($settings->video_subtext)
            <p class="text-neutral-300 leading-relaxed">{{ $settings->video_subtext }}</p>
        @endif
    </div>
</div>
@endif

    {{-- Shop grid teaser: reuses the actual product grid, full width,
         so the homepage flows straight into shoppable product cards
         rather than just linking away to a separate page. --}}
    <div class="py-16 px-6 border-b border-neutral-800">
        <h2 class="font-display text-3xl text-center mb-10">Shop the Collection</h2>
        <div class="max-w-5xl mx-auto grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-8">
            @foreach ($shopProducts as $product)
                <a href="{{ route('product.show', $product) }}" class="group block">
                    <div class="aspect-[3/4] bg-neutral-900 border border-neutral-800 flex items-center justify-center overflow-hidden">
                        @if ($product->primaryImage())
                            <img src="{{ $product->primaryImage()->url() }}" alt="{{ $product->name }}" class="w-full h-full object-cover group-hover:opacity-80 transition">
                        @else
                            <span class="font-mono text-xs text-neutral-600 tracking-widest">PRODUCT IMAGE</span>
                        @endif
                    </div>
                    <h3 class="font-display text-lg mt-4 group-hover:text-copper-light transition">{{ $product->name }}</h3>
                    <p class="font-mono text-sm text-neutral-500 mt-1">
                        @if ($product->isOnSale())
                            <span class="line-through text-neutral-600">${{ number_format($product->base_price, 2) }}</span>
                            <span class="text-copper-light ml-2">${{ number_format($product->sale_price, 2) }}</span>
                        @else
                            ${{ number_format($product->base_price, 2) }} AUD
                        @endif
                    </p>
                </a>
            @endforeach
        </div>
    </div>

    {{-- Social proof: real average rating pulled from actual reviews,
         only shown if there's at least one — no fabricated testimonials. --}}
    @php
        $reviewedProducts = $shopProducts->filter(fn ($p) => $p->reviewCount() > 0);
    @endphp
    @if ($reviewedProducts->isNotEmpty())
        <div class="py-16 px-6 border-b border-neutral-800 text-center">
            <p class="font-mono text-xs uppercase tracking-widest text-neutral-500 mb-4">What People Are Saying</p>
            <div class="flex items-center justify-center gap-3">
                <span class="font-display text-4xl text-copper-light">
                    {{ number_format($reviewedProducts->avg(fn ($p) => $p->averageRating()), 1) }}
                </span>
                <span class="text-neutral-500 text-sm text-left">
                    out of 5<br>{{ $reviewedProducts->sum(fn ($p) => $p->reviewCount()) }} verified reviews
                </span>
            </div>
        </div>
    @endif

    {{-- Brand story, adapted from the existing "why one jean" section --}}
    <div class="relative py-24 px-6 text-center overflow-hidden">
        @if ($settings->storyImageUrl())
            <img src="{{ $settings->storyImageUrl() }}" alt="" class="absolute inset-0 w-full h-full object-cover">
            <div class="absolute inset-0 bg-ink/70"></div>
        @endif
        <div class="relative z-10 max-w-2xl mx-auto">
            <p class="font-mono text-xs tracking-widest text-copper-light uppercase mb-4">Our Story</p>
            <p class="text-neutral-300 leading-relaxed">
                Most denim brands chase trends across dozens of fits and washes. We picked one fabric,
                one silhouette, and one colour — and spent the time that would've gone into variety on
                getting the one thing right instead.
            </p>
            <a href="{{ route('craft') }}" class="inline-block mt-6 font-mono text-xs uppercase tracking-widest text-copper-light hover:text-bone">
                The Craft of Denim →
            </a>
        </div>
    </div>
</div>