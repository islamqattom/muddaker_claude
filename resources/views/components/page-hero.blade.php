@props([
    'icon' => 'fas fa-circle',
])

<div class="page-banner">
    <div class="page-banner-content">
        <div class="page-banner-icon"><i class="{{ $icon }}"></i></div>
        <div class="page-banner-text">
            {{ $slot }}
        </div>
        @isset($actions)
            <div class="page-banner-actions">{{ $actions }}</div>
        @endisset
    </div>
</div>
