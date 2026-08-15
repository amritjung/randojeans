<?php

use App\Mail\AccountDeletionRequested;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Delete Account — Rando Jeans')] class extends Component {
    public bool $requested = false;

    public function requestDeletion(): void
    {
        // Notifies admins rather than deleting anything automatically —
        // order/invoice records generally need to be retained for tax
        // and legal purposes, so this is handled as a manual request
        // rather than instant self-service deletion.
        $adminEmails = User::where('is_admin', true)->pluck('email');
        Mail::to($adminEmails)->send(new AccountDeletionRequested(auth()->user()));

        $this->requested = true;
    }
};

?>

<div class="mx-auto max-w-md px-6 py-16">
    <a href="{{ route('account') }}" class="font-mono text-xs uppercase tracking-widest text-neutral-500 hover:text-bone">
        ← Back to Account
    </a>

    <h1 class="font-display text-3xl mt-6 mb-6">Delete Account</h1>

    <p class="text-neutral-400 text-sm leading-relaxed">
        We keep order and invoice records for a period as required by Australian tax law,
        even after an account is deleted. Because of this, account deletion is handled as
        a manual request rather than an instant action.
    </p>

    <p class="text-neutral-400 text-sm leading-relaxed mt-4">
        Submitting a request below will notify our team, and we'll follow up by email to
        confirm and complete the deletion.
    </p>

    @if ($requested)
        <p class="text-copper-light text-sm mt-8">
            Request submitted. We'll be in touch shortly.
        </p>
    @else
        <button wire:click="requestDeletion" class="mt-8 border border-red-900 text-red-400 hover:bg-red-950 font-mono text-xs uppercase tracking-widest px-6 py-3 transition">
            Request Account Deletion
        </button>
    @endif
</div>