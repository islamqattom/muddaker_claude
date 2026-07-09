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
                    @if($message->attachments->isNotEmpty())
                        <div class="message-attachments">
                            @foreach($message->attachments as $att)
                                @if($att->is_image)
                                    <a href="{{ $att->url }}"
                                       data-lightbox="chat-{{ $message->id }}"
                                       data-title="{{ $att->original_name }}"
                                       class="msg-attachment msg-attachment-image"
                                       title="{{ $att->original_name }}">
                                        <img src="{{ $att->url }}" alt="{{ $att->original_name }}" loading="lazy">
                                        <span class="msg-image-overlay">
                                            <i class="fas fa-search-plus"></i>
                                        </span>
                                    </a>
                                @elseif($att->is_video)
                                    <div class="msg-attachment msg-attachment-video media-player"
                                         data-player-type="video"
                                         data-src="{{ $att->url }}"
                                         data-name="{{ $att->original_name }}">
                                        <div class="media-player-shell">
                                            <video class="media-element" preload="metadata" playsinline>
                                                <source src="{{ $att->url }}" type="{{ $att->mime_type }}">
                                            </video>
                                            <div class="media-overlay" data-role="overlay">
                                                <button type="button" class="media-play-btn" data-role="play">
                                                    <i class="fas fa-play"></i>
                                                </button>
                                                <span class="media-duration" data-role="duration">0:00</span>
                                            </div>
                                            <div class="media-controls" data-role="controls">
                                                <button type="button" class="media-ctrl-btn" data-role="play" aria-label="تشغيل/إيقاف">
                                                    <i class="fas fa-play"></i>
                                                </button>
                                                <span class="media-time" data-role="current">0:00</span>
                                                <div class="media-progress" data-role="progress">
                                                    <div class="media-progress-fill" data-role="progress-fill"></div>
                                                </div>
                                                <span class="media-time" data-role="total">0:00</span>
                                                <button type="button" class="media-ctrl-btn" data-role="mute" aria-label="كتم الصوت">
                                                    <i class="fas fa-volume-up"></i>
                                                </button>
                                                <button type="button" class="media-ctrl-btn" data-role="fullscreen" aria-label="ملء الشاشة">
                                                    <i class="fas fa-expand"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <a href="{{ route('chat.attachment.download', $att) }}" class="msg-attachment-download" target="_blank">
                                            <i class="fas fa-download"></i> {{ $att->original_name }}
                                        </a>
                                    </div>
                                @elseif($att->is_audio)
                                    <div class="msg-attachment msg-attachment-audio media-player"
                                         data-player-type="audio"
                                         data-src="{{ $att->url }}"
                                         data-name="{{ $att->original_name }}">
                                        <button type="button" class="media-audio-play" data-role="play" aria-label="تشغيل/إيقاف">
                                            <i class="fas fa-play"></i>
                                        </button>
                                        <div class="media-audio-bars" data-role="bars" aria-hidden="true">
                                            @for($i = 0; $i < 28; $i++)
                                                <span></span>
                                            @endfor
                                        </div>
                                        <div class="media-audio-info">
                                            <div class="media-audio-name" title="{{ $att->original_name }}">
                                                <i class="fas fa-music"></i>
                                                {{ $att->original_name }}
                                            </div>
                                            <div class="media-audio-meta">
                                                <span data-role="current">0:00</span>
                                                <span> / </span>
                                                <span data-role="total">0:00</span>
                                            </div>
                                        </div>
                                        <audio class="media-element" preload="metadata">
                                            <source src="{{ $att->url }}" type="{{ $att->mime_type }}">
                                        </audio>
                                        <a href="{{ route('chat.attachment.download', $att) }}" class="msg-attachment-download" target="_blank" title="تحميل">
                                            <i class="fas fa-download"></i>
                                        </a>
                                    </div>
                                @else
                                    <a href="{{ route('chat.attachment.download', $att) }}" class="msg-attachment msg-attachment-file" target="_blank">
                                        <span class="msg-attachment-icon"><i class="fas {{ $att->icon }}"></i></span>
                                        <span class="msg-attachment-meta">
                                            <span class="msg-attachment-name">{{ $att->original_name }}</span>
                                            <span class="msg-attachment-size">{{ $att->human_size }}</span>
                                        </span>
                                        <span class="msg-attachment-download"><i class="fas fa-download"></i></span>
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    @endif
                    @if(!empty($message->content))
                        <p class="message-text">{{ $message->content }}</p>
                    @endif
                    <span class="message-time">
                        {{ $message->created_at->format('H:i') }}
                        @if($message->attachments->isNotEmpty())
                            <i class="fas fa-paperclip ms-1" title="مرفقات"></i>
                        @endif
                    </span>
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
        <div id="attachmentPreview" class="attachment-preview d-none"></div>
        <form id="messageForm" class="message-form" enctype="multipart/form-data">
            @csrf
            <div class="chat-input-wrapper">
                <button type="button" class="attach-btn" id="attachBtn" title="إرفاق ملف">
                    <i class="fas fa-paperclip"></i>
                </button>
                <input type="file" id="attachmentInput" name="attachments[]" multiple
                       accept="image/*,video/*,audio/*,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.csv,.zip,.rar,.7z,.json,.xml"
                       style="display:none">
                <input type="text" id="chatInput" class="chat-input" placeholder="اكتب رسالة أو أرفق ملفاً..." autocomplete="off">
                <button type="submit" class="send-btn" id="sendBtn">
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

<div id="lightboxModal" class="lightbox-modal" aria-hidden="true">
    <button type="button" class="lightbox-close" aria-label="إغلاق"><i class="fas fa-times"></i></button>
    <button type="button" class="lightbox-nav lightbox-prev" aria-label="السابق"><i class="fas fa-chevron-right"></i></button>
    <button type="button" class="lightbox-nav lightbox-next" aria-label="التالي"><i class="fas fa-chevron-left"></i></button>
    <div class="lightbox-stage">
        <img id="lightboxImage" src="" alt="">
    </div>
    <div class="lightbox-caption" id="lightboxCaption"></div>
</div>
@endsection

@push('styles')
<style>
.chat-container {
    display: flex;
    flex-direction: column;
    height: calc(100vh - 180px);
    height: calc(100dvh - 180px);
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
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    max-width: 100%;
}

.chat-header-status {
    font-size: 12px;
    opacity: 0.8;
}

.chat-messages {
    flex: 1;
    overflow-y: auto;
    overflow-x: hidden;
    padding: 24px;
    display: flex;
    flex-direction: column;
    gap: 16px;
    background: #f0f4f8;
    min-height: 0;
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

.message-attachments {
    display: flex;
    flex-direction: column;
    gap: 8px;
    margin-bottom: 8px;
    max-width: 100%;
}

.message.received .message-attachments { max-width: 320px; }
.message.sent .message-attachments { max-width: 320px; }

.msg-attachment-image {
    display: block;
    border-radius: 12px;
    overflow: hidden;
    background: rgba(0,0,0,0.05);
    line-height: 0;
}
.msg-attachment-image img {
    width: 100%;
    max-width: 320px;
    max-height: 320px;
    object-fit: cover;
    display: block;
    border-radius: 12px;
    transition: transform 0.2s;
}
.msg-attachment-image:hover img { transform: scale(1.02); }

.msg-attachment-video {
    background: rgba(0,0,0,0.2);
    border-radius: 12px;
    padding: 8px;
    display: flex;
    flex-direction: column;
    gap: 6px;
}
.msg-attachment-video video {
    width: 100%;
    max-width: 320px;
    max-height: 320px;
    border-radius: 8px;
    background: #000;
}
.message.sent .msg-attachment-video { background: rgba(255,255,255,0.1); }
.message.sent .msg-attachment-video video { background: #000; }

.msg-attachment-audio {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 12px;
    background: rgba(0,0,0,0.06);
    border-radius: 12px;
}
.message.sent .msg-attachment-audio { background: rgba(255,255,255,0.15); }
.msg-attachment-audio i { font-size: 18px; opacity: 0.8; }
.msg-attachment-audio audio { height: 32px; flex: 1; min-width: 180px; }

.msg-attachment-file {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 12px;
    background: rgba(0,0,0,0.06);
    border-radius: 12px;
    text-decoration: none;
    color: inherit;
    transition: background 0.2s;
    min-width: 200px;
    max-width: 320px;
}
.msg-attachment-file:hover { background: rgba(0,0,0,0.12); }
.message.sent .msg-attachment-file { background: rgba(255,255,255,0.15); }
.message.sent .msg-attachment-file:hover { background: rgba(255,255,255,0.25); }

.msg-attachment-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    background: linear-gradient(135deg, #182B48, #2A4570);
    color: #d4a030;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    flex-shrink: 0;
}
.message.sent .msg-attachment-icon { background: rgba(255,255,255,0.25); color: #fff; }

.msg-attachment-meta {
    flex: 1;
    min-width: 0;
    display: flex;
    flex-direction: column;
    gap: 2px;
}
.msg-attachment-name {
    font-size: 13px;
    font-weight: 600;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.msg-attachment-size {
    font-size: 11px;
    opacity: 0.75;
}
.msg-attachment-download {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(0,0,0,0.1);
    color: inherit;
    flex-shrink: 0;
    text-decoration: none;
}
.message.sent .msg-attachment-download { background: rgba(255,255,255,0.2); }

.attachment-preview {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    padding: 12px 20px 0;
    background: #fff;
    border-top: 1px solid #f0f4f8;
}
.attachment-preview.d-none { display: none !important; }
.attachment-preview-item {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 12px;
    background: #f0f4f8;
    border-radius: 12px;
    font-size: 12px;
    max-width: 220px;
}
.attachment-preview-item .api-thumb {
    width: 36px;
    height: 36px;
    border-radius: 8px;
    object-fit: cover;
    background: #d4a030;
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.attachment-preview-item .api-name {
    flex: 1;
    min-width: 0;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    font-weight: 600;
    color: #182B48;
}
.attachment-preview-item .api-size {
    font-size: 10px;
    color: #7f8c8d;
}
.attachment-preview-item .api-remove {
    border: none;
    background: rgba(192, 57, 43, 0.12);
    color: #c0392b;
    width: 22px;
    height: 22px;
    border-radius: 50%;
    font-size: 10px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.message-text {
    margin: 0 0 8px 0;
    font-size: 14px;
    line-height: 1.5;
}

/* === Image overlay (lightbox trigger) === */
.msg-attachment-image {
    position: relative;
    overflow: hidden;
}
.msg-image-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(180deg, rgba(0,0,0,0) 50%, rgba(0,0,0,0.55) 100%);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
    opacity: 0;
    transition: opacity 0.2s;
    pointer-events: none;
}
.msg-attachment-image:hover .msg-image-overlay { opacity: 1; }
.msg-image-overlay i { transform: scale(0.85); transition: transform 0.2s; }
.msg-attachment-image:hover .msg-image-overlay i { transform: scale(1); }

/* === Video player === */
.msg-attachment-video {
    background: #000;
    border-radius: 14px;
    padding: 0;
    overflow: hidden;
    max-width: 340px;
    border: 1px solid rgba(0,0,0,0.1);
}
.media-player-shell {
    position: relative;
    width: 100%;
    aspect-ratio: 16 / 9;
    background: #000;
    overflow: hidden;
}
.media-element {
    width: 100%;
    height: 100%;
    display: block;
    object-fit: contain;
    background: #000;
}
.media-overlay {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-direction: column;
    gap: 12px;
    background: linear-gradient(180deg, rgba(0,0,0,0.15), rgba(0,0,0,0.45));
    transition: opacity 0.2s;
    cursor: pointer;
    z-index: 2;
}
.media-overlay.is-hidden { opacity: 0; pointer-events: none; }
.media-play-btn {
    width: 64px;
    height: 64px;
    border-radius: 50%;
    border: none;
    background: linear-gradient(135deg, #d4a030, #B4802C);
    color: #fff;
    font-size: 22px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    box-shadow: 0 8px 24px rgba(0,0,0,0.4);
    transition: transform 0.2s, box-shadow 0.2s;
}
.media-play-btn:hover { transform: scale(1.08); }
.media-play-btn.is-playing { background: linear-gradient(135deg, #182B48, #2A4570); }
.media-duration {
    color: #fff;
    font-size: 12px;
    font-weight: 700;
    text-shadow: 0 1px 3px rgba(0,0,0,0.6);
    background: rgba(0,0,0,0.4);
    padding: 3px 10px;
    border-radius: 10px;
}
.media-controls {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    padding: 8px 10px;
    background: linear-gradient(0deg, rgba(0,0,0,0.85), rgba(0,0,0,0));
    display: flex;
    align-items: center;
    gap: 8px;
    color: #fff;
    z-index: 3;
    transition: opacity 0.25s, transform 0.25s;
    opacity: 0;
}
.media-player-shell.is-active .media-controls,
.media-player-shell:hover .media-controls { opacity: 1; }
.media-player-shell.is-fullscreen .media-controls { padding: 14px 18px; gap: 12px; }
.media-ctrl-btn {
    background: transparent;
    border: none;
    color: #fff;
    width: 32px;
    height: 32px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 13px;
    transition: background 0.2s;
    flex-shrink: 0;
}
.media-ctrl-btn:hover { background: rgba(255,255,255,0.15); }
.media-time {
    font-size: 11px;
    font-weight: 600;
    min-width: 38px;
    text-align: center;
    opacity: 0.9;
    font-variant-numeric: tabular-nums;
}
.media-progress {
    flex: 1;
    height: 5px;
    background: rgba(255,255,255,0.25);
    border-radius: 3px;
    cursor: pointer;
    position: relative;
    transition: height 0.15s;
}
.media-progress:hover { height: 7px; }
.media-progress-fill {
    position: absolute;
    inset: 0 auto 0 0;
    width: 0%;
    background: linear-gradient(90deg, #d4a030, #B4802C);
    border-radius: 3px;
    pointer-events: none;
}
.media-player-shell.is-fullscreen {
    width: 100vw;
    height: 100vh;
    aspect-ratio: auto;
}
.media-player-shell.is-fullscreen .media-element { object-fit: contain; }
.msg-attachment-video .msg-attachment-download {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 8px 12px;
    color: rgba(255,255,255,0.85);
    font-size: 12px;
    background: rgba(0,0,0,0.85);
    border-top: 1px solid rgba(255,255,255,0.08);
    text-decoration: none;
}
.msg-attachment-video .msg-attachment-download:hover { color: #d4a030; }

/* === Audio player === */
.msg-attachment-audio {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 12px;
    background: rgba(0,0,0,0.06);
    border-radius: 14px;
    min-width: 260px;
    max-width: 340px;
    border: 1px solid rgba(0,0,0,0.05);
}
.message.sent .msg-attachment-audio { background: rgba(255,255,255,0.15); border-color: rgba(255,255,255,0.1); }
.media-audio-play {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    border: none;
    background: linear-gradient(135deg, #d4a030, #B4802C);
    color: #fff;
    font-size: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    flex-shrink: 0;
    box-shadow: 0 4px 12px rgba(212,160,48,0.4);
    transition: transform 0.2s;
}
.media-audio-play:hover { transform: scale(1.05); }
.media-audio-play.is-playing { background: linear-gradient(135deg, #182B48, #2A4570); box-shadow: 0 4px 12px rgba(24,43,72,0.4); }
.media-audio-bars {
    display: flex;
    align-items: center;
    gap: 2px;
    height: 32px;
    flex-shrink: 0;
    width: 90px;
    cursor: pointer;
}
.media-audio-bars span {
    flex: 1;
    background: currentColor;
    color: rgba(24, 43, 72, 0.35);
    border-radius: 2px;
    min-height: 4px;
    transition: background 0.15s, height 0.15s;
}
.message.sent .media-audio-bars span { color: rgba(255,255,255,0.4); }
.media-audio-bars span.is-played { color: #d4a030; }
.message.sent .media-audio-bars span.is-played { color: #fff; }
.media-audio-bars span.is-active { animation: bar-pulse 0.7s ease-in-out infinite; }
@keyframes bar-pulse {
    0%, 100% { transform: scaleY(1); }
    50% { transform: scaleY(0.6); }
}
.media-audio-info {
    flex: 1;
    min-width: 0;
    display: flex;
    flex-direction: column;
    gap: 2px;
}
.media-audio-name {
    font-size: 12px;
    font-weight: 700;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    color: inherit;
    opacity: 0.95;
}
.media-audio-name i { margin-left: 4px; opacity: 0.7; }
.media-audio-meta {
    font-size: 10px;
    opacity: 0.75;
    font-variant-numeric: tabular-nums;
}
.msg-attachment-audio .msg-attachment-download {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(0,0,0,0.1);
    color: inherit;
    flex-shrink: 0;
    text-decoration: none;
}
.message.sent .msg-attachment-audio .msg-attachment-download { background: rgba(255,255,255,0.2); }

/* === Lightbox === */
.lightbox-modal {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.92);
    z-index: 10000;
    display: none;
    align-items: center;
    justify-content: center;
    padding: 24px;
    animation: lightbox-fade 0.2s ease;
}
.lightbox-modal.is-open { display: flex; }
@keyframes lightbox-fade {
    from { opacity: 0; }
    to { opacity: 1; }
}
.lightbox-stage {
    max-width: 92vw;
    max-height: 86vh;
    display: flex;
    align-items: center;
    justify-content: center;
}
.lightbox-stage img {
    max-width: 100%;
    max-height: 86vh;
    object-fit: contain;
    border-radius: 8px;
    box-shadow: 0 20px 60px rgba(0,0,0,0.5);
    transition: transform 0.2s;
    user-select: none;
    cursor: zoom-in;
}
.lightbox-stage img.is-zoomed {
    transform: scale(1.8);
    cursor: zoom-out;
}
.lightbox-close,
.lightbox-nav {
    position: absolute;
    background: rgba(255,255,255,0.15);
    color: #fff;
    border: none;
    width: 48px;
    height: 48px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 18px;
    transition: background 0.2s, transform 0.2s;
    z-index: 2;
}
.lightbox-close:hover, .lightbox-nav:hover { background: rgba(255,255,255,0.3); }
.lightbox-close { top: 20px; left: 20px; }
.lightbox-nav { top: 50%; transform: translateY(-50%); }
.lightbox-nav:hover { transform: translateY(-50%) scale(1.05); }
.lightbox-prev { right: 20px; }
.lightbox-next { left: 20px; }
.lightbox-caption {
    position: absolute;
    bottom: 24px;
    left: 50%;
    transform: translateX(-50%);
    color: #fff;
    font-size: 13px;
    background: rgba(0,0,0,0.5);
    padding: 8px 16px;
    border-radius: 20px;
    max-width: 80%;
    text-align: center;
}
.lightbox-counter {
    position: absolute;
    top: 24px;
    right: 24px;
    color: #fff;
    font-size: 13px;
    background: rgba(0,0,0,0.5);
    padding: 6px 14px;
    border-radius: 20px;
    font-weight: 600;
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
    gap: 8px;
    background: #f0f4f8;
    border-radius: 25px;
    padding: 6px;
    align-items: center;
}

.attach-btn {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    border: none;
    background: rgba(24, 43, 72, 0.08);
    color: #182B48;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 16px;
    transition: all 0.2s ease;
    flex-shrink: 0;
}
.attach-btn:hover { background: rgba(24, 43, 72, 0.18); transform: scale(1.05); }
.attach-btn.has-files { background: linear-gradient(135deg, #d4a030, #B4802C); color: #fff; }

.chat-input {
    flex: 1;
    border: none;
    background: transparent;
    padding: 12px 16px;
    font-size: 16px;
    outline: none;
    min-width: 0;
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
    .page-content:has(> .chat-container) {
        padding: 0;
    }

    .chat-container {
        height: calc(100vh - 55px);
        height: calc(100dvh - 55px);
        border-radius: 0;
        box-shadow: none;
    }

    .chat-header {
        padding: 12px 16px;
        gap: 12px;
    }

    .back-btn {
        width: 36px;
        height: 36px;
        border-radius: 9px;
        flex-shrink: 0;
    }

    .chat-header-info {
        gap: 10px;
        min-width: 0;
    }

    .chat-header-avatar {
        width: 40px;
        height: 40px;
        font-size: 17px;
        border-radius: 10px;
        flex-shrink: 0;
    }

    .chat-header-name {
        font-size: 15px;
        max-width: 50vw;
    }

    .chat-header-status {
        font-size: 11px;
    }

    .chat-actions .btn {
        width: 36px;
        height: 36px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .chat-messages {
        padding: 14px 12px;
        gap: 10px;
    }

    .message {
        max-width: 88%;
    }

    .message-content {
        padding: 10px 14px;
        border-radius: 14px;
    }

    .message-text {
        font-size: 14px;
    }

    .message-attachments {
        max-width: 100%;
    }

    .msg-attachment-image img,
    .msg-attachment-video video {
        max-width: 240px;
        max-height: 240px;
    }

    .msg-attachment-video {
        max-width: 260px;
    }

    .msg-attachment-file {
        max-width: 260px;
        min-width: 180px;
    }

    .msg-attachment-audio {
        min-width: 0;
        max-width: 280px;
        padding: 8px 10px;
        gap: 8px;
    }

    .msg-attachment-audio audio {
        min-width: 0;
        width: 100%;
    }

    .media-audio-bars {
        width: 60px;
    }

    .media-audio-name {
        font-size: 11px;
    }

    .chat-input-container {
        padding: 10px 12px;
        padding-bottom: calc(10px + env(safe-area-inset-bottom, 0px));
    }

    .chat-input-wrapper {
        padding: 4px;
        border-radius: 22px;
    }

    .attach-btn {
        width: 38px;
        height: 38px;
        font-size: 15px;
    }

    .send-btn {
        width: 42px;
        height: 42px;
    }

    .empty-chat {
        padding: 40px 16px;
    }

    .empty-chat .empty-icon {
        width: 70px;
        height: 70px;
        font-size: 28px;
        margin-bottom: 14px;
    }

    .empty-chat h5 {
        font-size: 16px;
    }

    .lightbox-modal {
        padding: 12px;
    }

    .lightbox-close {
        top: 12px;
        left: 12px;
        width: 42px;
        height: 42px;
    }

    .lightbox-prev { right: 12px; }
    .lightbox-next { left: 12px; }
    .lightbox-nav { width: 42px; height: 42px; }

    .lightbox-caption {
        font-size: 12px;
        padding: 6px 12px;
        max-width: 90%;
    }
}

@media (max-width: 576px) {
    .chat-container {
        height: calc(100dvh - 55px);
    }

    .chat-messages {
        padding: 10px 10px;
    }

    .message {
        max-width: 92%;
    }

    .message-content {
        padding: 9px 12px;
    }

    .msg-attachment-image img,
    .msg-attachment-video video {
        max-width: 200px;
        max-height: 200px;
    }

    .msg-attachment-file {
        min-width: 0;
    }

    .msg-attachment-file .msg-attachment-meta {
        min-width: 0;
    }

    .chat-header-name {
        max-width: 40vw;
    }
}
</style>
@endpush

@push('scripts')
<script src="https://js.pusher.com/7.2/pusher.min.js"></script>
<script>
/* =========================================================
 *  Media Player + Lightbox controller
 *  ------------------------------------------------------
 *  Single class-based controller for all <video> and <audio>
 *  elements rendered in the chat. Also wires up image
 *  lightbox with zoom + keyboard navigation.
 * ========================================================= */
(function () {
    function fmtTime(s) {
        if (!isFinite(s) || s < 0) return '0:00';
        const m = Math.floor(s / 60);
        const sec = Math.floor(s % 60);
        return m + ':' + String(sec).padStart(2, '0');
    }

    function findAll(root, selector) {
        return Array.from((root || document).querySelectorAll(selector));
    }

    class MediaPlayer {
        constructor(root) {
            this.root = root;
            this.type = root.dataset.playerType;
            this.media = root.querySelector('audio, video');
            if (!this.media) return;
            this.playBtns = findAll(root, '[data-role="play"]');
            this.currentEls = findAll(root, '[data-role="current"]');
            this.totalEls = findAll(root, '[data-role="total"]');
            this.durationEl = root.querySelector('[data-role="duration"]');
            this.progress = root.querySelector('[data-role="progress"]');
            this.progressFill = root.querySelector('[data-role="progress-fill"]');
            this.muteBtn = root.querySelector('[data-role="mute"]');
            this.fullscreenBtn = root.querySelector('[data-role="fullscreen"]');
            this.overlay = root.querySelector('[data-role="overlay"]');
            this.shell = root.querySelector('.media-player-shell');
            this.bars = root.querySelectorAll('[data-role="bars"] span');
            this.bind();
        }

        bind() {
            this.playBtns.forEach(b => b.addEventListener('click', e => {
                e.preventDefault();
                e.stopPropagation();
                this.toggle();
            }));
            if (this.overlay) {
                this.overlay.addEventListener('click', e => {
                    if (e.target.closest('[data-role="play"]')) return;
                    this.toggle();
                });
            }
            this.media.addEventListener('loadedmetadata', () => this.onMeta());
            this.media.addEventListener('timeupdate', () => this.onTime());
            this.media.addEventListener('play', () => this.onPlay());
            this.media.addEventListener('pause', () => this.onPause());
            this.media.addEventListener('ended', () => this.onEnded());
            this.media.addEventListener('volumechange', () => this.onVolume());
            if (this.progress) {
                this.progress.addEventListener('click', e => this.seek(e));
            }
            if (this.bars.length) {
                this.bars.forEach((bar, i) => {
                    bar.style.height = (30 + Math.sin(i * 1.3) * 25 + Math.random() * 30) + '%';
                    bar.addEventListener('click', () => {
                        if (!isFinite(this.media.duration)) return;
                        this.media.currentTime = (i + 0.5) / this.bars.length * this.media.duration;
                    });
                });
            }
            if (this.muteBtn) {
                this.muteBtn.addEventListener('click', e => {
                    e.preventDefault();
                    this.media.muted = !this.media.muted;
                });
            }
            if (this.fullscreenBtn && this.shell) {
                this.fullscreenBtn.addEventListener('click', e => {
                    e.preventDefault();
                    this.toggleFullscreen();
                });
            }
            document.addEventListener('fullscreenchange', () => this.onFullscreenChange());
        }

        toggle() {
            if (this.media.paused) this.play(); else this.pause();
        }

        async play() {
            MediaPlayer.pauseAll(this);
            try { await this.media.play(); } catch (e) { /* autoplay blocked */ }
        }

        pause() { this.media.pause(); }

        seek(e) {
            if (!this.progress || !isFinite(this.media.duration)) return;
            const rect = this.progress.getBoundingClientRect();
            const x = (e.clientX - rect.left) / rect.width;
            this.media.currentTime = Math.max(0, Math.min(1, x)) * this.media.duration;
        }

        onMeta() {
            this.totalEls.forEach(el => el.textContent = fmtTime(this.media.duration));
            if (this.durationEl) this.durationEl.textContent = fmtTime(this.media.duration);
        }

        onTime() {
            const t = this.media.currentTime;
            const d = this.media.duration || 0;
            this.currentEls.forEach(el => el.textContent = fmtTime(t));
            const pct = d ? (t / d) * 100 : 0;
            if (this.progressFill) this.progressFill.style.width = pct + '%';
            if (this.bars.length) {
                this.bars.forEach((bar, i) => {
                    const barPos = (i + 0.5) / this.bars.length;
                    bar.classList.toggle('is-played', barPos <= (t / d));
                    bar.classList.toggle('is-active', Math.abs(barPos - (t / d)) < 0.04 && !this.media.paused);
                });
            }
        }

        onPlay() {
            this.playBtns.forEach(b => {
                b.classList.add('is-playing');
                const icon = b.querySelector('i');
                if (icon) { icon.classList.remove('fa-play'); icon.classList.add('fa-pause'); }
            });
            if (this.overlay) this.overlay.classList.add('is-hidden');
            if (this.shell) this.shell.classList.add('is-active');
        }

        onPause() {
            this.playBtns.forEach(b => {
                b.classList.remove('is-playing');
                const icon = b.querySelector('i');
                if (icon) { icon.classList.add('fa-play'); icon.classList.remove('fa-pause'); }
            });
            if (this.overlay) this.overlay.classList.remove('is-hidden');
        }

        onEnded() {
            this.onPause();
            this.media.currentTime = 0;
            if (this.progressFill) this.progressFill.style.width = '0%';
            this.bars.forEach(b => b.classList.remove('is-played', 'is-active'));
        }

        onVolume() {
            if (!this.muteBtn) return;
            const icon = this.muteBtn.querySelector('i');
            if (!icon) return;
            icon.classList.remove('fa-volume-up', 'fa-volume-mute', 'fa-volume-off');
            if (this.media.muted || this.media.volume === 0) icon.classList.add('fa-volume-mute');
            else icon.classList.add('fa-volume-up');
        }

        toggleFullscreen() {
            if (!this.shell) return;
            if (document.fullscreenElement) {
                document.exitFullscreen();
            } else if (this.shell.requestFullscreen) {
                this.shell.requestFullscreen();
            }
        }

        onFullscreenChange() {
            if (!this.shell) return;
            this.shell.classList.toggle('is-fullscreen', !!document.fullscreenElement);
            const icon = this.fullscreenBtn?.querySelector('i');
            if (icon) {
                icon.classList.toggle('fa-expand', !document.fullscreenElement);
                icon.classList.toggle('fa-compress', !!document.fullscreenElement);
            }
        }

        static pauseAll(except) {
            MediaPlayer.instances.forEach(p => {
                if (p !== except && !p.media.paused) p.pause();
            });
        }
    }
    MediaPlayer.instances = [];

    /* === Lightbox controller === */
    class Lightbox {
        constructor() {
            this.modal = document.getElementById('lightboxModal');
            this.img = document.getElementById('lightboxImage');
            this.caption = document.getElementById('lightboxCaption');
            this.items = [];
            this.index = 0;
            this.bind();
        }

        bind() {
            this.modal.querySelector('.lightbox-close').addEventListener('click', () => this.close());
            this.modal.querySelector('.lightbox-prev').addEventListener('click', () => this.nav(-1));
            this.modal.querySelector('.lightbox-next').addEventListener('click', () => this.nav(1));
            this.img.addEventListener('click', () => this.img.classList.toggle('is-zoomed'));
            this.modal.addEventListener('click', e => {
                if (e.target === this.modal || e.target.classList.contains('lightbox-stage')) this.close();
            });
            document.addEventListener('keydown', e => {
                if (!this.modal.classList.contains('is-open')) return;
                if (e.key === 'Escape') this.close();
                else if (e.key === 'ArrowLeft') this.nav(1);
                else if (e.key === 'ArrowRight') this.nav(-1);
            });
        }

        open(items, index) {
            this.items = items;
            this.index = index;
            this.render();
            this.modal.classList.add('is-open');
            document.body.style.overflow = 'hidden';
        }

        close() {
            this.modal.classList.remove('is-open');
            this.img.classList.remove('is-zoomed');
            document.body.style.overflow = '';
        }

        nav(dir) {
            if (!this.items.length) return;
            this.index = (this.index + dir + this.items.length) % this.items.length;
            this.render();
        }

        render() {
            const it = this.items[this.index];
            this.img.src = it.src;
            this.img.alt = it.title || '';
            this.caption.textContent = (it.title || '') +
                (this.items.length > 1 ? ` (${this.index + 1}/${this.items.length})` : '');
        }
    }

    function init() {
        // Expose for the chat script that re-inits new messages
        window.MediaPlayer = MediaPlayer;
        window.__mediaPlayers = window.__mediaPlayers || [];
        findAll(document, '.media-player').forEach(root => {
            try { window.__mediaPlayers.push(new MediaPlayer(root)); } catch (e) { console.error(e); }
        });
        // Sync the static list used by MediaPlayer.pauseAll
        MediaPlayer.instances = window.__mediaPlayers;

        const lb = new Lightbox();
        window.__lightbox = lb;
        const galleries = window.__lightboxGalleries = window.__lightboxGalleries || {};
        findAll(document, '[data-lightbox]').forEach(a => {
            const key = a.dataset.lightbox;
            (galleries[key] = galleries[key] || []).push({
                src: a.getAttribute('href'),
                title: a.dataset.title || a.title || '',
            });
            a.addEventListener('click', e => {
                e.preventDefault();
                const idx = galleries[key].findIndex(i => i.src === a.getAttribute('href'));
                if (idx >= 0) {
                    lb.items = galleries[key];
                    lb.index = idx;
                    lb.render();
                    lb.modal.classList.add('is-open');
                    document.body.style.overflow = 'hidden';
                }
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const conversationId = {{ $conversation->id }};
    const currentUserId = {{ auth()->id() }};
    const messagesContainer = document.getElementById('chatMessages');
    const messageForm = document.getElementById('messageForm');
    const chatInput = document.getElementById('chatInput');
    const attachBtn = document.getElementById('attachBtn');
    const attachmentInput = document.getElementById('attachmentInput');
    const attachmentPreview = document.getElementById('attachmentPreview');
    const sendBtn = document.getElementById('sendBtn');

    let pendingFiles = [];

    messagesContainer.scrollTop = messagesContainer.scrollHeight;

    // Pusher setup for real-time
    @if(config('broadcasting.default') === 'pusher')
    const pusher = new Pusher('{{ config('broadcasting.connections.pusher.key') }}', {
        cluster: '{{ config('broadcasting.connections.pusher.options.cluster') }}'
    });

    const channel = pusher.subscribe('chat.' + currentUserId);
    channel.bind('new-message', function(data) {
        addMessageToChat(data);
    });
    @endif

    function humanSize(bytes) {
        if (!bytes) return '0 B';
        const units = ['B', 'KB', 'MB', 'GB'];
        const i = Math.min(Math.floor(Math.log(bytes) / Math.log(1024)), units.length - 1);
        return (bytes / Math.pow(1024, i)).toFixed(1) + ' ' + units[i];
    }

    function rebuildAttachmentPreview() {
        attachmentPreview.innerHTML = '';
        if (pendingFiles.length === 0) {
            attachmentPreview.classList.add('d-none');
            attachBtn.classList.remove('has-files');
            return;
        }
        attachmentPreview.classList.remove('d-none');
        attachBtn.classList.add('has-files');

        pendingFiles.forEach((file, idx) => {
            const isImage = file.type.startsWith('image/');
            const isVideo = file.type.startsWith('video/');
            const isAudio = file.type.startsWith('audio/');

            const item = document.createElement('div');
            item.className = 'attachment-preview-item';

            const thumb = document.createElement('div');
            thumb.className = 'api-thumb';
            if (isImage) {
                const img = document.createElement('img');
                img.src = URL.createObjectURL(file);
                img.style.width = '36px';
                img.style.height = '36px';
                img.style.objectFit = 'cover';
                img.style.borderRadius = '8px';
                thumb.appendChild(img);
            } else if (isVideo) {
                thumb.innerHTML = '<i class="fas fa-video"></i>';
            } else if (isAudio) {
                thumb.innerHTML = '<i class="fas fa-music"></i>';
            } else {
                thumb.innerHTML = '<i class="fas fa-file"></i>';
            }

            const meta = document.createElement('div');
            meta.style.flex = '1';
            meta.style.minWidth = '0';
            meta.style.overflow = 'hidden';
            meta.innerHTML = `
                <div class="api-name">${file.name}</div>
                <div class="api-size">${humanSize(file.size)}</div>
            `;

            const remove = document.createElement('button');
            remove.type = 'button';
            remove.className = 'api-remove';
            remove.innerHTML = '<i class="fas fa-times"></i>';
            remove.addEventListener('click', () => {
                pendingFiles.splice(idx, 1);
                rebuildAttachmentPreview();
            });

            item.appendChild(thumb);
            item.appendChild(meta);
            item.appendChild(remove);
            attachmentPreview.appendChild(item);
        });
    }

    attachBtn.addEventListener('click', () => attachmentInput.click());

    attachmentInput.addEventListener('change', function() {
        for (const f of Array.from(this.files || [])) {
            if (pendingFiles.length >= 10) break;
            pendingFiles.push(f);
        }
        this.value = '';
        rebuildAttachmentPreview();
    });

    messageForm.addEventListener('submit', function(e) {
        e.preventDefault();

        const content = chatInput.value.trim();
        if (!content && pendingFiles.length === 0) return;

        sendBtn.disabled = true;
        const originalIcon = sendBtn.innerHTML;
        sendBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

        const formData = new FormData();
        formData.append('content', content);
        pendingFiles.forEach(f => formData.append('attachments[]', f));

        // Refresh CSRF token from meta tag in case it rotated
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content
            || document.querySelector('input[name="_token"]')?.value
            || '{{ csrf_token() }}';

        fetch(`/chat/${conversationId}/message`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrf,
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
            body: formData
        })
        .then(async r => {
            const text = await r.text();
            let data;
            try { data = text ? JSON.parse(text) : {}; }
            catch (parseErr) {
                console.error('Non-JSON response:', r.status, text.slice(0, 500));
                throw new Error(`استجابة غير متوقعة من الخادم (HTTP ${r.status})`);
            }
            if (!r.ok) {
                const msg = data.error
                    || (data.errors ? Object.values(data.errors).flat().join('\n') : null)
                    || data.message
                    || `خطأ في الخادم (HTTP ${r.status})`;
                throw new Error(msg);
            }
            return data;
        })
        .then(data => {
            if (data.success) {
                chatInput.value = '';
                pendingFiles = [];
                rebuildAttachmentPreview();
                // Render the sent message in the chat. If client-side
                // rendering throws (e.g. a future regression in
                // addMessageToChat), we must NOT show "send failed" —
                // the message is already saved on the server.
                try {
                    addMessageToChat(data.message);
                } catch (renderErr) {
                    console.error('Render error (message was sent):', renderErr);
                }
            } else {
                alert(data.error || 'استجابة غير معروفة من الخادم');
            }
        })
        .catch(err => {
            console.error('Send error:', err);
            alert(err.message || 'حدث خطأ أثناء إرسال الرسالة');
        })
        .finally(() => {
            sendBtn.disabled = false;
            sendBtn.innerHTML = originalIcon;
        });
    });

    function addMessageToChat(data) {
        updateChatBadge(data);

        if (data.conversation_id && data.conversation_id !== conversationId) return;

        const isSent = data.user_id === currentUserId;
        const normalized = (data.attachments || []).map(a => Object.assign({}, a, {
            is_image: !!a.is_image,
            is_video: !!a.is_video,
            is_audio: !!a.is_audio,
            icon: a.icon || 'fa-file',
            human_size: a.human_size || '',
        }));
        const attachmentsHtml = renderAttachments(normalized);
        const contentHtml = data.content
            ? `<p class="message-text">${escapeHtml(data.content)}</p>`
            : '';
        const timeStr = new Date(data.created_at).toLocaleTimeString('ar', { hour: '2-digit', minute: '2-digit' });
        const hasAttachments = normalized.length > 0;

        const messageHtml = `
            <div class="message ${isSent ? 'sent' : 'received'}" data-message-id="${data.id}">
                <div class="message-content">
                    ${!isSent ? `<span class="message-sender">${escapeHtml(data.user.name)}</span>` : ''}
                    ${attachmentsHtml}
                    ${contentHtml}
                    <span class="message-time">
                        ${timeStr}
                        ${hasAttachments ? '<i class="fas fa-paperclip ms-1" title="مرفقات"></i>' : ''}
                    </span>
                </div>
            </div>
        `;

        const emptyState = messagesContainer.querySelector('.empty-chat');
        if (emptyState) emptyState.remove();

        messagesContainer.insertAdjacentHTML('beforeend', messageHtml);
        messagesContainer.scrollTop = messagesContainer.scrollHeight;

        // Re-init player + lightbox for the new message
        initMediaPlayersFor(messagesContainer.lastElementChild);
        initLightboxFor(messagesContainer.lastElementChild);
    }

    function initMediaPlayersFor(scope) {
        if (!scope) return;
        const MediaPlayerCtor = window.MediaPlayer;
        if (!MediaPlayerCtor) return;
        Array.from(scope.querySelectorAll('.media-player')).forEach(root => {
            try {
                window.__mediaPlayers = window.__mediaPlayers || [];
                window.__mediaPlayers.push(new MediaPlayerCtor(root));
            } catch (e) { console.error('MediaPlayer init failed:', e); }
        });
    }

    function initLightboxFor(scope) {
        if (!scope) return;
        const galleries = window.__lightboxGalleries = window.__lightboxGalleries || {};
        Array.from(scope.querySelectorAll('[data-lightbox]')).forEach(a => {
            const key = a.dataset.lightbox;
            (galleries[key] = galleries[key] || []).push({
                src: a.getAttribute('href'),
                title: a.dataset.title || a.title || '',
            });
            a.addEventListener('click', e => {
                e.preventDefault();
                const lb = window.__lightbox;
                if (!lb) return;
                lb.items = galleries[key];
                lb.index = galleries[key].findIndex(i => i.src === a.getAttribute('href'));
                lb.render();
                lb.modal.classList.add('is-open');
                document.body.style.overflow = 'hidden';
            });
        });
    }

    function renderAttachments(attachments) {
        if (!attachments.length) return '';
        let html = '<div class="message-attachments">';
        const messageId = 'live-' + Date.now();
        for (const a of attachments) {
            const url = a.url;
            const downloadUrl = a.download_url;
            if (a.is_image) {
                html += `<a href="${url}" data-lightbox="${messageId}" data-title="${escapeAttr(a.original_name)}" class="msg-attachment msg-attachment-image" title="${escapeAttr(a.original_name)}">
                    <img src="${url}" alt="${escapeAttr(a.original_name)}" loading="lazy">
                    <span class="msg-image-overlay"><i class="fas fa-search-plus"></i></span>
                </a>`;
            } else if (a.is_video) {
                let bars = '';
                for (let i = 0; i < 28; i++) bars += '<span></span>';
                html += `<div class="msg-attachment msg-attachment-video media-player" data-player-type="video" data-src="${url}" data-name="${escapeAttr(a.original_name)}">
                    <div class="media-player-shell">
                        <video class="media-element" preload="metadata" playsinline>
                            <source src="${url}" type="${a.mime_type}">
                        </video>
                        <div class="media-overlay" data-role="overlay">
                            <button type="button" class="media-play-btn" data-role="play"><i class="fas fa-play"></i></button>
                            <span class="media-duration" data-role="duration">0:00</span>
                        </div>
                        <div class="media-controls" data-role="controls">
                            <button type="button" class="media-ctrl-btn" data-role="play"><i class="fas fa-play"></i></button>
                            <span class="media-time" data-role="current">0:00</span>
                            <div class="media-progress" data-role="progress"><div class="media-progress-fill" data-role="progress-fill"></div></div>
                            <span class="media-time" data-role="total">0:00</span>
                            <button type="button" class="media-ctrl-btn" data-role="mute"><i class="fas fa-volume-up"></i></button>
                            <button type="button" class="media-ctrl-btn" data-role="fullscreen"><i class="fas fa-expand"></i></button>
                        </div>
                    </div>
                    <a href="${downloadUrl}" class="msg-attachment-download" target="_blank">
                        <i class="fas fa-download"></i> ${escapeHtml(a.original_name)}
                    </a>
                </div>`;
            } else if (a.is_audio) {
                let bars = '';
                for (let i = 0; i < 28; i++) bars += '<span></span>';
                html += `<div class="msg-attachment msg-attachment-audio media-player" data-player-type="audio" data-src="${url}" data-name="${escapeAttr(a.original_name)}">
                    <button type="button" class="media-audio-play" data-role="play"><i class="fas fa-play"></i></button>
                    <div class="media-audio-bars" data-role="bars">${bars}</div>
                    <div class="media-audio-info">
                        <div class="media-audio-name"><i class="fas fa-music"></i> ${escapeHtml(a.original_name)}</div>
                        <div class="media-audio-meta"><span data-role="current">0:00</span> / <span data-role="total">0:00</span></div>
                    </div>
                    <audio class="media-element" preload="metadata"><source src="${url}" type="${a.mime_type}"></audio>
                    <a href="${downloadUrl}" class="msg-attachment-download" target="_blank" title="تحميل"><i class="fas fa-download"></i></a>
                </div>`;
            } else {
                html += `<a href="${downloadUrl}" class="msg-attachment msg-attachment-file" target="_blank">
                    <span class="msg-attachment-icon"><i class="fas ${a.icon}"></i></span>
                    <span class="msg-attachment-meta">
                        <span class="msg-attachment-name">${escapeHtml(a.original_name)}</span>
                        <span class="msg-attachment-size">${a.human_size}</span>
                    </span>
                    <span class="msg-attachment-download"><i class="fas fa-download"></i></span>
                </a>`;
            }
        }
        html += '</div>';
        return html;
    }

    function escapeHtml(s) {
        if (s == null) return '';
        return String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    }
    function escapeAttr(s) { return escapeHtml(s); }

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
                    if (!existingIds.includes(user.id) && user.id !== currentUserId) {
                        const item = document.createElement('div');
                        item.className = 'search-result-item';
                        item.innerHTML = `
                            <div class="user-avatar-sm" style="background: linear-gradient(135deg, #182B48, #2A4570); color: #d4a030; width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                                <i class="fas fa-user"></i>
                            </div>
                            <div>
                                <div style="font-weight: 600; color: #182B48;">${escapeHtml(user.name)}</div>
                                <div style="font-size: 12px; color: #7f8c8d;">${escapeHtml(user.role)}</div>
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