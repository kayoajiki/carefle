<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>サービス移行のお知らせ</title>
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            background: #f8fafc;
            color: #1f2937;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Hiragino Kaku Gothic ProN", "Hiragino Sans", "Noto Sans JP", sans-serif;
        }
        .card {
            width: min(680px, 92vw);
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 28px;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
        }
        h1 {
            margin: 0 0 16px;
            font-size: 1.5rem;
        }
        p {
            margin: 10px 0;
            line-height: 1.7;
        }
        a {
            color: #2563eb;
            word-break: break-all;
        }
        .muted {
            color: #6b7280;
            font-size: 0.95rem;
        }
    </style>
</head>
<body>
    <main class="card">
        <h1>サービス移行のお知らせ</h1>
        <p>本サービスは Next.js 版へ移行しました。</p>
        <p>新URL: <a href="{{ $newUrl }}">{{ $newUrl }}</a></p>
        <p class="muted">このページは旧版です。旧システムは順次クローズします。</p>
    </main>
</body>
</html>
