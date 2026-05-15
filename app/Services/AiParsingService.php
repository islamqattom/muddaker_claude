<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class AiParsingService
{
    private string $apiKey;
    private string $model;

    public function __construct()
    {
        $this->apiKey = config('app.openai.api_key', env('OPENAI_API_KEY', ''));
        $this->model = config('app.openai.model', env('OPENAI_MODEL', 'gpt-4o'));
    }

    public function analyzeMeetingMinutes(array $meetingData): array
    {
        if (empty($this->apiKey)) {
            Log::warning('AiParsingService: OpenAI API key not configured');
            return ['success' => false, 'error' => 'AI service not configured', 'tasks' => []];
        }

        $prompt = $this->buildPrompt($meetingData);

        try {
            $response = $this->callOpenAI($prompt);

            if (!$response['success']) {
                return $response;
            }

            return $this->parseAIResponse($response['content']);
        } catch (\Exception $e) {
            Log::error('AiParsingService: Failed to analyze meeting minutes', [
                'error' => $e->getMessage(),
            ]);
            return ['success' => false, 'error' => $e->getMessage(), 'tasks' => []];
        }
    }

    private function buildPrompt(array $meetingData): string
    {
        $title = $meetingData['title'] ?? '';
        $decisions = $meetingData['decisions'] ?? '';
        $recommendations = $meetingData['recommendations'] ?? '';
        $agenda = is_array($meetingData['agenda'] ?? null) ? implode(', ', $meetingData['agenda']) : '';
        $attendees = is_array($meetingData['attendees'] ?? null) ? implode(', ', $meetingData['attendees']) : '';

        return <<<PROMPT
أنت مساعد ذكاء اصطناعي متخصص في تحليل محاضر الاجتماعات في مركز تسميع القرآن الكريم.

قم بتحليل محضر الاجتماع التالي واستخرج التكليفات والمهام منها.

معلومات الاجتماع:
- العنوان: {$title}
- جدول الأعمال: {$agenda}
- الحاضرون: {$attendees}
- القرارات: {$decisions}
- التوصيات: {$recommendations}

المشرفين المتاحين:
PROMPT;
        foreach ($meetingData['supervisors'] ?? [] as $supervisor) {
            $prompt .= "- {$supervisor['name']} (ID: {$supervisor['id']})\n";
        }

        $prompt .= <<<PROMPT

المطلوب منك:
1. قراءة محضر الاجتماع بعناية
2. استخراج جميع التكليفات والمهام المذكورة
3. لكل تكليف، حدد المشرف الأنسب لإسناده بناءً على طبيعة التكليف
4. أرجع النتائج بتنسيق JSON فقط بهذا الشكل:
{
    "tasks": [
        {
            "title": "عنوان التكليف",
            "description": "وصف التكليف",
            "assignee_id": "رقم المشرف",
            "priority": "high|medium|low"
        }
    ]
}

قواعد مهمة:
- إذا لم يتم ذكر أي تكليفات صريحة، ابحث عن قرارات أو توصيات يمكن تحويلها لمهام
- حدد assignee_id بشكل صحيح من قائمة المشرفين
- priority: high إذا كان عاجلًا، medium إذا كان خلال أسبوع، lowotherwise
- أرجع JSON صالح فقط بدون أي نص إضافي
PROMPT;
    }

    private function callOpenAI(string $prompt): array
    {
        $cacheKey = 'ai_meeting_' . md5($prompt);
        $cached = Cache::get($cacheKey);
        if ($cached) {
            return ['success' => true, 'content' => $cached, 'cached' => true];
        }

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type' => 'application/json',
        ])->timeout(30)->post('https://api.openai.com/v1/chat/completions', [
            'model' => $this->model,
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'أنت مساعد ذكي متخصص في تحليل النصوص العربية.'
                ],
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ],
            'temperature' => 0.3,
            'max_tokens' => 1000,
        ]);

        if (!$response->successful()) {
            Log::error('AiParsingService: OpenAI API call failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return ['success' => false, 'error' => 'Failed to call OpenAI API'];
        }

        $content = $response->json('choices.0.message.content', '');
        Cache::put($cacheKey, $content, now()->addMinutes(30));

        return ['success' => true, 'content' => $content, 'cached' => false];
    }

    private function parseAIResponse(string $content): array
    {
        $content = trim($content);

        if (preg_match('/\{.*\}/s', $content, $matches)) {
            $content = $matches[0];
        }

        $data = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::warning('AiParsingService: Failed to parse JSON', ['content' => $content]);
            return ['success' => false, 'error' => 'Failed to parse AI response', 'tasks' => []];
        }

        return [
            'success' => true,
            'tasks' => $data['tasks'] ?? [],
        ];
    }
}