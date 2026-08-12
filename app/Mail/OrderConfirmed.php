<?php

namespace App\Mail;

use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;

class OrderConfirmed extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order)
    {
        $this->order->load('items');
    }

    public function build(): self
    {
        return $this->subject("Order confirmed — {$this->order->order_number}")
            ->view('emails.order-confirmed');
    }

    // attachments() is where Laravel expects file attachments to be
    // defined for a Mailable — this renders the invoice Blade view to
    // PDF bytes on the fly and attaches it, without ever saving a PDF
    // file to disk first.
    public function attachments(): array
    {
        $pdf = Pdf::loadView('pdfs.invoice', ['order' => $this->order]);

        return [
            Attachment::fromData(fn () => $pdf->output(), "invoice-{$this->order->order_number}.pdf")
                ->withMime('application/pdf'),
        ];
    }
}