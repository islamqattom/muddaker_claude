{{-- resources/views/components/supervisor-performance-card.blade.php --}}
@props(['supervisor', 'perf'])

<div class="col-md-4 mb-3">
    <div class="card border-primary h-100">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h6 class="mb-0"><i class="fas fa-user-tie me-2"></i>{{ $supervisor['name'] }}</h6>
            @if($perf->pendingWarningDrafts > 0)
                <span class="badge bg-warning text-dark">{{ $perf->pendingWarningDrafts }} @lang('pending')</span>
            @endif
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-6 mb-2">
                    <small class="text-muted d-block">@lang('Students')</small>
                    <strong>{{ $perf->studentsCount }}</strong>
                </div>
                <div class="col-6 mb-2">
                    <small class="text-muted d-block">@lang('Evaluations/Month')</small>
                    <strong>{{ $perf->evaluationsThisMonth }}</strong>
                </div>
                <div class="col-6 mb-2">
                    <small class="text-muted d-block">@lang('Avg Score')</small>
                    <strong>{{ $perf->avgScoreThisMonth > 0 ? $perf->avgScoreThisMonth . '%' : '-' }}</strong>
                </div>
                <div class="col-6 mb-2">
                    <small class="text-muted d-block">@lang('Response Time')</small>
                    <strong>{{ $perf->avgResponseTimeDays !== null ? $perf->avgResponseTimeDays . 'd' : '-' }}</strong>
                </div>
            </div>
            <canvas id="sparkline{{ $supervisor['supervisor_id'] }}" height="40" class="mt-2"></canvas>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var ctx = document.getElementById('sparkline{{ $supervisor['supervisor_id'] }}');
    if (!ctx) return;
    var trendData = @json(array_values($perf->monthlyTrend));
    var trendLabels = Object.keys(@json($perf->monthlyTrend));
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: trendLabels,
            datasets: [{
                data: trendData,
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 2,
                tension: 0.3,
                pointRadius: 2,
                fill: true,
                backgroundColor: 'rgba(75, 192, 192, 0.1)',
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                x: { display: false },
                y: { display: false }
            }
        }
    });
});
</script>
@endpush
