<!DOCTYPE html>
<html>
<head><meta charset="utf-8"></head>
<body style="font-family: sans-serif; background: #0d0c0a; color: #f2ede4; padding: 40px 20px;">
    <div style="max-width: 500px; margin: 0 auto;">
        <h1 style="font-size: 20px;">Account Deletion Requested</h1>
        <p style="color: #a3a3a3;">
            {{ $user->name }} ({{ $user->email }}) has requested their account be deleted.
        </p>
        <p style="color: #a3a3a3;">
            Remember: order/invoice records generally need to be retained for tax purposes even
            if the account itself is removed or anonymised.
        </p>
    </div>
</body>
</html>