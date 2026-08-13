<?php

namespace App\Mail;

use App\Models\ReturnRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReturnRejected extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public ReturnRequest $returnRequest)
    {
        $this->returnRequest->load('order', 'items.orderItem');
    }

    public function build(): self
    {
        return $this->subject("Update on your return request — {$this->returnRequest->order->order_number}")
            ->view('emails.return-rejected');
    }
}