{{-- resources/views/components/student-progress-chart.blade.php --}}
@props(['studentId', 'velocityData' => []])

<div class="card mb-4">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>@lang('Memorization Velocity')</h5>
    </div>
    <div class="card-body">
        <canvas id="velocityChart{{ $studentId }}" height="80"></canvas>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var ctx = document.getElementById('velocityChart{{ $studentId }}');
    if (!ctx) return;

    var velocityData = @json($velocityData);
    var labels = Object.keys(velocityData);
    var values = Object.values(velocityData);

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: '{{ __('Pages Memorized') }}',
                data: values,
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.1)',
                tension: 0.3,
                fill: true,
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: { display: true, text: '{{ __('Pages') }}' }
                },
                x: {
                    title: { display: true, text: '{{ __('Week') }}' }
                }
            }
        }
    });
});
</script>
@endpush
