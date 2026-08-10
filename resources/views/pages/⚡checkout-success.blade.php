<?php

use App\Models\CartItem;
use App\Models\Order;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Order Confirmed — Rando Jeans')] class extends Component {
    public ?Order $order = null;

    public function mount(): void
    {
        $orderNumber = request()->query('order');
        $this->order = Order::where('order_number', $orderNumber)->first();

        // Cart clearing is just a UX nicety here — the webhook is what actually confirms payment.
        CartItem::where('session_id', session()->getId())->delete();
        $this->dispatch('cart-updated');
    }
};

?>

<div class="mx-auto max-w-2xl px-6 py-24 text-center">
    @if ($order)
        <p class="font-mono text-xs uppercase tracking-widest text-copper-light mb-4">Order Confirmed</p>
        <h1 class="font-display text-4xl mb-4">Thank you, {{ explode(' ', $order->shipping_name)[0] }}.</h1>
        <p class="text-neutral-400">Order <span class="font-mono text-bone">{{ $order->order_number }}</span> is confirmed.
            A confirmation has been sent to {{ $order->email }}.</p>
        <p class="font-mono text-2xl mt-8">${{ number_format($order->total, 2) }} AUD</p>
        <a href="{{ route('shop') }}" class="inline-block mt-10 font-mono text-xs uppercase tracking-widest text-copper-light hover:text-bone">
            Continue shopping →
        </a>
    @else
        <p class="text-neutral-400">We couldn't find that order.</p>
    @endif
</div>