<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class NanobananaImageService
{
    protected ?string $apiKey;
    protected ?string $apiUrl;

    public function __construct()
    {
        $this->apiKey = config('services.nanobanana.api_key');
        $this->apiUrl = config('services.nanobanana.api_url', 'https://api.nanobanana.com/v1/generate');
    }

    /**
     * ゴールイメージの図式を生成して保存
     */
    public function generateAndSave(string $goalText): ?string
    {
        $user = Auth::user();
        if (!$user) {
            return null;
        }

        if (empty($this->apiKey)) {
            Log::warning('Nanobanana API key not configured');
            return null;
        }

        try {
            // nanobanana APIを呼び出して画像を生成
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post($this->apiUrl, [
                'text' => $goalText,
                'style' => 'goal',
            ]);

            if ($response->successful()) {
                $imageData = $response->body();
                
                // 画像を保存
                $filename = 'goal_images/' . $user->id . '_' . time() . '.png';
                Storage::disk('public')->put($filename, $imageData);
                
                $url = Storage::disk('public')->url($filename);
                
                // ユーザーのgoal_image_urlを更新
                $user->update([
                    'goal_image_url' => $url,
                ]);
                
                return $url;
            } else {
                Log::warning('Nanobanana API request failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to generate nanobanana image', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
            ]);
        }

        return null;
    }
}


