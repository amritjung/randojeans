<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body style="font-family: sans-serif; background: #0d0c0a; color: #f2ede4; padding: 40px 20px;">
    <div style="max-width: 500px; margin: 0 auto;">
        <h1 style="font-size: 24px;">Your order is on its way</h1>

        <p style="color: #a3a3a3;">
            Order <strong style="color: #f2ede4;">{{ $order->order_number }}</strong> has shipped.
        </p>

        <div style="margin: 24px 0;">
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
                </div>
            @endforeach
        </div>

        @if ($order->tracking_number)
            <div style="border: 1px solid #404040; padding: 20px; margin: 24px 0;">
                <p style="font-size: 12px; text-transform: uppercase; letter-spacing: 0.1em; color: #737373; margin: 0 0 8px;">
                    Tracking Number
                </p>
                <p style="font-family: monospace; font-size: 16px; margin: 0;">
                    {{ $order->tracking_number }}
                </p>
                @if ($order->carrier)
                    <p style="color: #a3a3a3; font-size: 14px; margin: 8px 0 0;">
                        Carrier: {{ $order->carrier }}
                    </p>
                @endif
            </div>
        @endif  

        <p style="color: #a3a3a3;">
            Shipping to: {{ $order->shipping_name }}<br>
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