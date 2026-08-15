<?php

use App\Models\CartItem;
use Livewire\Attributes\On;
use Livewire\Component;

new class extends Component {
    public int $count = 0;

    public function mount(): void
    {
        $this->refreshCount();
    }

    #[On('cart-updated')]
    public function refreshCount(): void
    {
        $this->count = CartItem::currentItems()->sum('quantity');
    }
};

?>

<a href="{{ route('cart') }}" class="relative text-neutral-400 hover:text-bone" aria-label="Cart, {{ $count }} item{{ $count === 1 ? '' : 's' }}">
    {{-- Simple outline shopping-bag icon, hand-coded SVG rather than an
         icon library dependency — keeps the whole icon set consistent
         and dependency-free, same approach as the hamburger menu. --}}
    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
        <path stroke-linecap="round" stroke-linejoin="round" d="M6 6.75V6a6 6 0 1112 0v.75m-13.5 0h15l1 14.25H4.5L5.5 6.75z" />
    </svg>

    @if ($count > 0)
        {{-- Small copper badge overlaying the icon's corner, showing the
             count — only rendered when the cart actually has something,
             so an empty cart just shows a clean bag icon with no badge. --}}
        <span class="absolute -top-1.5 -right-1.5 bg-copper text-ink text-[10px] font-mono w-4 h-4 flex items-center justify-center rounded-full">
            {{ $count }}
        </span>
    @endif
</a>