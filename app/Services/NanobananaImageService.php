<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NanobananaImageService
{
    public function generateGoalImage(string $goalText, ?array $options = null): ?string
    {
        $apiKey = config('nanobanana.api_key');
        $endpoint = rtrim(config('nanobanana.endpoint'), '/');
        $imageSize = $options['image_size'] ?? config('nanobanana.image_size', '1024x1024');
        $styleHint = $options['style'] ?? config('nanobanana.style_hint', '');
        $promptTemplate = $options['prompt'] ?? config('nanobanana.prompt_template');

        if (!$apiKey || !$endpoint || !$promptTemplate) {
            Log::warning('Nanobanana API configuration missing');
            return null;
        }

        $prompt = str_replace(
            ['{goal_text}', '{style_hint}'],
            [$goalText, $styleHint],
            $promptTemplate
        );

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post("{$endpoint}?key={$apiKey}", [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt],
                        ],
                    ],
                ],
                'generationConfig' => [
                    'imageSize' => $imageSize,
                ],
            ]);

            if (!$response->successful()) {
                Log::error('Nanobanana API error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return null;
            }

            $data = $response->json();
            $url = $this->extractImageUrl($data);

            return $url;
        } catch (\Throwable $e) {
            Log::error('Failed to call Nanobanana API', ['error' => $e->getMessage()]);
            return null;
        }
    }

    public function saveGoalImage(int $userId, string $imageUrl): void
    {
        $user = User::find($userId);
        if (!$user) {
            return;
        }

        $user->update([
            'goal_image_url' => $imageUrl,
            'goal_display_mode' => 'image',
        ]);
    }

    public function generateAndSave(?string $goalText = null, ?array $options = null): ?string
    {
        $user = Auth::user();
        if (!$user) {
            return null;
        }

        $text = $goalText ?? $user->goal_image;
        if (!$text) {
            return null;
        }

        $imageUrl = $this->generateGoalImage($text, $options);
        if ($imageUrl) {
            $this->saveGoalImage($user->id, $imageUrl);
        }

        return $imageUrl;
    }

    private function extractImageUrl(?array $data): ?string
    {
        if (!$data) {
            return null;
        }

        // 仮の構造: data['candidates'][0]['content']['parts'][0]['inline_data']['file_url']
        $candidates = $data['candidates'] ?? null;
        if (is_array($candidates)) {
            foreach ($candidates as $candidate) {
                $parts = $candidate['content']['parts'] ?? [];
                foreach ($parts as $part) {
                    if (!empty($part['file_url'])) {
                        return $part['file_url'];
                    }
                    if (!empty($part['inline_data']['file_url'])) {
                        return $part['inline_data']['file_url'];
                    }
                    if (!empty($part['inlineData']['fileUrl'])) {
                        return $part['inlineData']['fileUrl'];
                    }
                }
            }
        }

        // 直接URLが返る場合
        if (!empty($data['file_url'])) {
            return $data['file_url'];
        }

        return null;
    }
}

