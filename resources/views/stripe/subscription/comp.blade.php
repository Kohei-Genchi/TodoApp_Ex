<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- <meta http-equiv="X-UA-Compatible" content="ie=edge"> -->
    <meta http-equiv="Content-Security-Policy" content="font-src 'self' data:;">

    <title>支払い完了ページ</title>
</head>
<body>
    サブスクの支払いが完了しました。
    <a href="{{ route('stripe.subscription.customer_portal') }}">カスタマーポータルに進む</a>
</body>
</html>
