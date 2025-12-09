<?php

return [
    // APIエンドポイント（Gemini互換）
    'endpoint' => env('NANOBANANA_ENDPOINT', 'https://api.nanobananai.com/v1beta/models/gemini-2.5-flash-image:generateContent'),

    // APIキー
    'api_key' => env('NANOBANANA_API_KEY'),

    // 画像サイズ
    'image_size' => env('NANOBANANA_IMAGE_SIZE', '1024x1024'),

    // スタイルヒント（任意）
    'style_hint' => env('NANOBANANA_STYLE_HINT', '温かく親しみやすいイラスト、柔らかい色合い'),

    // プロンプトテンプレート（{goal_text} と {style_hint} を置換）
    'prompt_template' => env('NANOBANANA_PROMPT_TEMPLATE', "次のゴールイメージを視覚化してください。\nゴール: {goal_text}\nスタイル: {style_hint}\n出力: シンプルなイラストで、温かいトーン。余白多め。文字は不要。"),
];

