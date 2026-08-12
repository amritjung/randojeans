<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrderShipped;
use App\Mail\OrderConfirmed;
use App\Mail\OrderDelivered;

class Order extends Model
{
    protected $fillable = [
        'order_number', 'user_id', 'email', 'shipping_name', 'shipping_address',
        'shipping_street', 'shipping_suburb', 'shipping_state', 'shipping_postcode',
        'status', 'carrier', 'tracking_number', 'shipped_at', 'delivered_at',
        'subtotal', 'shipping_cost', 'gst', 'total',
        'stripe_payment_intent_id', 'stripe_checkout_session_id',
    ];

    protected function casts(): array
    {
        return [
            'shipped_at' => 'datetime',
            'delivered_at' => 'datetime',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    // booted() runs once when the model class is first loaded, and is
    // where Eloquent model events (hooks that fire automatically on
    // save/update/delete) get registered.
    protected static function booted(): void
    {
        // 'updating' fires right before a save actually writes to the
        // database, giving us a chance to adjust fields based on what's
        // changing — here, automatically stamping shipped_at/delivered_at
        // the moment status changes, rather than relying on an admin to
        // remember to fill in a separate date field manually.
        static::updating(function (Order $order) {
            if ($order->isDirty('status')) {
                if ($order->status === 'shipped' && ! $order->shipped_at) {
                    $order->shipped_at = now();
                }
                if ($order->status === 'delivered' && ! $order->delivered_at) {
                    $order->delivered_at = now();
                }
            }
        });

        // 'updated' fires right after a save completes — this is where
        // we send the "your order has shipped" email, checking
        // wasChanged() so it only fires exactly once, at the moment
        // status actually transitions to 'shipped', not on every
        // subsequent save of an already-shipped order.
        static::updated(function (Order $order) {
            if (! $order->wasChanged('status')) {
                return;
            }
        
            // Each status only ever triggers its own specific email, and only
            // at the exact moment it transitions to that status — not on every
            // save of an order that's already in that state.
            match ($order->status) {
                'paid' => Mail::to($order->email)->send(new OrderConfirmed($order)),
                'shipped' => Mail::to($order->email)->send(new OrderShipped($order)),
                'delivered' => Mail::to($order->email)->send(new OrderDelivered($order)),
                default => null,
            };
        });
    }
}