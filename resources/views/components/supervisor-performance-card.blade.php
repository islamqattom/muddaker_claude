{{-- resources/views/components/supervisor-performance-card.blade.php --}}
@props(['supervisor', 'perf'])

<div class="supervisor-card">
    <div class="supervisor-header">
        <div class="supervisor-avatar">
            <i class="fas fa-user-tie"></i>
        </div>
        <div class="supervisor-info">
            <h6>{{ $supervisor['name'] }}</h6>
            <span class="role-badge">مشرف</span>
        </div>
    </div>

    <div class="supervisor-stats">
        <div class="stat-item">
            <div class="stat-value">{{ $perf->studentsCount }}</div>
            <div class="stat-label">الطلاب</div>
        </div>
        <div class="stat-item">
            <div class="stat-value">{{ $perf->evaluationsThisMonth }}</div>
            <div class="stat-label">التقييمات</div>
        </div>
        <div class="stat-item">
            <div class="stat-value">{{ $perf->avgScoreThisMonth > 0 ? $perf->avgScoreThisMonth . '%' : '-' }}</div>
            <div class="stat-label">المتوسط</div>
        </div>
    </div>

    <div class="supervisor-chart">
        <canvas id="sparkline{{ $supervisor['supervisor_id'] }}"></canvas>
    </div>
</div>

<style>
.supervisor-card {
    background: #fff;
    border-radius: 16px;
    padding: 20px;
    border: 1px solid #f0f4f8;
    transition: all 0.3s ease;
}

.supervisor-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.08);
    border-color: rgba(212,160,48,0.3);
}

.supervisor-header {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 1px solid #f0f4f8;
}

.supervisor-avatar {
    width: 50px;
    height: 50px;
    background: linear-gradient(135deg, #182B48, #2A4570);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #d4a030;
    font-size: 20px;
}

.supervisor-info {
    flex: 1;
}

.supervisor-info h6 {
    color: #182B48;
    font-size: 15px;
    font-weight: 700;
    margin: 0 0 4px 0;
}

.role-badge {
    background: linear-gradient(135deg, rgba(212,160,48,0.1), rgba(180,128,44,0.1));
    color: #B4802C;
    padding: 3px 10px;
    border-radius: 6px;
    font-size: 11px;
    font-weight: 600;
}

.supervisor-stats {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 10px;
    margin-bottom: 15px;
}

.stat-item {
    text-align: center;
    padding: 10px 5px;
    background: #f8f9fa;
    border-radius: 10px;
}

.stat-value {
    font-size: 18px;
    font-weight: 800;
    color: #182B48;
    margin-bottom: 2px;
}

.stat-label {
    font-size: 10px;
    color: #7f8c8d;
    font-weight: 500;
}

.supervisor-chart {
    height: 50px;
}
</style>

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
                borderColor: '#d4a030',
                borderWidth: 2,
                tension: 0.4,
                pointRadius: 3,
                pointBackgroundColor: '#d4a030',
                fill: true,
                backgroundColor: 'rgba(212,160,48,0.1)',
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
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