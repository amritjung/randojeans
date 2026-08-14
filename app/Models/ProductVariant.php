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

    // Priority order: a variant-specific override (rare — e.g. a
    // particular size priced differently) wins first if set; otherwise,
    // the product's sale price if it's actually on sale; otherwise, the
    // normal base price. Because cart, checkout, and order totals all
    // already call this one method, sale pricing now applies everywhere
    // automatically without touching any of that logic separately.
    public function price(): float
    {
        return (float) ($this->price_override ?? $this->product->currentPrice());
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
