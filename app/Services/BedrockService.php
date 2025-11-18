<?php

namespace App\Services;

use Aws\BedrockRuntime\BedrockRuntimeClient;
use Aws\Exception\AwsException;
use Illuminate\Support\Facades\Log;

class BedrockService
{
    protected BedrockRuntimeClient $client;
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

        $this->client = new BedrockRuntimeClient([
            'region' => config('bedrock.region'),
            'version' => 'latest',
            'credentials' => [
                'key' => config('bedrock.access_key_id'),
                'secret' => config('bedrock.secret_access_key'),
            ],
        ]);
    }

    /**
     * Send a chat message to Bedrock and get a response
     *
     * @param string $message
     * @param array $conversationHistory
     * @return string|null
     */
    public function chat(string $message, array $conversationHistory = []): ?string
    {
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

            // Add current user message
            $messages[] = [
                'role' => 'user',
                'content' => $message,
            ];

            // Prepare the request body for Claude
            $body = [
                'anthropic_version' => 'bedrock-2023-05-31',
                'max_tokens' => $this->maxTokens,
                'temperature' => $this->temperature,
                'top_p' => $this->topP,
                'system' => $this->systemPrompt,
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
            Log::error('AWS Bedrock error', [
                'error_code' => $e->getAwsErrorCode(),
                'error_message' => $e->getMessage(),
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

