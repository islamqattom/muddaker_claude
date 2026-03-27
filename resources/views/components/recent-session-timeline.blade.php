{{-- resources/views/components/recent-session-timeline.blade.php --}}
@props(['sessions' => []])

<div class="card mb-4">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0"><i class="fas fa-history me-2"></i>@lang('Recent Sessions')</h5>
    </div>
    <div class="card-body p-0">
        <ul class="list-group list-group-flush">
            @forelse($sessions as $session)
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <span class="badge bg-secondary me-2">{{ __($session['type'] ?? 'unknown') }}</span>
                        <small class="text-muted">{{ $session['date'] ?? '' }}</small>
                    </div>
                    <div>
                        @php
                            $score = $session['score'] ?? 0;
                            $badgeClass = $score >= 90 ? 'bg-success' : ($score >= 70 ? 'bg-warning text-dark' : 'bg-danger');
                        @endphp
                        <span class="badge {{ $badgeClass }}">{{ round($score) }}%</span>
                    </div>
                </li>
            @empty
                <li class="list-group-item text-muted text-center py-3">@lang('No sessions yet')</li>
            @endforelse
        </ul>
    </div>
</div>
