<?php

namespace App\Http\Controllers\Chat;

use App\Events\NewMessage;
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
    public function __construct(
        private readonly ChatService $chatService,
    ) {}
    public function index()
    {
        $user = Auth::user();
        $conversations = Conversation::forUser($user->id)
            ->with('participants')
            ->with(['messages' => function ($query) {
                $query->orderBy('created_at', 'desc')->limit(1);
            }])
            ->orderByDesc('updated_at')
            ->get();

        return view('chat.index', compact('conversations'));
    }

    public function show(Conversation $conversation)
    {
        $user = Auth::user();

        if (!$conversation->participants()->where('user_id', $user->id)->exists()) {
            abort(403);
        }

        $messages = Message::where('conversation_id', $conversation->id)
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->limit(100)
            ->get()
            ->reverse()
            ->values();

        $conversation->setRelation('messages', $messages);
        $conversation->load('participants');

        $this->chatService->markConversationAsRead($conversation, $user->id);

        return view('chat.show', compact('conversation'));
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
                return redirect()->route('chat.show', $existing);
            }

            $conversation = Conversation::create([
                'is_group' => false,
                'created_by' => $user->id,
            ]);
            $userIds = [(int)$user->id, $otherUserId];
        }

        $conversation->participants()->attach($userIds);

        return redirect()->route('chat.show', $conversation);
    }

    public function sendMessage(Request $request, Conversation $conversation)
    {
        $request->validate([
            'content' => 'nullable|string|max:5000',
            'attachments' => 'nullable|array|max:10',
            'attachments.*' => 'file|max:25600',
        ]);

        $user = Auth::user();

        if (!$conversation->participants()->where('user_id', $user->id)->exists()) {
            abort(403);
        }

        $content = (string) $request->input('content', '');
        $files = $request->file('attachments') ?? [];

        try {
            $message = $this->chatService->sendMessage($conversation, $user, $content, $files);
        } catch (\InvalidArgumentException $e) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'error' => $e->getMessage()], 422);
            }
            return back()->with('error', $e->getMessage());
        }

        event(new NewMessage($message));

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message->load(['user', 'attachments']),
            ]);
        }

        return back();
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