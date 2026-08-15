<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Login & Security — Rando Jeans')] class extends Component {
    public string $current_password = '';
    public string $new_password = '';
    public string $new_password_confirmation = '';
    public bool $saved = false;

    public function updatePassword(): void
    {
        $this->validate([
            // 'current_password' is a built-in Laravel validation rule —
            // it automatically checks the value against the logged-in
            // user's actual current password hash, so we don't need to
            // manually write that comparison ourselves.
            'current_password' => ['required', 'current_password'],
            'new_password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()->uncompromised()],
        ], [
            'current_password.current_password' => 'That\'s not your current password.',
        ]);

        Auth::user()->update([
            'password' => Hash::make($this->new_password),
        ]);

        $this->reset(['current_password', 'new_password', 'new_password_confirmation']);
        $this->saved = true;
    }
};

?>

<div class="mx-auto max-w-md px-6 py-16">
    <a href="{{ route('account') }}" class="font-mono text-xs uppercase tracking-widest text-neutral-500 hover:text-bone">
        ← Back to Account
    </a>

    <h1 class="font-display text-3xl mt-6 mb-8">Login & Security</h1>

    <div>
        <p class="font-mono text-xs uppercase tracking-widest text-neutral-500">Email</p>
        <p class="mt-1 text-bone">{{ auth()->user()->email }}</p>
    </div>

    <form wire:submit="updatePassword" class="space-y-4 mt-8 border-t border-neutral-800 pt-8">
        <p class="font-mono text-xs uppercase tracking-widest text-neutral-500">Change Password</p>

        <div>
            <label class="text-xs text-neutral-500">Current password</label>
            <input type="password" wire:model="current_password" class="w-full mt-2 bg-transparent border border-neutral-700 focus:border-copper px-4 py-3 outline-none">
            @error('current_password') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="text-xs text-neutral-500">New password</label>
            <input type="password" wire:model="new_password" class="w-full mt-2 bg-transparent border border-neutral-700 focus:border-copper px-4 py-3 outline-none">
            @error('new_password') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="text-xs text-neutral-500">Confirm new password</label>
            <input type="password" wire:model="new_password_confirmation" class="w-full mt-2 bg-transparent border border-neutral-700 focus:border-copper px-4 py-3 outline-none">
        </div>

        <button type="submit" class="w-full bg-copper hover:bg-copper-light text-ink font-medium py-4 transition mt-2">
            Update Password
        </button>

        @if ($saved)
            <p class="text-copper-light text-xs text-center">Password updated.</p>
        @endif
    </form>
</div>