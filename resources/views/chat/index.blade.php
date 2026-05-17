@extends('layouts.app')
@section('title', 'المحادثات')
@section('page-title', 'المحادثات')

@php
    $role = auth()->user()->role;
    $roleSidebar = $role === 'admin' ? 'admin.partials.sidebar' : ($role === 'supervisor' ? 'supervisor.partials.sidebar' : ($role === 'student' ? 'student.partials.sidebar' : 'parent.partials.sidebar'));
@endphp

@section('sidebar-menu')
    @include($roleSidebar)
@endsection

@section('content')
<div class="page-header mb-4">
    <div class="header-content">
        <div class="header-icon" style="background: linear-gradient(135deg, #27ae60, #2ecc71);">
            <i class="fas fa-comments"></i>
        </div>
        <div class="header-text">
            <h4>المحادثات</h4>
            <p>تواصل مع الطلاب والمشرفين</p>
        </div>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newChatModal">
        <i class="fas fa-plus me-2"></i>
        محادثة جديدة
    </button>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="table-card">
            <div class="table-header">
                <div class="table-title">
                    <i class="fas fa-list"></i>
                    <span>المحادثات</span>
                </div>
                <div class="record-count">
                    <i class="fas fa-comment"></i>
                    <span>{{ $conversations->count() }} محادثة</span>
                </div>
            </div>

            @if($conversations->isEmpty())
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="fas fa-comments"></i>
                    </div>
                    <h5>لا توجد محادثات</h5>
                    <p>ابدأ محادثة جديدة للتواصل مع الآخرين</p>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newChatModal">
                        <i class="fas fa-plus me-2"></i>
                        بدء محادثة جديدة
                    </button>
                </div>
            @else
                <div class="conversations-list">
                    @foreach($conversations as $conversation)
                        @php
                            $otherUser = $conversation->is_group ? null : $conversation->participants->first(fn($p) => $p->id !== auth()->id());
                            $unreadCount = $conversation->getUnreadCountAttribute(auth()->id());
                        @endphp
                        <a href="{{ route('chat.show', $conversation) }}" class="conversation-item {{ $unreadCount > 0 ? 'unread' : '' }}">
                            <div class="conversation-avatar">
                                @if($conversation->is_group)
                                    <i class="fas fa-users"></i>
                                @else
                                    <i class="fas fa-user"></i>
                                @endif
                            </div>
                            <div class="conversation-info">
                                <div class="conversation-name">
                                    {{ $conversation->is_group ? $conversation->name : ($otherUser ? $otherUser->name : 'مستخدم') }}
                                    @if($unreadCount > 0)
                                        <span class="unread-badge">{{ $unreadCount }}</span>
                                    @endif
                                </div>
                                <div class="conversation-preview">
                                    @if($conversation->messages->count() > 0)
                                        <span>{{ Str::limit($conversation->messages->first()->content, 40) }}</span>
                                    @else
                                        <span class="text-muted">لا توجد رسائل بعد</span>
                                    @endif
                                </div>
                            </div>
                            <div class="conversation-meta">
                                @if($conversation->messages->count() > 0)
                                    <span class="conversation-time">{{ $conversation->messages->first()->created_at->diffForHumans() }}</span>
                                @endif
                                @if($conversation->is_group)
                                    <span class="group-badge"><i class="fas fa-users"></i></span>
                                @endif
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>

<div class="modal fade" id="newChatModal" tabindex="-1" aria-labelledby="newChatModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #182B48, #2A4570); color: #fff;">
                <h5 class="modal-title" id="newChatModalLabel">
                    <i class="fas fa-plus me-2"></i>
                    محادثة جديدة
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('chat.store') }}" method="POST">
                @csrf
                @if(session('error'))
                    <div class="alert alert-danger mx-3 mt-3">{{ session('error') }}</div>
                @endif
                <div class="modal-body">
                    <div class="mb-3">
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
                            <select id="userSelect" class="form-select" name="user_ids[]" required>
                                <option value="">-- اختر --</option>
                                <optgroup label="الطلاب">
                                    @foreach(\App\Models\User::where('role', 'student')->where('is_active', true)->get() as $student)
                                        <option value="{{ $student->id }}">{{ $student->name }}</option>
                                    @endforeach
                                </optgroup>
                                <optgroup label="المشرفون">
                                    @foreach(\App\Models\User::where('role', 'supervisor')->where('is_active', true)->get() as $supervisor)
                                        <option value="{{ $supervisor->id }}">{{ $supervisor->name }}</option>
                                    @endforeach
                                </optgroup>
                            </select>
                        </div>
                    </div>

                    <div id="groupChatSection" class="d-none">
                        <div class="mb-3">
                            <label class="form-label">اختر المشاركين (3 على الأقل)</label>
                            <select id="groupUsersSelect" class="form-select" name="user_ids[]" multiple required>
                                <optgroup label="الطلاب">
                                    @foreach(\App\Models\User::where('role', 'student')->where('is_active', true)->get() as $student)
                                        <option value="{{ $student->id }}">{{ $student->name }}</option>
                                    @endforeach
                                </optgroup>
                                <optgroup label="المشرفون">
                                    @foreach(\App\Models\User::where('role', 'supervisor')->where('is_active', true)->get() as $supervisor)
                                        <option value="{{ $supervisor->id }}">{{ $supervisor->name }}</option>
                                    @endforeach
                                </optgroup>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>
                        إنشاء
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.conversations-list {
    display: flex;
    flex-direction: column;
}

.conversation-item {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 18px 24px;
    border-bottom: 1px solid #f0f4f8;
    text-decoration: none;
    transition: all 0.3s ease;
}

.conversation-item:last-child {
    border-bottom: none;
}

.conversation-item:hover {
    background: linear-gradient(135deg, rgba(24, 43, 72, 0.02) 0%, rgba(42, 69, 112, 0.04) 100%);
}

.conversation-item.unread {
    background: linear-gradient(135deg, rgba(212, 160, 48, 0.04) 0%, rgba(180, 128, 44, 0.06) 100%);
}

.conversation-item.unread::before {
    content: '';
    position: absolute;
    right: 0;
    top: 0;
    bottom: 0;
    width: 4px;
    background: linear-gradient(180deg, #d4a030, #B4802C);
}

.conversation-avatar {
    width: 52px;
    height: 52px;
    background: linear-gradient(135deg, #182B48, #2A4570);
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #d4a030;
    font-size: 20px;
    flex-shrink: 0;
}

.conversation-info {
    flex: 1;
    min-width: 0;
}

.conversation-name {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 15px;
    font-weight: 700;
    color: #182B48;
    margin-bottom: 4px;
}

.conversation-preview {
    font-size: 13px;
    color: #7f8c8d;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.unread-badge {
    background: linear-gradient(135deg, #e74c3c, #c0392b);
    color: #fff;
    font-size: 11px;
    font-weight: 700;
    padding: 3px 10px;
    border-radius: 20px;
}

.conversation-meta {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 8px;
}

.conversation-time {
    font-size: 11px;
    color: #95a5a6;
}

.group-badge {
    background: rgba(24, 43, 72, 0.1);
    color: #182B48;
    padding: 4px 8px;
    border-radius: 6px;
    font-size: 11px;
}

.chat-type-buttons {
    display: flex;
    gap: 12px;
}

.chat-type-btn {
    flex: 1;
    padding: 14px 20px;
    background: #f0f4f8;
    border: 2px solid transparent;
    border-radius: 12px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
}

.chat-type-btn:hover {
    background: rgba(24, 43, 72, 0.05);
}

.chat-type-btn.active {
    background: linear-gradient(135deg, rgba(24, 43, 72, 0.1), rgba(42, 69, 112, 0.1));
    border-color: #182B48;
    color: #182B48;
}

.user-search-container {
    position: relative;
}

.search-results {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.15);
    max-height: 250px;
    overflow-y: auto;
    z-index: 100;
}

.search-result-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 16px;
    cursor: pointer;
    transition: all 0.2s;
}

.search-result-item:hover {
    background: #f0f4f8;
}

.selected-users {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.selected-user-tag {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 14px;
    background: linear-gradient(135deg, rgba(24, 43, 72, 0.1), rgba(42, 69, 112, 0.1));
    border-radius: 20px;
    font-size: 13px;
    font-weight: 600;
    color: #182B48;
}

.selected-user-tag .remove-btn {
    width: 18px;
    height: 18px;
    background: rgba(192, 57, 43, 0.2);
    color: #c0392b;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 10px;
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

    chatTypeBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            chatTypeBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');

            const type = this.dataset.type;
            chatTypeInput.value = type === 'group' ? 1 : 0;

            if (type === 'group') {
                groupNameSection.classList.remove('d-none');
                directChatSection.classList.add('d-none');
                groupChatSection.classList.remove('d-none');
                userSelect.removeAttribute('required');
                groupUsersSelect.setAttribute('required', 'required');
            } else {
                groupNameSection.classList.add('d-none');
                directChatSection.classList.remove('d-none');
                groupChatSection.classList.add('d-none');
                userSelect.setAttribute('required', 'required');
                groupUsersSelect.removeAttribute('required');
            }
        });
    });
});
</script>
@endpush