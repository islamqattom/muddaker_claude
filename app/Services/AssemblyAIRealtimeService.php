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
        $this->apiKey = config('services.assemblyai.api_key', env('ASSEMBLYAI_API_KEY', ''));
    }

    /**
     * Get a WebSocket token for real-time streaming
     *
     * @param int $expiresIn Token expiration in seconds (default 3600)
     * @return array{success: bool, token?: string, websocket_url?: string, error?: string}
     */
    public function getWebSocketToken(int $expiresIn = 600): array
    {
        if (empty($this->apiKey)) {
            Log::warning('AssemblyAI API key not configured');
            return ['success' => false, 'error' => 'AssemblyAI API key not configured'];
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
                return ['success' => false, 'error' => 'Failed to get WebSocket token'];
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
