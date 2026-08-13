<?php

namespace App\Mail;

use App\Models\Product;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewReview extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Product $product, public User $reviewer)
    {
    }

    public function build(): self
    {
        return $this->subject("New review awaiting approval — {$this->product->name}")
            ->view('emails.new-review');
    }
}