@extends('layouts.app')

@php
    $role = auth()->user()->role;
    $roleSidebar = $role === 'admin' ? 'admin.partials.sidebar' : ($role === 'supervisor' ? 'supervisor.partials.sidebar' : ($role === 'student' ? 'student.partials.sidebar' : 'parent.partials.sidebar'));
    $otherUser = !$conversation->is_group ? $conversation->participants->first(fn($p) => $p->id !== auth()->id()) : null;
@endphp

@section('title', $conversation->is_group ? $conversation->name : ($otherUser ? $otherUser->name : 'المحادثة'))

@section('sidebar-menu')
    @include($roleSidebar)
@endsection

@section('content')
<div class="chat-container">
    <div class="chat-header">
        <a href="{{ route('chat.index') }}" class="back-btn">
            <i class="fas fa-arrow-right"></i>
        </a>
        <div class="chat-header-info">
            <div class="chat-header-avatar">
                @if($conversation->is_group)
                    <i class="fas fa-users"></i>
                @else
                    <i class="fas fa-user"></i>
                @endif
            </div>
            <div>
                <h5 class="chat-header-name">
                    {{ $conversation->is_group ? $conversation->name : ($otherUser ? $otherUser->name : 'مستخدم') }}
                </h5>
                @if($conversation->is_group)
                    <span class="chat-header-status">{{ $conversation->participants->count() }} مشارك</span>
                @else
                    <span class="chat-header-status">{{ $otherUser && $otherUser->is_active ? 'متصل' : 'غير متصل' }}</span>
                @endif
            </div>
        </div>
        @if($conversation->is_group)
            <div class="chat-actions">
                <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addParticipantModal">
                    <i class="fas fa-user-plus"></i>
                </button>
            </div>
        @endif
    </div>

    <div class="chat-messages" id="chatMessages">
        @forelse($conversation->messages as $message)
            <div class="message {{ $message->user_id === auth()->id() ? 'sent' : 'received' }}" data-message-id="{{ $message->id }}">
                <div class="message-content">
                    @if($message->user_id !== auth()->id())
                        <span class="message-sender">{{ $message->user->name }}</span>
                    @endif
                    <p class="message-text">{{ $message->content }}</p>
                    <span class="message-time">{{ $message->created_at->format('H:i') }}</span>
                </div>
            </div>
        @empty
            <div class="empty-chat">
                <div class="empty-icon">
                    <i class="fas fa-comment-dots"></i>
                </div>
                <h5>لا توجد رسائل</h5>
                <p>ابدأ المحادثة بإرسال رسالة</p>
            </div>
        @endforelse
    </div>

    <div class="chat-input-container">
        <form id="messageForm" class="message-form">
            @csrf
            <input type="hidden" name="content" id="messageInput">
            <div class="chat-input-wrapper">
                <input type="text" id="chatInput" class="chat-input" placeholder="اكتب رسالة..." autocomplete="off">
                <button type="submit" class="send-btn">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
        </form>
    </div>
</div>

@if($conversation->is_group)
<div class="modal fade" id="addParticipantModal" tabindex="-1" aria-labelledby="addParticipantModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #182B48, #2A4570); color: #fff;">
                <h5 class="modal-title" id="addParticipantModalLabel">
                    <i class="fas fa-user-plus me-2"></i>
                    إضافة مشارك
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('chat.addParticipant', $conversation) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">ابحث عن مستخدم</label>
                        <input type="text" id="participantSearch" class="form-control" placeholder="اكتب اسم المستخدم..." autocomplete="off">
                        <div id="participantSearchResults" class="search-results d-none mt-2"></div>
                    </div>
                    <input type="hidden" name="user_id" id="selectedParticipantId" value="">
                    <div id="selectedParticipantDisplay" class="selected-users mt-3 d-none">
                        <div class="selected-user-tag">
                            <i class="fas fa-user"></i>
                            <span id="selectedParticipantName"></span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>
                        إضافة
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection

@push('styles')
<style>
.chat-container {
    display: flex;
    flex-direction: column;
    height: calc(100vh - 180px);
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.05);
    overflow: hidden;
}

.chat-header {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 20px 24px;
    background: linear-gradient(135deg, #182B48, #2A4570);
    color: #fff;
}

.back-btn {
    width: 40px;
    height: 40px;
    background: rgba(255,255,255,0.15);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    text-decoration: none;
    transition: all 0.3s ease;
}

.back-btn:hover {
    background: rgba(255,255,255,0.25);
}

.chat-header-info {
    display: flex;
    align-items: center;
    gap: 14px;
    flex: 1;
}

.chat-header-avatar {
    width: 48px;
    height: 48px;
    background: rgba(212,160,48,0.2);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #d4a030;
    font-size: 20px;
}

.chat-header-name {
    font-size: 16px;
    font-weight: 700;
    margin: 0;
}

.chat-header-status {
    font-size: 12px;
    opacity: 0.8;
}

.chat-messages {
    flex: 1;
    overflow-y: auto;
    padding: 24px;
    display: flex;
    flex-direction: column;
    gap: 16px;
    background: #f0f4f8;
}

.message {
    display: flex;
    max-width: 70%;
}

.message.sent {
    align-self: flex-start;
}

.message.received {
    align-self: flex-end;
}

.message-content {
    padding: 14px 18px;
    border-radius: 16px;
    position: relative;
}

.message.sent .message-content {
    background: linear-gradient(135deg, #182B48, #2A4570);
    color: #fff;
    border-bottom-right-radius: 4px;
}

.message.received .message-content {
    background: #fff;
    color: #2c3e50;
    border-bottom-left-radius: 4px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}

.message-sender {
    font-size: 11px;
    font-weight: 700;
    color: #B4802C;
    display: block;
    margin-bottom: 6px;
}

.message-text {
    margin: 0 0 8px 0;
    font-size: 14px;
    line-height: 1.5;
}

.message-time {
    font-size: 10px;
    opacity: 0.7;
    display: block;
    text-align: left;
}

.chat-input-container {
    padding: 20px;
    background: #fff;
    border-top: 1px solid #f0f4f8;
}

.message-form {
    display: flex;
    gap: 12px;
}

.chat-input-wrapper {
    flex: 1;
    display: flex;
    gap: 12px;
    background: #f0f4f8;
    border-radius: 25px;
    padding: 6px;
}

.chat-input {
    flex: 1;
    border: none;
    background: transparent;
    padding: 12px 16px;
    font-size: 14px;
    outline: none;
}

.send-btn {
    width: 48px;
    height: 48px;
    background: linear-gradient(135deg, #182B48, #2A4570);
    border: none;
    border-radius: 50%;
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s ease;
}

.send-btn:hover {
    transform: scale(1.05);
    box-shadow: 0 4px 15px rgba(24,43,72,0.3);
}

.empty-chat {
    text-align: center;
    padding: 60px 20px;
    color: #7f8c8d;
}

.empty-chat .empty-icon {
    width: 90px;
    height: 90px;
    background: linear-gradient(135deg, rgba(24, 43, 72, 0.1), rgba(42, 69, 112, 0.1));
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
    font-size: 36px;
    color: #d4a030;
}

.empty-chat h5 {
    color: #182B48;
    font-size: 18px;
    font-weight: 700;
    margin-bottom: 8px;
}

@media (max-width: 768px) {
    .chat-container {
        height: calc(100vh - 120px);
    }

    .message {
        max-width: 85%;
    }
}
</style>
@endpush

@push('scripts')
<script src="https://js.pusher.com/7.2/pusher.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const conversationId = {{ $conversation->id }};
    const messagesContainer = document.getElementById('chatMessages');
    const messageForm = document.getElementById('messageForm');
    const chatInput = document.getElementById('chatInput');

    messagesContainer.scrollTop = messagesContainer.scrollHeight;

    // Pusher setup for real-time
    @if(config('broadcasting.default') === 'pusher')
    const pusher = new Pusher('{{ config('broadcasting.connections.pusher.key') }}', {
        cluster: '{{ config('broadcasting.connections.pusher.options.cluster') }}'
    });

    const channel = pusher.subscribe('chat.{{ auth()->id() }}');
    channel.bind('new-message', function(data) {
        addMessageToChat(data);
    });
    @endif

    messageForm.addEventListener('submit', function(e) {
        e.preventDefault();

        const content = chatInput.value.trim();
        if (!content) return;

        fetch(`/chat/${conversationId}/message`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
            body: JSON.stringify({ content })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                chatInput.value = '';
                location.reload();
            }
        });
    });

    function addMessageToChat(data) {
        // Update header chat badge (for messages in other conversations)
        updateChatBadge(data);

        // Only add message to current conversation if it matches
        if (data.conversation_id !== conversationId) return;

        const isSent = data.user_id === {{ auth()->id() }};
        const messageHtml = `
            <div class="message ${isSent ? 'sent' : 'received'}" data-message-id="${data.id}">
                <div class="message-content">
                    ${!isSent ? `<span class="message-sender">${data.user.name}</span>` : ''}
                    <p class="message-text">${data.content}</p>
                    <span class="message-time">${new Date(data.created_at).toLocaleTimeString('ar', { hour: '2-digit', minute: '2-digit' })}</span>
                </div>
            </div>
        `;

        const emptyState = messagesContainer.querySelector('.empty-chat');
        if (emptyState) {
            emptyState.remove();
        }

        messagesContainer.insertAdjacentHTML('beforeend', messageHtml);
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    function updateChatBadge(data) {
        const badge = document.getElementById('chatBadge');
        if (!badge) return;

        if (data.conversation_id !== conversationId) {
            let count = parseInt(badge.textContent) || 0;
            badge.textContent = count + 1;
            badge.style.display = 'flex';
        }
    }

    // Mark as read
    fetch(`/chat/${conversationId}/read`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
        }
    });

    // Participant search in group chat
    @if($conversation->is_group)
    const participantSearch = document.getElementById('participantSearch');
    const participantResults = document.getElementById('participantSearchResults');
    const selectedParticipantId = document.getElementById('selectedParticipantId');
    const selectedParticipantDisplay = document.getElementById('selectedParticipantDisplay');
    const selectedParticipantName = document.getElementById('selectedParticipantName');

    participantSearch.addEventListener('input', function() {
        const query = this.value.trim();

        if (query.length < 2) {
            participantResults.classList.add('d-none');
            return;
        }

        fetch('/chat/users/search?search=' + encodeURIComponent(query))
            .then(response => response.json())
            .then(users => {
                participantResults.innerHTML = '';
                users.forEach(user => {
                    const existingIds = @json($conversation->participants->pluck('id')->toArray());
                    if (!existingIds.includes(user.id) && user.id !== {{ auth()->id() }}) {
                        const item = document.createElement('div');
                        item.className = 'search-result-item';
                        item.innerHTML = `
                            <div class="user-avatar-sm" style="background: linear-gradient(135deg, #182B48, #2A4570); color: #d4a030; width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-user"></i>
                            </div>
                            <div>
                                <div style="font-weight: 600; color: #182B48;">${user.name}</div>
                                <div style="font-size: 12px; color: #7f8c8d;">${user.role}</div>
                            </div>
                        `;
                        item.addEventListener('click', function() {
                            selectedParticipantId.value = user.id;
                            selectedParticipantName.textContent = user.name;
                            selectedParticipantDisplay.classList.remove('d-none');
                            participantSearch.value = '';
                            participantResults.classList.add('d-none');
                        });
                        participantResults.appendChild(item);
                    }
                });
                participantResults.classList.remove('d-none');
            });
    });
    @endif
});
</script>
@endpush