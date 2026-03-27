@extends('layouts.app')

@section('title', __('Notifications'))
@section('page-title', __('Notifications'))

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">@lang('Notifications')</h4>
                    @if($notifications->where('is_read', false)->count() > 0)
                        <form action="{{ route('notifications.markAllAsRead') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-primary">
                                @lang('Mark All as Read')
                            </button>
                        </form>
                    @endif
                </div>
                <div class="card-body">
                    {{-- Filter tabs --}}
                    <ul class="nav nav-pills mb-3">
                        <li class="nav-item">
                            <a class="nav-link {{ !request('filter') ? 'active' : '' }}"
                               href="{{ route('notifications.index') }}">@lang('All')</a>
                        </li>
                        @foreach(['warning_draft', 'warning', 'recitation_session', 'attendance', 'milestone'] as $type)
                            <li class="nav-item">
                                <a class="nav-link {{ request('filter') === $type ? 'active' : '' }}"
                                   href="{{ route('notifications.index', ['filter' => $type]) }}">
                                    @lang(ucfirst(str_replace('_', ' ', $type)))
                                </a>
                            </li>
                        @endforeach
                    </ul>

                    {{-- Notification list --}}
                    <div class="list-group">
                        @forelse($notifications as $notification)
                            <a href="{{ route('notifications.markAsRead', $notification->id) }}"
                               class="list-group-item list-group-item-action d-flex gap-3 py-3
                                      {{ !$notification->is_read ? 'bg-light' : '' }}">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1 {{ !$notification->is_read ? 'fw-bold' : '' }}">
                                        {{ $notification->title }}
                                    </h6>
                                    <p class="mb-1 text-muted">{{ $notification->message }}</p>
                                    <small class="text-muted">{{ $notification->created_at->toDateTimeString() }}</small>
                                </div>
                                @if(!$notification->is_read)
                                    <span class="badge bg-primary rounded-pill">@lang('New')</span>
                                @endif
                            </a>
                        @empty
                            <p class="text-muted text-center py-4">@lang('No notifications')</p>
                        @endforelse
                    </div>

                    {{ $notifications->withQueryString()->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
