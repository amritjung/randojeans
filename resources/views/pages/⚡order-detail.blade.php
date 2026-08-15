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

        $this->order = $order->load(['items', 'returnRequests']);
    }
};

?>

<div class="mx-auto max-w-2xl px-6 py-16">
    <a href="{{ route('orders') }}" class="font-mono text-xs uppercase tracking-widest text-neutral-500 hover:text-bone">
        ← Back to Order History
    </a>

    {{-- Two-column grid: left side groups "what/where" (order number +
     delivery address), right side groups "the facts" (date, quantity,
     total) — stacks to a single column automatically on mobile via
     grid-cols-1 md:grid-cols-2. --}}
<div class="border border-neutral-800 p-6 mt-6 mb-6 grid grid-cols-1 md:grid-cols-2 gap-8">
    {{-- Left: Order number + Delivery address --}}
    <div class="space-y-4">
        <div>
            <p class="font-mono text-xs uppercase tracking-widest text-neutral-500">Order</p>
            <p class="font-mono text-sm mt-1">{{ $order->order_number }}</p>
        </div>

        <div>
            <p class="font-mono text-xs uppercase tracking-widest text-neutral-500">Delivery</p>
            <p class="text-sm mt-1">
                <span class="text-neutral-400">To: </span>{{ $order->shipping_name }}<br>
                <span class="text-neutral-500">
                    {{ $order->shipping_street ?? $order->shipping_address }},
                    @if ($order->shipping_suburb)
                        {{ strtoupper($order->shipping_suburb) }}, {{ $order->shipping_state }}, {{ $order->shipping_postcode }}
                    @endif
                </span>
            </p>
        </div>
    </div>

    {{-- Right: Order Date, Item Qty, Total Paid --}}
    <div class="space-y-4 md:border-l md:border-neutral-800 md:pl-8">
        <div class="flex justify-between items-center">
            <span class="font-mono text-xs uppercase tracking-widest text-neutral-500">Order Date</span>
            <span class="font-mono text-sm">{{ $order->created_at->format('d M Y') }}</span>
        </div>

        <div class="flex justify-between items-center">
            <span class="font-mono text-xs uppercase tracking-widest text-neutral-500">Item Qty</span>
            <span class="font-mono text-sm">{{ $order->items->sum('quantity') }} {{ Str::plural('Item', $order->items->sum('quantity')) }}</span>
        </div>

        <div class="flex justify-between items-center">
            <span class="font-mono text-xs uppercase tracking-widest text-neutral-500">Total Paid</span>
            <span class="font-mono text-sm text-copper-light">${{ number_format($order->total, 2) }}</span>
        </div>
    </div>
</div>

{{-- Delivery status: a simple horizontal progress indicator through
     the four customer-relevant stages. Cancelled orders are handled
     separately below rather than trying to fit into this progression. --}}
<div class="mb-10">
    <p class="font-mono text-xs uppercase tracking-widest text-neutral-500 mb-3">Delivery</p>

    @if ($order->status === 'cancelled')
        <span class="font-mono text-xs uppercase tracking-widest px-3 py-1 border border-red-900 text-red-400">
            Cancelled
        </span>
    @else
        @php
            // Where this order sits in the simple 4-stage journey — used
            // to decide which steps below show as "reached" (copper) vs
            // still pending (neutral grey).
            $stages = ['paid' => 'Paid', 'fulfilled' => 'Preparing', 'shipped' => 'Shipped', 'delivered' => 'Delivered'];
            $stageKeys = array_keys($stages);
            $currentIndex = array_search($order->status, $stageKeys);
            $currentIndex = $currentIndex === false ? 0 : $currentIndex;
        @endphp

        <div class="flex items-center">
            @foreach ($stages as $key => $label)
                @php $reached = array_search($key, $stageKeys) <= $currentIndex; @endphp
                <div class="flex items-center {{ ! $loop->last ? 'flex-1' : '' }}">
                    <div class="flex flex-col items-center">
                        <div class="w-2.5 h-2.5 rounded-full {{ $reached ? 'bg-copper' : 'bg-neutral-700' }}"></div>
                        <span class="font-mono text-[10px] uppercase tracking-widest mt-2 {{ $reached ? 'text-copper-light' : 'text-neutral-600' }}">
                            {{ $label }}
                        </span>
                    </div>
                    @if (!$loop->last)
                        <div class="flex-1 h-px {{ $reached && array_search($stageKeys[$loop->index + 1], $stageKeys) <= $currentIndex ? 'bg-copper' : 'bg-neutral-800' }} mx-2"></div>
                    @endif
                </div>
            @endforeach
        </div>

        @if ($order->trackingUrl())
            <a href="{{ $order->trackingUrl() }}" target="_blank" rel="noopener"
               class="inline-block mt-6 bg-copper hover:bg-copper-light text-ink font-medium text-sm px-6 py-3 transition">
                Track Order →
            </a>
        @elseif ($order->tracking_number)
            <p class="mt-6 font-mono text-sm text-neutral-400">
                Tracking: {{ $order->tracking_number }} ({{ $order->carrier }})
            </p>
        @endif
    @endif
</div>

    <div class="border border-neutral-800 p-6 mb-6">
        <p class="font-mono text-xs uppercase tracking-widest text-neutral-500 mb-4">Items</p>
    
        @foreach ($order->items as $item)
            @php
                $imageUrl = $item->productVariant?->product?->primaryImage()?->url();
            @endphp
            <div
                x-data="{ zoomed: false }"
                class="flex items-center gap-4 text-sm mb-4"
            >
                <div class="w-16 h-16 bg-neutral-900 border border-neutral-800 flex-shrink-0 overflow-hidden">
                    @if ($imageUrl)
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
                    <span class="font-mono text-right">
                        @if ($item->wasOnSale())
                            <span class="block text-xs text-neutral-600 line-through">${{ number_format($item->original_price * $item->quantity, 2) }}</span>
                        @endif
                        <span class="{{ $item->wasOnSale() ? 'text-copper-light' : '' }}">${{ number_format($item->unit_price * $item->quantity, 2) }}</span>
                    </span>
                </div>
    
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
    </div>
    
        {{-- rest of the file (subtotal/shipping/total block) stays unchanged --}}

    
    @if ($order->discount_amount > 0)
    <div class="flex justify-between text-copper-light">
        <span>Discount ({{ $order->discount_code }})</span>
        <span class="font-mono">-${{ number_format($order->discount_amount, 2) }}</span>
    </div>
    @endif
    @if ($order->returnRequests->isNotEmpty())
    <div class="border border-neutral-800 p-6 mt-6">
        <p class="font-mono text-xs uppercase tracking-widest text-neutral-500 mb-2">Return Status</p>
        <p class="text-sm text-bone capitalize">{{ $order->returnRequests->first()->status }}</p>
    </div>
@elseif ($order->isReturnEligible())
    <a href="{{ route('orders.return', $order) }}" class="block text-center mt-6 border border-neutral-700 hover:border-copper py-3 font-mono text-xs uppercase tracking-widest text-neutral-400 hover:text-bone transition">
        Request a Return
    </a>
@endif
    @if ($order->tracking_number)
    <div class="border border-neutral-800 p-6 mt-6">
        <p class="font-mono text-xs uppercase tracking-widest text-neutral-500 mb-4">Tracking</p>
        <p class="font-mono text-sm text-copper-light">{{ $order->tracking_number }}</p>
        @if ($order->carrier)
            <p class="text-neutral-400 text-sm mt-1">via {{ $order->carrier }}</p>
        @endif
        @if ($order->shipped_at)
            <p class="text-neutral-500 text-xs mt-2">Shipped {{ $order->shipped_at->format('d M Y') }}</p>
        @endif
    </div>
@endif
</div>