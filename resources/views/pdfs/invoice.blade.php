<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        /* dompdf has limited CSS support compared to a real browser —
           keep styling simple (basic tables, inline-ish styles) rather
           than relying on flexbox/grid, which dompdf doesn't render. */
        body { font-family: sans-serif; color: #1a1a1a; font-size: 12px; }
        h1 { font-size: 20px; margin-bottom: 4px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { text-align: left; border-bottom: 2px solid #1a1a1a; padding: 8px 0; font-size: 10px; text-transform: uppercase; letter-spacing: 0.05em; }
        td { padding: 8px 0; border-bottom: 1px solid #ddd; }
        .right { text-align: right; }
        .totals td { border: none; padding: 4px 0; }
        .label { color: #666; }
        .grand-total { font-size: 16px; font-weight: bold; border-top: 2px solid #1a1a1a; padding-top: 8px; }
    </style>
</head>
<body>
    <h1>Rando Jeans</h1>
    <p class="label">Tax Invoice</p>

    <table style="margin-top: 20px;">
        <tr>
            <td class="label">Invoice Number</td>
            <td>{{ $order->order_number }}</td>
            <td class="label">Date</td>
            <td>{{ $order->created_at->format('d M Y') }}</td>
        </tr>
    </table>

    <p style="margin-top: 20px;">
        <strong>Billed To:</strong><br>
        {{ $order->shipping_name }}<br>
        {{ $order->email }}<br>
        {{ $order->shipping_street ?? $order->shipping_address }}<br>
        @if ($order->shipping_suburb)
            {{ $order->shipping_suburb }} {{ $order->shipping_state }} {{ $order->shipping_postcode }}
        @endif
    </p>

    <table>
        <thead>
            <tr>
                <th>Item</th>
                <th>Size</th>
                <th class="right">Qty</th>
                <th class="right">Unit Price</th>
                <th class="right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($order->items as $item)
                <tr>
                    <td>{{ $item->product_name }}</td>
                    <td>{{ $item->variant_label }}</td>
                    <td class="right">{{ $item->quantity }}</td>
                    <td class="right">${{ number_format($item->unit_price, 2) }}</td>
                    <td class="right">${{ number_format($item->unit_price * $item->quantity, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals" style="width: 250px; margin-left: auto;">
        <tr>
            <td class="label">Subtotal</td>
            <td class="right">${{ number_format($order->subtotal, 2) }}</td>
        </tr>
        <tr>
            <td class="label">Shipping</td>
            <td class="right">{{ $order->shipping_cost > 0 ? '$' . number_format($order->shipping_cost, 2) : 'Free' }}</td>
        </tr>
        <tr>
            <td class="label">Includes GST</td>
            <td class="right">${{ number_format($order->gst, 2) }}</td>
        </tr>
        <tr class="grand-total">
            <td>Total (AUD)</td>
            <td class="right">${{ number_format($order->total, 2) }}</td>
        </tr>
    </table>

    {{-- ABN placeholder: Australian tax invoices are technically required
         to show a valid ABN once registered. Uncomment and fill this in
         once yours comes through — leaving it out entirely for now since
         a placeholder/fake ABN would be worse than omitting it. --}}
    {{-- <p style="margin-top: 40px; color: #666; font-size: 10px;">ABN: XX XXX XXX XXX</p> --}}
</body>
</html>