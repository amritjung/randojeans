<?php

use App\Models\WishlistItem;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component {
    public int $count = 0;

    public function mount(): void
    {
        $this->refreshCount();
    }

    // Listens for the same 'wishlist-updated' event already dispatched
    // by the heart-toggle component and the wishlist page's remove/
    // move-to-cart actions — so this badge stays live and in sync with
    // wishlist changes happening anywhere else on the page.
    #[On('wishlist-updated')]
    public function refreshCount(): void
    {
        $this->count = WishlistItem::where('user_id', auth()->id())->count();
    }
};

?>

<a href="{{ route('wishlist') }}" class="relative text-neutral-400 hover:text-bone" aria-label="Wishlist, {{ $count }} item{{ $count === 1 ? '' : 's' }}">
    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
        <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
    </svg>

    @if ($count > 0)
        <span class="absolute -top-1.5 -right-1.5 bg-copper text-ink text-[10px] font-mono w-4 h-4 flex items-center justify-center rounded-full">
            {{ $count }}
        </span>
    @endif
</a>