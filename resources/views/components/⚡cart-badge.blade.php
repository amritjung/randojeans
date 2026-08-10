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

<a href="{{ route('cart') }}" class="hover:text-bone">
    Cart ({{ $count }})
</a>