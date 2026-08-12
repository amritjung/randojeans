<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Title;
use Livewire\Component;
use Illuminate\Support\Facades\RateLimiter;

new #[Title('Create Account — Rando Jeans')] class extends Component {
    public string $name = '';
    public string $email = '';
    public string $email_confirmation = '';
    public string $phone = '';
    public string $password = '';
    public string $password_confirmation = '';
    public bool $accepts_terms = false;

    // Honeypot: a hidden field real users never see or fill in (hidden via
    // CSS, not visible to humans), but bots that auto-fill every form field
    // on a page will fill it in. If it's not empty, we silently treat the
    // submission as spam instead of processing it.
    public string $website = '';

    public function register(): void
    {
        // Keyed by IP only (not email, since a new registration has no
            // existing account to key against) — limits how many accounts can
            // be created from a single location, guarding against automated
            // mass account-creation/spam signups.
            $throttleKey = 'register|' . request()->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 8)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            $this->addError('email', "Too many signup attempts. Please try again in " . ceil($seconds / 60) . " minutes.");
            return;
        }

        RateLimiter::hit($throttleKey, 3600); // 1 hour decay — 5 signups/hour/IP max

        $this->validate([
            // regex explanation: requires at least two "words" separated by
            // a space, each at least 2 letters (allowing hyphens/apostrophes
            // for names like "Mary-Jane" or "O'Brien") — enforces a first
            // AND last name rather than a single word.
            'name' => [
                'required', 'string', 'max:255',
                'regex:/^[A-Za-z\'\-]{2,}(\s[A-Za-z\'\-]{2,})+$/',
            ],

            'email' => 'required|email:rfc,dns|max:255|unique:users,email',
            // Must exactly match the email field above — catches typos in
            // an easily-mistyped field before the account is created.
            'email_confirmation' => 'required|same:email',

            // Basic AU-friendly phone format: optional +61 or 0 prefix,
            // then 9 digits, spaces allowed. Optional field overall.
            'phone' => ['nullable', 'regex:/^(\+?61|0)[0-9\s]{9,11}$/'],

            // Password::min(8)->mixedCase()->numbers() is Laravel's built-in
            // password strength helper — mixedCase() requires both upper
            // and lower case, numbers() requires at least one digit.
            // ->uncompromised() checks the password against a database of
            // known-breached passwords (via the haveibeenpwned API) without
            // ever sending your actual password over the network — only a
            // partial hash prefix is sent, so this is safe and private.
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()->uncompromised()],

            'accepts_terms' => 'accepted', // must be checked/true to pass

            // The honeypot field must stay empty — 'size:0' means exactly
            // zero characters. A bot filling it in fails this silently.
            'website' => 'size:0',
        ], [
            'name.regex' => 'Please enter your full first and last name.',
            'email_confirmation.same' => 'Email addresses do not match.',
            'phone.regex' => 'Please enter a valid Australian phone number.',
            'accepts_terms.accepted' => 'You must accept the terms to create an account.',
        ]);

        $user = User::create([
            'name' => $this->name,
            // Storing email lowercase avoids duplicate accounts differing
            // only by case (e.g. "Test@x.com" vs "test@x.com").
            'email' => strtolower($this->email),
            'phone' => $this->phone ?: null,
            'password' => Hash::make($this->password),
        ]);

        Auth::login($user);

        // Fires Laravel's built-in verification email — a signed, time-limited
        // link the user clicks to confirm they own this email address.
        $user->sendEmailVerificationNotification();

        request()->session()->regenerate();

        \App\Models\CartItem::where('session_id', session()->getId())
            ->whereNull('user_id')
            ->update(['user_id' => $user->id]);

        $this->redirect(route('account'), navigate: false);
    }
};

?>

<div class="mx-auto max-w-md px-6 py-24">
    <h1 class="font-display text-3xl mb-8 text-center">Create Account</h1>

    <form wire:submit="register" class="space-y-4">
        {{-- Honeypot field: visually hidden via absolute positioning off-screen
             (not display:none, which some bots detect and skip) rather than
             a visible field, so real users never see or interact with it. --}}
        <input type="text" wire:model="website" tabindex="-1" autocomplete="off"
               class="absolute -left-[9999px]" aria-hidden="true">

        <div>
            <label class="font-mono text-xs uppercase tracking-widest text-neutral-500">Full name</label>
            <input type="text" wire:model="name" placeholder="First Last" class="w-full mt-2 bg-transparent border border-neutral-700 focus:border-copper px-4 py-3 outline-none">
            @error('name') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="font-mono text-xs uppercase tracking-widest text-neutral-500">Email</label>
            <input type="email" wire:model="email" class="w-full mt-2 bg-transparent border border-neutral-700 focus:border-copper px-4 py-3 outline-none">
            @error('email') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="font-mono text-xs uppercase tracking-widest text-neutral-500">Confirm email</label>
            <input type="email" wire:model="email_confirmation" class="w-full mt-2 bg-transparent border border-neutral-700 focus:border-copper px-4 py-3 outline-none">
            @error('email_confirmation') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="font-mono text-xs uppercase tracking-widest text-neutral-500">Phone (optional)</label>
            <input type="tel" wire:model="phone" placeholder="0400 000 000" class="w-full mt-2 bg-transparent border border-neutral-700 focus:border-copper px-4 py-3 outline-none">
            @error('phone') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="font-mono text-xs uppercase tracking-widest text-neutral-500">Password</label>
            <input type="password" wire:model="password" class="w-full mt-2 bg-transparent border border-neutral-700 focus:border-copper px-4 py-3 outline-none">
            <p class="text-neutral-600 text-xs mt-1">At least 8 characters, with upper and lower case letters and a number.</p>
            @error('password') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="font-mono text-xs uppercase tracking-widest text-neutral-500">Confirm password</label>
            <input type="password" wire:model="password_confirmation" class="w-full mt-2 bg-transparent border border-neutral-700 focus:border-copper px-4 py-3 outline-none">
        </div>

        <label class="flex items-start gap-3 text-xs text-neutral-400 pt-2">
            <input type="checkbox" wire:model="accepts_terms" class="mt-0.5">
            <span>I agree to the Terms of Service and Privacy Policy.</span>
        </label>
        @error('accepts_terms') <p class="text-red-400 text-xs">{{ $message }}</p> @enderror

        <button type="submit" class="w-full bg-copper hover:bg-copper-light text-ink font-medium py-4 transition mt-2">
            Create Account
        </button>
    </form>

    <p class="text-center font-mono text-xs text-neutral-500 mt-6">
        Already have an account?
        <a href="{{ route('login') }}" class="text-copper-light hover:text-bone">Log in</a>
    </p>
</div>