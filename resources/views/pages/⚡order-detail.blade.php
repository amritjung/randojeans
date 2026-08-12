<?php

use App\Models\Order;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Order Details — Rando Jeans')] class extends Component {
    public Order $order;

    // mount(Order $order) uses Laravel's route model binding — Livewire
    // automatically finds the Order matching the {order} route parameter
    // and injects it here, so we don't have to manually look it up.
    public function mount(Order $order): void
    {
        // Security check: make sure this order actually belongs to the
        // logged-in user. Without this, anyone could view any order by
        // just guessing/changing the order ID in the URL — a classic
        // "insecure direct object reference" vulnerability.
        abort_unless($order->user_id === auth()->id(), 403);

        $this->order = $order->load('items');
    }
};

?>

<div class="mx-auto max-w-2xl px-6 py-16">
    <a href="{{ route('orders') }}" class="font-mono text-xs uppercase tracking-widest text-neutral-500 hover:text-bone">
        ← Back to Order History
    </a>

    <div class="flex items-center justify-between mt-6 mb-10">
        <div>
            <h1 class="font-display text-3xl">{{ $order->order_number }}</h1>
            <p class="text-neutral-500 text-sm mt-1">{{ $order->created_at->format('d M Y, g:ia') }}</p>
        </div>
        <span class="font-mono text-xs uppercase tracking-widest px-3 py-1 border
            {{ $order->status === 'paid' ? 'border-green-700 text-green-400' : 'border-neutral-700 text-neutral-400' }}">
            {{ $order->status }}
        </span>
    </div>

    <div class="border border-neutral-800 p-6 mb-6">
        <p class="font-mono text-xs uppercase tracking-widest text-neutral-500 mb-4">Items</p>
    
        @foreach ($order->items as $item)
            @php
                // Computed once per item here (rather than inline in the Blade
                // template multiple times) — pulls the product's current image,
                // same relationship chain we used in the admin order items tab.
                $imageUrl = $item->productVariant?->product?->primaryImage()?->url();
            @endphp
    
            <div
                {{-- x-data scoped to just this item — each thumbnail gets its
                     own independent "zoomed" state, so clicking one doesn't
                     affect any other item's zoom overlay. --}}
                x-data="{ zoomed: false }"
                class="flex items-center gap-4 text-sm mb-4"
            >
                <div class="w-16 h-16 bg-neutral-900 border border-neutral-800 flex-shrink-0 overflow-hidden">
                    @if ($imageUrl)
                        {{-- @click="zoomed = true" opens the full-size overlay
                             below; cursor-zoom-in signals to the user that
                             it's clickable for a closer look. --}}
                        <img src="{{ $imageUrl }}" alt="{{ $item->product_name }}"
                             class="w-full h-full object-cover cursor-zoom-in"
                             @click="zoomed = true">
                    @else
                        <div class="w-full h-full flex items-center justify-center">
                            <span class="font-mono text-[9px] text-neutral-600">IMAGE</span>
                        </div>
                    @endif
                </div>
    
                <div class="flex-1 flex justify-between">
                    <span>{{ $item->product_name }} ({{ $item->variant_label }}) × {{ $item->quantity }}</span>
                    <span class="font-mono">${{ number_format($item->unit_price * $item->quantity, 2) }}</span>
                </div>
    
                {{-- The zoom overlay itself: fixed full-screen dark backdrop,
                     hidden until zoomed becomes true. x-cloak prevents it
                     flashing visible for a split-second before Alpine loads. --}}
                <div x-show="zoomed" x-cloak
                     @click="zoomed = false"
                     @keydown.escape.window="zoomed = false"
                     class="fixed inset-0 bg-ink/95 z-50 flex items-center justify-center p-6">
                    <img src="{{ $imageUrl }}" alt="{{ $item->product_name }}"
                         class="max-w-full max-h-[85vh] object-contain">
                    <button @click.stop="zoomed = false" class="absolute top-6 right-6 text-bone text-2xl hover:text-copper-light">✕</button>
                </div>
            </div>
        @endforeach
    
        {{-- rest of the file (subtotal/shipping/total block) stays unchanged --}}

    <div class="border border-neutral-800 p-6">
        <p class="font-mono text-xs uppercase tracking-widest text-neutral-500 mb-4">Shipping To</p>
        <p class="text-sm text-bone">{{ $order->shipping_name }}</p>
        <p class="text-sm text-neutral-400 mt-1">
            {{ $order->shipping_street ?? $order->shipping_address }}<br>
            @if ($order->shipping_suburb)
                {{ $order->shipping_suburb }} {{ $order->shipping_state }} {{ $order->shipping_postcode }}
            @endif
        </p>
    </div>
</div>