<?php

use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Str;
use Livewire\Attributes\Title;
use Livewire\Component;
use Stripe\Checkout\Session;
use Stripe\Stripe;

new #[Title('Checkout — Rando Jeans')] class extends Component {
    public $items;
    public string $email = '';
    public string $shipping_name = '';
    public string $shipping_address = '';

    public function mount(): void
    {
        $this->items = CartItem::currentItems();

        if ($this->items->isEmpty()) {
            redirect()->route('cart');
        }
    }

    public function getSubtotalProperty(): float
    {
        return $this->items->sum(fn ($item) => $item->lineTotal());
    }

    public function getShippingCostProperty(): float
    {
        return $this->subtotal >= 150 ? 0 : 12.00;
    }

    public function getGstProperty(): float
    {
        // Prices are GST-inclusive (standard AU practice) — GST is the 1/11th component, not added on top.
        return round(($this->subtotal + $this->shippingCost) / 11, 2);
    }

    public function getTotalProperty(): float
    {
        return $this->subtotal + $this->shippingCost;
    }

    public function submit(): void
    {
        $this->validate([
            'email' => 'required|email',
            'shipping_name' => 'required|string|max:255',
            'shipping_address' => 'required|string|max:500',
        ]);

        $order = Order::create([
            'order_number' => 'RJ-' . strtoupper(Str::random(8)),
            'email' => $this->email,
            'shipping_name' => $this->shipping_name,
            'shipping_address' => $this->shipping_address,
            'status' => 'pending',
            'subtotal' => $this->subtotal,
            'shipping_cost' => $this->shippingCost,
            'gst' => $this->gst,
            'total' => $this->total,
        ]);

        foreach ($this->items as $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_variant_id' => $item->product_variant_id,
                'product_name' => $item->productVariant->product->name,
                'variant_label' => $item->productVariant->label(),
                'quantity' => $item->quantity,
                'unit_price' => $item->productVariant->price(),
            ]);
        }

        Stripe::setApiKey(config('services.stripe.secret'));

        $lineItems = $this->items->map(fn ($item) => [
            'price_data' => [
                'currency' => 'aud',
                'product_data' => ['name' => $item->productVariant->product->name . ' — ' . $item->productVariant->label()],
                'unit_amount' => (int) round($item->productVariant->price() * 100),
            ],
            'quantity' => $item->quantity,
        ])->values()->all();

        if ($this->shippingCost > 0) {
            $lineItems[] = [
                'price_data' => [
                    'currency' => 'aud',
                    'product_data' => ['name' => 'Shipping'],
                    'unit_amount' => (int) round($this->shippingCost * 100),
                ],
                'quantity' => 1,
            ];
        }

        $session = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => $lineItems,
            'mode' => 'payment',
            'customer_email' => $this->email,
            'success_url' => route('checkout.success') . '?session_id={CHECKOUT_SESSION_ID}&order=' . $order->order_number,
            'cancel_url' => route('checkout.cancel') . '?order=' . $order->order_number,
        ]);

        $order->update(['stripe_checkout_session_id' => $session->id]);

        $this->redirect($session->url, navigate: false);
    }
};

?>

<div class="mx-auto max-w-3xl px-6 py-16">
    <h1 class="font-display text-4xl mb-10">Checkout</h1>

    <form wire:submit="submit" class="grid md:grid-cols-2 gap-12">
        <div class="space-y-4">
            <div>
                <label class="font-mono text-xs uppercase tracking-widest text-neutral-500">Email</label>
                <input type="email" wire:model="email" class="w-full mt-2 bg-transparent border border-neutral-700 focus:border-copper px-4 py-3 outline-none">
                @error('email') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="font-mono text-xs uppercase tracking-widest text-neutral-500">Full name</label>
                <input type="text" wire:model="shipping_name" class="w-full mt-2 bg-transparent border border-neutral-700 focus:border-copper px-4 py-3 outline-none">
                @error('shipping_name') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="font-mono text-xs uppercase tracking-widest text-neutral-500">Shipping address</label>
                <textarea wire:model="shipping_address" rows="4" class="w-full mt-2 bg-transparent border border-neutral-700 focus:border-copper px-4 py-3 outline-none"></textarea>
                @error('shipping_address') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <button type="submit" class="w-full bg-copper hover:bg-copper-light text-ink font-medium py-4 transition mt-4">
                Continue to payment
            </button>
            <p class="font-mono text-[11px] text-neutral-600 text-center">You'll be redirected to Stripe to pay securely.</p>
        </div>

        <div class="border border-neutral-800 p-6 h-fit">
            <p class="font-mono text-xs uppercase tracking-widest text-neutral-500 mb-4">Order Summary</p>
            @foreach ($items as $item)
                <div class="flex justify-between text-sm mb-2">
                    <span>{{ $item->productVariant->product->name }} ({{ $item->productVariant->label() }}) × {{ $item->quantity }}</span>
                    <span class="font-mono">${{ number_format($item->lineTotal(), 2) }}</span>
                </div>
            @endforeach

            <div class="border-t border-dashed border-neutral-700 mt-4 pt-4 space-y-2 text-sm">
                <div class="flex justify-between text-neutral-400">
                    <span>Subtotal</span>
                    <span class="font-mono">${{ number_format($this->subtotal, 2) }}</span>
                </div>
                <div class="flex justify-between text-neutral-400">
                    <span>Shipping</span>
                    <span class="font-mono">{{ $this->shippingCost > 0 ? '$' . number_format($this->shippingCost, 2) : 'Free' }}</span>
                </div>
                <div class="flex justify-between text-neutral-500 text-xs">
                    <span>(incl. GST)</span>
                    <span class="font-mono">${{ number_format($this->gst, 2) }}</span>
                </div>
                <div class="flex justify-between font-display text-lg text-bone pt-2">
                    <span>Total</span>
                    <span>${{ number_format($this->total, 2) }} AUD</span>
                </div>
            </div>
        </div>
    </form>
</div>