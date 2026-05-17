<?php

namespace Tests\Unit\Services;

use App\Services\AssemblyAIRealtimeService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class AssemblyAIRealtimeServiceTest extends TestCase
{
    protected AssemblyAIRealtimeService $service;

    protected function setUp(): void
    {
        parent::setUp();
        // Ensure a valid API key is set by default
        config(['services.assemblyai.api_key' => 'test-api-key-for-testing']);
        $this->service = new AssemblyAIRealtimeService();
    }

    public function test_get_websocket_token_success(): void
    {
        Http::fake([
            'https://api.assemblyai.com/v2/realtime/token' => Http::response([
                'token' => 'test-token-123',
                'websocket_url' => 'wss://api.assemblyai.com/v2/realtime/stream?token=test-token-123',
            ], 200),
        ]);

        $result = $this->service->getWebSocketToken();

        $this->assertTrue($result['success']);
        $this->assertEquals('test-token-123', $result['token']);
        $this->assertEquals('wss://api.assemblyai.com/v2/realtime/ws?sample_rate=16000&token=test-token-123', $result['websocket_url']);
    }

    public function test_get_websocket_token_failure(): void
    {
        Http::fake([
            'https://api.assemblyai.com/v2/realtime/token' => Http::response([
                'error' => 'Invalid API key',
            ], 401),
        ]);

        $result = $this->service->getWebSocketToken();

        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('error', $result);
    }

    public function test_get_websocket_token_no_api_key(): void
    {
        // Override with empty API key
        config(['services.assemblyai.api_key' => '']);

        $service = new AssemblyAIRealtimeService();
        $result = $service->getWebSocketToken();

        $this->assertFalse($result['success']);
        $this->assertEquals('AssemblyAI API key not configured', $result['error']);
    }

    public function test_get_websocket_token_custom_expiration(): void
    {
        Http::fake([
            'https://api.assemblyai.com/v2/realtime/token' => Http::response([
                'token' => 'test-token',
                'websocket_url' => 'wss://api.assemblyai.com/v2/realtime/ws?sample_rate=16000&token=test-token',
            ], 200),
        ]);

        $this->service->getWebSocketToken(7200);

        Http::assertSent(function ($request) {
            return $request->data()['expires_in'] === 7200;
        });
    }

    public function test_build_websocket_url(): void
    {
        $token = 'my-token-abc123';
        $url = $this->service->buildWebSocketUrl($token);

        $this->assertEquals('wss://api.assemblyai.com/v2/realtime/ws?sample_rate=16000&token=my-token-abc123', $url);
    }

    public function test_get_realtime_config_arabic(): void
    {
        $config = $this->service->getRealtimeConfig();

        $this->assertEquals('ar', $config['language_code']);
        $this->assertEquals('nano', $config['model']);
        $this->assertTrue($config['audio_start']);
    }

    public function test_get_websocket_token_logs_error_on_http_failure(): void
    {
        Http::fake([
            'https://api.assemblyai.com/v2/realtime/token' => Http::response([
                'error' => 'Server error',
            ], 500),
        ]);

        Log::shouldReceive('error')
            ->once()
            ->with('AssemblyAI token request failed', \Mockery::type('array'));

        $result = $this->service->getWebSocketToken();

        $this->assertFalse($result['success']);
    }
}
