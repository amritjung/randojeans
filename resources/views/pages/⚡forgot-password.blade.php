<?php

use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Reset Password — Rando Jeans')] class extends Component {
    public string $email = '';
    public bool $sent = false;

    public function sendResetLink(): void
    {
        $this->validate(['email' => 'required|email']);

        // Password::sendResetLink() is Laravel's built-in mechanism — it
        // generates a secure, time-limited token, stores a hash of it in
        // the password_reset_tokens table, and emails the actual link.
        // We never see or handle the raw token ourselves.
        $status = Password::sendResetLink(['email' => $this->email]);

        // Deliberately show the same "sent" confirmation regardless of
        // whether the email actually exists in the system — this prevents
        // an attacker from using this form to check which email addresses
        // have accounts (an "enumeration" attack).
        $this->sent = true;
    }
};

?>

<div class="mx-auto max-w-md px-6 py-24">
    <h1 class="font-display text-3xl mb-4 text-center">Reset Password</h1>

    @if ($sent)
        <p class="text-neutral-400 text-center">
            If an account exists for that email, a password reset link has been sent.
        </p>
    @else
        <p class="text-neutral-500 text-sm text-center mb-8">
            Enter your email and we'll send you a link to reset your password.
        </p>

        <form wire:submit="sendResetLink" class="space-y-4">
            <div>
                <label class="font-mono text-xs uppercase tracking-widest text-neutral-500">Email</label>
                <input type="email" wire:model="email" class="w-full mt-2 bg-transparent border border-neutral-700 focus:border-copper px-4 py-3 outline-none">
                @error('email') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <button type="submit" class="w-full bg-copper hover:bg-copper-light text-ink font-medium py-4 transition">
                Send Reset Link
            </button>
        </form>
    @endif

    <p class="text-center font-mono text-xs text-neutral-500 mt-6">
        <a href="{{ route('login') }}" class="text-copper-light hover:text-bone">Back to login</a>
    </p>
</div>