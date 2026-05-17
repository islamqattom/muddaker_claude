<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * AssemblyAI Real-time WebSocket Service
 *
 * Handles WebSocket token generation for AssemblyAI's real-time transcription API.
 */
class AssemblyAIRealtimeService
{
    private string $apiKey;
    private const WS_BASE_URL = 'wss://api.assemblyai.com/v2/realtime';

    public function __construct()
    {
        $this->apiKey = config('app.assemblyai.api_key', env('ASSEMBLYAI_API_KEY', ''));
    }

    /**
     * Get a WebSocket token for real-time streaming
     *
     * @param int $expiresIn Token expiration in seconds (default 3600)
     * @return array{success: bool, token?: string, websocket_url?: string, error?: string}
     */
    public function getWebSocketToken(int $expiresIn = 600): array
    {
        $apiKey = $this->apiKey;
        $isPlaceholder = empty($apiKey) || $apiKey === 'your_assemblyai_api_key_here' || $apiKey === 'your_assemblyai_api_key_here';
        
        if ($isPlaceholder) {
            Log::info('AssemblyAI API key not configured - using Web Speech API fallback');
            return [
                'success' => false,
                'error' => 'مفتاح AssemblyAI غير موجود - سيتم استخدام التعرف على الصوت',
                'requires_setup' => true,
                'use_web_speech' => true
            ];
        }

        $expiresIn = min($expiresIn, 600);

        try {
            $response = Http::withToken($this->apiKey)
                ->get('https://streaming.assemblyai.com/v3/token', [
                    'expires_in_seconds' => $expiresIn,
                ]);

            if (!$response->successful()) {
                Log::error('AssemblyAI token request failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                return ['success' => false, 'error' => 'فشل في الحصول على رمز WebSocket'];
            }

            $data = $response->json();

            return [
                'success' => true,
                'token' => $data['token'] ?? null,
                'websocket_url' => 'wss://api.assemblyai.com/v2/realtime/ws',
            ];
        } catch (\Exception $e) {
            Log::error('AssemblyAI token error', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Build the WebSocket URL with token
     *
     * @param string $token
     * @return string
     */
    public function buildWebSocketUrl(string $token): string
    {
        return self::WS_BASE_URL . '/stream?token=' . $token;
    }

    /**
     * Get the real-time transcript configuration for Arabic
     *
     * @return array
     */
    public function getRealtimeConfig(): array
    {
        return [
            'language_code' => 'ar',
            'model' => 'nano',
            'audio_start' => true,
        ];
    }
}
