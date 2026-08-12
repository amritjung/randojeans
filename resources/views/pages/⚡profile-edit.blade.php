<?php

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Edit Profile — Rando Jeans')] class extends Component {
    public string $name = '';
    public string $phone = '';
    public string $address_street = '';
    public string $address_suburb = '';
    public string $address_state = '';
    public string $address_postcode = '';
    public bool $saved = false;

    // The fixed list of Australian states/territories — used to populate
    // the dropdown so users pick from valid options rather than typing
    // free text that might be misspelled or inconsistently abbreviated.
    public array $states = ['NSW', 'VIC', 'QLD', 'WA', 'SA', 'TAS', 'ACT', 'NT'];

    public function mount(): void
    {
        $user = Auth::user();
        $this->name = $user->name;
        $this->phone = $user->phone ?? '';
        $this->address_street = $user->address_street ?? '';
        $this->address_suburb = $user->address_suburb ?? '';
        $this->address_state = $user->address_state ?? '';
        $this->address_postcode = $user->address_postcode ?? '';
    }

    public function save(): void
{
    $this->validate([
        'name' => [
            'required', 'string', 'max:255',
            'regex:/^[A-Za-z\'\-]{2,}(\s[A-Za-z\'\-]{2,})+$/',
        ],
        'phone' => ['nullable', 'regex:/^(\+?61|0)[0-9\s]{9,11}$/'],
        'address_street' => 'nullable|string|max:255',
        'address_suburb' => 'nullable|string|max:255',
        'address_state' => 'nullable|in:NSW,VIC,QLD,WA,SA,TAS,ACT,NT',
        'address_postcode' => 'nullable|digits:4',
    ], [
        'name.regex' => 'Please enter your full first and last name.',
        'phone.regex' => 'Please enter a valid Australian phone number.',
        'address_postcode.digits' => 'Postcode must be exactly 4 digits.',
    ]);

    Auth::user()->update([
        'name' => $this->name,
        'phone' => $this->phone ?: null,
        'address_street' => $this->address_street ?: null,
        'address_suburb' => $this->address_suburb ?: null,
        'address_state' => $this->address_state ?: null,
        'address_postcode' => $this->address_postcode ?: null,
    ]);

    // Redirect back to the read-only profile view rather than staying on
    // the edit form — the updated details showing on that page IS the
    // confirmation that the save worked, no separate message needed.
    $this->redirect(route('profile'), navigate: false);
}
};

?>

<div class="mx-auto max-w-md px-6 py-16">
    <h1 class="font-display text-3xl mb-2">Edit Profile</h1>
    <p class="text-neutral-500 text-sm mb-8">
        Saved here will be used to pre-fill checkout automatically next time you order.
    </p>

    <form wire:submit="save" class="space-y-4">
        <div>
            <label class="font-mono text-xs uppercase tracking-widest text-neutral-500">Full name</label>
            <input type="text" wire:model="name" class="w-full mt-2 bg-transparent border border-neutral-700 focus:border-copper px-4 py-3 outline-none">
            @error('name') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="font-mono text-xs uppercase tracking-widest text-neutral-500">Phone</label>
            <input type="tel" wire:model="phone" placeholder="0400 000 000" class="w-full mt-2 bg-transparent border border-neutral-700 focus:border-copper px-4 py-3 outline-none">
            @error('phone') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="font-mono text-xs uppercase tracking-widest text-neutral-500">Street address</label>
            <input type="text" wire:model="address_street" placeholder="12 Smith Street" class="w-full mt-2 bg-transparent border border-neutral-700 focus:border-copper px-4 py-3 outline-none">
            @error('address_street') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="font-mono text-xs uppercase tracking-widest text-neutral-500">Suburb</label>
                <input type="text" wire:model="address_suburb" class="w-full mt-2 bg-transparent border border-neutral-700 focus:border-copper px-4 py-3 outline-none">
                @error('address_suburb') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="font-mono text-xs uppercase tracking-widest text-neutral-500">State</label>
                <select wire:model="address_state" class="w-full mt-2 bg-ink border border-neutral-700 focus:border-copper px-4 py-3 outline-none">
                    <option value="">Select</option>
                    @foreach ($states as $state)
                        <option value="{{ $state }}">{{ $state }}</option>
                    @endforeach
                </select>
                @error('address_state') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <div>
            <label class="font-mono text-xs uppercase tracking-widest text-neutral-500">Postcode</label>
            <input type="text" wire:model="address_postcode" maxlength="4" placeholder="2026" class="w-full mt-2 bg-transparent border border-neutral-700 focus:border-copper px-4 py-3 outline-none">
            @error('address_postcode') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <button type="submit" class="w-full bg-copper hover:bg-copper-light text-ink font-medium py-4 transition mt-2">
            Save Changes
        </button>

        @if ($saved)
            <p class="text-copper-light text-xs text-center">Saved.</p>
        @endif
    </form>
</div>