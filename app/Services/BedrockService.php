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
                $messages[] = [
                    'role' => $history['role'] === 'user' ? 'user' : 'assistant',
                    'content' => is_string($history['content']) 
                        ? $history['content'] 
                        : (is_array($history['content']) ? $history['content'] : (string)$history['content']),
                ];
            }

            // 最初のメッセージがuserでない場合は、最初のassistantメッセージを削除
            if (!empty($messages) && $messages[0]['role'] !== 'user') {
                // 最初のassistantメッセージを削除
                array_shift($messages);
            }
            
            // 最後のメッセージがuserでない場合のみ、現在のユーザーメッセージを追加
            $lastMessage = end($messages);
            if (empty($messages) || ($lastMessage && $lastMessage['role'] !== 'user')) {
                $messages[] = [
                    'role' => 'user',
                    'content' => $message,
                ];
            } elseif ($lastMessage && $lastMessage['role'] === 'user' && $lastMessage['content'] !== $message) {
                // 最後のメッセージがuserだが内容が異なる場合は更新
                $messages[count($messages) - 1] = [
                    'role' => 'user',
                    'content' => $message,
                ];
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

            // Prepare the request body for Claude
            $body = [
                'anthropic_version' => 'bedrock-2023-05-31',
                'max_tokens' => $this->maxTokens,
                'temperature' => $this->temperature,
                'top_p' => $this->topP,
                'system' => $systemPrompt,
                'messages' => $messages,
            ];

            $result = $this->client->invokeModel([
                'modelId' => $this->modelId,
                'contentType' => 'application/json',
                'accept' => 'application/json',
                'body' => json_encode($body),
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
}

