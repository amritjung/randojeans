<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    protected $fillable = ['session_id', 'user_id', 'product_variant_id', 'quantity'];

    public static function currentItems()
    {
        return static::query()
            ->where('session_id', session()->getId())
            ->with('productVariant.product')
            ->get();
    }

    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function lineTotal(): float
    {
        return $this->productVariant->price() * $this->quantity;
    }
}