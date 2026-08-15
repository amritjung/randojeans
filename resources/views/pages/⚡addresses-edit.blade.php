<?php

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Edit Address — Rando Jeans')] class extends Component {
    public string $address_street = '';
    public string $address_suburb = '';
    public string $address_state = '';
    public string $address_postcode = '';

    public array $states = ['NSW', 'VIC', 'QLD', 'WA', 'SA', 'TAS', 'ACT', 'NT'];

    public function mount(): void
    {
        $user = Auth::user();
        $this->address_street = $user->address_street ?? '';
        $this->address_suburb = $user->address_suburb ?? '';
        $this->address_state = $user->address_state ?? '';
        $this->address_postcode = $user->address_postcode ?? '';
    }

    public function save(): void
    {
        $this->validate([
            'address_street' => 'nullable|string|max:255',
            'address_suburb' => 'nullable|string|max:255',
            'address_state' => 'nullable|in:NSW,VIC,QLD,WA,SA,TAS,ACT,NT',
            'address_postcode' => 'nullable|digits:4',
        ], [
            'address_postcode.digits' => 'Postcode must be exactly 4 digits.',
        ]);

        Auth::user()->update([
            'address_street' => $this->address_street ?: null,
            'address_suburb' => $this->address_suburb ?: null,
            'address_state' => $this->address_state ?: null,
            'address_postcode' => $this->address_postcode ?: null,
        ]);

        $this->redirect(route('addresses'), navigate: false);
    }
};

?>

<div class="mx-auto max-w-md px-6 py-16">
    <h1 class="font-display text-3xl mb-8">Edit Address</h1>

    <form wire:submit="save" class="space-y-4">
        <div>
            <label class="font-mono text-xs uppercase tracking-widest text-neutral-500">Street address</label>
            <input type="text" wire:model="address_street" class="w-full mt-2 bg-transparent border border-neutral-700 focus:border-copper px-4 py-3 outline-none">
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="font-mono text-xs uppercase tracking-widest text-neutral-500">Suburb</label>
                <input type="text" wire:model="address_suburb" class="w-full mt-2 bg-transparent border border-neutral-700 focus:border-copper px-4 py-3 outline-none">
            </div>
            <div>
                <label class="font-mono text-xs uppercase tracking-widest text-neutral-500">State</label>
                <select wire:model="address_state" class="w-full mt-2 bg-ink border border-neutral-700 focus:border-copper px-4 py-3 outline-none">
                    <option value="">Select</option>
                    @foreach ($states as $state)
                        <option value="{{ $state }}">{{ $state }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div>
            <label class="font-mono text-xs uppercase tracking-widest text-neutral-500">Postcode</label>
            <input type="text" wire:model="address_postcode" maxlength="4" class="w-full mt-2 bg-transparent border border-neutral-700 focus:border-copper px-4 py-3 outline-none">
            @error('address_postcode') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <button type="submit" class="w-full bg-copper hover:bg-copper-light text-ink font-medium py-4 transition mt-2">
            Save Changes
        </button>
    </form>
</div>