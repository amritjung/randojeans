<?php

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Set New Password — Rando Jeans')] class extends Component {
    public string $token = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    // mount() reads the token from the URL and pre-fills the email from
    // the link's query string — both were embedded in the emailed link
    // by Laravel's password broker, not typed by the user.
    public function mount(string $token): void
    {
        $this->token = $token;
        $this->email = request()->query('email', '');
    }

    public function resetPassword(): void
    {
        $this->validate([
            'email' => 'required|email',
            // Same strength rules as registration — a password reset
            // shouldn't be a backdoor around the strength requirements.
            'password' => ['required', 'confirmed', PasswordRule::min(8)->mixedCase()->numbers()->uncompromised()],
        ]);

        // Password::reset() validates the token against what's stored
        // (hashed) in password_reset_tokens, checks it hasn't expired,
        // and only then runs the closure to actually update the password.
        $status = Password::reset(
            ['email' => $this->email, 'password' => $this->password, 'password_confirmation' => $this->password_confirmation, 'token' => $this->token],
            function ($user) {
                $user->forceFill(['password' => Hash::make($this->password)])->save();
                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            $this->redirect(route('login'), navigate: false);
            return;
        }

        // Covers an invalid or expired token — the link only works once
        // and only within a limited time window (60 minutes by default).
        $this->addError('email', 'This reset link is invalid or has expired.');
    }
};

?>

<div class="mx-auto max-w-md px-6 py-24">
    <h1 class="font-display text-3xl mb-8 text-center">Set New Password</h1>

    <form wire:submit="resetPassword" class="space-y-4">
        <div>
            <label class="font-mono text-xs uppercase tracking-widest text-neutral-500">Email</label>
            <input type="email" wire:model="email" class="w-full mt-2 bg-transparent border border-neutral-700 focus:border-copper px-4 py-3 outline-none">
            @error('email') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="font-mono text-xs uppercase tracking-widest text-neutral-500">New password</label>
            <input type="password" wire:model="password" class="w-full mt-2 bg-transparent border border-neutral-700 focus:border-copper px-4 py-3 outline-none">
            @error('password') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="font-mono text-xs uppercase tracking-widest text-neutral-500">Confirm new password</label>
            <input type="password" wire:model="password_confirmation" class="w-full mt-2 bg-transparent border border-neutral-700 focus:border-copper px-4 py-3 outline-none">
        </div>

        <button type="submit" class="w-full bg-copper hover:bg-copper-light text-ink font-medium py-4 transition">
            Reset Password
        </button>
    </form>
</div>