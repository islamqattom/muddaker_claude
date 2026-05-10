@extends('layouts.app')
@section('title', __('Notifications'))
@section('page-title', __('Notifications'))

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
        <div class="header-icon" style="background: linear-gradient(135deg, #d4a030, #B4802C);">
            <i class="fas fa-bell"></i>
        </div>
        <div class="header-text">
            <h4>الإشعارات</h4>
            <p>عرض وإدارة جميع الإشعارات</p>
        </div>
    </div>
    @if($notifications->where('is_read', false)->count() > 0)
    <form action="{{ route('notifications.markAllAsRead') }}" method="POST" class="d-inline">
        @csrf
        <button type="submit" class="btn" style="background: linear-gradient(135deg, #182B48, #2A4570); color: #fff;">
            <i class="fas fa-check-double me-2"></i>
            @lang('Mark All as Read')
        </button>
    </form>
    @endif
</div>

<div class="filter-card mb-4">
    <ul class="nav filter-tabs" style="gap: 8px; flex-wrap: wrap; list-style: none; padding: 0; margin: 0;">
        <li class="nav-item">
            <a class="nav-link {{ !request('filter') ? 'active' : '' }}"
               href="{{ route('notifications.index') }}">
                <i class="fas fa-list me-1"></i> @lang('All')
            </a>
        </li>
        @foreach(['warning_draft', 'warning', 'recitation_session', 'attendance', 'milestone', 'meeting_task'] as $type)
            <li class="nav-item">
                <a class="nav-link {{ request('filter') === $type ? 'active' : '' }}"
                   href="{{ route('notifications.index', ['filter' => $type]) }}">
                    @if($type === 'warning_draft')
                        <i class="fas fa-edit me-1"></i>
                    @elseif($type === 'warning')
                        <i class="fas fa-exclamation-triangle me-1"></i>
                    @elseif($type === 'recitation_session')
                        <i class="fas fa-microphone me-1"></i>
                    @elseif($type === 'attendance')
                        <i class="fas fa-calendar-check me-1"></i>
                    @elseif($type === 'milestone')
                        <i class="fas fa-trophy me-1"></i>
                    @elseif($type === 'meeting_task')
                        <i class="fas fa-tasks me-1"></i>
                    @endif
                    @lang(ucfirst(str_replace('_', ' ', $type)))
                </a>
            </li>
        @endforeach
    </ul>
</div>

<div class="table-card">
    <div class="table-header">
        <div class="table-title">
            <i class="fas fa-bell"></i>
            <span>جميع الإشعارات</span>
        </div>
        <div class="record-count">
            <i class="fas fa-layer-group"></i>
            <span>{{ $notifications->total() }} إشعار</span>
        </div>
    </div>
    <div class="notif-list-modern">
        @forelse($notifications as $notification)
            <a href="{{ route('notifications.markAsRead', $notification->id) }}"
               class="notif-item-modern {{ !$notification->is_read ? 'unread' : '' }}">
                <div class="notif-icon-wrapper
                    @if($notification->type === 'warning_draft') warning-draft
                    @elseif($notification->type === 'warning') warning
                    @elseif($notification->type === 'recitation_session') recitation
                    @elseif($notification->type === 'attendance') attendance
                    @elseif($notification->type === 'milestone') milestone
                    @elseif($notification->type === 'meeting_task') milestone
                    @endif">
                    @if($notification->type === 'warning_draft')
                        <i class="fas fa-edit"></i>
                    @elseif($notification->type === 'warning')
                        <i class="fas fa-exclamation-triangle"></i>
                    @elseif($notification->type === 'recitation_session')
                        <i class="fas fa-microphone"></i>
                    @elseif($notification->type === 'attendance')
                        <i class="fas fa-calendar-check"></i>
                    @elseif($notification->type === 'milestone')
                        <i class="fas fa-trophy"></i>
                    @elseif($notification->type === 'meeting_task')
                        <i class="fas fa-tasks"></i>
                    @else
                        <i class="fas fa-bell"></i>
                    @endif
                </div>
                <div class="notif-content-modern">
                    <div class="notif-title-row">
                        <h6 class="notif-title-modern">{{ $notification->title }}</h6>
                        @if(!$notification->is_read)
                            <span class="notif-badge-new">جديد</span>
                        @endif
                    </div>
                    <p class="notif-message-modern">{{ $notification->message }}</p>
                    <div class="notif-meta">
                        <span class="notif-time-modern">
                            <i class="fas fa-clock"></i>
                            {{ $notification->created_at->diffForHumans() }}
                        </span>
                        @if($notification->link)
                            <span class="notif-link-hint">
                                <i class="fas fa-external-link-alt"></i>
                                اضغط للمزيد
                            </span>
                        @endif
                    </div>
                </div>
                <div class="notif-arrow">
                    <i class="fas fa-chevron-right"></i>
                </div>
            </a>
        @empty
            <div class="empty-state-notif">
                <div class="empty-icon">
                    <i class="fas fa-bell-slash"></i>
                </div>
                <h5>لا توجد إشعارات</h5>
                <p>ليس لديك أي إشعارات حالياً</p>
            </div>
        @endforelse
    </div>
    @if($notifications->hasPages())
        <div class="table-footer">
            <div class="pagination-wrapper">
                {{ $notifications->withQueryString()->links() }}
            </div>
        </div>
    @endif
</div>
@endsection

@push('styles')
<style>
.notif-list-modern {
    display: flex;
    flex-direction: column;
}

.notif-item-modern {
    display: flex;
    align-items: center;
    gap: 18px;
    padding: 20px 25px;
    border-bottom: 1px solid #f0f4f8;
    text-decoration: none;
    transition: all 0.3s ease;
    position: relative;
}

.notif-item-modern:last-child {
    border-bottom: none;
}

.notif-item-modern:hover {
    background: linear-gradient(135deg, rgba(24, 43, 72, 0.02) 0%, rgba(42, 69, 112, 0.04) 100%);
}

.notif-item-modern.unread {
    background: linear-gradient(135deg, rgba(212, 160, 48, 0.04) 0%, rgba(180, 128, 44, 0.06) 100%);
}

.notif-item-modern.unread::before {
    content: '';
    position: absolute;
    right: 0;
    top: 0;
    bottom: 0;
    width: 4px;
    background: linear-gradient(180deg, #d4a030, #B4802C);
}

.notif-icon-wrapper {
    width: 52px;
    height: 52px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    flex-shrink: 0;
    transition: all 0.3s ease;
}

.notif-icon-wrapper.warning-draft {
    background: linear-gradient(135deg, rgba(142, 68, 173, 0.15), rgba(155, 89, 182, 0.2));
    color: #8e44ad;
}

.notif-icon-wrapper.warning {
    background: linear-gradient(135deg, rgba(192, 57, 43, 0.15), rgba(231, 76, 60, 0.2));
    color: #c0392b;
}

.notif-icon-wrapper.recitation {
    background: linear-gradient(135deg, rgba(39, 174, 96, 0.15), rgba(46, 204, 113, 0.2));
    color: #27ae60;
}

.notif-icon-wrapper.attendance {
    background: linear-gradient(135deg, rgba(52, 152, 219, 0.15), rgba(41, 128, 185, 0.2));
    color: #3498db;
}

.notif-icon-wrapper.milestone {
    background: linear-gradient(135deg, rgba(212, 160, 48, 0.15), rgba(180, 128, 44, 0.2));
    color: #d4a030;
}

.notif-icon-wrapper.meeting_task {
    background: linear-gradient(135deg, rgba(142, 68, 173, 0.15), rgba(125, 60, 152, 0.2));
    color: #8e44ad;
}

.notif-item-modern:hover .notif-icon-wrapper {
    transform: scale(1.05);
}

.notif-content-modern {
    flex: 1;
    min-width: 0;
}

.notif-title-row {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 6px;
}

.notif-title-modern {
    font-size: 15px;
    font-weight: 700;
    color: #182B48;
    margin: 0;
}

.notif-badge-new {
    background: linear-gradient(135deg, #e74c3c, #c0392b);
    color: #fff;
    font-size: 10px;
    font-weight: 700;
    padding: 4px 10px;
    border-radius: 20px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.notif-message-modern {
    font-size: 13px;
    color: #7f8c8d;
    margin: 0 0 10px 0;
    line-height: 1.5;
}

.notif-meta {
    display: flex;
    align-items: center;
    gap: 16px;
}

.notif-time-modern {
    font-size: 12px;
    color: #95a5a6;
    display: flex;
    align-items: center;
    gap: 6px;
}

.notif-time-modern i {
    color: #d4a030;
    font-size: 11px;
}

.notif-link-hint {
    font-size: 11px;
    color: #3498db;
    display: flex;
    align-items: center;
    gap: 5px;
}

.notif-arrow {
    color: #bdc3c7;
    font-size: 14px;
    transition: all 0.3s ease;
}

.notif-item-modern:hover .notif-arrow {
    color: #182B48;
}

.empty-state-notif {
    text-align: center;
    padding: 60px 20px;
}

.empty-state-notif .empty-icon {
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

.empty-state-notif h5 {
    color: #182B48;
    font-size: 18px;
    font-weight: 700;
    margin-bottom: 8px;
}

.empty-state-notif p {
    color: #7f8c8d;
    margin: 0;
}

@media (max-width: 768px) {
    .notif-item-modern {
        padding: 15px 20px;
        gap: 14px;
    }

    .notif-icon-wrapper {
        width: 44px;
        height: 44px;
        font-size: 18px;
    }

    .notif-title-modern {
        font-size: 14px;
    }

    .notif-message-modern {
        font-size: 12px;
    }

    .notif-arrow {
        display: none;
    }
}
</style>
@endpush