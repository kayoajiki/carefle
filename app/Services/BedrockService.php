<?php

namespace App\Services;

use Aws\BedrockRuntime\BedrockRuntimeClient;
use Aws\Exception\AwsException;
use Illuminate\Support\Facades\Log;

class BedrockService
{
    protected ?BedrockRuntimeClient $client = null;
    protected string $modelId;
    protected int $maxTokens;
    protected float $temperature;
    protected float $topP;
    protected string $systemPrompt;

    public function __construct()
    {
        $this->modelId = config('bedrock.model_id');
        $this->maxTokens = config('bedrock.max_tokens');
        $this->temperature = config('bedrock.temperature');
        $this->topP = config('bedrock.top_p');
        $this->systemPrompt = config('bedrock.system_prompt');

        // 認証情報のチェック
        $accessKeyId = config('bedrock.access_key_id');
        $secretAccessKey = config('bedrock.secret_access_key');
        
        if (empty($accessKeyId) || empty($secretAccessKey)) {
            // 認証情報がない場合は、後でエラーを返すようにする
            Log::warning('Bedrock credentials not configured');
            return;
        }

        try {
            $this->client = new BedrockRuntimeClient([
                'region' => config('bedrock.region'),
                'version' => 'latest',
                'credentials' => [
                    'key' => $accessKeyId,
                    'secret' => $secretAccessKey,
                ],
            ]);
        } catch (\Exception $e) {
            Log::warning('Failed to initialize Bedrock client', [
                'error' => $e->getMessage(),
            ]);
            $this->client = null;
        }
    }

    /**
     * Send a chat message to Bedrock and get a response
     *
     * @param string $message
     * @param array $conversationHistory
     * @param string|null $customSystemPrompt
     * @return string|null
     */
    public function chat(string $message, array $conversationHistory = [], ?string $customSystemPrompt = null): ?string
    {
        // 認証情報がない場合のチェック
        if ($this->client === null) {
            Log::warning('Bedrock client not initialized - credentials may be missing');
            return null;
        }

        try {
            // Build messages array for Claude API
            $messages = [];

            // Add conversation history
            foreach ($conversationHistory as $history) {
                // contentを文字列に変換（安全に）
                $content = '';
                if (is_string($history['content'])) {
                    $content = $history['content'];
                } elseif (is_array($history['content'])) {
                    // 配列の場合は、JSONエンコードして文字列に変換
                    $content = json_encode($history['content'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                    if ($content === false) {
                        Log::warning('BedrockService: Failed to encode message content', [
                            'content_type' => gettype($history['content']),
                        ]);
                        $content = '';
                    }
                } else {
                    $content = (string)$history['content'];
                }
                
                // 空のcontentはスキップ
                if (empty($content)) {
                    continue;
                }
                
                $messages[] = [
                    'role' => $history['role'] === 'user' ? 'user' : 'assistant',
                    'content' => $content,
                ];
            }

            // 最初のメッセージがuserでない場合は、最初のassistantメッセージを削除
            while (!empty($messages) && $messages[0]['role'] !== 'user') {
                // 最初のassistantメッセージを削除
                array_shift($messages);
            }
            
            // 会話履歴が空の場合、または最後のメッセージがuserでない場合、現在のユーザーメッセージを追加
            if (empty($messages)) {
                // 会話履歴が空の場合は、プロンプトを最初のuserメッセージとして追加
                $messages[] = [
                    'role' => 'user',
                    'content' => $message,
                ];
            } else {
                // 会話履歴がある場合
                $lastMessage = end($messages);
                if ($lastMessage['role'] !== 'user') {
                    // 最後のメッセージがuserでない場合は追加
                    $messages[] = [
                        'role' => 'user',
                        'content' => $message,
                    ];
                } elseif ($lastMessage['content'] !== $message) {
                    // 最後のメッセージがuserだが内容が異なる場合は更新
                    $messages[count($messages) - 1] = [
                        'role' => 'user',
                        'content' => $message,
                    ];
                }
            }
            
            // 重複するuserメッセージを削除（連続している場合）
            $cleanedMessages = [];
            $lastRole = null;
            foreach ($messages as $msg) {
                if ($msg['role'] === 'user' && $lastRole === 'user') {
                    // 連続するuserメッセージはスキップ（最後のものを保持）
                    continue;
                }
                $cleanedMessages[] = $msg;
                $lastRole = $msg['role'];
            }
            $messages = $cleanedMessages;

            // Use custom system prompt if provided, otherwise use default
            $systemPrompt = $customSystemPrompt ?? $this->systemPrompt;

            // UTF-8として正規化（不正な文字を削除）
            $systemPrompt = $this->sanitizeUtf8($systemPrompt);
            
            // メッセージのcontentもUTF-8として正規化
            foreach ($messages as &$msg) {
                if (isset($msg['content']) && is_string($msg['content'])) {
                    $msg['content'] = $this->sanitizeUtf8($msg['content']);
                }
            }
            unset($msg);

            // Prepare the request body for Claude
            $body = [
                'anthropic_version' => 'bedrock-2023-05-31',
                'max_tokens' => $this->maxTokens,
                'temperature' => $this->temperature,
                'top_p' => $this->topP,
                'system' => $systemPrompt,
                'messages' => $messages,
            ];

            // JSONエンコードを実行
            $jsonBody = json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            
            if ($jsonBody === false) {
                $error = json_last_error_msg();
                Log::error('BedrockService: Failed to encode request body', [
                    'json_error' => $error,
                    'messages_count' => count($messages),
                    'system_prompt_length' => strlen($systemPrompt),
                    'system_prompt_preview' => substr($systemPrompt, 0, 100),
                ]);
                
                // リトライ: 不正な文字を削除して再試行
                $systemPrompt = $this->sanitizeUtf8($systemPrompt, true);
                foreach ($messages as &$msg) {
                    if (isset($msg['content']) && is_string($msg['content'])) {
                        $msg['content'] = $this->sanitizeUtf8($msg['content'], true);
                    }
                }
                unset($msg);
                
                $body['system'] = $systemPrompt;
                $body['messages'] = $messages;
                $jsonBody = json_encode($body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                
                if ($jsonBody === false) {
                    Log::error('BedrockService: Retry also failed', [
                        'json_error' => json_last_error_msg(),
                    ]);
                    return null;
                }
            }

            $result = $this->client->invokeModel([
                'modelId' => $this->modelId,
                'contentType' => 'application/json',
                'accept' => 'application/json',
                'body' => $jsonBody,
            ]);

            $response = json_decode($result['body'], true);

            if (isset($response['content'][0]['text'])) {
                return $response['content'][0]['text'];
            }

            Log::error('Bedrock response format unexpected', ['response' => $response]);
            return null;

        } catch (AwsException $e) {
            $errorCode = $e->getAwsErrorCode();
            $errorMessage = $e->getMessage();
            
            Log::error('AWS Bedrock error', [
                'error_code' => $errorCode,
                'error_message' => $errorMessage,
            ]);
            
            // より詳細なエラーメッセージを返す（デバッグ用）
            if (str_contains($errorMessage, 'use case details')) {
                Log::warning('Anthropic use case form may need to be submitted', [
                    'model_id' => $this->modelId,
                ]);
            }
            
            // デバッグ情報を追加（完全なエラーメッセージを記録）
            Log::info('Bedrock API call failed', [
                'model_id' => $this->modelId,
                'error_code' => $errorCode,
                'error_message' => $errorMessage, // 完全なメッセージを記録
            ]);
            
            return null;
        } catch (\Exception $e) {
            Log::error('Bedrock service error', [
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Stream chat response (for future implementation)
     *
     * @param string $message
     * @param array $conversationHistory
     * @return \Generator
     */
    public function chatStream(string $message, array $conversationHistory = []): \Generator
    {
        // TODO: Implement streaming response
        // This can be implemented later for real-time streaming
        yield '';
    }

    /**
     * UTF-8文字列を正規化（不正な文字を削除）
     *
     * @param string $string
     * @param bool $strict 厳密モード（不正な文字を削除）
     * @return string
     */
    protected function sanitizeUtf8(string $string, bool $strict = false): string
    {
        // UTF-8として正規化
        $string = mb_convert_encoding($string, 'UTF-8', 'UTF-8');
        
        if ($strict) {
            // 厳密モード: 不正なUTF-8文字を削除
            $string = mb_convert_encoding($string, 'UTF-8', 'UTF-8');
            // 制御文字（改行・タブ以外）を削除
            $string = preg_replace('/[\x00-\x08\x0B-\x0C\x0E-\x1F\x7F]/', '', $string);
        }
        
        return $string;
    }
}

