<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariant extends Model
{
    protected $fillable = [
        'product_id', 'size', 'inseam', 'sku', 'price_override', 'stock_quantity',
    ];

    protected function casts(): array
    {
        return [
            'price_override' => 'decimal:2',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // Falls back to the product's base price if this variant has no override —
    // this is the one place "what does this variant cost" is decided.
    public function price(): float
    {
        return (float) ($this->price_override ?? $this->product->base_price);
    }

    public function inStock(): bool
    {
        return $this->stock_quantity > 0;
    }

    public function label(): string
    {
        return trim("Size {$this->size}".($this->inseam ? ", {$this->inseam}" : ''));
    }
}
