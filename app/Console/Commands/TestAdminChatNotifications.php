<?php

namespace App\Console\Commands;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\Notification;
use App\Models\User;
use App\Services\ChatService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;

class TestAdminChatNotifications extends Command
{
    protected $signature = 'chat:test-admin-notifications
                            {--sender=2 : ID المستخدم المرسل (المشرف افتراضياً)}
                            {--admin=1 : ID المدير المستقبل}
                            {--count=4 : عدد الرسائل المراد إرسالها}
                            {--interval=5 : الفاصل الزمني بالثواني بين كل رسالتين}
                            {--reset : حذف الإشعارات القديمة المتعلقة بالمحادثة قبل الاختبار}';

    protected $description = 'إرسال عدة رسائل من مستخدم إلى المدير بفواصل زمنية، ثم التحقق من عمل الإشعارات';

    public function handle(ChatService $chatService): int
    {
        $senderId = (int) $this->option('sender');
        $adminId = (int) $this->option('admin');
        $count = max(1, (int) $this->option('count'));
        $interval = max(1, (int) $this->option('interval'));
        $reset = (bool) $this->option('reset');

        $sender = User::find($senderId);
        $admin = User::find($adminId);

        if (!$sender || !$admin) {
            $this->error('المستخدم المرسل أو المدير غير موجود.');
            return self::FAILURE;
        }

        if ($sender->id === $admin->id) {
            $this->error('لا يمكن أن يكون المرسل هو نفسه المستقبل.');
            return self::FAILURE;
        }

        $this->info("=== بدء اختبار نظام المحادثات والإشعارات ===");
        $this->line("المرسل: {$sender->name} (#{$sender->id}, {$sender->role})");
        $this->line("المدير : {$admin->name} (#{$admin->id}, {$admin->role})");
        $this->line("عدد الرسائل: {$count} | الفاصل الزمني: {$interval} ثانية");
        $this->newLine();

        Auth::loginUsingId($sender->id);

        $conversation = $this->findOrCreateDirectConversation($sender->id, $admin->id);

        if ($reset) {
            $deleted = Notification::where('user_id', $admin->id)
                ->whereIn(
                    'message_id',
                    Message::where('conversation_id', $conversation->id)->pluck('id')
                )
                ->delete();
            $this->warn("تم حذف {$deleted} إشعار قديم مرتبط بهذه المحادثة.");
        }

        $baseNotifCount = Notification::where('user_id', $admin->id)->count();
        $baseMsgCount = Message::where('conversation_id', $conversation->id)->count();
        $this->line("عدد الإشعارات الحالية للمدير: {$baseNotifCount}");
        $this->line("عدد الرسائل الحالية بالمحادثة: {$baseMsgCount}");
        $this->newLine();

        $samples = [
            'السلام عليكم، يرجى مراجعة آخر تقرير للحضور.',
            'تم رفع إنذار جديد للطالب، هل تريد مراجعته الآن؟',
            'لدي استفسار بخصوص مواعيد جلسات التسميع للأسبوع القادم.',
            'تذكير: لدينا اجتماع المشرفين اليوم الساعة الثامنة مساءً.',
            'يرجى اعتماد فاتورة شهر يونيو الخاصة بالجمعية.',
        ];

        $startedAt = now();
        $rows = [];

        for ($i = 1; $i <= $count; $i++) {
            $content = $samples[($i - 1) % count($samples)] . " [رسالة #{$i}]";

            $message = $chatService->sendMessage($conversation, $sender, $content);

            $sentAt = now();
            $rows[] = [
                $i,
                $message->id,
                $sentAt->format('H:i:s'),
                mb_substr($content, 0, 40) . (mb_strlen($content) > 40 ? '…' : ''),
            ];

            $this->info("✓ [{$sentAt->format('H:i:s')}] أُرسلت الرسالة #{$i} (ID={$message->id})");

            if ($i < $count) {
                $this->line("  … انتظار {$interval} ثانية …");
                sleep($interval);
            }
        }

        $this->newLine();
        $this->info('=== جدول الرسائل المرسلة ===');
        $this->table(
            ['#', 'Message ID', 'وقت الإرسال', 'المحتوى'],
            $rows
        );

        $elapsed = $startedAt->diffInSeconds(now());
        $this->newLine();
        $this->info("المدة الإجمالية للاختبار: {$elapsed} ثانية");
        $this->newLine();

        $this->verifyAdminNotifications($admin->id, $conversation->id, $baseNotifCount, $count);

        Auth::logout();

        return self::SUCCESS;
    }

    private function findOrCreateDirectConversation(int $userId1, int $userId2): Conversation
    {
        $existing = Conversation::where('is_group', false)
            ->whereHas('participants', fn ($q) => $q->where('user_id', $userId1))
            ->whereHas('participants', fn ($q) => $q->where('user_id', $userId2))
            ->get()
            ->first(fn ($c) => $c->participants()->count() === 2);

        if ($existing) {
            $this->line("استخدام محادثة ثنائية موجودة (ID={$existing->id})");
            return $existing;
        }

        $conversation = Conversation::create([
            'is_group' => false,
            'created_by' => $userId1,
        ]);
        $conversation->participants()->attach([$userId1, $userId2]);
        $this->line("تم إنشاء محادثة ثنائية جديدة (ID={$conversation->id})");
        return $conversation;
    }

    private function verifyAdminNotifications(int $adminId, int $conversationId, int $baseCount, int $expectedNew): void
    {
        $this->info('=== التحقق من الإشعارات الواردة للمدير ===');

        $total = Notification::where('user_id', $adminId)->count();
        $newTotal = $total - $baseCount;
        $this->line("إجمالي إشعارات المدير الآن: {$total} (新增: {$newTotal}، متوقع: {$expectedNew})");

        $byMessage = Notification::where('user_id', $adminId)
            ->whereIn(
                'message_id',
                Message::where('conversation_id', $conversationId)->pluck('id')
            )
            ->orderBy('id')
            ->get(['id', 'message_id', 'type', 'title', 'is_read', 'created_at']);

        $this->line("إشعارات مرتبطة بهذه المحادثة: " . $byMessage->count());
        $this->newLine();

        if ($byMessage->isEmpty()) {
            $this->error('✗ لا توجد إشعارات لهذه المحادثة — فشل الاختبار!');
            return;
        }

        $this->table(
            ['Notif ID', 'Msg ID', 'النوع', 'العنوان', 'مقروء؟', 'وقت الإنشاء'],
            $byMessage->map(fn ($n) => [
                $n->id,
                $n->message_id,
                $n->type,
                mb_substr($n->title ?? '-', 0, 35),
                $n->is_read ? 'نعم' : 'لا',
                $n->created_at?->format('H:i:s'),
            ])->toArray()
        );

        $allHaveMessageId = $byMessage->every(fn ($n) => !is_null($n->message_id));
        $allMessageType = $byMessage->every(fn ($n) => $n->type === 'message');
        $countOk = $byMessage->count() >= $expectedNew;

        $this->newLine();
        $this->line($allHaveMessageId ? '✓' : '✗', "كل إشعار مرتبط بـ message_id");
        $this->line($allMessageType ? '✓' : '✗', "نوع الإشعارات = message");
        $this->line($countOk ? '✓' : '✗', "عدد الإشعارات الجديدة مطابق للمتوقع ({$byMessage->count()}/{$expectedNew})");

        $deltas = [];
        $prev = null;
        foreach ($byMessage->take($expectedNew) as $n) {
            if ($prev) {
                $deltas[] = $prev->diffInSeconds($n->created_at) . 's';
            }
            $prev = $n->created_at;
        }
        if (!empty($deltas)) {
            $this->newLine();
            $this->line('الفواصل الزمنية بين الإشعارات: ' . implode(' ، ', $deltas));
        }

        if ($allHaveMessageId && $allMessageType && $countOk) {
            $this->newLine();
            $this->info('🎉 الاختبار نجح: الرسائل والإشعارات تعمل بشكل صحيح عبر الفترات الزمنية المختلفة.');
        } else {
            $this->newLine();
            $this->error('⚠️  الاختبار كشف مشاكل في نظام الإشعارات.');
        }
    }
}
