<?php

use App\Models\Order;
use App\Models\ReturnRequest;
use App\Models\ReturnRequestItem;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Request a Return — Rando Jeans')] class extends Component {
    public Order $order;
    // Keyed by order_item_id => quantity to return (0 = not selected).
    // Starting everything at 0 means the customer explicitly chooses
    // what they're returning, rather than defaulting to "return everything."
    public array $quantities = [];
    public string $reason = '';
    public string $notes = '';

    public array $reasons = [
        'Wrong size', 'Changed my mind', 'Item faulty or defective',
        'Wrong item received', 'Other',
    ];

    public function mount(Order $order): void
    {
        abort_unless($order->user_id === auth()->id(), 403);
        abort_unless($order->isReturnEligible(), 403);

        $this->order = $order->load('items');

        foreach ($this->order->items as $item) {
            $this->quantities[$item->id] = 0;
        }
    }

    public function submit(): void
    {
        $this->validate([
            'reason' => 'required|string',
            'notes' => 'nullable|string|max:1000',
        ]);

        // At least one item must have a quantity greater than 0 selected —
        // otherwise this would be a "return request" for nothing.
        $selectedItems = collect($this->quantities)->filter(fn ($qty) => $qty > 0);

        if ($selectedItems->isEmpty()) {
            $this->addError('reason', 'Please select at least one item to return.');
            return;
        }

        $returnRequest = ReturnRequest::create([
            'order_id' => $this->order->id,
            'user_id' => auth()->id(),
            'reason' => $this->reason,
            'notes' => $this->notes,
            'status' => 'pending',
        ]);

        foreach ($selectedItems as $orderItemId => $quantity) {
            ReturnRequestItem::create([
                'return_request_id' => $returnRequest->id,
                'order_item_id' => $orderItemId,
                'quantity' => $quantity,
            ]);
        }

        $this->redirect(route('orders.show', $this->order), navigate: false);
    }
};

?>

<div class="mx-auto max-w-2xl px-6 py-16">
    <a href="{{ route('orders.show', $order) }}" class="font-mono text-xs uppercase tracking-widest text-neutral-500 hover:text-bone">
        ← Back to Order
    </a>

    <h1 class="font-display text-3xl mt-6 mb-8">Request a Return</h1>

    <form wire:submit="submit" class="space-y-6">
        <div>
            <p class="font-mono text-xs uppercase tracking-widest text-neutral-500 mb-4">Select items to return</p>
            <div class="space-y-4">
                @foreach ($order->items as $item)
                    <div class="flex items-center justify-between border border-neutral-800 p-4">
                        <div>
                            <p class="text-sm">{{ $item->product_name }}</p>
                            <p class="text-xs text-neutral-500 mt-1">{{ $item->variant_label }} — Purchased {{ $item->quantity }}</p>
                        </div>
                        <select wire:model="quantities.{{ $item->id }}" class="bg-ink border border-neutral-700 px-3 py-2 text-sm">
                            <option value="0">Not returning</option>
                            @for ($i = 1; $i <= $item->quantity; $i++)
                                <option value="{{ $i }}">Return {{ $i }}</option>
                            @endfor
                        </select>
                    </div>
                @endforeach
            </div>
        </div>

        <div>
            <label class="font-mono text-xs uppercase tracking-widest text-neutral-500">Reason</label>
            <select wire:model="reason" class="w-full mt-2 bg-ink border border-neutral-700 focus:border-copper px-4 py-3 outline-none">
                <option value="">Select a reason</option>
                @foreach ($reasons as $r)
                    <option value="{{ $r }}">{{ $r }}</option>
                @endforeach
            </select>
            @error('reason') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="font-mono text-xs uppercase tracking-widest text-neutral-500">Additional notes (optional)</label>
            <textarea wire:model="notes" rows="4" class="w-full mt-2 bg-transparent border border-neutral-700 focus:border-copper px-4 py-3 outline-none"></textarea>
        </div>

        <button type="submit" class="w-full bg-copper hover:bg-copper-light text-ink font-medium py-4 transition">
            Submit Return Request
        </button>
    </form>
</div>