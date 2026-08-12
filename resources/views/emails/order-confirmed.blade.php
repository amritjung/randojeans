<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body style="font-family: sans-serif; background: #0d0c0a; color: #f2ede4; padding: 40px 20px;">
    <div style="max-width: 500px; margin: 0 auto;">
        <h1 style="font-size: 24px;">Order confirmed</h1>

        <p style="color: #a3a3a3;">
            Thanks, {{ explode(' ', $order->shipping_name)[0] }}. Your order
            <strong style="color: #f2ede4;">{{ $order->order_number }}</strong> is confirmed and being prepared.
        </p>

        <div style="border: 1px solid #404040; padding: 20px; margin: 24px 0;">
            @foreach ($order->items as $item)
                @php
                    $imageUrl = $item->productVariant?->product?->primaryImage()?->url();
                @endphp
                <div style="display: table; width: 100%; margin-bottom: 14px;">
                    <div style="display: table-cell; width: 60px; vertical-align: top;">
                        @if ($imageUrl)
                            <img src="{{ $imageUrl }}" alt="{{ $item->product_name }}" width="50" height="50" style="object-fit: cover; border: 1px solid #404040;">
                        @endif
                    </div>
                    <div style="display: table-cell; vertical-align: top; padding-left: 12px;">
                        <p style="margin: 0; font-size: 14px;">{{ $item->product_name }}</p>
                        <p style="margin: 2px 0 0; font-size: 12px; color: #737373;">Size {{ $item->variant_label }} &times; {{ $item->quantity }}</p>
                    </div>
                    <div style="display: table-cell; vertical-align: top; text-align: right; font-family: monospace; font-size: 14px;">
                        ${{ number_format($item->unit_price * $item->quantity, 2) }}
                    </div>
                </div>
            @endforeach

            <div style="border-top: 1px dashed #404040; margin-top: 16px; padding-top: 16px;">
                <div style="display: flex; justify-content: space-between; color: #a3a3a3; font-size: 14px; margin-bottom: 6px;">
                    <span>Subtotal</span>
                    <span style="font-family: monospace;">${{ number_format($order->subtotal, 2) }}</span>
                </div>
                <div style="display: flex; justify-content: space-between; color: #a3a3a3; font-size: 14px; margin-bottom: 6px;">
                    <span>Shipping</span>
                    <span style="font-family: monospace;">{{ $order->shipping_cost > 0 ? '$' . number_format($order->shipping_cost, 2) : 'Free' }}</span>
                </div>
                <div style="display: flex; justify-content: space-between; font-size: 18px; margin-top: 10px;">
                    <span>Total</span>
                    <span>${{ number_format($order->total, 2) }} AUD</span>
                </div>
            </div>
        </div>

        <p style="color: #a3a3a3;">
            Shipping to:<br>
            {{ $order->shipping_name }}<br>
            {{ $order->shipping_street ?? $order->shipping_address }}<br>
            @if ($order->shipping_suburb)
                {{ $order->shipping_suburb }} {{ $order->shipping_state }} {{ $order->shipping_postcode }}
            @endif
        </p>

        <p style="color: #737373; font-size: 12px; margin-top: 40px;">
            Rando Jeans
        </p>
    </div>
</body>
</html>