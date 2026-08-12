<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;

class StripeWebhookController extends Controller
{
    public function __invoke(Request $request)
    {
        try {
            $event = Webhook::constructEvent(
                $request->getContent(),
                $request->header('Stripe-Signature'),
                config('services.stripe.webhook_secret'),
            );
        } catch (SignatureVerificationException $e) {
            Log::warning('Stripe webhook signature verification failed: ' . $e->getMessage());
            return response('Invalid signature', 400);
        }

        if ($event->type === 'checkout.session.completed') {
            $session = $event->data->object;
            $order = Order::where('stripe_checkout_session_id', $session->id)->first();

            if (! $order) {
                // Worth keeping this log permanently — if it ever fires in
                // production, it means Stripe confirmed a payment we have
                // no matching order for, which would need investigating.
                Log::warning("Stripe webhook: no order found for session {$session->id}");
            } elseif ($order->status === 'pending') {
                $order->update([
                    'status' => 'paid',
                    'stripe_payment_intent_id' => $session->payment_intent,
                ]);
                Log::info("Order {$order->order_number} marked paid via webhook.");
            }
        }

        return response('Webhook handled', 200);
    }
}