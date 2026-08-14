<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
        'stripe_payment_intent_id', 'stripe_checkout_session_id', 'discount_code', 'discount_amount',
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function returnRequests(): HasMany
{
    return $this->hasMany(ReturnRequest::class);
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
    
    // A return can only be requested once an order has actually been
    // delivered (not while still pending/paid/shipped — the customer
    // doesn't have the item yet), and within a 30-day window after
    // delivery — a common, reasonable return policy window. Adjust the
    // "30" if you want a different policy.
    public function isReturnEligible(): bool
    {
        if ($this->status !== 'delivered' || ! $this->delivered_at) {
            return false;
        }

        return $this->delivered_at->diffInDays(now()) <= 30
            && $this->returnRequests->isEmpty(); // only one request per order
    }

    // Maps the raw database status value to a friendlier customer-facing
// label. The stored value stays the same ('fulfilled' in the database
// row, order form, etc.) — only how it's displayed changes, so no
// data migration is needed for any orders already in this status.
public function statusLabel(): string
{
    return match ($this->status) {
        'pending' => 'Pending',
        'paid' => 'Paid',
        'fulfilled' => 'Ready to Ship',
        'shipped' => 'Shipped',
        'delivered' => 'Delivered',
        'cancelled' => 'Cancelled',
        default => ucfirst($this->status),
    };
}
}