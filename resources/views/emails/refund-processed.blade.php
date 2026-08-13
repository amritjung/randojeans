<!DOCTYPE html>
<html>
<head><meta charset="utf-8"></head>
<body style="font-family: sans-serif; background: #0d0c0a; color: #f2ede4; padding: 40px 20px;">
    <div style="max-width: 500px; margin: 0 auto;">
        <p style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.1em; color: #a8622f; margin: 0 0 12px;">
            Refund Confirmed
        </p>

        <h1 style="font-size: 24px; margin: 0 0 16px;">
            Your refund is on its way
        </h1>

        <p style="color: #a3a3a3;">
            Hi {{ explode(' ', $returnRequest->order->shipping_name)[0] }}, we've processed your refund for
            order <strong style="color: #f2ede4;">{{ $returnRequest->order->order_number }}</strong>.
            Thanks for your patience — we're sorry this one didn't work out.
        </p>

        <div style="border: 1px solid #404040; padding: 20px; margin: 24px 0;">
            @foreach ($returnRequest->items as $item)
                @php
                    $imageUrl = $item->orderItem->productVariant?->product?->primaryImage()?->url();
                @endphp
                <div style="display: table; width: 100%; margin-bottom: 14px;">
                    <div style="display: table-cell; width: 56px; vertical-align: top;">
                        @if ($imageUrl)
                            <img src="{{ $imageUrl }}" alt="{{ $item->orderItem->product_name }}" width="48" height="48" style="object-fit: cover; border: 1px solid #404040;">
                        @endif
                    </div>
                    <div style="display: table-cell; vertical-align: top; padding-left: 12px; font-size: 14px;">
                        <p style="margin: 0;">{{ $item->orderItem->product_name }}</p>
                        <p style="margin: 2px 0 0; color: #a3a3a3; font-size: 12px;">
                            Size {{ $item->orderItem->variant_label }} &times; {{ $item->quantity }}
                        </p>
                    </div>
                </div>
            @endforeach

            <div style="border-top: 1px dashed #404040; margin-top: 12px; padding-top: 16px; display: flex; justify-content: space-between; align-items: center;">
                <span style="font-size: 14px; color: #a3a3a3;">Amount refunded</span>
                <span style="font-family: monospace; font-size: 20px; color: #c9905f;">
                    ${{ number_format($returnRequest->refund_amount, 2) }} AUD
                </span>
            </div>
        </div>

        <p style="color: #a3a3a3; font-size: 13px;">
            This has been refunded to your original payment method. Depending on your bank,
            it can take <strong style="color: #f2ede4;">5-10 business days</strong> to appear on your statement.
        </p>

        <p style="color: #a3a3a3; margin-top: 24px;">
            If you have any questions about this refund, just reply to this email.
        </p>

        <p style="color: #737373; font-size: 12px; margin-top: 40px;">
            Rando Jeans
        </p>
    </div>
</body>
</html>