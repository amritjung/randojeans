<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Log In — Rando Jeans')] class extends Component {
    public string $email = '';
    public string $password = '';

    public function login(): void
    {
        $this->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Rate limiting key combines email + IP address, so a lockout is
        // specific to "this person trying this email from this location" —
        // it won't lock out someone else on a shared network, and it won't
        // let an attacker bypass the limit just by switching IPs while
        // still guessing the same email's password.
        $throttleKey = Str::lower($this->email) . '|' . request()->ip();

        // If there have been 5+ failed attempts, block further attempts
        // for a cooldown period rather than letting someone brute-force
        // guess passwords indefinitely.
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $this->addError('email', "Too many login attempts. Please try again in {$seconds} seconds.");
            return;
        }

        if (! Auth::attempt(['email' => $this->email, 'password' => $this->password])) {
            // Records this as a failed attempt, counting toward the limit above.
            RateLimiter::hit($throttleKey, 60); // 60 second decay per hit
            $this->addError('email', 'Those credentials don\'t match our records.');
            return;
        }

        // Successful login clears any prior failed-attempt count for this key.
        RateLimiter::clear($throttleKey);

        request()->session()->regenerate();

        \App\Models\CartItem::where('session_id', session()->getId())
            ->whereNull('user_id')
            ->update(['user_id' => Auth::id()]);

        $this->redirect(route('account'), navigate: false);
    }
};

?>

<div class="mx-auto max-w-md px-6 py-24">
    <h1 class="font-display text-3xl mb-8 text-center">Log In</h1>

    <form wire:submit="login" class="space-y-4">
        <div>
            <label class="font-mono text-xs uppercase tracking-widest text-neutral-500">Email</label>
            <input type="email" wire:model="email" class="w-full mt-2 bg-transparent border border-neutral-700 focus:border-copper px-4 py-3 outline-none">
            @error('email') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="font-mono text-xs uppercase tracking-widest text-neutral-500">Password</label>
            <input type="password" wire:model="password" class="w-full mt-2 bg-transparent border border-neutral-700 focus:border-copper px-4 py-3 outline-none">
            @error('password') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <p class="text-right">
            <a href="{{ route('password.request') }}" class="font-mono text-xs text-neutral-500 hover:text-copper-light">
                Forgot password?
            </a>
        </p>

        <button type="submit" class="w-full bg-copper hover:bg-copper-light text-ink font-medium py-4 transition mt-2">
            Log In
        </button>
    </form>

    <p class="text-center font-mono text-xs text-neutral-500 mt-6">
        No account?
        <a href="{{ route('register') }}" class="text-copper-light hover:text-bone">Create one</a>
    </p>
</div>