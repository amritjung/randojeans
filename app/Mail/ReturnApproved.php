<?php

namespace App\Mail;

use App\Models\ReturnRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReturnApproved extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public ReturnRequest $returnRequest)
    {
        $this->returnRequest->load('order', 'items.orderItem.productVariant.product.images');
    }

    public function build(): self
    {
        return $this->subject("Return approved — please send it back ({$this->returnRequest->reference_number})")
            ->view('emails.return-approved');
    }
}