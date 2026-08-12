<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderShipped extends Mailable
{
    use Queueable, SerializesModels;

    // Storing the order here means it's available inside the email
    // template below via $order — SerializesModels handles converting
    // it safely for the mail queue (if you ever enable queueing) rather
    // than trying to serialize the whole Eloquent object naively.
    public function __construct(public Order $order)
    {
        // Eager-load items (and their product/variant chain) so the email
    // template can show photos and details without triggering extra
    // queries while rendering.
    $this->order->load('items.productVariant.product.images');
    }

    public function build(): self
    {
        return $this->subject("Your order {$this->order->order_number} has shipped")
            ->view('emails.order-shipped');
    }
}