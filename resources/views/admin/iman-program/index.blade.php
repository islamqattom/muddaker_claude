@extends('layouts.app')

@section('title', 'البرنامج الإيماني')
@section('page-title', $scope === 'self' ? 'برنامجي الإيماني' : 'إدارة البرامج الإيمانية')
@section('role-label', 'إدارة')

@section('sidebar-menu')
    @include('admin.partials.sidebar')
@endsection

@push('styles')
<style>
.check-cell {
        background: #fff !important;
        border-color: #d6dbe5 !important;
        color: transparent !important;
    }
.check-cell:hover { border-color: #d4a030 !important; }
.check-cell.is-checked {
        background: linear-gradient(135deg, #27ae60, #2ecc71) !important;
        border-color: #27ae60 !important;
        color: #fff !important;
    }
.check-cell.is-saving { opacity: 0.6; pointer-events: none; }
.check-cell-static {
        background: #f0f4f8 !important;
        color: transparent !important;
    }
.check-cell-static.is-checked {
        background: linear-gradient(135deg, #27ae60, #2ecc71) !important;
        color: #fff !important;
    }
.number-cell {
        background: #fff !important;
        border-color: #d6dbe5 !important;
        color: #182B48 !important;
    }
.number-cell:focus { border-color: #d4a030 !important; outline: none; }
.number-cell.is-done {
        background: rgba(39,174,96,0.1) !important;
        border-color: #27ae60 !important;
        color: #27ae60 !important;
    }
.number-cell-static {
        background: #f0f4f8 !important;
        color: #6b7a90 !important;
    }
.number-cell-static.is-done {
        background: rgba(39,174,96,0.1) !important;
        color: #27ae60 !important;
    }
</style>
@endpush

@section('content')
@php $today = \Carbon\Carbon::today()->toDateString(); @endphp

<div style="background:linear-gradient(135deg,#182B48 0%,#2A4570 100%);border-radius:14px;padding:16px 22px;margin-bottom:16px;color:#fff;display:flex;align-items:center;gap:18px;flex-wrap:wrap">
    <div style="width:38px;height:38px;background:linear-gradient(135deg,#d4a030,#e6b84d);border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:16px">
        <i class="fas fa-star-and-crescent"></i>
    </div>
    <div>
        <h1 style="margin:0;font-size:22px;font-weight:700">{{ $scope === 'self' ? 'برنامجي الإيماني' : 'إدارة البرامج الإيمانية' }}</h1>
        <p style="margin:0;color:rgba(255,255,255,0.8);font-size:13px">{{ $scope === 'self' ? 'حدد معاييرك الشخصية وتابع التزامك اليومي' : 'إنشاء ومتابعة البرامج الإيمانية للطلاب' }}</p>
    </div>
    @if($scope === 'self' || $student)
        <div style="margin-right:auto;display:flex;gap:10px;flex-wrap:wrap">
            <a href="{{ route('admin.iman-program.bulk-assign') }}"
               style="background:rgba(255,255,255,0.15);color:#fff;border:1px solid rgba(255,255,255,0.3);padding:10px 16px;border-radius:12px;font-weight:700;text-decoration:none;display:inline-flex;align-items:center;gap:8px">
                <i class="fas fa-bullhorn"></i> تعيين جماعي
            </a>
            <a href="{{ $scope === 'self' ? route('admin.iman-program.create', ['scope' => 'self']) : route('admin.iman-program.create', ['scope' => 'student', 'student_id' => $student->id]) }}"
               style="background:linear-gradient(135deg,#d4a030,#e6b84d);color:#fff;border:none;padding:10px 16px;border-radius:12px;font-weight:700;text-decoration:none;display:inline-flex;align-items:center;gap:8px">
                <i class="fas fa-plus-circle"></i> برنامج جديد
            </a>
        </div>
    @else
        <div style="margin-right:auto">
            <a href="{{ route('admin.iman-program.bulk-assign') }}"
               style="background:linear-gradient(135deg,#d4a030,#e6b84d);color:#fff;border:none;padding:10px 16px;border-radius:12px;font-weight:700;text-decoration:none;display:inline-flex;align-items:center;gap:8px">
                <i class="fas fa-bullhorn"></i> تعيين جماعي
            </a>
        </div>
    @endif
</div>

<div style="background:#fff;border-radius:13px;padding:14px 16px;box-shadow:0 2px 10px rgba(24,43,72,0.05);margin-bottom:16px;display:flex;gap:8px;flex-wrap:wrap">
    <a href="{{ route('admin.iman-program.index', ['scope' => 'self']) }}"
       style="padding:10px 16px;border-radius:10px;font-weight:700;text-decoration:none;display:inline-flex;align-items:center;gap:8px;{{ $scope === 'self' ? 'background:linear-gradient(135deg,#182B48,#2A4570);color:#fff;':'background:#f0f4f8;color:#182B48;' }}">
        <i class="fas fa-user"></i> برنامجي الشخصي
    </a>
    <a href="{{ route('admin.iman-program.index', ['scope' => 'student']) }}"
       style="padding:10px 16px;border-radius:10px;font-weight:700;text-decoration:none;display:inline-flex;align-items:center;gap:8px;{{ $scope === 'student' ? 'background:linear-gradient(135deg,#182B48,#2A4570);color:#fff;':'background:#f0f4f8;color:#182B48;' }}">
        <i class="fas fa-user-graduate"></i> برامج الطلاب
    </a>
    <a href="{{ route('admin.iman-program.index', ['scope' => 'reports']) }}"
       style="padding:10px 16px;border-radius:10px;font-weight:700;text-decoration:none;display:inline-flex;align-items:center;gap:8px;{{ $scope === 'reports' ? 'background:linear-gradient(135deg,#182B48,#2A4570);color:#fff;':'background:#f0f4f8;color:#182B48;' }}">
        <i class="fas fa-chart-pie"></i> تقارير الطلاب
    </a>
</div>

@if($scope === 'student')
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:14px;margin-bottom:16px">
        <div style="background:#fff;border-radius:13px;padding:16px;box-shadow:0 2px 10px rgba(24,43,72,0.05);display:flex;align-items:center;gap:14px">
            <div style="width:38px;height:38px;background:linear-gradient(135deg,#182B48,#2A4570);border-radius:10px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:16px"><i class="fas fa-bookmark"></i></div>
            <div><div style="font-size:12px;color:#6b7a90">إجمالي البرامج</div><div style="font-size:22px;font-weight:800;color:#182B48">{{ $stats['total_programs'] }}</div></div>
        </div>
        <div style="background:#fff;border-radius:13px;padding:16px;box-shadow:0 2px 10px rgba(24,43,72,0.05);display:flex;align-items:center;gap:14px">
            <div style="width:38px;height:38px;background:linear-gradient(135deg,#d4a030,#e6b84d);border-radius:10px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:16px"><i class="fas fa-list-check"></i></div>
            <div><div style="font-size:12px;color:#6b7a90">إجمالي المعايير</div><div style="font-size:22px;font-weight:800;color:#182B48">{{ $stats['total_criteria'] }}</div></div>
        </div>
        <div style="background:#fff;border-radius:13px;padding:16px;box-shadow:0 2px 10px rgba(24,43,72,0.05);display:flex;align-items:center;gap:14px">
            <div style="width:38px;height:38px;background:linear-gradient(135deg,#27ae60,#2ecc71);border-radius:10px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:16px"><i class="fas fa-circle-check"></i></div>
            <div><div style="font-size:12px;color:#6b7a90">إجمالي الإدخالات</div><div style="font-size:22px;font-weight:800;color:#182B48">{{ $stats['total_entries'] }}</div></div>
        </div>
        <div style="background:#fff;border-radius:13px;padding:16px;box-shadow:0 2px 10px rgba(24,43,72,0.05);display:flex;align-items:center;gap:14px">
            <div style="width:38px;height:38px;background:linear-gradient(135deg,#3498db,#5dade2);border-radius:10px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:16px"><i class="fas fa-user-graduate"></i></div>
            <div><div style="font-size:12px;color:#6b7a90">طلاب لديهم برامج</div><div style="font-size:22px;font-weight:800;color:#182B48">{{ $stats['students_with_programs'] }}</div></div>
        </div>
    </div>
@endif

@if($scope === 'student')
    <div style="background:#fff;border-radius:13px;padding:16px 16px;box-shadow:0 2px 10px rgba(24,43,72,0.05);margin-bottom:16px">
        <form method="GET" action="{{ route('admin.iman-program.index') }}" class="row g-3 align-items-end">
            <input type="hidden" name="scope" value="student">
            <div class="col-md-7">
                <label class="form-label">اختر طالباً</label>
                <select name="student_id" class="form-select" onchange="this.form.submit()">
                    <option value="">— اختر طالباً —</option>
                    @foreach($students as $s)
                        <option value="{{ $s->id }}" {{ $studentFilter == $s->id ? 'selected' : '' }}>
                            {{ $s->user->name }} {{ $s->student_number ? '(' . $s->student_number . ')' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">الأسبوع</label>
                <input type="date" name="week" class="form-control" value="{{ $weekStart->toDateString() }}">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search"></i> عرض</button>
            </div>
        </form>
    </div>
@else
    <div style="background:#fff;border-radius:13px;padding:16px 16px;box-shadow:0 2px 10px rgba(24,43,72,0.05);margin-bottom:16px">
        <form method="GET" action="{{ route('admin.iman-program.index') }}" class="row g-3 align-items-end">
            <input type="hidden" name="scope" value="self">
            <div class="col-md-9">
                <label class="form-label">الأسبوع</label>
                <input type="date" name="week" class="form-control" value="{{ $weekStart->toDateString() }}">
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search"></i> عرض</button>
            </div>
        </form>
    </div>
@endif

@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

@if($scope === 'student' && !$student)
    <div style="background:#fff;border-radius:13px;padding:40px 16px;text-align:center;color:#6b7a90;box-shadow:0 2px 10px rgba(24,43,72,0.05)">
        <i class="fas fa-user-graduate" style="font-size:50px;opacity:0.3"></i>
        <h5 style="color:#182B48;margin-top:14px">اختر طالباً لعرض/إنشاء برنامجه الإيماني</h5>
    </div>
@elseif($scope === 'reports')
    @if($studentReports->isEmpty())
        <div style="background:#fff;border-radius:13px;padding:40px 16px;text-align:center;color:#6b7a90;box-shadow:0 2px 10px rgba(24,43,72,0.05)">
            <i class="fas fa-chart-pie" style="font-size:50px;opacity:0.3"></i>
            <h5 style="color:#182B48;margin-top:14px">لا يوجد طلاب لديهم برامج إيمانية بعد</h5>
        </div>
    @else
        <div style="background:#fff;border-radius:13px;padding:16px;box-shadow:0 2px 10px rgba(24,43,72,0.05);margin-bottom:16px">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;flex-wrap:wrap;gap:12px">
                <h5 style="color:#182B48;font-weight:700;margin:0;display:flex;align-items:center;gap:10px">
                    <i class="fas fa-chart-area" style="color:#d4a030"></i> إجمالي نقاط الطلاب خلال آخر 30 يوم
                </h5>
                <div id="listChartToggle" style="display:inline-flex;background:#f0f4f8;border-radius:10px;padding:4px;gap:4px">
                    <button type="button" data-range="week"
                            style="padding:7px 16px;border:none;border-radius:8px;font-weight:700;font-size:12px;cursor:pointer;background:linear-gradient(135deg,#182B48,#2A4570);color:#fff">
                        أسبوعي
                    </button>
                    <button type="button" data-range="month"
                            style="padding:7px 16px;border:none;border-radius:8px;font-weight:700;font-size:12px;cursor:pointer;background:transparent;color:#182B48">
                        شهري
                    </button>
                </div>
            </div>
            <div style="position:relative;height:220px">
                <canvas id="aggregateAreaChart"></canvas>
            </div>
        </div>
        <div style="background:#fff;border-radius:13px;padding:16px;box-shadow:0 2px 10px rgba(24,43,72,0.05);overflow-x:auto">
            <table style="width:100%;border-collapse:collapse">
                <thead>
                    <tr style="background:linear-gradient(135deg,#182B48,#2A4570);color:#fff">
                        <th style="padding:14px 16px;text-align:right;font-weight:700;font-size:13px;border-top-right-radius:10px">الطالب</th>
                        <th style="padding:14px 16px;text-align:right;font-weight:700;font-size:13px">المشرف</th>
                        <th style="padding:14px 16px;text-align:center;font-weight:700;font-size:13px">البرامج</th>
                        <th style="padding:14px 16px;text-align:center;font-weight:700;font-size:13px">المعايير</th>
                        <th style="padding:14px 16px;text-align:center;font-weight:700;font-size:13px">الإدخالات</th>
                        <th style="padding:14px 16px;text-align:center;font-weight:700;font-size:13px">آخر إدخال</th>
                        <th style="padding:14px 16px;text-align:center;font-weight:700;font-size:13px">نسبة الإنجاز</th>
                        <th style="padding:14px 16px;text-align:center;font-weight:700;font-size:13px;border-top-left-radius:10px">إجراء</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($studentReports as $report)
                        <tr style="border-bottom:1px solid #f0f4f8;transition:background 0.2s" onmouseover="this.style.background='#fafbfc'" onmouseout="this.style.background='#fff'">
                            <td style="padding:14px 16px">
                                <div style="display:flex;align-items:center;gap:10px">
                                    <div style="width:38px;height:38px;border-radius:10px;background:linear-gradient(135deg,#d4a030,#e6b84d);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:16px;flex-shrink:0">
                                        {{ mb_substr($report->student->user->name ?? '؟', 0, 1) }}
                                    </div>
                                    <div>
                                        <div style="font-weight:700;color:#182B48;font-size:14px">{{ $report->student->user->name ?? '—' }}</div>
                                        @if($report->student->student_number)
                                            <div style="font-size:11px;color:#6b7a90">#{{ $report->student->student_number }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td style="padding:14px 16px;color:#2c3e50;font-size:13px">{{ $report->student->supervisor->name ?? '—' }}</td>
                            <td style="padding:14px 16px;text-align:center;font-weight:700;color:#182B48">{{ $report->programs_count }}</td>
                            <td style="padding:14px 16px;text-align:center;font-weight:700;color:#182B48">{{ $report->criteria_count }}</td>
                            <td style="padding:14px 16px;text-align:center;font-weight:700;color:#182B48">{{ $report->entries_count }}</td>
                            <td style="padding:14px 16px;text-align:center;font-size:12px;color:#6b7a90">{{ $report->last_entry_date ? \Carbon\Carbon::parse($report->last_entry_date)->format('Y/m/d') : 'لا يوجد' }}</td>
                            <td style="padding:14px 16px;text-align:center">
                                @php
                                    $c = $report->completion;
                                    $color = $c >= 70 ? '#27ae60' : ($c >= 40 ? '#f39c12' : '#c0392b');
                                @endphp
                                <div style="display:flex;align-items:center;gap:8px;justify-content:center">
                                    <div style="flex:1;max-width:100px;height:8px;background:#f0f4f8;border-radius:4px;overflow:hidden">
                                        <div style="height:100%;background:{{ $color }};width:{{ $c }}%"></div>
                                    </div>
                                    <span style="font-weight:700;color:{{ $color }};font-size:13px;min-width:40px">{{ $c }}%</span>
                                </div>
                            </td>
                            <td style="padding:14px 16px;text-align:center">
                                <a href="{{ route('admin.iman-program.student-report', $report->student->id) }}"
                                   style="display:inline-flex;align-items:center;gap:6px;background:linear-gradient(135deg,#182B48,#2A4570);color:#fff;padding:8px 14px;border-radius:10px;font-weight:700;font-size:12px;text-decoration:none">
                                    <i class="fas fa-chart-line"></i> عرض التقرير
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
@else
    @forelse($programs as $program)
        <x-iman-program-card
            :program="$program"
            :week-dates="$weekDates"
            :entries="$entries"
            :today="$today"
            :edit-route="route('admin.iman-program.edit', $program)"
            :delete-route="route('admin.iman-program.destroy', $program)"
            :toggle-route="route('admin.iman-program.toggle-entry', $program)"
            :can-manage="true"
            :can-edit="true" />
    @empty
        <div style="background:#fff;border-radius:13px;padding:40px 16px;text-align:center;color:#6b7a90;box-shadow:0 2px 10px rgba(24,43,72,0.05)">
            <i class="fas fa-star-and-crescent" style="font-size:50px;opacity:0.3"></i>
            <h5 style="color:#182B48;margin-top:14px">{{ $scope === 'self' ? 'لا توجد برامج شخصية بعد' : 'لا توجد برامج إيمانية لهذا الطالب' }}</h5>
            <a href="{{ $scope === 'self' ? route('admin.iman-program.create', ['scope' => 'self']) : route('admin.iman-program.create', ['scope' => 'student', 'student_id' => $student->id]) }}"
               style="display:inline-block;margin-top:14px;background:linear-gradient(135deg,#d4a030,#e6b84d);color:#fff;padding:12px 16px;border-radius:12px;font-weight:700;text-decoration:none">
                <i class="fas fa-plus-circle"></i> {{ $scope === 'self' ? 'أنشئ برنامجك الشخصي' : 'أنشئ أول برنامج للطالب' }}
            </a>
        </div>
    @endforelse
@endif
@endsection

@push('scripts')
<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
document.querySelectorAll('.check-cell').forEach(function (btn) {
    btn.addEventListener('click', function () {
        const td = btn.closest('td');
        const data = { criterion_id: td.dataset.criterion, date: td.dataset.date, is_checked: !btn.classList.contains('is-checked') };
        btn.classList.add('is-saving');
        fetch(btn.dataset.url, { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }, body: JSON.stringify(data) })
        .then(r => r.json()).then(json => { if (json.success) btn.classList.toggle('is-checked', json.entry.is_checked); })
        .catch(() => {}).finally(() => btn.classList.remove('is-saving'));
    });
});
document.querySelectorAll('.number-cell').forEach(function (input) {
    let timer = null;
    input.addEventListener('input', function () {
        clearTimeout(timer);
        const td = input.closest('td');
        const data = { criterion_id: td.dataset.criterion, date: td.dataset.date, value: parseInt(input.value || 0, 10) };
        timer = setTimeout(() => {
            fetch(input.dataset.url, { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }, body: JSON.stringify(data) })
            .then(r => r.json()).then(json => {
                if (json.success) {
                    const target = parseInt(input.dataset.target || 0, 10);
                    input.classList.toggle('is-done', target > 0 && (json.entry.value || 0) >= target);
                }
            }).catch(() => {});
        }, 400);
    });
});
@if($scope === 'reports' && $studentReports->isNotEmpty() && count($aggregateDailyPoints) > 1)
(function () {
    const allData = @json($aggregateDailyPoints);
    const weekData = allData.slice(-7);
    const monthData = allData;

    const ctx = document.getElementById('aggregateAreaChart');
    if (!ctx) return;

    const gradient = ctx.getContext('2d').createLinearGradient(0, 0, 0, 220);
    gradient.addColorStop(0, 'rgba(39, 174, 96, 0.45)');
    gradient.addColorStop(1, 'rgba(39, 174, 96, 0.02)');

    const gradientLine = ctx.getContext('2d').createLinearGradient(0, 0, 0, 220);
    gradientLine.addColorStop(0, 'rgba(39, 174, 96, 1)');
    gradientLine.addColorStop(1, 'rgba(33, 154, 82, 1)');

    let chart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: monthData.map(d => d.label),
            datasets: [{
                label: 'إجمالي النقاط اليومية',
                data: monthData.map(d => d.points),
                borderColor: gradientLine,
                backgroundColor: gradient,
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointRadius: monthData.length > 20 ? 2 : 4,
                pointHoverRadius: 6,
                pointBackgroundColor: '#fff',
                pointBorderColor: '#27ae60',
                pointBorderWidth: 2,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: 'rgba(24, 43, 72, 0.95)',
                    titleColor: '#27ae60',
                    bodyColor: '#fff',
                    borderColor: '#27ae60',
                    borderWidth: 1,
                    rtl: true,
                    padding: 10,
                    callbacks: {
                        title: function (items) {
                            return monthData[items[0].dataIndex].date;
                        },
                        label: function (context) {
                            return ' النقاط: ' + context.parsed.y;
                        },
                    },
                },
            },
            scales: {
                x: {
                    grid: { color: 'rgba(24,43,72,0.04)' },
                    ticks: { color: '#6b7a90', font: { size: 10 } },
                },
                y: {
                    beginAtZero: true,
                    position: 'right',
                    grid: { color: 'rgba(24,43,72,0.05)' },
                    ticks: { color: '#6b7a90', precision: 0 },
                },
            },
        },
    });

    function switchRange(range) {
        const data = range === 'week' ? weekData : monthData;
        chart.data.labels = data.map(d => d.label);
        chart.data.datasets[0].data = data.map(d => d.points);
        chart.data.datasets[0].pointRadius = data.length > 20 ? 2 : 4;
        chart.update();
    }

    document.querySelectorAll('#listChartToggle button').forEach(btn => {
        btn.addEventListener('click', function () {
            const range = this.dataset.range;
            document.querySelectorAll('#listChartToggle button').forEach(b => {
                b.style.background = 'transparent';
                b.style.color = '#182B48';
            });
            this.style.background = 'linear-gradient(135deg,#182B48,#2A4570)';
            this.style.color = '#fff';
            switchRange(range);
        });
    });
})();
@endif
</script>
@endpush
