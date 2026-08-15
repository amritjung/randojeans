<?php

use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Str;
use Livewire\Attributes\Title;
use Livewire\Component;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Cache;
use App\Models\Discount;
use App\Models\DiscountUsage;
use Stripe\Coupon;
use Illuminate\Support\Facades\Log;

new #[Title('Checkout — Rando Jeans')] class extends Component {
    public $items;
    public string $email = '';
    public string $shipping_name = '';
    public string $shipping_street = '';
    public string $shipping_suburb = '';
    public string $shipping_state = '';
    public string $shipping_postcode = '';
    public string $discountCode = '';
    public ?Discount $appliedDiscount = null;
    public string $discountError = '';

    public array $states = ['NSW', 'VIC', 'QLD', 'WA', 'SA', 'TAS', 'ACT', 'NT'];

    public function mount(): void
    {
        $this->items = CartItem::currentItems();

        if ($this->items->isEmpty()) {
            redirect()->route('cart');
            return;
        }

        // A logged-in user with an unverified email gets redirected to verify
        // first — their order confirmation would otherwise go to an email
        // address we've never actually confirmed they own. Guests aren't
        // affected by this check at all (auth()->check() is false for them),
        // since guest checkout intentionally doesn't require any account.
        if (auth()->check() && ! auth()->user()->hasVerifiedEmail()) {
            redirect()->route('verification.notice');
            return;
        }

        if (auth()->check()) {
            $user = auth()->user();
            $this->email = $user->email;
            $this->shipping_name = $user->name;
            $this->shipping_street = $user->address_street ?? '';
            $this->shipping_suburb = $user->address_suburb ?? '';
            $this->shipping_state = $user->address_state ?? '';
            $this->shipping_postcode = $user->address_postcode ?? '';
        }
    }

    public function getSubtotalProperty(): float
    {
        return $this->items->sum(fn ($item) => $item->lineTotal());
    }

    public function getDiscountAmountProperty(): float
    {
        if (! $this->appliedDiscount) {
            return 0;
        }

        // Discount applies to the product subtotal only, not shipping —
        // standard practice; a "10% off" code shouldn't also discount the
        // delivery fee.
        return round($this->subtotal * ($this->appliedDiscount->percentage / 100), 2);
    }

    public function getShippingCostProperty(): float
    {
        return $this->subtotal >= 150 ? 0 : 12.00;
    }

    public function getGstProperty(): float
    {
        $discountedSubtotal = $this->subtotal - $this->discountAmount;
        return round(($discountedSubtotal + $this->shippingCost) / 11, 2);
    }

    public function getTotalProperty(): float
    {
        return $this->subtotal - $this->discountAmount + $this->shippingCost;
    }

    public function applyDiscount(): void
    {
        $this->discountError = '';

        if (blank($this->discountCode)) {
            return;
        }

        // Keyed by IP — this is guarding against exactly the same class of
        // attack as the checkout/login throttles: someone scripting rapid
        // repeated guesses to brute-force-discover a valid discount code
        // (codes like "SAVE10" are short and guessable at scale if nothing
        // stops rapid automated attempts).
        $throttleKey = 'discount-attempt|' . request()->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 10)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            // Logged, not just silently blocked — so if this ever actually
            // fires in production, you have a record to notice a real
            // attack pattern rather than it happening invisibly.
            Log::warning("Discount code brute-force throttle hit from IP " . request()->ip());

            $this->discountError = "Too many attempts. Please try again in " . ceil($seconds / 60) . " minute(s).";
            return;
        }

        RateLimiter::hit($throttleKey, 600); // 10 attempts per 10 minutes per IP

        $discount = Discount::where('code', strtoupper(trim($this->discountCode)))->first();

        if (! $discount) {
            $this->discountError = 'That code doesn\'t exist.';
            return;
        }

        if (! $discount->isValid()) {
            $this->discountError = 'That code has expired or is no longer active.';
            return;
        }

        if (blank($this->email)) {
            $this->discountError = 'Please enter your email above first, then apply the code.';
            return;
        }

        if ($discount->hasBeenUsedBy($this->email)) {
            $this->discountError = 'This code has already been used on this email address.';
            return;
        }

        // A successful application clears this IP's attempt count — so a
        // legitimate customer who mistyped a code a couple of times before
        // getting it right isn't left sitting near the limit unnecessarily.
        RateLimiter::clear($throttleKey);

        $this->appliedDiscount = $discount;
    }

    public function removeDiscount(): void
    {
        $this->appliedDiscount = null;
        $this->discountCode = '';
        $this->discountError = '';
    }

    public function submit(): void
    {
            // Keyed by IP — this is specifically guarding against Stripe
            // "card testing" fraud, where an attacker scripts rapid repeated
            // checkout attempts with different stolen card numbers to find
            // one that works. A genuine customer never needs more than a
            // handful of checkout attempts in a short window.
            $throttleKey = 'checkout|' . request()->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 8)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $this->addError('email', "Too many checkout attempts. Please try again in " . ceil($seconds / 60) . " minutes.");
            return;
        }

        RateLimiter::hit($throttleKey, 600); // 10 minute decay — 8 attempts/10min/IP max

        // Cache::lock() is an atomic lock — if two nearly-simultaneous
            // requests both reach this line (e.g. a very fast double-click that
            // slips past the disabled-button check), only ONE of them actually
            // acquires the lock and proceeds. The other gets $lock->get() 
            // returning false immediately and exits without creating a
            // duplicate order. Keyed by session ID so it's specific to this
            // one shopper's checkout attempt, not a global lock affecting
            // every customer.
            $lock = Cache::lock('checkout-submit-' . session()->getId(), 10);

        if (! $lock->get()) {
            // Someone (or some duplicate request) is already processing
            // this exact checkout — silently do nothing rather than create
            // a second order.
            return;
        }
        try {
        $this->validate([
            'email' => 'required|email',
            'shipping_name' => [
                'required', 'string', 'max:255',
                'regex:/^[A-Za-z\'\-]{2,}(\s[A-Za-z\'\-]{2,})+$/',
            ],
            'shipping_street' => 'required|string|max:255',
            'shipping_suburb' => 'required|string|max:255',
            'shipping_state' => 'required|in:NSW,VIC,QLD,WA,SA,TAS,ACT,NT',
            'shipping_postcode' => 'required|digits:4',
        ], [
            'shipping_name.regex' => 'Please enter the recipient\'s full first and last name.',
            'shipping_postcode.digits' => 'Postcode must be exactly 4 digits.',
        ]);

        // Combine the structured fields into one readable line for the
        // legacy shipping_address column, so anything still reading that
        // column (e.g. an old admin view) still shows something sensible.
        $combinedAddress = "{$this->shipping_street}, {$this->shipping_suburb} {$this->shipping_state} {$this->shipping_postcode}";

        $order = Order::create([
        // Format: RJ-YYMMDD-XXXX — e.g. RJ-260815-K3M9 for an order placed
        // 15 Aug 2026. The date prefix means you (or a customer reading
        // it out over email) can tell roughly when an order was placed
        // just by looking at the number, without opening it up — useful
        // for quick sorting/support conversations. The 5 random characters
        // at the end still keep it unique even with multiple orders on
        // the same day.
        'order_number' => 'RJ-' . now()->format('ymd') . '-' . strtoupper(Str::random(5)),
            'user_id' => auth()->id(),
            'email' => $this->email,
            'shipping_name' => $this->shipping_name,
            'shipping_address' => $combinedAddress,
            'shipping_street' => $this->shipping_street,
            'shipping_suburb' => $this->shipping_suburb,
            'shipping_state' => $this->shipping_state,
            'shipping_postcode' => $this->shipping_postcode,
            'status' => 'pending',
            'subtotal' => $this->subtotal,
            'shipping_cost' => $this->shippingCost,
            'gst' => $this->gst,
            'total' => $this->total,
            'discount_code' => $this->appliedDiscount?->code,
            'discount_amount' => $this->discountAmount,
        ]);

        if ($this->appliedDiscount) {
            // Recorded now, at order creation, not waiting for payment
            // confirmation. This is a deliberate tradeoff: it means a code
            // technically gets "used up" even if the customer abandons payment
            // on Stripe's page — but it closes off a worse problem, where
            // someone could open multiple browser tabs and apply the same
            // single-use code in several simultaneous pending orders before
            // any of them completes payment. Given this is a low-stakes
            // percentage-off code (not literally giving away free money), this
            // tradeoff favours simplicity and closing the exploit over
            // being lenient to the rare genuine abandoned-payment case.
            DiscountUsage::create([
                'discount_id' => $this->appliedDiscount->id,
                'email' => $this->email,
                'order_id' => $order->id,
            ]);
        }

        foreach ($this->items as $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_variant_id' => $item->product_variant_id,
                'product_name' => $item->productVariant->product->name,
                'variant_label' => $item->productVariant->label(),
                'quantity' => $item->quantity,
                'unit_price' => $item->productVariant->price(),
                'original_price' => $item->productVariant->product->base_price,
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

        $sessionParams = [
            'payment_method_types' => ['card'],
            'line_items' => $lineItems,
            'mode' => 'payment',
            'customer_email' => $this->email,
            'success_url' => route('checkout.success') . '?session_id={CHECKOUT_SESSION_ID}&order=' . $order->order_number,
            'cancel_url' => route('checkout.cancel') . '?order=' . $order->order_number,
        ];

        if ($this->discountAmount > 0) {
            // Stripe requires a real Coupon object to apply a discount — it
            // doesn't support a negative-amount line item. This creates a
            // one-off coupon just for this checkout session (duration: 'once'
            // means it applies a single time and isn't reusable/stored beyond
            // this), rather than a general reusable Stripe-side coupon.
            $coupon = Coupon::create([
                'amount_off' => (int) round($this->discountAmount * 100),
                'currency' => 'aud',
                'duration' => 'once',
                'name' => $this->appliedDiscount->code,
            ]);

            $sessionParams['discounts'] = [['coupon' => $coupon->id]];
        }

        $session = Session::create($sessionParams);

        $order->update(['stripe_checkout_session_id' => $session->id]);

        $this->redirect($session->url, navigate: false);
        } finally {
            // Always release the lock when we're done — whether the checkout
            // succeeded, failed validation, or threw an error. Without this,
            // a single failed attempt would lock the person out of retrying
            // checkout for the full 10 seconds.
            $lock->release();
        }
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
                <label class="font-mono text-xs uppercase tracking-widest text-neutral-500">Street address</label>
                <input type="text" wire:model="shipping_street" placeholder="12 Smith Street" class="w-full mt-2 bg-transparent border border-neutral-700 focus:border-copper px-4 py-3 outline-none">
                @error('shipping_street') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="font-mono text-xs uppercase tracking-widest text-neutral-500">Suburb</label>
                    <input type="text" wire:model="shipping_suburb" class="w-full mt-2 bg-transparent border border-neutral-700 focus:border-copper px-4 py-3 outline-none">
                    @error('shipping_suburb') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="font-mono text-xs uppercase tracking-widest text-neutral-500">State</label>
                    <select wire:model="shipping_state" class="w-full mt-2 bg-ink border border-neutral-700 focus:border-copper px-4 py-3 outline-none">
                        <option value="">Select</option>
                        @foreach ($states as $state)
                            <option value="{{ $state }}">{{ $state }}</option>
                        @endforeach
                    </select>
                    @error('shipping_state') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label class="font-mono text-xs uppercase tracking-widest text-neutral-500">Postcode</label>
                <input type="text" wire:model="shipping_postcode" maxlength="4" class="w-full mt-2 bg-transparent border border-neutral-700 focus:border-copper px-4 py-3 outline-none">
                @error('shipping_postcode') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            
        </div>

        <div class="border border-neutral-800 p-6 h-fit">
            <p class="font-mono text-xs uppercase tracking-widest text-neutral-500 mb-4">Order Summary</p>
            @foreach ($items as $item)
                <div class="flex justify-between text-sm mb-2">
                    <span>{{ $item->productVariant->product->name }} ({{ $item->productVariant->label() }}) × {{ $item->quantity }}</span>
                    <span class="font-mono">${{ number_format($item->lineTotal(), 2) }}</span>
                </div>
            @endforeach

            <div class="mb-4">
                <div class="flex gap-2">
                    <input type="text" wire:model="discountCode" placeholder="Discount code"
                           class="flex-1 bg-transparent border border-neutral-700 focus:border-copper px-3 py-2 text-sm outline-none uppercase">
                    <button type="button" wire:click="applyDiscount" class="border border-neutral-700 hover:border-copper px-4 text-xs font-mono uppercase">
                        Apply
                    </button>
                </div>
                @if ($discountError)
                    <p class="text-red-400 text-xs mt-1">{{ $discountError }}</p>
                @endif
                @if ($appliedDiscount)
                    <div class="flex items-center justify-between mt-2 text-xs text-copper-light">
                        <span>{{ $appliedDiscount->code }} applied ({{ $appliedDiscount->percentage }}% off)</span>
                        <button type="button" wire:click="removeDiscount" class="text-neutral-500 hover:text-red-400">Remove</button>
                    </div>
                @endif
            </div>

            <div class="border-t border-dashed border-neutral-700 mt-4 pt-4 space-y-2 text-sm">
                <div class="flex justify-between text-neutral-400">
                    <span>Subtotal</span>
                    <span class="font-mono">${{ number_format($this->subtotal, 2) }}</span>
                </div>
                @if ($this->discountAmount > 0)
                    <div class="flex justify-between text-copper-light">
                        <span>Discount</span>
                        <span class="font-mono">-${{ number_format($this->discountAmount, 2) }}</span>
                    </div>
                @endif
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
            {{-- Moved here from the form column — sits at the bottom of the
            summary, right after the total, matching the standard checkout
            pattern (the "place order" action lives with the price it's
            confirming, not buried in the address form above it). This also
            naturally fixes the mobile stacking order: form fields → summary
            → button, rather than the button appearing before the summary. --}}
        <button type="submit"
        wire:loading.attr="disabled"
        wire:target="submit"
        class="w-full bg-copper hover:bg-copper-light text-ink font-medium py-4 transition mt-6 disabled:opacity-50 disabled:cursor-not-allowed">
        <span wire:loading.remove wire:target="submit">Continue to payment</span>
        <span wire:loading wire:target="submit">Processing...</span>
        </button>
        <p class="font-mono text-[11px] text-neutral-600 text-center mt-2">You'll be redirected to Stripe to pay securely.</p>
        </div>
    </form>
</div>