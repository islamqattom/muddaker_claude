<?php

namespace App\Console\Commands;

use App\Models\Conversation;
use App\Models\MessageAttachment;
use App\Models\User;
use App\Services\ChatService;
use Illuminate\Console\Command;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class TestChatAttachments extends Command
{
    protected $signature = 'chat:test-attachments
                            {--sender=2 : ID المستخدم المرسل}
                            {--admin=1 : ID المستقبل}';

    protected $description = 'اختبار إرسال رسائل مع مرفقات (صور، فيديو، ملفات)';

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

        $this->info("=== اختبار المرفقات في المحادثة #{$conversation->id} ===");
        $this->newLine();

        $tmpDir = storage_path('framework/testing');
        if (!is_dir($tmpDir)) {
            mkdir($tmpDir, 0777, true);
        }

        $imageFile = $this->makeJpegFile($tmpDir . '/test-image.jpg', 80, 60);
        $textFile = $this->makeTextFile($tmpDir . '/test-doc.txt', 'تقرير اختبار المرفقات - ' . now()->toDateTimeString());
        $pdfFile = $this->makePdfFile($tmpDir . '/test-doc.pdf');

        $image = new UploadedFile($imageFile, 'test-image.jpg', 'image/jpeg', null, true);
        $document = new UploadedFile($textFile, 'notes.txt', 'text/plain', null, true);
        $pdf = new UploadedFile($pdfFile, 'report.pdf', 'application/pdf', null, true);

        $cases = [
            ['رسالة مع صورة مرفقة', [$image]],
            ['رسالة نصية فقط', []],
            ['رسالة مع ملف PDF', [$pdf]],
            ['رسالة مع صورة + ملف في نفس الوقت', [$image, $document]],
            ['رسالة فارغة (يجب أن تفشل)', []],
        ];

        foreach ($cases as $i => [$content, $files]) {
            $this->line("--- الحالة " . ($i + 1) . ": {$content} ---");
            try {
                $message = $chatService->sendMessage($conversation, $sender, $content, $files);
                $filesCount = $message->attachments->count();
                $this->info("  ✓ أُرسلت الرسالة #{$message->id} بعدد {$filesCount} مرفق(ات)");

                foreach ($message->attachments as $att) {
                    $exists = Storage::disk($att->disk)->exists($att->path);
                    $url = $att->url;
                    $downloadUrl = route('chat.attachment.download', $att);
                    $this->line(sprintf(
                        "    - [%s] %s (%s, %s) - مخزّن: %s",
                        $att->category,
                        $att->original_name,
                        $att->mime_type,
                        $att->human_size,
                        $exists ? 'نعم' : 'لا'
                    ));
                    $this->line("      URL: {$url}");
                    $this->line("      Download: {$downloadUrl}");
                }
            } catch (\Throwable $e) {
                $this->warn("  ✗ فشل متوقع: " . $e->getMessage());
            }
            $this->newLine();
        }

        $this->verifyNotifications($admin->id, $conversation->id);
        $this->summaryTable($conversation->id);

        Auth::logout();
        return self::SUCCESS;
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

    private function makeJpegFile(string $path, int $w, int $h): string
    {
        $im = imagecreate($w, $h);
        $bg = imagecolorallocate($im, 24, 43, 72);
        $fg = imagecolorallocate($im, 212, 160, 48);
        imagestring($im, 5, 10, 20, 'Muddaker Test', $fg);
        imagejpeg($im, $path);
        imagedestroy($im);
        return $path;
    }

    private function makeTextFile(string $path, string $content): string
    {
        file_put_contents($path, $content);
        return $path;
    }

    private function makePdfFile(string $path): string
    {
        $pdf = "%PDF-1.4\n1 0 obj<</Type/Catalog/Pages 2 0 R>>endobj\n2 0 obj<</Type/Pages/Kids[3 0 R]/Count 1>>endobj\n3 0 obj<</Type/Page/Parent 2 0 R/MediaBox[0 0 200 200]>>endobj\nxref\n0 4\n0000000000 65535 f\n0000000009 00000 n\n0000000051 00000 n\n0000000073 00000 n\ntrailer<</Size 4/Root 1 0 R>>\nstartxref\n112\n%%EOF";
        file_put_contents($path, $pdf);
        return $path;
    }

    private function verifyNotifications(int $adminId, int $conversationId): void
    {
        $this->info('=== الإشعارات الواردة للمدير ===');
        $rows = \App\Models\Notification::where('user_id', $adminId)
            ->whereIn('message_id', \App\Models\Message::where('conversation_id', $conversationId)->pluck('id'))
            ->where('type', 'message')
            ->orderBy('id', 'desc')
            ->limit(8)
            ->get(['id', 'message_id', 'title', 'message', 'data', 'is_read', 'created_at']);

        if ($rows->isEmpty()) {
            $this->warn('لا توجد إشعارات.');
            return;
        }

        foreach ($rows as $n) {
            $data = is_string($n->data) ? json_decode($n->data, true) : $n->data;
            $hasAtt = $data['has_attachments'] ?? false;
            $count = $data['attachment_count'] ?? 0;
            $this->line(sprintf(
                "  #%d → Msg #%d | %s%s",
                $n->id,
                $n->message_id,
                mb_substr($n->message ?? '-', 0, 60),
                $hasAtt ? " | 📎 {$count} مرفق" : ''
            ));
        }
        $this->newLine();
    }

    private function summaryTable(int $conversationId): void
    {
        $messages = \App\Models\Message::where('conversation_id', $conversationId)
            ->withCount('attachments')
            ->orderBy('id', 'desc')
            ->limit(6)
            ->get(['id', 'user_id', 'content', 'created_at', 'attachments_count']);

        $rows = $messages->map(fn ($m) => [
            $m->id,
            $m->user_id === 1 ? 'المدير' : 'المشرف',
            $m->attachments_count,
            mb_substr($m->content ?? '(مرفق فقط)', 0, 40),
            $m->created_at->format('H:i:s'),
        ])->toArray();

        $this->info('=== آخر الرسائل في المحادثة ===');
        $this->table(['#', 'المرسل', 'مرفقات', 'المحتوى', 'الوقت'], $rows);
    }
}
