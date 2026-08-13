<!DOCTYPE html>
<html>
<head><meta charset="utf-8"></head>
<body style="font-family: sans-serif; background: #0d0c0a; color: #f2ede4; padding: 40px 20px;">
    <div style="max-width: 500px; margin: 0 auto;">
        <h1 style="font-size: 22px;">New Return Request</h1>

        <p style="color: #a3a3a3;">
            Order <strong style="color: #f2ede4;">{{ $returnRequest->order->order_number }}</strong>
        </p>

        {{-- Customer details --}}
        <div style="border: 1px solid #404040; padding: 16px; margin: 20px 0; font-size: 13px;">
            <p style="margin: 0 0 4px; color: #737373; text-transform: uppercase; font-size: 10px; letter-spacing: 0.05em;">Customer</p>
            <p style="margin: 0;">{{ $returnRequest->order->shipping_name }}</p>
            <p style="margin: 2px 0 0; color: #a3a3a3;">{{ $returnRequest->order->email }}</p>
            @if ($returnRequest->order->user?->phone)
                <p style="margin: 2px 0 0; color: #a3a3a3;">{{ $returnRequest->order->user->phone }}</p>
            @endif
        </div>

        {{-- Order timeline --}}
        <div style="display: table; width: 100%; margin-bottom: 20px; font-size: 13px;">
            <div style="display: table-cell; width: 50%;">
                <p style="margin: 0; color: #737373; text-transform: uppercase; font-size: 10px; letter-spacing: 0.05em;">Purchased</p>
                <p style="margin: 2px 0 0;">{{ $returnRequest->order->created_at->format('d M Y') }}</p>
            </div>
            <div style="display: table-cell; width: 50%;">
                <p style="margin: 0; color: #737373; text-transform: uppercase; font-size: 10px; letter-spacing: 0.05em;">Delivered</p>
                <p style="margin: 2px 0 0;">{{ $returnRequest->order->delivered_at?->format('d M Y') ?? '—' }}</p>
            </div>
        </div>

        {{-- Reason --}}
        <div style="margin-bottom: 20px;">
            <p style="margin: 0; color: #737373; text-transform: uppercase; font-size: 10px; letter-spacing: 0.05em;">Reason</p>
            <p style="margin: 4px 0 0; font-size: 14px;">{{ $returnRequest->reason }}</p>
            @if ($returnRequest->notes)
                <p style="margin: 8px 0 0; color: #a3a3a3; font-style: italic; font-size: 13px;">"{{ $returnRequest->notes }}"</p>
            @endif
        </div>

        {{-- Items being returned, with photo --}}
        <div style="border: 1px solid #404040; padding: 16px;">
            <p style="margin: 0 0 12px; color: #737373; text-transform: uppercase; font-size: 10px; letter-spacing: 0.05em;">Items to Return</p>

            @foreach ($returnRequest->items as $item)
                @php
                    $imageUrl = $item->orderItem->productVariant?->product?->primaryImage()?->url();
                @endphp
                <div style="display: table; width: 100%; margin-bottom: 12px;">
                    <div style="display: table-cell; width: 50px; vertical-align: top;">
                        @if ($imageUrl)
                            <img src="{{ $imageUrl }}" alt="{{ $item->orderItem->product_name }}" width="44" height="44" style="object-fit: cover; border: 1px solid #404040;">
                        @endif
                    </div>
                    <div style="display: table-cell; vertical-align: top; padding-left: 12px; font-size: 13px;">
                        <p style="margin: 0;">{{ $item->orderItem->product_name }}</p>
                        <p style="margin: 2px 0 0; color: #a3a3a3;">
                            Size {{ $item->orderItem->variant_label }} — Qty {{ $item->quantity }}
                        </p>
                    </div>
                </div>
            @endforeach
        </div>

        <p style="color: #a3a3a3; margin-top: 24px; font-size: 13px;">
            Review and approve/reject this in the admin panel under Return Requests.
        </p>
    </div>
</body>
</html>