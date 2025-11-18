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

    'model_id' => env('AWS_BEDROCK_MODEL_ID', 'anthropic.claude-3-5-sonnet-20241022-v2:0'),

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

];

