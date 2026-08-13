<!DOCTYPE html>
<html>
<head><meta charset="utf-8"></head>
<body style="font-family: sans-serif; background: #0d0c0a; color: #f2ede4; padding: 40px 20px;">
    <div style="max-width: 500px; margin: 0 auto;">
        <p style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.1em; color: #a3a3a3; margin: 0 0 12px;">
            Return Request Update
        </p>

        <h1 style="font-size: 22px; margin: 0 0 16px;">
            We can't process this return
        </h1>

        <p style="color: #a3a3a3;">
            Hi {{ explode(' ', $returnRequest->order->shipping_name)[0] }}, we've reviewed your return
            request for order <strong style="color: #f2ede4;">{{ $returnRequest->order->order_number }}</strong>,
            and unfortunately we're not able to process it.
        </p>

        @if ($returnRequest->admin_response)
            <div style="border: 1px solid #404040; padding: 20px; margin: 24px 0;">
                <p style="margin: 0 0 6px; color: #737373; text-transform: uppercase; font-size: 10px; letter-spacing: 0.05em;">
                    Reason
                </p>
                <p style="margin: 0; font-size: 14px;">{{ $returnRequest->admin_response }}</p>
            </div>
        @endif

        <div style="margin: 20px 0;">
            @foreach ($returnRequest->items as $item)
                <p style="font-size: 14px; margin: 0 0 6px; color: #a3a3a3;">
                    {{ $item->orderItem->product_name }} ({{ $item->orderItem->variant_label }}) &times; {{ $item->quantity }}
                </p>
            @endforeach
        </div>

        <p style="color: #a3a3a3;">
            If you think this is a mistake, or want to talk it through, just reply to this email —
            we're happy to take another look.
        </p>

        <p style="color: #737373; font-size: 12px; margin-top: 40px;">
            Rando Jeans
        </p>
    </div>
</body>
</html>