<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'order_number', 'user_id', 'email', 'shipping_name', 'shipping_address',
        'status', 'subtotal', 'shipping_cost', 'gst', 'total',
        'stripe_payment_intent_id', 'stripe_checkout_session_id',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}