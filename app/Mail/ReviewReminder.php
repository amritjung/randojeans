<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReviewReminder extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order)
    {
        $this->order->load('items.productVariant.product.images');
    }

    public function build(): self
    {
        return $this->subject('How are the jeans treating you?')
            ->view('emails.review-reminder');
    }
}