<?php

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Verify Your Email — Rando Jeans')] class extends Component {
    public bool $sent = false;

    public function resend(): void
    {
        // hasVerifiedEmail() just checks whether email_verified_at is set —
        // avoids re-sending if they've already verified in another tab.
        if (! Auth::user()->hasVerifiedEmail()) {
            Auth::user()->sendEmailVerificationNotification();
            $this->sent = true;
        }
    }
};

?>

<div class="mx-auto max-w-md px-6 py-24 text-center">
    <h1 class="font-display text-3xl mb-4">Verify Your Email</h1>
    <p class="text-neutral-400">
        We've sent a verification link to <span class="text-bone">{{ auth()->user()->email }}</span>.
        Click it to confirm your account.
    </p>

    @if ($sent)
        <p class="text-copper-light text-sm mt-6">Verification email resent.</p>
    @else
        <button wire:click="resend" class="mt-8 font-mono text-xs uppercase tracking-widest text-copper-light hover:text-bone">
            Resend verification email
        </button>
    @endif
</div>