<?php

use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Order History — Rando Jeans')] class extends Component {
    public $orders;

    public function mount(): void
    {
        // Pulls every order ever placed by this account, regardless of
        // status (pending, paid, shipped, etc.) — "all order history"
        // means the complete record, not just successful/paid ones.
        $this->orders = Order::where('user_id', Auth::id())
            ->orderByDesc('created_at')
            ->get();
    }
};

?>

<div class="mx-auto max-w-3xl px-6 py-16">
    <h1 class="font-display text-4xl mb-10">Order History</h1>

    @if ($orders->isEmpty())
        <div class="border border-neutral-800 p-10 text-center">
            <p class="text-neutral-400">No orders yet.</p>
            <a href="{{ route('shop') }}" class="inline-block mt-4 font-mono text-xs uppercase tracking-widest text-copper-light hover:text-bone">
                Start shopping →
            </a>
        </div>
    @else
    <div class="space-y-4">
        @foreach ($orders as $order)
            @php
                // Order History is order-level, not item-level, so we just
                // grab the first item's product image as a representative
                // thumbnail for the whole order — good enough for a quick
                // visual scan; full per-item detail lives on the detail page.
                $firstItem = $order->items->first();
                $imageUrl = $firstItem?->productVariant?->product?->primaryImage()?->url();
            @endphp
    
            <div x-data="{ zoomed: false }" class="border border-neutral-800 hover:border-copper transition">
                <a href="{{ route('orders.show', $order) }}" class="flex items-center gap-4 p-6">
                    <div class="w-14 h-14 bg-neutral-900 border border-neutral-800 flex-shrink-0 overflow-hidden">
                        @if ($imageUrl)
                            {{-- @click.stop.prevent keeps this click from also
                                 triggering the parent <a> tag's navigation —
                                 clicking the thumbnail zooms it, clicking
                                 anywhere else on the row navigates to the
                                 order detail page as normal. --}}
                            <img src="{{ $imageUrl }}" alt="Order {{ $order->order_number }}"
                                 class="w-full h-full object-cover cursor-zoom-in"
                                 @click.stop.prevent="zoomed = true">
                        @else
                            <div class="w-full h-full flex items-center justify-center">
                                <span class="font-mono text-[8px] text-neutral-600">IMAGE</span>
                            </div>
                        @endif
                    </div>
    
                    <div class="flex-1 flex items-center justify-between">
                        <div>
                            <p class="font-mono text-sm text-bone">{{ $order->order_number }}</p>
                            <p class="font-mono text-xs text-neutral-500 mt-1">{{ $order->created_at->format('d M Y') }}</p>
                        </div>
                        <span class="font-mono text-xs uppercase tracking-widest px-3 py-1 border
                            {{ $order->status === 'paid' ? 'border-green-700 text-green-400' : 'border-neutral-700 text-neutral-400' }}">
                            {{ $order->statusLabel() }}
                        </span>
                        <p class="font-mono text-sm text-copper-light">${{ number_format($order->total, 2) }}</p>
                    </div>
                </a>
    
                <div x-show="zoomed" x-cloak
                     @click="zoomed = false"
                     @keydown.escape.window="zoomed = false"
                     class="fixed inset-0 bg-ink/95 z-50 flex items-center justify-center p-6">
                    @if ($imageUrl)
                        <img src="{{ $imageUrl }}" alt="Order {{ $order->order_number }}" class="max-w-full max-h-[85vh] object-contain">
                    @endif
                    <button @click.stop="zoomed = false" class="absolute top-6 right-6 text-bone text-2xl hover:text-copper-light">✕</button>
                </div>
            </div>
        @endforeach
    </div>
    @endif
</div>