{{-- resources/views/components/notification-bell.blade.php --}}
@php
    $unreadCount = auth()->user()->notifications()->where('is_read', false)->count();
@endphp

<li class="nav-item dropdown">
    <a class="nav-link position-relative" data-bs-toggle="dropdown" aria-expanded="false" href="#" role="button">
        <i class="fas fa-bell"></i>
        @if($unreadCount > 0)
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                  style="font-size: 0.6rem;"
                  id="notificationBadge">
                {{ $unreadCount > 99 ? '99+' : $unreadCount }}
            </span>
        @endif
    </a>
    <ul class="dropdown-menu dropdown-menu-end" style="min-width: 320px; max-width: 400px;">
        <li class="dropdown-header d-flex justify-content-between align-items-center">
            <span>@lang('Notifications')</span>
            @if($unreadCount > 0)
                <a href="{{ route('notifications.markAllAsRead') }}" class="text-decoration-none small">
                    @lang('Mark all as read')
                </a>
            @endif
        </li>
        <li><hr class="dropdown-divider"></li>
        @forelse(auth()->user()->notifications()->where('is_read', false)->limit(5)->get() as $notification)
            <li>
                <a class="dropdown-item d-flex flex-column py-2"
                   href="{{ route('notifications.markAsRead', $notification->id) }}"
                   style="white-space: normal;">
                    <small class="fw-bold">{{ $notification->title }}</small>
                    <small class="text-muted">{{ Str::limit($notification->message, 80) }}</small>
                    <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                </a>
            </li>
        @empty
            <li>
                <span class="dropdown-item text-muted text-center py-3">@lang('No new notifications')</span>
            </li>
        @endforelse
        <li><hr class="dropdown-divider"></li>
        <li class="text-center">
            <a href="{{ route('notifications.index') }}" class="dropdown-item small">
                @lang('View All Notifications')
            </a>
        </li>
    </ul>
</li>
