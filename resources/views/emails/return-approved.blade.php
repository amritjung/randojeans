<!DOCTYPE html>
<html>
<head><meta charset="utf-8"></head>
<body style="font-family: sans-serif; background: #0d0c0a; color: #f2ede4; padding: 40px 20px;">
    <div style="max-width: 500px; margin: 0 auto;">
        <p style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.1em; color: #a8622f; margin: 0 0 12px;">
            Return Approved
        </p>

        <h1 style="font-size: 22px; margin: 0 0 16px;">
            Please send your item back
        </h1>

        <p style="color: #a3a3a3;">
            Hi {{ explode(' ', $returnRequest->order->shipping_name)[0] }}, your return for order
            <strong style="color: #f2ede4;">{{ $returnRequest->order->order_number }}</strong> has been approved.
            Once we receive it back, we'll process your refund.
        </p>

        {{-- Reference number, made prominent since this is the one thing
             they need to physically write on the parcel --}}
        <div style="border: 2px solid #a8622f; padding: 20px; margin: 24px 0; text-align: center;">
            <p style="margin: 0 0 6px; color: #a3a3a3; font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em;">
                Write this on your parcel
            </p>
            <p style="margin: 0; font-family: monospace; font-size: 24px; color: #c9905f; letter-spacing: 0.05em;">
                {{ $returnRequest->reference_number }}
            </p>
        </div>

        {{-- Items being returned --}}
        <div style="margin: 20px 0;">
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
                        <p style="margin: 2px 0 0; color: #a3a3a3;">Size {{ $item->orderItem->variant_label }} &times; {{ $item->quantity }}</p>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Return address placeholder — fill in your actual return
             address before this goes live for real customers. --}}
        <div style="border: 1px solid #404040; padding: 20px; margin: 24px 0;">
            <p style="margin: 0 0 8px; color: #737373; text-transform: uppercase; font-size: 10px; letter-spacing: 0.05em;">
                Send to
            </p>
            <p style="margin: 0; font-size: 14px; line-height: 1.6;">
                Rando Jeans Returns<br>
                [Your return address here]<br>
                [Suburb, State, Postcode]
            </p>
        </div>

        @if ($returnRequest->returnDeadline())
        <p style="color: #a3a3a3; font-size: 13px;">
            Please post it back by <strong style="color: #f2ede4;">{{ $returnRequest->returnDeadline()->format('d M Y') }}</strong>
            (this is your postmark/shipping date, not an arrival deadline).
            We recommend using a tracked shipping method so you have proof of postage.
        </p>
    @endif

        <p style="color: #737373; font-size: 12px; margin-top: 40px;">
            Rando Jeans
        </p>
    </div>
</body>
</html>