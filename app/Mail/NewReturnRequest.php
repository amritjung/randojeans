<?php

namespace App\Mail;

use App\Models\ReturnRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewReturnRequest extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public ReturnRequest $returnRequest)
{
    // Deep eager-load everything the email template needs: the order
    // and the customer who placed it, plus each returned item's full
    // chain down to the product's image — all loaded up front so the
    // template itself doesn't trigger a cascade of extra queries.
    $this->returnRequest->load([
        'order.user',
        'items.orderItem.productVariant.product.images',
    ]);
}

    public function build(): self
    {
        return $this->subject("New return request — Order {$this->returnRequest->order->order_number}")
            ->view('emails.new-return-request');
    }
}