<?php

namespace App\Models;

use App\Mail\NewReturnRequest;
use App\Mail\ReturnApproved;
use App\Mail\ReturnRejected;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class ReturnRequest extends Model
{
    protected $fillable = [
        'order_id', 'user_id', 'reason', 'notes', 'status',
        'admin_response', 'refund_amount', 'stripe_refund_id',
        'reference_number', 'approved_at',
    ];

    protected function casts(): array
    {
        return ['approved_at' => 'datetime'];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ReturnRequestItem::class);
    }

    // The date by which the physical item needs to actually arrive back,
    // measured from when it was approved (not from delivery) — gives a
    // clear, fair deadline once the customer knows they're allowed to
    // send it. Returns null if not yet approved (nothing to count from).
    public function returnDeadline(): ?\Carbon\CarbonInterface
    {
        return $this->approved_at?->copy()->addDays(14);
    }

    protected static function booted(): void
    {
        static::creating(function (ReturnRequest $returnRequest) {
            // Generates a short, human-writable reference like "RR-A3F9K2"
            // — uppercase letters/numbers only, easy to read and copy by
            // hand onto a parcel, unlike a raw database ID or UUID.
            $returnRequest->reference_number = 'RR-' . strtoupper(Str::random(6));
        });

        static::created(function (ReturnRequest $returnRequest) {
            $adminEmails = User::where('is_admin', true)->pluck('email');
            Mail::to($adminEmails)->send(new NewReturnRequest($returnRequest));
        });

        static::updating(function (ReturnRequest $returnRequest) {
            if ($returnRequest->isDirty('status') && $returnRequest->status === 'approved' && ! $returnRequest->approved_at) {
                $returnRequest->approved_at = now();
            }
        });

        static::updated(function (ReturnRequest $returnRequest) {
            if (! $returnRequest->wasChanged('status')) {
                return;
            }

            match ($returnRequest->status) {
                'approved' => Mail::to($returnRequest->order->email)->send(new ReturnApproved($returnRequest)),
                'rejected' => Mail::to($returnRequest->order->email)->send(new ReturnRejected($returnRequest)),
                default => null,
            };
        });
    }
}