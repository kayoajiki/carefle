<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>持ち味レポ - {{ $user->name }}</title>
    <style>
        body {
            font-family: 'Noto Sans JP', sans-serif;
            color: #1E3A5F;
            line-height: 1.8;
            padding: 40px;
            max-width: 800px;
            margin: 0 auto;
        }
        h1 {
            color: #2E5C8A;
            font-size: 32px;
            text-align: center;
            margin-bottom: 10px;
        }
        h2 {
            color: #2E5C8A;
            font-size: 24px;
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #2E5C8A;
        }
        h3 {
            color: #2E5C8A;
            font-size: 20px;
            margin-top: 30px;
            margin-bottom: 15px;
        }
        .strength-item {
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #E0E0E0;
        }
        .strength-number {
            display: inline-block;
            width: 30px;
            height: 30px;
            background-color: #6BB6FF;
            color: white;
            border-radius: 50%;
            text-align: center;
            line-height: 30px;
            font-weight: bold;
            margin-right: 15px;
        }
        .strength-title {
            font-size: 18px;
            font-weight: bold;
            color: #2E5C8A;
            margin-bottom: 10px;
        }
        .strength-description {
            color: #1E3A5F;
            line-height: 1.8;
            white-space: pre-wrap;
        }
        .footer {
            text-align: center;
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid #E0E0E0;
            color: #666;
            font-size: 12px;
        }
        .generated-date {
            text-align: center;
            color: #666;
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <h1>{{ $manual['content']['title'] ?? '私の持ち味レポ' }}</h1>
    <p class="generated-date">生成日: {{ $manual['generated_at']->format('Y年n月j日') }}</p>
    
    @if(isset($manual['content']['agenda']))
    <h2>{{ $manual['content']['agenda'] }}</h2>
    @endif

    @if(isset($manual['content']['strengths']) && is_array($manual['content']['strengths']) && !empty($manual['content']['strengths']))
        @foreach($manual['content']['strengths'] as $index => $strength)
            <div class="strength-item">
                <div>
                    <span class="strength-number">{{ $index + 1 }}</span>
                    <span class="strength-title">{{ $strength['title'] ?? '' }}</span>
                </div>
                <p class="strength-description">{{ $strength['description'] ?? '' }}</p>
            </div>
        @endforeach
    @else
        <p>データが不足しています。診断と日記の記録を続けることで、あなたの持ち味がより明確になります。</p>
    @endif

    <div class="footer">
        <p>この持ち味レポはキャリフレで生成されました</p>
    </div>
</body>
</html>

