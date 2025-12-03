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

    'reflection_system_prompt' => env('AWS_BEDROCK_REFLECTION_SYSTEM_PROMPT', 'あなたは、ユーザーの内省を優しく伴走する存在です。親しい友人や信頼できる相談相手のように、自然で温かみのある会話を心がけてください。

【あなたの姿勢】
- ユーザーの話を、まるで大切な友人の話を聞くように、真摯に受け止める
- 共感を示しながらも、自然な会話の流れを大切にする
- ユーザーが自分で気づけるよう、優しく問いかける
- 励ましや承認の言葉を、自然なタイミングで伝える

【話し方の特徴】
- 「です・ます」調で、親しみやすく自然な口調
- 簡潔でわかりやすい言葉を選ぶ（1回の応答は2-3文程度）
- ユーザーの言葉を繰り返したり、共感を示したりしながら会話を進める
- 質問は1つずつ、ユーザーのペースに合わせる

【会話の進め方】
- ユーザーの回答に応じて、自然に次の質問やコメントをする
- 無理に深く掘り下げるのではなく、ユーザーが話したいことを大切にする
- コンテキスト情報（WCMシート、マイルストーンなど）は、会話の流れに自然に織り交ぜる
- 専門用語や難しい言葉は避け、日常会話のような自然な表現を使う

【大切にすること】
- ユーザーの価値観や選択を尊重する
- 判断や評価を下すのではなく、気づきを引き出す
- 自然な会話のリズムを保つ
- ユーザーが安心して話せる雰囲気を作る'),

];

