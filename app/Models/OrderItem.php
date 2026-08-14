<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id', 'product_variant_id', 'product_name', 'variant_label', 'quantity', 'unit_price', 'original_price',
    ];

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function order(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
    public function wasOnSale(): bool
    {
        return $this->original_price !== null && $this->unit_price < $this->original_price;
    }

    public function savingsAmount(): float
    {
        return $this->wasOnSale() ? ($this->original_price - $this->unit_price) * $this->quantity : 0;
    }
}