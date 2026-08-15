<?php

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Edit Profile — Rando Jeans')] class extends Component {
    public string $name = '';
    public string $phone = '';

    public function mount(): void
    {
        $user = Auth::user();
        $this->name = $user->name;
        $this->phone = $user->phone ?? '';
    }

    public function save(): void
    {
        $this->validate([
            'name' => [
                'required', 'string', 'max:255',
                'regex:/^[A-Za-z\'\-]{2,}(\s[A-Za-z\'\-]{2,})+$/',
            ],
            'phone' => ['nullable', 'regex:/^(\+?61|0)[0-9\s]{9,11}$/'],
        ], [
            'name.regex' => 'Please enter your full first and last name.',
            'phone.regex' => 'Please enter a valid Australian phone number.',
        ]);

        Auth::user()->update([
            'name' => $this->name,
            'phone' => $this->phone ?: null,
        ]);

        $this->redirect(route('profile'), navigate: false);
    }
};

?>

<div class="mx-auto max-w-md px-6 py-16">
    <h1 class="font-display text-3xl mb-8">Edit Profile</h1>

    {{-- Email deliberately isn't editable here — changing it would need
         a re-verification flow (proving they own the new address)
         which doesn't exist yet, so keeping it read-only avoids a
         security gap where someone could silently swap the email on
         file without confirming they actually control it. --}}
    <p class="text-neutral-500 text-xs mb-6">
        To change your email, please contact support.
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

        <button type="submit" class="w-full bg-copper hover:bg-copper-light text-ink font-medium py-4 transition mt-2">
            Save Changes
        </button>
    </form>
</div>