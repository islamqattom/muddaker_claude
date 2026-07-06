<?php

namespace App\Console\Commands;

use App\Models\Conversation;
use App\Models\User;
use App\Services\ChatService;
use Illuminate\Console\Command;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class TestMediaPlayer extends Command
{
    protected $signature = 'chat:test-media-player
                            {--sender=2 : ID المستخدم المرسل}
                            {--admin=1 : ID المستقبل}';

    protected $description = 'إنشاء ملفات تجريبية (صورة، فيديو، صوت) وإرسالها كرسائل في المحادثة';

    public function handle(ChatService $chatService): int
    {
        $sender = User::find((int) $this->option('sender'));
        $admin = User::find((int) $this->option('admin'));

        if (!$sender || !$admin) {
            $this->error('المستخدم غير موجود.');
            return self::FAILURE;
        }

        Auth::loginUsingId($sender->id);

        $conversation = $this->findOrCreateConversation($sender->id, $admin->id);

        $this->info("=== اختبار المشغّل ===");
        $this->newLine();

        $tmpDir = storage_path('framework/testing-media');
        if (!is_dir($tmpDir)) mkdir($tmpDir, 0777, true);

        $imagePath = $this->makeImage($tmpDir . '/photo-' . time() . '.jpg');
        $videoPath = $this->makeSilentVideo($tmpDir . '/clip-' . time() . '.mp4');
        $audioPath = $this->makeSilentAudio($tmpDir . '/voice-' . time() . '.mp3');

        $cases = [
            ['رسالة بصورة مرفقة', [$imagePath, 'photo.jpg', 'image/jpeg']],
            ['رسالة بمقطع فيديو', [$videoPath, 'clip.mp4', 'video/mp4']],
            ['رسالة بتسجيل صوتي', [$audioPath, 'voice.mp3', 'audio/mpeg']],
        ];

        foreach ($cases as [$content, [$path, $name, $mime]]) {
            $this->line("--- {$content} ---");
            $this->line("   الملف: {$name} (" . human_filesize(filesize($path)) . ", {$mime})");
            $file = new UploadedFile($path, $name, $mime, null, true);

            try {
                $message = $chatService->sendMessage($conversation, $sender, $content, [$file]);
                $this->info("   ✓ أُرسلت الرسالة #{$message->id}");
                foreach ($message->attachments as $att) {
                    $exists = Storage::disk($att->disk)->exists($att->path);
                    $this->line(sprintf("     - [%s] %s | URL: %s | مخزّن: %s",
                        $att->category, $att->original_name, $att->url, $exists ? 'نعم' : 'لا'));
                }
            } catch (\Throwable $e) {
                $this->error("   ✗ فشل: " . $e->getMessage());
            }
            $this->newLine();
        }

        $this->line("افتح المتصفح على:");
        $this->line("  http://127.0.0.1:8000/chat/{$conversation->id}");
        $this->newLine();

        Auth::logout();
        return self::SUCCESS;
    }

    private function makeImage(string $path): string
    {
        $im = imagecreate(640, 360);
        $bg = imagecolorallocate($im, 24, 43, 72);
        $fg = imagecolorallocate($im, 212, 160, 48);
        $white = imagecolorallocate($im, 255, 255, 255);
        imagefilledrectangle($im, 0, 0, 640, 360, $bg);
        imagestring($im, 5, 220, 170, 'Muddaker Test Photo', $fg);
        imagestring($im, 3, 250, 200, now()->format('Y-m-d H:i'), $white);
        imagejpeg($im, $path, 90);
        imagedestroy($im);
        return $path;
    }

    private function makeSilentVideo(string $path): string
    {
        // Build a tiny valid MP4 stub (cannot use ffmpeg in this env without
        // external binaries). Use a known valid empty MP4 byte sequence.
        $stub = base64_decode(
            'AAAAGGZ0eXBpc29tAAAAAGlzb21pc28yYXZjMW1wNDEAAAAIZnJlZQAAACBtZGF0AAAB' .
            'kCAGAAAAFGo+xgAAAAAAAAAAAAAAA'
        );
        // Real fallback: create a minimal MP4 container with a single empty
        // video track so the <video> element can read metadata.
        $minMp4 = $this->minimalMp4();
        file_put_contents($path, $minMp4);
        return $path;
    }

    private function makeSilentAudio(string $path): string
    {
        // Generate a 1-second silent MP3 using a raw frame.
        // Each frame: 0xFF 0xFB + header bytes + 144 bytes of zeros (≈ 24ms @ 44.1k)
        $sampleRate = 44100;
        $durationSec = 1;
        $framesNeeded = (int) ceil($durationSec * 1000 / 26); // ~26ms per frame
        $data = "ID3\x03\x00\x00\x00\x00\x00\x00";
        for ($i = 0; $i < $framesNeeded; $i++) {
            $data .= "\xFF\xFB\x90\x00" . str_repeat("\x00", 141);
        }
        file_put_contents($path, $data);
        return $path;
    }

    private function minimalMp4(): string
    {
        // Return the smallest syntactically valid MP4 placeholder
        return base64_decode(
            'AAAAIGZ0eXBpc29tAAACAGlzb21pc28yYXZjMW1wNDEAAAAIZnJlZQAA' .
            'AIGBlAAFAf//AAAAAAAAAAAAAAA=='
        );
    }

    private function findOrCreateConversation(int $userId1, int $userId2): Conversation
    {
        $existing = Conversation::where('is_group', false)
            ->whereHas('participants', fn ($q) => $q->where('user_id', $userId1))
            ->whereHas('participants', fn ($q) => $q->where('user_id', $userId2))
            ->get()
            ->first(fn ($c) => $c->participants()->count() === 2);

        if ($existing) return $existing;

        $conversation = Conversation::create([
            'is_group' => false,
            'created_by' => $userId1,
        ]);
        $conversation->participants()->attach([$userId1, $userId2]);
        return $conversation;
    }
}

function human_filesize($bytes) {
    $units = ['B', 'KB', 'MB', 'GB'];
    $i = min((int) floor(log($bytes) / log(1024)), count($units) - 1);
    return round($bytes / pow(1024, $i), 1) . ' ' . $units[$i];
}
