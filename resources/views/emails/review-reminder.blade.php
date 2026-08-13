<!DOCTYPE html>
<html>
<head><meta charset="utf-8"></head>
<body style="font-family: sans-serif; background: #0d0c0a; color: #f2ede4; padding: 40px 20px;">
    <div style="max-width: 500px; margin: 0 auto;">
        <h1 style="font-size: 22px;">How are they wearing in?</h1>

        <p style="color: #a3a3a3;">
            Hi {{ explode(' ', $order->shipping_name)[0] }}, it's been a week since your order
            <strong style="color: #f2ede4;">{{ $order->order_number }}</strong> arrived.
            We'd love to know what you think.
        </p>

        <div style="margin: 24px 0;">
            @foreach ($order->items as $item)
                @php
                    $product = $item->productVariant?->product;
                    $imageUrl = $product?->primaryImage()?->url();
                @endphp
                @if ($product)
                    <div style="display: table; width: 100%; margin-bottom: 14px; align-items: center;">
                        <div style="display: table-cell; width: 60px; vertical-align: top;">
                            @if ($imageUrl)
                                <img src="{{ $imageUrl }}" alt="{{ $item->product_name }}" width="50" height="50" style="object-fit: cover; border: 1px solid #404040;">
                            @endif
                        </div>
                        <div style="display: table-cell; vertical-align: middle; padding-left: 12px;">
                            <p style="margin: 0 0 8px; font-size: 14px;">{{ $item->product_name }}</p>
                            <a href="{{ route('product.show', $product) }}#reviews"
                               style="display: inline-block; background: #a8622f; color: #0d0c0a; text-decoration: none; padding: 8px 16px; font-size: 12px; font-weight: 600;">
                                Leave a Review
                            </a>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>

        <p style="color: #737373; font-size: 12px; margin-top: 30px;">
            Takes two minutes, and genuinely helps other people figure out if these are right for them.
        </p>

        <p style="color: #737373; font-size: 12px; margin-top: 30px;">
            Rando Jeans
        </p>
    </div>
</body>
</html>