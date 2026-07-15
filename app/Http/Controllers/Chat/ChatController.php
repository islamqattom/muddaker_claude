<?php

namespace App\Http\Controllers\Chat;

use App\Events\MessageRead;
use App\Events\NewMessage;
use App\Events\UserTyping;
use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\MessageAttachment;
use App\Models\User;
use App\Services\ChatService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ChatController extends Controller
{
    public const PAGE_SIZE = 30;

    public function __construct(
        private readonly ChatService $chatService,
    ) {}

    /**
     * Single-page WhatsApp-style chat. Renders the split-pane layout with the
     * conversation list + (optionally) the right pane for a given conversation.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $conversations = Conversation::forUser($user->id)
            ->with(['participants', 'lastMessage'])
            ->orderByDesc('updated_at')
            ->get()
            ->each(function ($c) use ($user) {
                $c->unread_count = $c->getUnreadCountAttribute($user->id);
                $c->title = $c->displayTitleFor($user->id);
                $c->avatar = $c->displayAvatarFor($user->id);
                if ($c->lastMessage) {
                    $c->lastMessage->loadMissing(['user', 'attachments']);
                }
            });

        $active = null;
        $activeId = $request->query('conversation');
        if ($activeId) {
            $active = Conversation::with(['participants', 'messages.user', 'messages.attachments'])
                ->find($activeId);
            if ($active && $active->participants()->where('user_id', $user->id)->exists()) {
                $messages = Message::where('conversation_id', $active->id)
                    ->with(['user', 'attachments'])
                    ->orderBy('created_at', 'desc')
                    ->orderBy('id', 'desc')
                    ->limit(self::PAGE_SIZE)
                    ->get()
                    ->reverse()
                    ->values();
                $active->setRelation('messages', $messages);
                $active->other_user = $active->is_group
                    ? null
                    : $active->participants->first(fn ($p) => $p->id !== $user->id);
                $this->chatService->markConversationAsRead($active, $user->id);
                $active->load('participants');
            } else {
                $active = null;
            }
        }

        return response()->view('chat.app', [
            'conversations' => $conversations,
            'activeConversation' => $active,
        ]);
    }

    /**
     * Deep-link alias for /chat/{id}: redirects to the split-pane view.
     */
    public function show(Conversation $conversation)
    {
        $user = Auth::user();
        if (!$conversation->participants()->where('user_id', $user->id)->exists()) {
            abort(403);
        }
        return redirect()->route('chat.index', ['conversation' => $conversation->id]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'exists:users,id',
            'is_group' => 'boolean',
            'name' => 'nullable|string|max:255',
        ]);

        $user = Auth::user();

        if ($request->boolean('is_group')) {
            if (count($validated['user_ids']) < 2) {
                return back()->with('error', 'المجموعة يجب أن تحتوي على 3 أعضاء على الأقل');
            }
            $conversation = Conversation::create([
                'name' => $request->name ?? 'مجموعة جديدة',
                'is_group' => true,
                'created_by' => $user->id,
            ]);
            $userIds = array_unique(array_map('intval', array_merge([(int)$user->id], $validated['user_ids'])));
        } else {
            $otherUserId = (int) $validated['user_ids'][0];
            $existing = $this->findDirectConversation($user->id, $otherUserId);
            if ($existing) {
                return redirect()->route('chat.index', ['conversation' => $existing->id]);
            }

            $conversation = Conversation::create([
                'is_group' => false,
                'created_by' => $user->id,
            ]);
            $userIds = [(int)$user->id, $otherUserId];
        }

        $conversation->participants()->attach($userIds);

        return redirect()->route('chat.index', ['conversation' => $conversation->id]);
    }

    public function sendMessage(Request $request, Conversation $conversation)
    {
        $request->validate([
            'content' => 'nullable|string|max:5000',
            'attachments' => 'nullable|array|max:10',
            'attachments.*' => 'file|max:25600',
        ], [
            'content.max' => 'الرسالة طويلة جداً (الحد الأقصى 5000 حرف).',
            'attachments.max' => 'لا يمكن إرفاق أكثر من 10 ملفات.',
            'attachments.*.file' => 'الملف المرفق غير صالح.',
            'attachments.*.max' => 'حجم الملف يتجاوز الحد المسموح (25 ميجابايت).',
        ]);

        $user = Auth::user();

        if (!$conversation->participants()->where('user_id', $user->id)->exists()) {
            abort(403);
        }

        $content = (string) $request->input('content', '');
        $files = $request->file('attachments') ?? [];

        if (trim($content) === '' && empty($files)) {
            return response()->json([
                'success' => false,
                'error' => 'لا يمكن إرسال رسالة فارغة. اكتب نصاً أو أرفق ملفاً.',
            ], 422);
        }

        try {
            $message = $this->chatService->sendMessage($conversation, $user, $content, $files);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Chat sendMessage failed', [
                'user_id' => $user->id,
                'conversation_id' => $conversation->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
                'error' => 'حدث خطأ في الخادم أثناء إرسال الرسالة: ' . $e->getMessage(),
            ], 500);
        }

        event(new NewMessage($message));

        return response()->json([
            'success' => true,
            'message' => $message->load(['user', 'attachments']),
        ]);
    }

    public function download(MessageAttachment $attachment)
    {
        $user = Auth::user();

        $conversationId = $attachment->message?->conversation_id;
        if (!$conversationId) {
            abort(404);
        }

        $isParticipant = Conversation::where('id', $conversationId)
            ->whereHas('participants', fn ($q) => $q->where('user_id', $user->id))
            ->exists();

        if (!$isParticipant) {
            abort(403);
        }

        $disk = Storage::disk($attachment->disk);
        if (!$disk->exists($attachment->path)) {
            abort(404, 'الملف غير موجود على التخزين.');
        }

        return $disk->download($attachment->path, $attachment->original_name);
    }

    public function markAsRead(Conversation $conversation)
    {
        $user = Auth::user();

        if (!$conversation->participants()->where('user_id', $user->id)->exists()) {
            abort(403);
        }

        $this->chatService->markConversationAsRead($conversation, $user->id);

        return response()->json(['success' => true]);
    }

    /**
     * Heartbeat: updates last_seen_at for the current user in this conversation.
     * Returns the latest presence info for the conversation.
     */
    public function heartbeat(Conversation $conversation)
    {
        $user = Auth::user();

        if (!$conversation->participants()->where('user_id', $user->id)->exists()) {
            abort(403);
        }

        $this->chatService->heartbeat($conversation, $user->id);

        return response()->json([
            'presence' => $this->chatService->getPresence($conversation->fresh('participants')),
        ]);
    }

    /**
     * Broadcast a typing indicator (debounced on the client).
     */
    public function typing(Conversation $conversation)
    {
        $user = Auth::user();
        if (!$conversation->participants()->where('user_id', $user->id)->exists()) {
            abort(403);
        }
        try {
            event(new UserTyping($conversation, $user));
        } catch (\Throwable $e) {
            // Typing is best-effort; never block the user
            \Illuminate\Support\Facades\Log::debug('Typing broadcast failed', ['error' => $e->getMessage()]);
        }
        return response()->json(['ok' => true]);
    }

    /**
     * Paginate older messages (for scroll-up loading). Returns up to PAGE_SIZE
     * messages with id < $beforeId in descending order, then reversed.
     */
    public function messages(Request $request, Conversation $conversation)
    {
        $user = Auth::user();
        if (!$conversation->participants()->where('user_id', $user->id)->exists()) {
            abort(403);
        }

        $beforeId = (int) $request->query('before_id', 0);
        $query = Message::where('conversation_id', $conversation->id)
            ->with(['user', 'attachments'])
            ->orderBy('id', 'desc')
            ->limit(self::PAGE_SIZE);
        if ($beforeId > 0) {
            $query->where('id', '<', $beforeId);
        }
        $messages = $query->get()->reverse()->values();

        return response()->json([
            'messages' => $messages,
            'has_more' => $messages->count() === self::PAGE_SIZE,
            'oldest_id' => $messages->isNotEmpty() ? $messages->first()->id : null,
        ]);
    }

    public function getUsers(Request $request)
    {
        $search = $request->get('search', '');

        $users = User::where('is_active', true)
            ->where('id', '!=', Auth::id())
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->select(['id', 'name', 'email', 'role', 'avatar'])
            ->limit(20)
            ->get();

        return response()->json($users);
    }

    public function addParticipant(Request $request, Conversation $conversation)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $user = Auth::user();

        if (!$conversation->participants()->where('user_id', $user->id)->exists()) {
            abort(403);
        }

        if (!$conversation->is_group) {
            return back()->with('error', 'لا يمكن إضافة أعضاء لمحادثة ثنائية');
        }

        if ($conversation->participants()->where('user_id', $request->user_id)->exists()) {
            return back()->with('error', 'المستخدم موجود بالفعل في المحادثة');
        }

        $conversation->participants()->attach($request->user_id);
        $conversation->update(['updated_at' => now()]);

        return back()->with('success', 'تمت إضافة المشارك بنجاح');
    }

    public function removeParticipant(Request $request, Conversation $conversation, User $user)
    {
        $currentUser = Auth::user();

        if (!$conversation->participants()->where('user_id', $currentUser->id)->exists()) {
            abort(403);
        }

        if ($user->id === $conversation->created_by) {
            return back()->with('error', 'لا يمكن إزالة منشئ المجموعة');
        }

        $conversation->participants()->detach($user->id);

        return back()->with('success', 'تمت إزالة المشارك بنجاح');
    }

    private function findDirectConversation(int $userId1, int $userId2): ?Conversation
    {
        $conversations = Conversation::where('is_group', false)
            ->whereHas('participants', function ($query) use ($userId1) {
                $query->where('user_id', $userId1);
            })
            ->whereHas('participants', function ($query) use ($userId2) {
                $query->where('user_id', $userId2);
            })
            ->get();

        foreach ($conversations as $conversation) {
            if ($conversation->participants()->count() === 2) {
                return $conversation;
            }
        }

        return null;
    }

    public function unreadCount()
    {
        $userId = Auth::id();

        $conversations = Conversation::forUser($userId)
            ->with(['messages' => function ($query) use ($userId) {
                $query->where('user_id', '!=', $userId)->whereNull('read_at');
            }])
            ->get();

        $totalUnread = 0;
        foreach ($conversations as $conversation) {
            $participant = $conversation->participants()->where('user_id', $userId)->first();
            if ($participant && $participant->pivot->last_read_at) {
                $totalUnread += $conversation->messages()
                    ->where('created_at', '>', $participant->pivot->last_read_at)
                    ->count();
            } else {
                $totalUnread += $conversation->messages->count();
            }
        }

        return response()->json(['unread' => $totalUnread]);
    }
}
