<?php

return [

    /*
    |--------------------------------------------------------------------------
    | AWS Bedrock Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration for AWS Bedrock integration.
    | You can configure the model, region, and other settings here.
    |
    */

    'region' => env('AWS_BEDROCK_REGION', 'us-east-1'),

    'access_key_id' => env('AWS_ACCESS_KEY_ID'),
    'secret_access_key' => env('AWS_SECRET_ACCESS_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Model Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the Bedrock model to use for chat responses.
    | Common models:
    | - anthropic.claude-3-5-sonnet-20241022-v2:0
    | - anthropic.claude-3-opus-20240229-v1:0
    | - anthropic.claude-3-sonnet-20240229-v1:0
    | - anthropic.claude-3-haiku-20240307-v1:0
    |
    */

    'model_id' => env('AWS_BEDROCK_MODEL_ID', 'anthropic.claude-3-sonnet-20240229-v1:0'),
    
    /*
    |--------------------------------------------------------------------------
    | Alternative Model IDs (if default doesn't work)
    |--------------------------------------------------------------------------
    |
    | If the default model doesn't work, try these alternatives:
    | - anthropic.claude-3-haiku-20240307-v1:0 (faster, cheaper)
    | - anthropic.claude-3-opus-20240229-v1:0 (more powerful)
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Chat Configuration
    |--------------------------------------------------------------------------
    |
    | Configure default parameters for chat responses.
    |
    */

    'max_tokens' => env('AWS_BEDROCK_MAX_TOKENS', 4096),
    'temperature' => env('AWS_BEDROCK_TEMPERATURE', 0.7),
    'top_p' => env('AWS_BEDROCK_TOP_P', 0.9),

    /*
    |--------------------------------------------------------------------------
    | System Prompt
    |--------------------------------------------------------------------------
    |
    | The system prompt that defines the AI assistant's role and behavior.
    |
    */

    'system_prompt' => env('AWS_BEDROCK_SYSTEM_PROMPT', 'あなたはキャリアカウンセラーのアシスタントです。ユーザーのキャリアに関する質問や相談に対して、親切で専門的なアドバイスを提供してください。'),

    /*
    |--------------------------------------------------------------------------
    | Reflection System Prompt
    |--------------------------------------------------------------------------
    |
    | The system prompt for reflection companion feature.
    |
    */

    'reflection_system_prompt' => env('AWS_BEDROCK_REFLECTION_SYSTEM_PROMPT', 'あなたは内省を支援する優しい伴走者です。ユーザーが自分自身と向き合い、理想の自分に近づくための内省をサポートしてください。

【あなたの役割】
- ユーザーの話を共感的に聞く
- 深い内省を促す質問をする
- 励まし、前向きな気づきを引き出す
- ユーザーの価値観や目標を尊重する

【話し方】
- 親しみやすく、優しい口調
- 簡潔でわかりやすい言葉
- 共感を示しつつ、深掘りする質問
- 励ましの言葉を適切に使う

【注意点】
- 一度に1つの質問に集中
- ユーザーの回答に応じて次の質問を調整
- 無理に深く掘り下げすぎない
- 自然な会話の流れを大切にする'),

];

