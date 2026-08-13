<!DOCTYPE html>
<html>
<head><meta charset="utf-8"></head>
<body style="font-family: sans-serif; background: #0d0c0a; color: #f2ede4; padding: 40px 20px;">
    <div style="max-width: 500px; margin: 0 auto;">
        <h1 style="font-size: 20px;">New Review Awaiting Approval</h1>
        <p style="color: #a3a3a3;">
            {{ $reviewer->name }} left a review for <strong style="color: #f2ede4;">{{ $product->name }}</strong>.
        </p>
        <p style="color: #a3a3a3;">Review it in the admin panel under Reviews.</p>
    </div>
</body>
</html>