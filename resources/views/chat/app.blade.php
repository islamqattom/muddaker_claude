@extends('layouts.app')

@php
    $role = auth()->user()->role;
    $roleSidebar = $role === 'admin' ? 'admin.partials.sidebar' : ($role === 'supervisor' ? 'supervisor.partials.sidebar' : ($role === 'student' ? 'student.partials.sidebar' : 'parent.partials.sidebar'));
    $currentUserId = (int) auth()->id();
    $active = $activeConversation ?? null;
    $otherUser = $active && !$active->is_group ? $active->participants->first(fn($p) => $p->id !== $currentUserId) : null;
    $activeTitle = $active ? $active->displayTitleFor($currentUserId) : '';
@endphp

@section('title', $active ? $activeTitle : 'المحادثات')

@section('sidebar-menu')
    @include($roleSidebar)
@endsection

@section('content')
@php
    $pusherKey = config('broadcasting.connections.pusher.key');
    $pusherCluster = config('broadcasting.connections.pusher.options.cluster');
    $pusherEnabled = config('broadcasting.default') === 'pusher' && !empty($pusherKey);
@endphp
<div class="wa-app {{ $active ? 'has-active' : '' }}" id="waApp" data-current-user-id="{{ $currentUserId }}" data-active-id="{{ $active?->id }}" data-pusher="{{ $pusherEnabled ? '1' : '0' }}">

    {{-- ============== SIDEBAR ============== --}}
    <aside class="wa-sidebar" id="waSidebar">
        <header class="wa-sidebar-header">
            <div class="wa-sidebar-title">
                <i class="fas fa-comments"></i>
                <span>المحادثات</span>
            </div>
            <button class="wa-new-btn" id="waNewChatBtn" title="محادثة جديدة">
                <i class="fas fa-plus"></i>
            </button>
        </header>

        <div class="wa-search">
            <i class="fas fa-search"></i>
            <input type="text" id="waSearchInput" placeholder="ابحث في المحادثات...">
        </div>

        <div class="wa-conv-list" id="waConvList">
            @forelse($conversations as $c)
                @php
                    $lastMsg = $c->lastMessage;
                    $preview = $lastMsg
                        ? (\Illuminate\Support\Str::limit($lastMsg->content ?: '📎 مرفق', 50))
                        : 'ابدأ المحادثة بإرسال رسالة';
                    $isActive = $active && $active->id === $c->id;
                @endphp
                <a href="{{ route('chat.index', ['conversation' => $c->id]) }}"
                   class="wa-conv {{ $isActive ? 'active' : '' }} {{ ($c->unread_count ?? 0) > 0 ? 'unread' : '' }}"
                   data-id="{{ $c->id }}"
                   data-title="{{ $c->title }}"
                   data-search="{{ $c->title }} {{ $preview }}">
                    <div class="wa-avatar {{ $c->is_group ? 'is-group' : '' }}">
                        @if($c->avatar)
                            <img src="{{ $c->avatar }}" alt="">
                        @elseif($c->is_group)
                            <i class="fas fa-users"></i>
                        @else
                            <i class="fas fa-user"></i>
                        @endif
                    </div>
                    <div class="wa-conv-body">
                        <div class="wa-conv-top">
                            <div class="wa-conv-name">{{ $c->title }}</div>
                            <div class="wa-conv-time">
                                @if($lastMsg)
                                    {{ $lastMsg->created_at->diffForHumans(null, true) }}
                                @endif
                            </div>
                        </div>
                        <div class="wa-conv-bottom">
                            <div class="wa-conv-preview">{{ $preview }}</div>
                            @if(($c->unread_count ?? 0) > 0)
                                <span class="wa-unread-badge">{{ $c->unread_count }}</span>
                            @endif
                        </div>
                    </div>
                </a>
            @empty
                <div class="wa-conv-empty">
                    <div class="wa-conv-empty-icon"><i class="fas fa-comments"></i></div>
                    <p>لا توجد محادثات بعد</p>
                </div>
            @endforelse
        </div>
    </aside>

    {{-- ============== MAIN PANE ============== --}}
    <main class="wa-main" id="waMain">
        @if($active)
            <header class="wa-main-header">
                <button class="wa-back-btn" id="waBackBtn" aria-label="رجوع">
                    <i class="fas fa-arrow-right"></i>
                </button>
                <div class="wa-main-avatar {{ $active->is_group ? 'is-group' : '' }}">
                    @if($active->is_group)
                        <i class="fas fa-users"></i>
                    @else
                        <i class="fas fa-user"></i>
                    @endif
                </div>
                <div class="wa-main-info">
                    <div class="wa-main-name" id="waMainName">{{ $activeTitle }}</div>
                    <div class="wa-main-status" id="waMainStatus">
                        @if($active->is_group)
                            {{ $active->participants->count() }} مشارك
                        @else
                            <span class="wa-online-dot d-none" id="waOnlineDot"></span>
                            <span id="waMainStatusText">اضغط لعرض التفاصيل</span>
                        @endif
                    </div>
                </div>
                @if($active->is_group)
                    <button class="wa-icon-btn" data-bs-toggle="modal" data-bs-target="#addParticipantModal" title="إضافة مشارك">
                        <i class="fas fa-user-plus"></i>
                    </button>
                @endif
            </header>

            <section class="wa-messages" id="waMessages" data-conversation-id="{{ $active->id }}">
                @forelse($active->messages as $message)
                    @php
                        $isSent = (int) $message->user_id === $currentUserId;
                        $bubbleClass = $isSent ? 'sent' : 'received';
                    @endphp
                    @include('chat.partials.message', [
                        'message' => $message,
                        'isSent' => $isSent,
                        'bubbleClass' => $bubbleClass,
                        'showDateSeparator' => $loop->first || $active->messages[$loop->index - 1]->created_at->toDateString() !== $message->created_at->toDateString(),
                        'previousDate' => $loop->first ? null : $active->messages[$loop->index - 1]->created_at->toDateString(),
                        'currentDate' => $message->created_at->toDateString(),
                        'currentUserId' => $currentUserId,
                    ])
                @empty
                    <div class="wa-empty-chat">
                        <div class="wa-empty-icon"><i class="fas fa-comment-dots"></i></div>
                        <h5>لا توجد رسائل</h5>
                        <p>ابدأ المحادثة بإرسال رسالة</p>
                    </div>
                @endforelse

                <div class="wa-typing-indicator d-none" id="waTypingIndicator">
                    <div class="wa-typing-bubble">
                        <span class="wa-typing-dot"></span>
                        <span class="wa-typing-dot"></span>
                        <span class="wa-typing-dot"></span>
                    </div>
                </div>
            </section>

            <footer class="wa-input-bar">
                <div id="waAttachmentPreview" class="wa-attachment-preview d-none"></div>
                <form id="waMessageForm" class="wa-input-form" enctype="multipart/form-data">
                    @csrf
                    <button type="button" class="wa-icon-btn" id="waAttachBtn" title="إرفاق ملف">
                        <i class="fas fa-paperclip"></i>
                    </button>
                    <input type="file" id="waAttachmentInput" name="attachments[]" multiple
                           accept="image/*,video/*,audio/*,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.csv,.zip,.rar,.7z,.json,.xml"
                           style="display:none">
                    <input type="text" id="waMessageInput" class="wa-input" placeholder="اكتب رسالة..." autocomplete="off">
                    <button type="submit" class="wa-send-btn" id="waSendBtn" title="إرسال">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </form>
            </footer>
        @else
            <div class="wa-empty-state">
                <div class="wa-empty-illustration">
                    <i class="fas fa-comments"></i>
                </div>
                <h3>مرحباً بك في المحادثات</h3>
                <p>اختر محادثة من القائمة أو ابدأ محادثة جديدة للتواصل مع الطلاب والمشرفين</p>
                <button class="btn btn-primary" id="waEmptyNewChatBtn">
                    <i class="fas fa-plus"></i> ابدأ محادثة جديدة
                </button>
            </div>
        @endif
    </main>
</div>

@if($active && $active->is_group)
<div class="modal fade" id="addParticipantModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #182B48, #2A4570); color: #fff;">
                <h5 class="modal-title"><i class="fas fa-user-plus me-2"></i> إضافة مشارك</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('chat.addParticipant', $active) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">ابحث عن مستخدم</label>
                        <input type="text" id="waParticipantSearch" class="form-control" placeholder="اكتب اسم المستخدم..." autocomplete="off">
                        <div id="waParticipantResults" class="search-results d-none mt-2"></div>
                    </div>
                    <input type="hidden" name="user_id" id="waSelectedParticipantId" value="">
                    <div id="waSelectedParticipantDisplay" class="selected-users mt-3 d-none">
                        <div class="selected-user-tag">
                            <i class="fas fa-user"></i>
                            <span id="waSelectedParticipantName"></span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-plus me-2"></i> إضافة</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<div class="modal fade" id="newChatModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-plus me-2"></i> محادثة جديدة</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('chat.store') }}" method="POST" id="newChatForm">
                @csrf
                <div class="modal-body">
                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif
                    <div class="mb-4">
                        <label class="form-label">نوع المحادثة</label>
                        <div class="wa-type-buttons">
                            <button type="button" class="wa-type-btn active" data-type="direct">
                                <i class="fas fa-user"></i> محادثة خاصة
                            </button>
                            <button type="button" class="wa-type-btn" data-type="group">
                                <i class="fas fa-users"></i> مجموعة
                            </button>
                        </div>
                        <input type="hidden" name="is_group" id="waChatTypeInput" value="0">
                    </div>
                    <div id="waGroupNameSection" class="mb-3 d-none">
                        <label class="form-label">اسم المجموعة</label>
                        <input type="text" name="name" class="form-control" placeholder="أدخل اسم المجموعة">
                    </div>
                    <div id="waDirectChatSection">
                        <label class="form-label">اختر المستخدم</label>
                        <select id="waUserSelect" class="form-select" name="user_ids[]" required>
                            <option value="">-- اختر مستخدم --</option>
                            @foreach(\App\Models\User::whereIn('role', ['student','supervisor'])->where('is_active', true)->where('id', '!=', $currentUserId)->orderBy('name')->get() as $u)
                                <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->role }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div id="waGroupChatSection" class="d-none">
                        <label class="form-label">اختر المشاركين (3 على الأقل)</label>
                        <select id="waGroupUsersSelect" class="form-select" name="user_ids[]" multiple required>
                            @foreach(\App\Models\User::whereIn('role', ['student','supervisor'])->where('is_active', true)->where('id', '!=', $currentUserId)->orderBy('name')->get() as $u)
                                <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->role }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-plus me-2"></i> إنشاء</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
/* ============ WhatsApp-style layout ============ */
.wa-app {
    display: flex;
    height: calc(100vh - 80px);
    height: calc(100dvh - 80px);
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.06);
    overflow: hidden;
    border: 1px solid #e9ecef;
    font-family: 'Tajawal', sans-serif;
}

/* ----- Sidebar ----- */
.wa-sidebar {
    width: 360px;
    flex-shrink: 0;
    display: flex;
    flex-direction: column;
    background: #f7f8fa;
    border-left: 1px solid #e9ecef;
}
.wa-sidebar-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 14px 18px;
    background: linear-gradient(135deg, #182B48, #2A4570);
    color: #fff;
}
.wa-sidebar-title {
    display: flex; align-items: center; gap: 10px;
    font-weight: 700; font-size: 16px;
}
.wa-sidebar-title i { color: #d4a030; font-size: 18px; }
.wa-new-btn {
    width: 38px; height: 38px; border-radius: 50%;
    background: rgba(255,255,255,0.18); color: #fff;
    border: none; cursor: pointer; display: flex; align-items: center; justify-content: center;
    transition: all 0.2s;
}
.wa-new-btn:hover { background: rgba(255,255,255,0.3); transform: scale(1.05); }

.wa-search {
    padding: 12px 14px;
    background: #f7f8fa;
    border-bottom: 1px solid #e9ecef;
    position: relative;
}
.wa-search i {
    position: absolute; right: 24px; top: 50%; transform: translateY(-50%);
    color: #95a5a6; font-size: 13px;
}
.wa-search input {
    width: 100%; padding: 9px 36px 9px 14px;
    border: none; border-radius: 24px; background: #e9ecef;
    font-family: inherit; font-size: 14px; color: #2c3e50;
    transition: background 0.2s;
}
.wa-search input:focus { outline: none; background: #fff; box-shadow: 0 0 0 2px rgba(24,43,72,0.1); }

.wa-conv-list { flex: 1; overflow-y: auto; }
.wa-conv {
    display: flex; align-items: center; gap: 12px;
    padding: 12px 16px;
    text-decoration: none; color: #2c3e50;
    border-bottom: 1px solid #f0f4f8;
    transition: background 0.15s;
    cursor: pointer;
}
.wa-conv:hover { background: #eef1f5; }
.wa-conv.active { background: #e9f0f7; }
.wa-conv.unread .wa-conv-name { font-weight: 700; color: #182B48; }
.wa-conv.unread .wa-conv-preview { color: #2c3e50; font-weight: 600; }

.wa-avatar {
    width: 46px; height: 46px; border-radius: 50%;
    background: linear-gradient(135deg, #182B48, #2A4570);
    color: #d4a030;
    display: flex; align-items: center; justify-content: center;
    font-size: 18px; flex-shrink: 0; overflow: hidden;
}
.wa-avatar.is-group { background: linear-gradient(135deg, #27ae60, #2ecc71); color: #fff; }
.wa-avatar img { width: 100%; height: 100%; object-fit: cover; }

.wa-conv-body { flex: 1; min-width: 0; }
.wa-conv-top { display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-bottom: 4px; }
.wa-conv-name { font-size: 14px; font-weight: 600; color: #2c3e50; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.wa-conv-time { font-size: 11px; color: #95a5a6; white-space: nowrap; flex-shrink: 0; }
.wa-conv-bottom { display: flex; align-items: center; justify-content: space-between; gap: 8px; }
.wa-conv-preview { font-size: 13px; color: #7f8c8d; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; flex: 1; }
.wa-unread-badge {
    background: #27ae60; color: #fff; font-size: 11px; font-weight: 700;
    min-width: 20px; height: 20px; border-radius: 10px; padding: 0 6px;
    display: inline-flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}

.wa-conv-empty { padding: 60px 20px; text-align: center; color: #95a5a6; }
.wa-conv-empty-icon { font-size: 56px; color: #d4d4d4; margin-bottom: 12px; }

/* ----- Main pane ----- */
.wa-main {
    flex: 1; min-width: 0; display: flex; flex-direction: column;
    background: #efeae2; /* WhatsApp warm beige */
    position: relative;
}
.wa-main::before {
    content: '';
    position: absolute; inset: 0;
    background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='60' height='60' viewBox='0 0 60 60'><g fill='%23d4cfc4' fill-opacity='0.18'><circle cx='30' cy='30' r='2'/></g></svg>");
    opacity: 0.55;
    pointer-events: none;
}

.wa-main-header {
    display: flex; align-items: center; gap: 12px;
    padding: 12px 16px;
    background: linear-gradient(135deg, #182B48, #2A4570);
    color: #fff;
    z-index: 2;
    position: relative;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}
.wa-back-btn { display: none; }
.wa-main-avatar {
    width: 42px; height: 42px; border-radius: 50%;
    background: rgba(255,255,255,0.18); color: #d4a030;
    display: flex; align-items: center; justify-content: center;
    font-size: 17px; flex-shrink: 0;
}
.wa-main-avatar.is-group { background: rgba(46,204,113,0.4); color: #fff; }
.wa-main-info { flex: 1; min-width: 0; }
.wa-main-name { font-size: 15px; font-weight: 700; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.wa-main-status { font-size: 12px; opacity: 0.85; display: flex; align-items: center; gap: 6px; }
.wa-online-dot {
    width: 9px; height: 9px; border-radius: 50%;
    background: #2ecc71; box-shadow: 0 0 0 2px rgba(46,204,113,0.3);
    display: inline-block;
}
.wa-icon-btn {
    width: 38px; height: 38px; border-radius: 50%;
    background: rgba(255,255,255,0.12); color: #fff;
    border: none; cursor: pointer; display: flex; align-items: center; justify-content: center;
    transition: all 0.2s;
}
.wa-icon-btn:hover { background: rgba(255,255,255,0.22); }

/* ----- Messages area ----- */
.wa-messages {
    flex: 1; overflow-y: auto; overflow-x: hidden;
    padding: 16px 8% 12px;
    display: flex; flex-direction: column; gap: 3px;
    z-index: 1; min-height: 0;
}
.wa-messages::-webkit-scrollbar { width: 6px; }
.wa-messages::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.2); border-radius: 3px; }

/* Date separator */
.wa-date-sep {
    display: flex; justify-content: center; margin: 12px 0 8px;
    position: sticky; top: 0; z-index: 1;
}
.wa-date-sep span {
    background: rgba(255,255,255,0.92); color: #54656f;
    font-size: 12px; font-weight: 600;
    padding: 5px 12px; border-radius: 8px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.08);
}

/* Message bubble */
.wa-msg { display: flex; max-width: 75%; }
.wa-msg.sent { align-self: flex-end; }
.wa-msg.received { align-self: flex-start; }

.wa-bubble {
    position: relative;
    padding: 7px 12px 6px;
    border-radius: 8px;
    box-shadow: 0 1px 0.5px rgba(0,0,0,0.13);
    min-width: 80px;
    word-wrap: break-word; overflow-wrap: break-word;
}
.wa-msg.sent .wa-bubble {
    background: #d9fdd3;
    color: #111b21;
    border-top-right-radius: 0;
}
.wa-msg.received .wa-bubble {
    background: #fff;
    color: #111b21;
    border-top-left-radius: 0;
}
.wa-bubble-sender {
    font-size: 12.5px; font-weight: 700; display: block; margin-bottom: 2px;
    color: #b4802c;
}
.wa-bubble-text { font-size: 14.2px; line-height: 1.45; white-space: pre-wrap; word-break: break-word; }
.wa-bubble-meta {
    float: left; display: inline-flex; align-items: center; gap: 3px;
    margin: 4px -4px -2px 8px;
    font-size: 11px; color: #667781; line-height: 1;
}
.wa-msg.sent .wa-bubble-meta { color: #667781; }
.wa-tick { font-size: 13px; }
.wa-tick.sent-only { color: #667781; }
.wa-tick.delivered { color: #667781; }
.wa-tick.read { color: #34b7f1; }

/* Cluster: remove top rounding when consecutive same-sender */
.wa-msg.sent.followed .wa-bubble { border-top-right-radius: 0; }
.wa-msg.sent.leading .wa-bubble { border-top-right-radius: 8px; }
.wa-msg.received.followed .wa-bubble { border-top-left-radius: 0; }
.wa-msg.received.leading .wa-bubble { border-top-left-radius: 8px; }
.wa-msg.sent.leading + .wa-msg.sent:not(.leading) { margin-top: 0; }
.wa-msg.received.leading + .wa-msg.received:not(.leading) { margin-top: 0; }

/* Attachments */
.wa-attach { display: flex; flex-direction: column; gap: 4px; margin: 4px 0 2px; }
.wa-attach-img {
    display: block; max-width: 320px; max-height: 320px;
    border-radius: 6px; overflow: hidden; position: relative; cursor: pointer;
    background: rgba(0,0,0,0.05); line-height: 0;
}
.wa-attach-img img { width: 100%; height: auto; display: block; border-radius: 6px; }
.wa-attach-img:hover img { transform: scale(1.01); }
.wa-attach-video, .wa-attach-audio, .wa-attach-file {
    display: flex; align-items: center; gap: 10px;
    padding: 8px 10px; background: rgba(0,0,0,0.05); border-radius: 8px;
    max-width: 340px; text-decoration: none; color: inherit;
}
.wa-msg.sent .wa-attach-video, .wa-msg.sent .wa-attach-audio, .wa-msg.sent .wa-attach-file { background: rgba(0,0,0,0.08); }
.wa-attach-icon {
    width: 36px; height: 36px; border-radius: 8px;
    background: linear-gradient(135deg, #182B48, #2A4570); color: #d4a030;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.wa-attach-info { flex: 1; min-width: 0; }
.wa-attach-name { font-size: 13px; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.wa-attach-size { font-size: 11px; opacity: 0.7; }
.wa-attach-dl {
    width: 30px; height: 30px; border-radius: 6px;
    background: rgba(0,0,0,0.08); color: inherit;
    display: flex; align-items: center; justify-content: center;
    text-decoration: none; flex-shrink: 0;
}

/* Typing indicator */
.wa-typing-indicator { align-self: flex-start; max-width: 75%; margin-top: 4px; }
.wa-typing-bubble {
    background: #fff; padding: 10px 14px; border-radius: 8px; border-top-left-radius: 0;
    box-shadow: 0 1px 0.5px rgba(0,0,0,0.13);
    display: inline-flex; align-items: center; gap: 4px;
}
.wa-typing-dot {
    width: 7px; height: 7px; border-radius: 50%; background: #90a4ae;
    animation: wa-typing 1.4s infinite ease-in-out;
}
.wa-typing-dot:nth-child(2) { animation-delay: 0.2s; }
.wa-typing-dot:nth-child(3) { animation-delay: 0.4s; }
@keyframes wa-typing {
    0%, 60%, 100% { transform: translateY(0); opacity: 0.4; }
    30% { transform: translateY(-4px); opacity: 1; }
}

/* Empty chat */
.wa-empty-chat {
    margin: auto; text-align: center; color: #54656f;
    background: rgba(255,255,255,0.9); padding: 24px 32px; border-radius: 12px;
}
.wa-empty-icon { font-size: 48px; color: #d4a030; margin-bottom: 12px; }

/* Input bar */
.wa-input-bar {
    background: #f0f2f5; padding: 8px 14px; display: flex; flex-direction: column; gap: 8px;
    z-index: 2; position: relative;
}
.wa-attachment-preview { display: flex; flex-wrap: wrap; gap: 6px; }
.wa-attachment-preview.d-none { display: none !important; }
.wa-attachment-preview .api-item {
    display: flex; align-items: center; gap: 8px;
    background: #fff; padding: 6px 10px; border-radius: 16px; font-size: 12px;
    max-width: 220px;
}
.wa-attachment-preview .api-thumb {
    width: 28px; height: 28px; border-radius: 6px; background: #d4a030; color: #fff;
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
    overflow: hidden;
}
.wa-attachment-preview .api-thumb img { width: 100%; height: 100%; object-fit: cover; }
.wa-attachment-preview .api-name { font-weight: 600; color: #182B48; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.wa-attachment-preview .api-remove {
    width: 20px; height: 20px; border-radius: 50%; border: none;
    background: rgba(192,57,43,0.15); color: #c0392b; cursor: pointer;
    display: flex; align-items: center; justify-content: center; font-size: 10px;
}

.wa-input-form { display: flex; align-items: center; gap: 8px; }
.wa-input {
    flex: 1; padding: 10px 14px; border: none; border-radius: 24px;
    background: #fff; font-family: inherit; font-size: 14px; color: #111b21;
    box-shadow: 0 1px 2px rgba(0,0,0,0.05);
}
.wa-input:focus { outline: none; }
.wa-send-btn {
    width: 44px; height: 44px; border-radius: 50%; border: none;
    background: linear-gradient(135deg, #182B48, #2A4570);
    color: #fff; cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    transition: all 0.2s;
}
.wa-send-btn:hover { transform: scale(1.05); }
.wa-send-btn:disabled { opacity: 0.5; cursor: not-allowed; transform: none; }
.wa-send-btn i { transform: rotate(180deg); } /* RTL: paper-plane points right; rotate to point left */

/* Empty state (no conversation selected) */
.wa-empty-state {
    flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center;
    padding: 40px; text-align: center; color: #54656f; z-index: 2; position: relative;
}
.wa-empty-illustration {
    width: 140px; height: 140px; border-radius: 50%;
    background: linear-gradient(135deg, rgba(24,43,72,0.1), rgba(212,160,48,0.1));
    display: flex; align-items: center; justify-content: center;
    font-size: 60px; color: #d4a030;
    margin-bottom: 24px;
}
.wa-empty-state h3 { color: #182B48; font-weight: 700; margin-bottom: 8px; }
.wa-empty-state p { max-width: 380px; line-height: 1.6; margin-bottom: 20px; }

/* Type buttons (in new chat modal) */
.wa-type-buttons { display: flex; gap: 10px; }
.wa-type-btn {
    flex: 1; padding: 12px; border: 2px solid #e9ecef; border-radius: 10px;
    background: #fff; cursor: pointer; font-weight: 600;
    transition: all 0.2s;
}
.wa-type-btn.active { border-color: #d4a030; background: rgba(212,160,48,0.1); color: #182B48; }

/* Search results */
.search-results { background: #fff; border: 1px solid #e9ecef; border-radius: 10px; max-height: 240px; overflow-y: auto; }
.search-result-item {
    display: flex; align-items: center; gap: 10px; padding: 10px 12px;
    cursor: pointer; transition: background 0.15s;
}
.search-result-item:hover { background: #f7f8fa; }
.selected-users { display: flex; flex-wrap: wrap; gap: 8px; }
.selected-user-tag {
    display: inline-flex; align-items: center; gap: 6px;
    background: rgba(24,43,72,0.08); color: #182B48;
    padding: 6px 12px; border-radius: 16px; font-size: 13px; font-weight: 600;
}

/* ============ Mobile ============ */
@media (max-width: 768px) {
    .wa-app { border-radius: 0; height: calc(100vh - 60px); height: calc(100dvh - 60px); }
    .wa-sidebar { width: 100%; }
    .wa-main { display: none; }
    .wa-app.has-active .wa-sidebar { display: none; }
    .wa-app.has-active .wa-main { display: flex; }
    .wa-back-btn { display: flex; }
    .wa-messages { padding: 12px 4% 8px; }
    .wa-msg { max-width: 88%; }
}
</style>
@endpush

@push('scripts')
@if($pusherEnabled)
<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
@endif
<script>
document.addEventListener('DOMContentLoaded', function() {
    const app = document.getElementById('waApp');
    const currentUserId = parseInt(app.dataset.currentUserId);
    const activeId = parseInt(app.dataset.activeId || '0');
    const pusherEnabled = app.dataset.pusher === '1';

    // ============================================================
    // Sidebar search
    // ============================================================
    const searchInput = document.getElementById('waSearchInput');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const q = this.value.trim().toLowerCase();
            document.querySelectorAll('.wa-conv').forEach(el => {
                const text = (el.dataset.search || '').toLowerCase();
                el.style.display = (!q || text.includes(q)) ? '' : 'none';
            });
        });
    }

    // ============================================================
    // New chat modal
    // ============================================================
    const newChatBtn = document.getElementById('waNewChatBtn');
    const emptyNewChatBtn = document.getElementById('waEmptyNewChatBtn');
    function openNewChat() {
        const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('newChatModal'));
        modal.show();
    }
    if (newChatBtn) newChatBtn.addEventListener('click', openNewChat);
    if (emptyNewChatBtn) emptyNewChatBtn.addEventListener('click', openNewChat);

    document.querySelectorAll('.wa-type-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.wa-type-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            const type = this.dataset.type;
            document.getElementById('waChatTypeInput').value = type === 'group' ? '1' : '0';
            document.getElementById('waGroupNameSection').classList.toggle('d-none', type !== 'group');
            document.getElementById('waDirectChatSection').classList.toggle('d-none', type === 'group');
            document.getElementById('waGroupChatSection').classList.toggle('d-none', type !== 'group');
        });
    });

    // ============================================================
    // Mobile: back button
    // ============================================================
    const backBtn = document.getElementById('waBackBtn');
    if (backBtn) {
        backBtn.addEventListener('click', function() {
            window.location.href = '{{ route('chat.index') }}';
        });
    }

    if (!activeId) return;

    // ============================================================
    // Active conversation
    // ============================================================
    const messagesEl = document.getElementById('waMessages');
    const messageForm = document.getElementById('waMessageForm');
    const messageInput = document.getElementById('waMessageInput');
    const attachBtn = document.getElementById('waAttachBtn');
    const attachmentInput = document.getElementById('waAttachmentInput');
    const attachmentPreview = document.getElementById('waAttachmentPreview');
    const sendBtn = document.getElementById('waSendBtn');
    const typingIndicator = document.getElementById('waTypingIndicator');

    let pendingFiles = [];
    let typingTimeout = null;
    let lastTypingSent = 0;
    let heartbeatTimer = null;
    let typingHideTimer = null;
    let pollTimer = null;
    let lastKnownId = 0;
    let lastMessageInDom = messagesEl.querySelector('.wa-msg:last-of-type');
    if (lastMessageInDom) {
        lastKnownId = parseInt(lastMessageInDom.dataset.messageId) || 0;
    }

    function scrollToBottom() {
        messagesEl.scrollTop = messagesEl.scrollHeight;
    }
    scrollToBottom();

    // ----- Attachments -----
    function rebuildAttachmentPreview() {
        attachmentPreview.innerHTML = '';
        if (pendingFiles.length === 0) {
            attachmentPreview.classList.add('d-none');
            return;
        }
        attachmentPreview.classList.remove('d-none');
        pendingFiles.forEach((file, idx) => {
            const isImage = file.type.startsWith('image/');
            const isVideo = file.type.startsWith('video/');
            const item = document.createElement('div');
            item.className = 'api-item';
            const thumb = document.createElement('div');
            thumb.className = 'api-thumb';
            if (isImage) {
                const img = document.createElement('img');
                img.src = URL.createObjectURL(file);
                thumb.appendChild(img);
            } else if (isVideo) {
                thumb.innerHTML = '<i class="fas fa-video"></i>';
            } else {
                thumb.innerHTML = '<i class="fas fa-file"></i>';
            }
            const name = document.createElement('div');
            name.className = 'api-name';
            name.textContent = file.name;
            const remove = document.createElement('button');
            remove.type = 'button';
            remove.className = 'api-remove';
            remove.innerHTML = '<i class="fas fa-times"></i>';
            remove.addEventListener('click', () => {
                pendingFiles.splice(idx, 1);
                rebuildAttachmentPreview();
            });
            item.appendChild(thumb);
            item.appendChild(name);
            item.appendChild(remove);
            attachmentPreview.appendChild(item);
        });
    }

    if (attachBtn) {
        attachBtn.addEventListener('click', () => attachmentInput.click());
    }
    if (attachmentInput) {
        attachmentInput.addEventListener('change', function() {
            for (const f of Array.from(this.files || [])) {
                if (pendingFiles.length >= 10) break;
                pendingFiles.push(f);
            }
            this.value = '';
            rebuildAttachmentPreview();
        });
    }

    // ----- Typing indicator broadcast -----
    function sendTypingEvent() {
        const now = Date.now();
        if (now - lastTypingSent < 2000) return;
        lastTypingSent = now;
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
        fetch(`/chat/${activeId}/typing`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
        }).catch(() => {});
    }

    if (messageInput) {
        messageInput.addEventListener('input', function() {
            if (this.value.trim() === '' && pendingFiles.length === 0) return;
            sendTypingEvent();
        });
    }

    // ----- Send message -----
    if (messageForm) {
        messageForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const content = messageInput.value.trim();
            if (!content && pendingFiles.length === 0) return;

            sendBtn.disabled = true;
            const originalIcon = sendBtn.innerHTML;
            sendBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

            const formData = new FormData();
            formData.append('content', content);
            pendingFiles.forEach(f => formData.append('attachments[]', f));

            const csrf = document.querySelector('meta[name="csrf-token"]')?.content
                || document.querySelector('input[name="_token"]')?.value;

            fetch(`/chat/${activeId}/message`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: formData
            })
            .then(async r => {
                const text = await r.text();
                let data;
                try { data = text ? JSON.parse(text) : {}; }
                catch (e) { throw new Error('استجابة غير متوقعة من الخادم (HTTP ' + r.status + ')'); }
                if (!r.ok) {
                    throw new Error(data.error || (data.errors ? Object.values(data.errors).flat().join('\n') : null) || data.message || ('خطأ ' + r.status));
                }
                return data;
            })
            .then(data => {
                if (data.success) {
                    messageInput.value = '';
                    pendingFiles = [];
                    rebuildAttachmentPreview();
                    try { addMessageToChat(data.message, true); } catch (e) { console.error('render error', e); }
                } else {
                    alert(data.error || 'فشل إرسال الرسالة');
                }
            })
            .catch(err => alert(err.message || 'حدث خطأ أثناء إرسال الرسالة'))
            .finally(() => {
                sendBtn.disabled = false;
                sendBtn.innerHTML = originalIcon;
            });
        });
    }

    // ----- Helpers for rendering -----
    function escapeHtml(s) {
        if (s == null) return '';
        return String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    }
    function formatTime12(date) {
        const d = (date instanceof Date) ? date : new Date(date);
        let h = d.getHours();
        const m = String(d.getMinutes()).padStart(2, '0');
        const period = h >= 12 ? 'م' : 'ص';
        h = h % 12; if (h === 0) h = 12;
        return h + ':' + m + ' ' + period;
    }
    function humanSize(bytes) {
        if (!bytes) return '0 B';
        const units = ['B','KB','MB','GB'];
        const i = Math.min(Math.floor(Math.log(bytes) / Math.log(1024)), units.length - 1);
        return (bytes / Math.pow(1024, i)).toFixed(1) + ' ' + units[i];
    }
    function dateLabel(date) {
        const d = (date instanceof Date) ? date : new Date(date);
        const today = new Date();
        const yesterday = new Date(); yesterday.setDate(today.getDate() - 1);
        const sameDay = (a, b) => a.toDateString() === b.toDateString();
        if (sameDay(d, today)) return 'اليوم';
        if (sameDay(d, yesterday)) return 'أمس';
        const days = (today - d) / (1000*60*60*24);
        if (days < 7) {
            return d.toLocaleDateString('ar', { weekday: 'long' });
        }
        return d.toLocaleDateString('ar', { year: 'numeric', month: 'long', day: 'numeric' });
    }

    function renderAttachments(attachments) {
        if (!attachments || !attachments.length) return '';
        let html = '<div class="wa-attach">';
        const messageId = 'live-' + Date.now();
        for (const a of attachments) {
            const url = a.url;
            const downloadUrl = a.download_url;
            if (a.is_image) {
                html += `<a href="${url}" data-lightbox="${messageId}" data-title="${escapeHtml(a.original_name)}" class="wa-attach-img" title="${escapeHtml(a.original_name)}">
                    <img src="${url}" alt="${escapeHtml(a.original_name)}" loading="lazy">
                </a>`;
            } else if (a.is_video) {
                html += `<div class="wa-attach-video">
                    <div class="wa-attach-icon"><i class="fas fa-video"></i></div>
                    <div class="wa-attach-info">
                        <div class="wa-attach-name">${escapeHtml(a.original_name)}</div>
                        <div class="wa-attach-size">${a.human_size || humanSize(a.size)}</div>
                    </div>
                    <a href="${downloadUrl}" class="wa-attach-dl" target="_blank" title="تحميل"><i class="fas fa-download"></i></a>
                </div>`;
            } else if (a.is_audio) {
                html += `<div class="wa-attach-audio">
                    <div class="wa-attach-icon"><i class="fas fa-music"></i></div>
                    <div class="wa-attach-info">
                        <div class="wa-attach-name">${escapeHtml(a.original_name)}</div>
                        <div class="wa-attach-size">${a.human_size || humanSize(a.size)}</div>
                    </div>
                    <a href="${downloadUrl}" class="wa-attach-dl" target="_blank" title="تحميل"><i class="fas fa-download"></i></a>
                </div>`;
            } else {
                html += `<a href="${downloadUrl}" class="wa-attach-file" target="_blank">
                    <div class="wa-attach-icon"><i class="fas ${a.icon || 'fa-file'}"></i></div>
                    <div class="wa-attach-info">
                        <div class="wa-attach-name">${escapeHtml(a.original_name)}</div>
                        <div class="wa-attach-size">${a.human_size || humanSize(a.size)}</div>
                    </div>
                    <div class="wa-attach-dl"><i class="fas fa-download"></i></div>
                </a>`;
            }
        }
        html += '</div>';
        return html;
    }

    function renderTicks(state) {
        // state: 'sent' (1 grey), 'delivered' (2 grey), 'read' (2 blue)
        if (state === 'read') {
            return '<i class="fas fa-check-double wa-tick read"></i>';
        }
        if (state === 'delivered') {
            return '<i class="fas fa-check-double wa-tick delivered"></i>';
        }
        return '<i class="fas fa-check wa-tick sent-only"></i>';
    }

    function makeMessageHtml(data, opts) {
        const isSent = data.user_id === currentUserId;
        const senderName = data.user ? data.user.name : '';
        const attachments = (data.attachments || []).map(a => Object.assign({}, a, {
            is_image: !!a.is_image,
            is_video: !!a.is_video,
            is_audio: !!a.is_audio,
        }));
        const attachHtml = renderAttachments(attachments);
        const contentHtml = data.content ? `<div class="wa-bubble-text">${escapeHtml(data.content)}</div>` : '';
        const timeStr = formatTime12(new Date(data.created_at));
        const hasAttach = attachments.length > 0;
        const cls = ['wa-msg', isSent ? 'sent' : 'received'];
        if (opts && opts.leading) cls.push('leading');
        else cls.push('followed');
        let meta = `<span class="wa-bubble-meta">${timeStr}`;
        if (isSent) {
            meta += ' ' + renderTicks(opts && opts.readState ? opts.readState : 'sent');
        }
        if (hasAttach && !isSent) {
            meta += ' <i class="fas fa-paperclip" style="font-size:10px;opacity:0.6;"></i>';
        }
        meta += '</span>';
        let senderHtml = '';
        if (!isSent && opts && opts.showSender) {
            senderHtml = `<span class="wa-bubble-sender">${escapeHtml(senderName)}</span>`;
        }
        return `<div class="${cls.join(' ')}" data-message-id="${data.id}">
            <div class="wa-bubble">
                ${senderHtml}
                ${attachHtml}
                ${contentHtml}
                ${meta}
            </div>
        </div>`;
    }

    function prependDateSeparatorIfNeeded(date, beforeEl) {
        // The previous selector used `:last-of-type` which silently failed
        // when a sibling <div> (the typing indicator) came after the
        // messages, because `:last-of-type` matches the last *div*, not
        // the last `.wa-msg`. The result was `lastDate === null`, so a
        // fresh "Today" separator was added on every message. Look for
        // the last `.wa-msg` by iterating from the end instead, falling
        // back to the last `.wa-date-sep` if there are no messages yet.
        const newDate = new Date(date).toDateString();
        let lastDate = null;
        for (let i = messagesEl.children.length - 1; i >= 0; i--) {
            const child = messagesEl.children[i];
            if (child.classList.contains('wa-msg') || child.classList.contains('wa-date-sep')) {
                lastDate = child.dataset.date || null;
                break;
            }
        }
        if (lastDate === newDate) return;
        const sep = document.createElement('div');
        sep.className = 'wa-date-sep';
        sep.dataset.date = newDate;
        sep.innerHTML = `<span>${dateLabel(date)}</span>`;
        if (beforeEl) {
            messagesEl.insertBefore(sep, beforeEl);
        } else {
            messagesEl.appendChild(sep);
        }
    }

    function addMessageToChat(data, isLocal) {
        // Skip messages from other conversations
        if (data.conversation_id && data.conversation_id !== activeId) {
            updateSidebarPreview(data);
            return;
        }
        if (messagesEl.querySelector(`[data-message-id="${data.id}"]`)) return;

        const lastMsg = messagesEl.querySelector('.wa-msg:last-of-type');
        const isSent = data.user_id === currentUserId;
        const leading = !lastMsg || !lastMsg.classList.contains(isSent ? 'sent' : 'received');
        const showSender = !isSent && leading;

        // Date separator
        prependDateSeparatorIfNeeded(data.created_at, null);

        const html = makeMessageHtml(data, { leading, showSender, readState: isSent ? (isLocal ? 'sent' : 'delivered') : null });
        messagesEl.insertAdjacentHTML('beforeend', html);

        lastKnownId = Math.max(lastKnownId, parseInt(data.id) || 0);

        // Mark previous sent bubble as "delivered" if it was only "sent" (best effort)
        // (we don't know who else is in the conversation client-side; skip)

        scrollToBottom();
        if (isLocal) {
            // after a successful send, mark as read
            markAsRead();
        } else {
            // From others → also mark as read if the chat is open
            markAsRead();
        }

        // Re-render this sent message with current read state (delivered)
        if (isSent && !isLocal) {
            // The recipient has just received it; from server's perspective, only we
            // can determine read state once we have the data. Refresh after a moment.
        }
    }

    function updateSidebarPreview(data) {
        if (!data.conversation) return;
        const convLink = document.querySelector(`.wa-conv[data-id="${data.conversation_id}"]`);
        if (!convLink) return;
        const preview = convLink.querySelector('.wa-conv-preview');
        if (preview) {
            preview.textContent = data.content
                ? (data.content.length > 50 ? data.content.slice(0,50) + '…' : data.content)
                : '📎 مرفق';
        }
        // Bump unread badge
        let badge = convLink.querySelector('.wa-unread-badge');
        if (!badge) {
            badge = document.createElement('span');
            badge.className = 'wa-unread-badge';
            badge.textContent = '1';
            convLink.querySelector('.wa-conv-bottom').appendChild(badge);
        } else {
            badge.textContent = (parseInt(badge.textContent) || 0) + 1;
        }
        convLink.classList.add('unread');
        // Move to top
        const list = document.getElementById('waConvList');
        if (list && list.firstChild !== convLink) {
            list.insertBefore(convLink, list.firstChild);
        }
    }

    function markAsRead() {
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
        fetch(`/chat/${activeId}/read`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
        }).catch(() => {});
    }
    markAsRead();

    // ----- Heartbeat for last_seen -----
    function heartbeat() {
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
        fetch(`/chat/${activeId}/heartbeat`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
        })
        .then(r => r.ok ? r.json() : null)
        .then(data => {
            if (data && data.presence) updatePresence(data.presence);
        })
        .catch(() => {});
    }
    heartbeat();
    heartbeatTimer = setInterval(heartbeat, 15000);

    // ----- Polling fallback for new messages (used when Pusher isn't
    // configured, but always-on as a safety net).
    function pollNewMessages() {
        if (document.visibilityState === 'hidden') return;
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
        fetch(`/chat/${activeId}/messages?since_id=${lastKnownId}`, {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf }
        })
        .then(r => r.ok ? r.json() : null)
        .then(data => {
            if (!data || !data.messages) return;
            data.messages.forEach(m => {
                addMessageToChat(m, false);
                lastKnownId = Math.max(lastKnownId, parseInt(m.id) || 0);
            });
        })
        .catch(() => {});
    }
    pollNewMessages();
    pollTimer = setInterval(pollNewMessages, 3000);

    // Re-poll immediately when the tab becomes visible again
    document.addEventListener('visibilitychange', function() {
        if (document.visibilityState === 'visible') pollNewMessages();
    });

    function updatePresence(presence) {
        if (!Array.isArray(presence)) return;
        presence.forEach(p => {
            if (p.id === currentUserId) return;
            // For 1-on-1 chats, update header status
            const statusEl = document.getElementById('waMainStatusText');
            const dotEl = document.getElementById('waOnlineDot');
            if (statusEl && dotEl) {
                if (p.is_online) {
                    statusEl.textContent = 'متصل';
                    dotEl.classList.remove('d-none');
                } else if (p.last_seen) {
                    const seen = new Date(p.last_seen);
                    const minutesAgo = Math.floor((Date.now() - seen.getTime()) / 60000);
                    if (minutesAgo < 1) statusEl.textContent = 'آخر ظهور قبل لحظات';
                    else if (minutesAgo < 60) statusEl.textContent = 'آخر ظهور قبل ' + minutesAgo + ' دقيقة';
                    else if (minutesAgo < 1440) statusEl.textContent = 'آخر ظهور قبل ' + Math.floor(minutesAgo/60) + ' ساعة';
                    else statusEl.textContent = 'آخر ظهور ' + seen.toLocaleDateString('ar');
                    dotEl.classList.add('d-none');
                } else {
                    statusEl.textContent = 'غير متصل';
                    dotEl.classList.add('d-none');
                }
            }
        });
    }

    // ----- Typing indicator (receive) -----
    function showTyping(userId, userName) {
        if (userId === currentUserId) return;
        if (typingIndicator) {
            typingIndicator.classList.remove('d-none');
            scrollToBottom();
        }
        if (typingHideTimer) clearTimeout(typingHideTimer);
        typingHideTimer = setTimeout(() => {
            if (typingIndicator) typingIndicator.classList.add('d-none');
        }, 4000);
    }

    // ----- Real-time: Pusher -----
    @if($pusherEnabled)
    if (pusherEnabled) {
        const pusher = new Pusher('{{ $pusherKey }}', {
            cluster: '{{ $pusherCluster }}',
            authEndpoint: '/broadcasting/auth',
        });
        const channel = pusher.subscribe('chat.' + currentUserId);
        channel.bind('new-message', function(data) {
            addMessageToChat(data, false);
            markAsRead();
        });
        channel.bind('user-typing', function(data) {
            if (data.conversation_id !== activeId) return;
            showTyping(data.user.id, data.user.name);
        });
        channel.bind('message-read', function(data) {
            if (data.conversation_id !== activeId) return;
            // Update read state of our sent messages
            const ids = new Set(data.message_ids || []);
            document.querySelectorAll('.wa-msg.sent').forEach(msgEl => {
                const id = parseInt(msgEl.dataset.messageId);
                if (ids.has(id)) {
                    const meta = msgEl.querySelector('.wa-bubble-meta');
                    if (meta) {
                        // Replace tick icon: find the .wa-tick child
                        const tick = meta.querySelector('.wa-tick');
                        if (tick) {
                            tick.outerHTML = '<i class="fas fa-check-double wa-tick read"></i>';
                        } else {
                            // No tick yet — append one
                            const span = document.createElement('i');
                            span.className = 'fas fa-check-double wa-tick read';
                            meta.insertBefore(span, meta.firstChild.nextSibling);
                        }
                    }
                }
            });
        });

        // Presence channel (for online indicator in groups)
        try {
            const presenceChannel = pusher.subscribe('presence-chat.' + activeId);
            presenceChannel.bind('pusher:subscription_succeeded', function() {
                const members = presenceChannel.members;
                members.each(function(member) {
                    if (member.id !== currentUserId) {
                        const dotEl = document.getElementById('waOnlineDot');
                        const statusEl = document.getElementById('waMainStatusText');
                        if (dotEl) dotEl.classList.remove('d-none');
                        if (statusEl) statusEl.textContent = 'متصل';
                    }
                });
            });
            presenceChannel.bind('pusher:member_added', function(member) {
                if (member.id === currentUserId) return;
                const dotEl = document.getElementById('waOnlineDot');
                const statusEl = document.getElementById('waMainStatusText');
                if (dotEl) dotEl.classList.remove('d-none');
                if (statusEl) statusEl.textContent = 'متصل';
            });
            presenceChannel.bind('pusher:member_removed', function(member) {
                if (member.id === currentUserId) return;
                // Refresh via heartbeat to get the canonical state
                heartbeat();
            });
        } catch (e) {
            console.debug('Presence channel failed (using heartbeat fallback)', e);
        }
    }
    @endif

    // ----- Participant search (group) -----
    const partSearch = document.getElementById('waParticipantSearch');
    if (partSearch) {
        const results = document.getElementById('waParticipantResults');
        const selectedId = document.getElementById('waSelectedParticipantId');
        const selectedDisplay = document.getElementById('waSelectedParticipantDisplay');
        const selectedName = document.getElementById('waSelectedParticipantName');
        const existingIds = @json($active ? $active->participants->pluck('id')->toArray() : []);

        partSearch.addEventListener('input', function() {
            const q = this.value.trim();
            if (q.length < 2) { results.classList.add('d-none'); return; }
            fetch('/chat/users/search?search=' + encodeURIComponent(q))
                .then(r => r.json())
                .then(users => {
                    results.innerHTML = '';
                    users.forEach(u => {
                        if (existingIds.includes(u.id) || u.id === currentUserId) return;
                        const item = document.createElement('div');
                        item.className = 'search-result-item';
                        item.innerHTML = `
                            <div style="width:36px;height:36px;border-radius:10px;background:linear-gradient(135deg,#182B48,#2A4570);color:#d4a030;display:flex;align-items:center;justify-content:center;">
                                <i class="fas fa-user"></i>
                            </div>
                            <div>
                                <div style="font-weight:600;color:#182B48;">${escapeHtml(u.name)}</div>
                                <div style="font-size:12px;color:#7f8c8d;">${escapeHtml(u.role)}</div>
                            </div>
                        `;
                        item.addEventListener('click', function() {
                            selectedId.value = u.id;
                            selectedName.textContent = u.name;
                            selectedDisplay.classList.remove('d-none');
                            partSearch.value = '';
                            results.classList.add('d-none');
                        });
                        results.appendChild(item);
                    });
                    results.classList.remove('d-none');
                });
        });
    }

    // ----- Scroll up: load older messages -----
    let loadingOlder = false;
    let oldestId = parseInt(messagesEl.querySelector('.wa-msg')?.dataset.messageId || '0');
    messagesEl.addEventListener('scroll', function() {
        if (this.scrollTop < 80 && !loadingOlder && oldestId > 0) {
            loadingOlder = true;
            const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
            const beforeScroll = this.scrollHeight;
            fetch(`/chat/${activeId}/messages?before_id=${oldestId}`, {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf }
            })
            .then(r => r.json())
            .then(data => {
                if (data.messages && data.messages.length) {
                    const fragment = [];
                    let lastDate = null;
                    data.messages.forEach(m => {
                        const mDate = new Date(m.created_at).toDateString();
                        if (mDate !== lastDate) {
                            fragment.push(`<div class="wa-date-sep" data-date="${mDate}"><span>${dateLabel(m.created_at)}</span></div>`);
                            lastDate = mDate;
                        }
                        const isSent = m.user_id === currentUserId;
                        const prev = data.messages[data.messages.indexOf(m) - 1];
                        const leading = !prev || prev.user_id !== m.user_id;
                        const showSender = !isSent && leading;
                        fragment.push(makeMessageHtml(m, { leading, showSender, readState: isSent ? 'sent' : null }));
                    });
                    messagesEl.insertAdjacentHTML('afterbegin', fragment.join(''));
                    this.scrollTop = this.scrollHeight - beforeScroll;
                    oldestId = data.oldest_id || oldestId;
                }
            })
            .finally(() => { loadingOlder = false; });
        }
    });

    // Clean up on unload
    window.addEventListener('beforeunload', function() {
        if (heartbeatTimer) clearInterval(heartbeatTimer);
        if (pollTimer) clearInterval(pollTimer);
    });
});
</script>
@endpush
