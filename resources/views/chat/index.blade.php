@extends('layouts.app')
@section('title', 'المحادثات')
@section('page-title', 'المحادثات')

@php
    $role = auth()->user()->role;
    $roleSidebar = $role === 'admin' ? 'admin.partials.sidebar' : ($role === 'supervisor' ? 'supervisor.partials.sidebar' : ($role === 'student' ? 'student.partials.sidebar' : 'parent.partials.sidebar'));
    $currentUserId = auth()->id();
@endphp

@section('sidebar-menu')
    @include($roleSidebar)
@endsection

@section('content')
<div class="chat-container">
    <div class="chat-header">
        <div class="chat-header-info">
            <div class="chat-header-icon">
                <i class="fas fa-comments"></i>
            </div>
            <div>
                <h4>المحادثات</h4>
                <p>تواصل مع الطلاب والمشرفين بسهولة</p>
            </div>
        </div>
        <button class="btn btn-new-chat" data-bs-toggle="modal" data-bs-target="#newChatModal">
            <i class="fas fa-plus"></i>
            <span>محادثة جديدة</span>
        </button>
    </div>

    <div class="chat-card">
        <div class="chat-toolbar">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="conversationSearch" placeholder="ابحث في المحادثات...">
            </div>
            <div class="chat-count">
                <span>{{ $conversations->count() }}</span> محادثة
            </div>
        </div>

        @if($conversations->isEmpty())
            <div class="chat-empty">
                <div class="chat-empty-icon">
                    <i class="fas fa-comments"></i>
                </div>
                <h5>لا توجد محادثات بعد</h5>
                <p>ابدأ محادثة جديدة للتواصل مع الآخرين</p>
                <button class="btn btn-new-chat" data-bs-toggle="modal" data-bs-target="#newChatModal">
                    <i class="fas fa-plus"></i>
                    <span>بدء محادثة</span>
                </button>
            </div>
        @else
            <div class="conversations-list" id="conversationsList">
                @foreach($conversations as $conversation)
                    @php
                        $otherUser = $conversation->is_group ? null : $conversation->participants->first(fn($p) => $p->id !== $currentUserId);
                        $unreadCount = $conversation->getUnreadCountAttribute($currentUserId);
                        $lastMessage = $conversation->messages->first();
                    @endphp
                    <a href="{{ route('chat.show', $conversation) }}" class="conversation-item {{ $unreadCount > 0 ? 'unread' : '' }}">
                        <div class="conversation-avatar {{ $conversation->is_group ? 'group' : '' }}">
                            @if($conversation->is_group)
                                <i class="fas fa-users"></i>
                            @else
                                <i class="fas fa-user"></i>
                            @endif
                        </div>
                        <div class="conversation-body">
                            <div class="conversation-top">
                                <div class="conversation-title">
                                    {{ $conversation->is_group ? $conversation->name : ($otherUser ? $otherUser->name : 'مستخدم') }}
                                    @if($conversation->is_group)
                                        <span class="group-badge">مجموعة</span>
                                    @endif
                                </div>
                                @if($unreadCount > 0)
                                    <span class="unread-badge">{{ $unreadCount }}</span>
                                @endif
                            </div>
                            <div class="conversation-bottom">
                                <div class="conversation-preview">
                                    @if($lastMessage)
                                        <span>{{ Str::limit($lastMessage->content, 55) }}</span>
                                    @else
                                        <span class="text-muted">لا توجد رسائل بعد</span>
                                    @endif
                                </div>
                                @if($lastMessage)
                                    <span class="conversation-time">{{ $lastMessage->created_at->diffForHumans() }}</span>
                                @endif
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</div>

<div class="modal fade" id="newChatModal" tabindex="-1" aria-labelledby="newChatModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="newChatModalLabel">
                    <i class="fas fa-plus me-2"></i>
                    محادثة جديدة
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('chat.store') }}" method="POST" id="newChatForm">
                @csrf
                @if(session('error'))
                    <div class="alert alert-danger mx-3 mt-3 mb-0">{{ session('error') }}</div>
                @endif
                @if($errors->any())
                    <div class="alert alert-danger mx-3 mt-3 mb-0">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <div class="modal-body">
                    <div class="mb-4">
                        <label class="form-label">نوع المحادثة</label>
                        <div class="chat-type-buttons">
                            <button type="button" class="chat-type-btn active" data-type="direct">
                                <i class="fas fa-user"></i>
                                محادثة خاصة
                            </button>
                            <button type="button" class="chat-type-btn" data-type="group">
                                <i class="fas fa-users"></i>
                                مجموعة
                            </button>
                        </div>
                        <input type="hidden" name="is_group" id="chatTypeInput" value="0">
                    </div>

                    <div id="groupNameSection" class="mb-3 d-none">
                        <label class="form-label">اسم المجموعة</label>
                        <input type="text" name="name" class="form-control" placeholder="أدخل اسم المجموعة">
                    </div>

                    <div id="directChatSection">
                        <div class="mb-3">
                            <label class="form-label">اختر المستخدم</label>
                            <div class="search-select">
                                <i class="fas fa-search"></i>
                                <input type="text" class="user-search" data-target="userSelect" placeholder="ابحث عن مستخدم...">
                            </div>
                            <select id="userSelect" class="form-select" name="user_ids[]" required>
                                <option value="">-- اختر مستخدم --</option>
                                <optgroup label="الطلاب">
                                    @foreach(\App\Models\User::where('role', 'student')->where('is_active', true)->orderBy('name')->get() as $student)
                                        <option value="{{ $student->id }}" {{ $student->id == $currentUserId ? 'disabled' : '' }}>{{ $student->name }}</option>
                                    @endforeach
                                </optgroup>
                                <optgroup label="المشرفون">
                                    @foreach(\App\Models\User::where('role', 'supervisor')->where('is_active', true)->orderBy('name')->get() as $supervisor)
                                        <option value="{{ $supervisor->id }}" {{ $supervisor->id == $currentUserId ? 'disabled' : '' }}>{{ $supervisor->name }}</option>
                                    @endforeach
                                </optgroup>
                            </select>
                        </div>
                    </div>

                    <div id="groupChatSection" class="d-none">
                        <div class="mb-3">
                            <label class="form-label">اختر المشاركين (3 على الأقل)</label>
                            <div class="search-select">
                                <i class="fas fa-search"></i>
                                <input type="text" class="user-search" data-target="groupUsersSelect" placeholder="ابحث عن المشاركين...">
                            </div>
                            <select id="groupUsersSelect" class="form-select" name="user_ids[]" multiple required>
                                <optgroup label="الطلاب">
                                    @foreach(\App\Models\User::where('role', 'student')->where('is_active', true)->orderBy('name')->get() as $student)
                                        <option value="{{ $student->id }}" {{ $student->id == $currentUserId ? 'disabled' : '' }}>{{ $student->name }}</option>
                                    @endforeach
                                </optgroup>
                                <optgroup label="المشرفون">
                                    @foreach(\App\Models\User::where('role', 'supervisor')->where('is_active', true)->orderBy('name')->get() as $supervisor)
                                        <option value="{{ $supervisor->id }}" {{ $supervisor->id == $currentUserId ? 'disabled' : '' }}>{{ $supervisor->name }}</option>
                                    @endforeach
                                </optgroup>
                            </select>
                            <div id="groupSelectedTags" class="selected-users"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>
                        إنشاء المحادثة
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.chat-container {
    max-width: 950px;
    margin: 0 auto;
}

.chat-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    margin-bottom: 1.5rem;
    flex-wrap: wrap;
}

.chat-header-info {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.chat-header-icon {
    width: 58px;
    height: 58px;
    border-radius: 18px;
    background: linear-gradient(135deg, #182B48, #2A4570);
    color: #d4a030;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    box-shadow: 0 8px 24px rgba(24, 43, 72, 0.18);
}

.chat-header h4 {
    margin: 0;
    color: #182B48;
    font-weight: 800;
    font-size: 1.45rem;
}

.chat-header p {
    margin: 0;
    color: #7f8c8d;
    font-size: 0.92rem;
}

.btn-new-chat {
    background: linear-gradient(135deg, #27ae60, #2ecc71);
    border: none;
    color: #fff;
    padding: 0.75rem 1.5rem;
    border-radius: 14px;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    box-shadow: 0 8px 22px rgba(39, 174, 96, 0.28);
    transition: all 0.2s ease;
}

.btn-new-chat:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 26px rgba(39, 174, 96, 0.38);
    color: #fff;
}

.chat-card {
    background: #fff;
    border-radius: 22px;
    box-shadow: 0 12px 45px rgba(24, 43, 72, 0.09);
    overflow: hidden;
}

.chat-toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    padding: 1.25rem 1.75rem;
    border-bottom: 1px solid #f0f4f8;
    flex-wrap: wrap;
}

.search-box {
    position: relative;
    flex: 1;
    min-width: 240px;
}

.search-box i {
    position: absolute;
    right: 1rem;
    top: 50%;
    transform: translateY(-50%);
    color: #95a5a6;
    pointer-events: none;
}

.search-box input {
    width: 100%;
    padding: 0.7rem 2.5rem 0.7rem 1rem;
    border: 1px solid #e1e8ed;
    border-radius: 14px;
    background: #f8fafc;
    transition: all 0.2s ease;
    font-size: 0.92rem;
}

.search-box input:focus {
    background: #fff;
    border-color: #182B48;
    outline: none;
    box-shadow: 0 0 0 3px rgba(24, 43, 72, 0.08);
}

.chat-count {
    color: #7f8c8d;
    font-size: 0.92rem;
    font-weight: 700;
}

.chat-count span {
    color: #182B48;
    font-weight: 800;
}

.chat-empty {
    text-align: center;
    padding: 4.5rem 1.5rem;
}

.chat-empty-icon {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    background: linear-gradient(135deg, #f0f4f8, #e1e8ed);
    color: #182B48;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 42px;
    margin: 0 auto 1.25rem;
    box-shadow: 0 8px 24px rgba(24, 43, 72, 0.08);
}

.chat-empty h5 {
    color: #182B48;
    font-weight: 800;
    margin-bottom: 0.5rem;
}

.chat-empty p {
    color: #7f8c8d;
    margin-bottom: 1.5rem;
}

.conversations-list {
    display: flex;
    flex-direction: column;
}

.conversation-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1.15rem 1.75rem;
    text-decoration: none;
    color: inherit;
    border-bottom: 1px solid #f5f7fa;
    transition: all 0.2s ease;
    position: relative;
}

.conversation-item:last-child {
    border-bottom: none;
}

.conversation-item:hover {
    background: #f8fafc;
    transform: translateX(-4px);
}

.conversation-item.unread {
    background: #fffbf2;
}

.conversation-item.unread:hover {
    background: #fff5e0;
}

.conversation-item.unread::before {
    content: '';
    position: absolute;
    right: 0;
    top: 14px;
    bottom: 14px;
    width: 4px;
    background: linear-gradient(180deg, #d4a030, #B4802C);
    border-radius: 4px 0 0 4px;
}

.conversation-avatar {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    background: linear-gradient(135deg, #182B48, #2A4570);
    color: #d4a030;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    flex-shrink: 0;
    box-shadow: 0 4px 14px rgba(24, 43, 72, 0.14);
}

.conversation-avatar.group {
    background: linear-gradient(135deg, #d4a030, #B4802C);
    color: #fff;
}

.conversation-body {
    flex: 1;
    min-width: 0;
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
}

.conversation-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
}

.conversation-title {
    font-weight: 800;
    color: #182B48;
    font-size: 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.conversation-item.unread .conversation-title {
    color: #000;
}

.group-badge {
    background: rgba(24, 43, 72, 0.08);
    color: #182B48;
    padding: 0.2rem 0.55rem;
    border-radius: 6px;
    font-size: 0.7rem;
    font-weight: 800;
}

.unread-badge {
    background: linear-gradient(135deg, #e74c3c, #c0392b);
    color: #fff;
    font-size: 0.72rem;
    font-weight: 800;
    min-width: 22px;
    height: 22px;
    padding: 0 6px;
    border-radius: 11px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.conversation-bottom {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.75rem;
}

.conversation-preview {
    font-size: 0.86rem;
    color: #7f8c8d;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    flex: 1;
}

.conversation-item.unread .conversation-preview {
    color: #182B48;
    font-weight: 600;
}

.conversation-time {
    font-size: 0.75rem;
    color: #95a5a6;
    white-space: nowrap;
}

.modal-content {
    border: none;
    border-radius: 22px;
    overflow: hidden;
}

.modal-header {
    background: linear-gradient(135deg, #182B48, #2A4570);
    padding: 1.25rem 1.75rem;
    color: #fff;
}

.modal-title {
    font-weight: 800;
    font-size: 1.1rem;
}

.chat-type-buttons {
    display: flex;
    gap: 0.5rem;
    background: #f0f4f8;
    padding: 0.35rem;
    border-radius: 16px;
}

.chat-type-btn {
    flex: 1;
    padding: 0.75rem;
    border: none;
    border-radius: 12px;
    background: transparent;
    color: #7f8c8d;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    transition: all 0.2s ease;
}

.chat-type-btn:hover {
    background: rgba(24, 43, 72, 0.04);
}

.chat-type-btn.active {
    background: #fff;
    color: #182B48;
    box-shadow: 0 4px 12px rgba(24, 43, 72, 0.1);
}

.form-label {
    font-weight: 800;
    color: #182B48;
    font-size: 0.9rem;
    margin-bottom: 0.5rem;
}

.form-control,
.form-select {
    border-radius: 12px;
    border: 1px solid #e1e8ed;
    padding: 0.75rem 1rem;
    font-size: 0.92rem;
}

.form-control:focus,
.form-select:focus {
    border-color: #182B48;
    box-shadow: 0 0 0 3px rgba(24, 43, 72, 0.08);
}

.search-select {
    position: relative;
    margin-bottom: 0.5rem;
}

.search-select i {
    position: absolute;
    right: 0.85rem;
    top: 50%;
    transform: translateY(-50%);
    color: #95a5a6;
    font-size: 0.85rem;
    pointer-events: none;
}

.search-select input {
    width: 100%;
    padding: 0.55rem 2rem 0.55rem 0.75rem;
    border: 1px solid #e1e8ed;
    border-radius: 10px;
    font-size: 0.85rem;
    background: #f8fafc;
}

.search-select input:focus {
    outline: none;
    border-color: #182B48;
    background: #fff;
}

.form-select[multiple] {
    min-height: 170px;
    border-radius: 12px;
}

.form-select option {
    padding: 0.55rem 0.85rem;
}

.form-select option:checked {
    background: #182B48;
    color: #fff;
}

.form-select option:disabled {
    color: #bdc3c7;
    background: #f8fafc;
}

.form-select optgroup {
    font-weight: 800;
    color: #182B48;
    background: #f8fafc;
    padding: 0.4rem 0;
}

.selected-users {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-top: 0.75rem;
}

.selected-user-tag {
    background: linear-gradient(135deg, rgba(24, 43, 72, 0.08), rgba(42, 69, 112, 0.08));
    color: #182B48;
    padding: 0.4rem 0.85rem;
    border-radius: 20px;
    font-size: 0.82rem;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.selected-user-tag button {
    border: none;
    background: rgba(192, 57, 43, 0.12);
    color: #c0392b;
    width: 18px;
    height: 18px;
    border-radius: 50%;
    font-size: 9px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background 0.2s;
}

.selected-user-tag button:hover {
    background: rgba(192, 57, 43, 0.25);
}

.modal-footer {
    border-top: 1px solid #f0f4f8;
    padding: 1rem 1.75rem;
}

.btn-light {
    background: #f0f4f8;
    border: none;
    color: #182B48;
    border-radius: 12px;
    padding: 0.65rem 1.25rem;
    font-weight: 700;
}

.btn-light:hover {
    background: #e1e8ed;
}

.modal-footer .btn-primary {
    background: linear-gradient(135deg, #27ae60, #2ecc71);
    border: none;
    border-radius: 12px;
    padding: 0.65rem 1.5rem;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.modal-footer .btn-primary:hover {
    opacity: 0.92;
}

@media (max-width: 768px) {
    .chat-container {
        max-width: 100%;
    }

    .chat-header {
        gap: 0.75rem;
    }

    .chat-header-icon {
        width: 48px;
        height: 48px;
        font-size: 20px;
        border-radius: 14px;
    }

    .chat-header h4 {
        font-size: 1.15rem;
    }

    .chat-header p {
        font-size: 0.82rem;
    }

    .btn-new-chat {
        padding: 0.6rem 1.1rem;
        font-size: 0.85rem;
        border-radius: 12px;
    }

    .chat-toolbar {
        padding: 0.9rem 1rem;
        gap: 0.75rem;
    }

    .search-box {
        min-width: 0;
    }

    .conversation-item {
        padding: 0.9rem 1rem;
        gap: 0.75rem;
    }

    .conversation-avatar {
        width: 46px;
        height: 46px;
        font-size: 18px;
    }

    .conversation-title {
        font-size: 0.92rem;
    }

    .conversation-preview {
        font-size: 0.8rem;
    }

    .conversation-time {
        font-size: 0.7rem;
    }

    .unread-badge {
        font-size: 0.65rem;
        min-width: 20px;
        height: 20px;
    }

    .modal-header {
        padding: 1rem 1.25rem;
    }

    .modal-footer {
        padding: 0.85rem 1.25rem;
    }
}

@media (max-width: 576px) {
    .chat-header {
        flex-direction: column;
        align-items: stretch;
        gap: 0.85rem;
    }

    .chat-header-info {
        gap: 0.75rem;
    }

    .btn-new-chat {
        width: 100%;
        justify-content: center;
    }

    .chat-toolbar {
        flex-direction: column;
        align-items: stretch;
        padding: 0.85rem 1rem;
    }

    .chat-count {
        text-align: center;
    }

    .chat-empty {
        padding: 3rem 1rem;
    }

    .chat-empty-icon {
        width: 80px;
        height: 80px;
        font-size: 32px;
    }

    .chat-empty h5 {
        font-size: 1.1rem;
    }

    .chat-type-buttons {
        padding: 0.25rem;
        border-radius: 12px;
    }

    .chat-type-btn {
        padding: 0.6rem;
        font-size: 0.85rem;
        border-radius: 10px;
    }
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const chatTypeBtns = document.querySelectorAll('.chat-type-btn');
    const chatTypeInput = document.getElementById('chatTypeInput');
    const groupNameSection = document.getElementById('groupNameSection');
    const directChatSection = document.getElementById('directChatSection');
    const groupChatSection = document.getElementById('groupChatSection');
    const userSelect = document.getElementById('userSelect');
    const groupUsersSelect = document.getElementById('groupUsersSelect');
    const groupSelectedTags = document.getElementById('groupSelectedTags');
    const newChatForm = document.getElementById('newChatForm');
    const newChatModal = document.getElementById('newChatModal');
    const conversationSearch = document.getElementById('conversationSearch');
    const conversationsList = document.getElementById('conversationsList');

    groupUsersSelect.disabled = true;

    function switchChatType(type) {
        chatTypeBtns.forEach(b => b.classList.remove('active'));
        document.querySelector(`.chat-type-btn[data-type="${type}"]`).classList.add('active');
        chatTypeInput.value = type === 'group' ? 1 : 0;

        if (type === 'group') {
            groupNameSection.classList.remove('d-none');
            directChatSection.classList.add('d-none');
            groupChatSection.classList.remove('d-none');
            userSelect.removeAttribute('required');
            userSelect.disabled = true;
            groupUsersSelect.setAttribute('required', 'required');
            groupUsersSelect.disabled = false;
        } else {
            groupNameSection.classList.add('d-none');
            directChatSection.classList.remove('d-none');
            groupChatSection.classList.add('d-none');
            userSelect.setAttribute('required', 'required');
            userSelect.disabled = false;
            groupUsersSelect.removeAttribute('required');
            groupUsersSelect.disabled = true;
        }

        buildGroupTags();
    }

    chatTypeBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            switchChatType(this.dataset.type);
        });
    });

    function buildGroupTags() {
        groupSelectedTags.innerHTML = '';
        const selected = Array.from(groupUsersSelect.selectedOptions);
        if (selected.length === 0) return;

        selected.forEach(option => {
            const tag = document.createElement('div');
            tag.className = 'selected-user-tag';
            tag.innerHTML = `
                <span>${option.text}</span>
                <button type="button" data-value="${option.value}" aria-label="إزالة">
                    <i class="fas fa-times"></i>
                </button>
            `;
            tag.querySelector('button').addEventListener('click', function() {
                const opt = groupUsersSelect.querySelector(`option[value="${this.dataset.value}"]`);
                if (opt) {
                    opt.selected = false;
                    buildGroupTags();
                }
            });
            groupSelectedTags.appendChild(tag);
        });
    }

    groupUsersSelect.addEventListener('change', buildGroupTags);

    document.querySelectorAll('.user-search').forEach(input => {
        input.addEventListener('input', function() {
            const target = document.getElementById(this.dataset.target);
            const term = this.value.trim().toLowerCase();
            target.querySelectorAll('optgroup').forEach(group => {
                let visible = 0;
                group.querySelectorAll('option').forEach(option => {
                    const match = option.textContent.toLowerCase().includes(term);
                    option.style.display = match ? '' : 'none';
                    if (match) visible++;
                });
                group.style.display = visible > 0 ? '' : 'none';
            });
        });
    });

    if (conversationSearch && conversationsList) {
        conversationSearch.addEventListener('input', function() {
            const term = this.value.trim().toLowerCase();
            conversationsList.querySelectorAll('.conversation-item').forEach(item => {
                const text = item.textContent.toLowerCase();
                item.style.display = text.includes(term) ? '' : 'none';
            });
        });
    }

    newChatModal.addEventListener('hidden.bs.modal', function() {
        newChatForm.reset();
        switchChatType('direct');
        document.querySelectorAll('.user-search').forEach(input => input.value = '');
        document.querySelectorAll('.form-select option').forEach(option => option.style.display = '');
        document.querySelectorAll('.form-select optgroup').forEach(group => group.style.display = '');
        buildGroupTags();
    });
});
</script>
@endpush
