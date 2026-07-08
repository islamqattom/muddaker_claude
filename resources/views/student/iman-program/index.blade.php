@extends('layouts.app')

@php
if (! function_exists('thisDayName')) {
    function thisDayName($date) {
        $names = [
            0 => 'الأحد', 1 => 'الإثنين', 2 => 'الثلاثاء', 3 => 'الأربعاء',
            4 => 'الخميس', 5 => 'الجمعة', 6 => 'السبت',
        ];
        return $names[$date->dayOfWeek] ?? '';
    }
}
@endphp

@section('title', 'البرنامج الإيماني')
@section('page-title', 'البرنامج الإيماني')
@section('role-label', 'طالب')

@section('sidebar-menu')
    @include('student.partials.sidebar')
@endsection

@push('styles')
<style>
.iman-page {
    --gold: #d4a030;
    --gold-light: #e6b84d;
    --navy: #182B48;
    --navy-light: #2A4570;
    --success: #27ae60;
    --warning: #f39c12;
    --danger: #c0392b;
    --info: #3498db;
    --radius: 16px;
    --shadow-sm: 0 2px 10px rgba(24,43,72,0.05);
}

.iman-page .page-hero {
    background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
    border-radius: var(--radius);
    padding: 28px 32px;
    margin-bottom: 24px;
    position: relative;
    overflow: hidden;
    color: #fff;
}
.iman-page .page-hero::before {
    content: '';
    position: absolute; inset: 0;
    background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23d4a030' fill-opacity='0.08'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    pointer-events: none;
}
.iman-page .hero-content { position: relative; z-index: 1; display: flex; align-items: center; gap: 18px; }
.iman-page .hero-icon {
    width: 64px; height: 64px;
    background: linear-gradient(135deg, var(--gold), var(--gold-light));
    border-radius: 16px;
    display: flex; align-items: center; justify-content: center;
    font-size: 28px; color: #fff;
    box-shadow: 0 8px 24px rgba(212,160,48,0.4);
}
.iman-page .hero-title { color: #fff; font-size: 24px; font-weight: 700; margin: 0 0 4px 0; }
.iman-page .hero-sub { color: rgba(255,255,255,0.8); font-size: 14px; margin: 0; }
.iman-page .hero-actions { margin-right: auto; display: flex; gap: 10px; flex-wrap: wrap; }
.iman-page .btn-gold {
    background: linear-gradient(135deg, var(--gold), var(--gold-light));
    color: #fff; border: none; padding: 10px 22px; border-radius: 12px;
    font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 8px;
    box-shadow: 0 6px 18px rgba(212,160,48,0.35);
}
.iman-page .btn-gold:hover { color: #fff; transform: translateY(-1px); }
.iman-page .btn-ghost {
    background: rgba(255,255,255,0.12); color: #fff; border: 1px solid rgba(255,255,255,0.25);
    padding: 10px 18px; border-radius: 12px; font-weight: 600; text-decoration: none;
    display: inline-flex; align-items: center; gap: 8px;
}
.iman-page .btn-ghost:hover { background: rgba(255,255,255,0.2); color: #fff; }

.iman-page .stats-grid {
    display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 14px; margin-bottom: 24px;
}
.iman-page .stat-card {
    background: #fff; border-radius: var(--radius); padding: 18px 20px;
    box-shadow: var(--shadow-sm); display: flex; align-items: center; gap: 14px;
    border: 1px solid rgba(24,43,72,0.05);
}
.iman-page .stat-icon {
    width: 52px; height: 52px; border-radius: 12px;
    display: flex; align-items: center; justify-content: center; font-size: 22px; color: #fff;
}
.iman-page .stat-icon.navy { background: linear-gradient(135deg, var(--navy), var(--navy-light)); }
.iman-page .stat-icon.gold { background: linear-gradient(135deg, var(--gold), var(--gold-light)); }
.iman-page .stat-icon.success { background: linear-gradient(135deg, var(--success), #2ecc71); }
.iman-page .stat-icon.info { background: linear-gradient(135deg, var(--info), #5dade2); }
.iman-page .stat-label { font-size: 12px; color: #6b7a90; margin-bottom: 4px; }
.iman-page .stat-value { font-size: 22px; font-weight: 800; color: var(--navy); }

.iman-page .week-nav {
    background: #fff; border-radius: var(--radius); padding: 14px 20px;
    display: flex; align-items: center; justify-content: space-between; gap: 12px;
    box-shadow: var(--shadow-sm); margin-bottom: 20px; flex-wrap: wrap;
}
.iman-page .week-nav .week-label {
    font-weight: 700; color: var(--navy); display: inline-flex; align-items: center; gap: 8px;
}
.iman-page .week-nav .week-buttons { display: flex; gap: 6px; }
.iman-page .week-nav .btn-week {
    background: #f0f4f8; border: none; padding: 8px 14px; border-radius: 10px;
    color: var(--navy); font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;
}
.iman-page .week-nav .btn-week:hover { background: var(--navy); color: #fff; }

.iman-page .program-card {
    background: #fff; border-radius: var(--radius); box-shadow: var(--shadow-sm);
    border: 1px solid rgba(24,43,72,0.05); margin-bottom: 24px; overflow: hidden;
}
.iman-page .program-header {
    background: linear-gradient(135deg, rgba(24,43,72,0.04), rgba(24,43,72,0.02));
    padding: 18px 22px; display: flex; align-items: center; justify-content: space-between; gap: 12px;
    border-bottom: 1px solid #eef1f6; flex-wrap: wrap;
}
.iman-page .program-title { display: flex; align-items: center; gap: 12px; }
.iman-page .program-title h4 { margin: 0; color: var(--navy); font-weight: 800; font-size: 18px; }
.iman-page .program-title small { color: #6b7a90; font-size: 12px; }
.iman-page .program-title .prog-icon {
    width: 42px; height: 42px; border-radius: 10px;
    background: linear-gradient(135deg, var(--gold), var(--gold-light)); color: #fff;
    display: flex; align-items: center; justify-content: center; font-size: 18px;
}
.iman-page .program-actions { display: flex; gap: 8px; flex-wrap: wrap; }
.iman-page .program-actions a, .iman-page .program-actions button {
    background: #f0f4f8; border: none; padding: 8px 14px; border-radius: 10px;
    color: var(--navy); font-weight: 600; text-decoration: none; font-size: 13px;
    display: inline-flex; align-items: center; gap: 6px; cursor: pointer;
}
.iman-page .program-actions a:hover, .iman-page .program-actions button:hover { background: var(--navy); color: #fff; }
.iman-page .program-actions .danger:hover { background: var(--danger); }

.iman-page .iman-table {
    width: 100%; border-collapse: collapse; margin: 0;
}
.iman-page .iman-table th, .iman-page .iman-table td {
    padding: 12px 10px; text-align: center; vertical-align: middle; font-size: 13px;
    border-bottom: 1px solid #eef1f6;
}
.iman-page .iman-table thead th {
    background: #f8f9fc; color: var(--navy); font-weight: 700; font-size: 12px;
    text-align: center;
}
.iman-page .iman-table thead th.day-head { min-width: 90px; }
.iman-page .iman-table thead th.criterion-head { text-align: right; min-width: 180px; }
.iman-page .iman-table tbody td.criterion-cell { text-align: right; }
.iman-page .iman-table .criterion-name { color: var(--navy); font-weight: 700; }
.iman-page .iman-table .criterion-meta { color: #6b7a90; font-size: 11px; }
.iman-page .iman-table .day-label { display: block; font-weight: 700; }
.iman-page .iman-table .day-date { display: block; font-size: 11px; color: #6b7a90; }
.iman-page .iman-table .day-head.today { background: rgba(212,160,48,0.18); color: var(--navy); }
.iman-page .iman-table .today-cell { background: rgba(212,160,48,0.05); }

.iman-page .check-cell {
    width: 38px; height: 38px; border-radius: 10px;
    border: 2px solid #d6dbe5; background: #fff; cursor: pointer;
    display: inline-flex; align-items: center; justify-content: center;
    transition: all 0.2s ease; color: transparent; font-size: 16px;
}
.iman-page .check-cell:hover { border-color: var(--gold); }
.iman-page .check-cell.is-checked {
    background: linear-gradient(135deg, var(--success), #2ecc71);
    border-color: var(--success); color: #fff;
}
.iman-page .check-cell.is-saving { opacity: 0.6; pointer-events: none; }

.iman-page .number-cell {
    width: 90px; padding: 6px 8px; border-radius: 8px;
    border: 1.5px solid #d6dbe5; text-align: center; font-weight: 700;
    color: var(--navy); background: #fff; transition: all 0.2s;
}
.iman-page .number-cell:focus { border-color: var(--gold); outline: none; }
.iman-page .number-cell.is-done {
    background: rgba(39,174,96,0.1); border-color: var(--success); color: var(--success);
}

.iman-page .bulk-save-bar {
    background: rgba(24,43,72,0.04); padding: 14px 22px; display: flex; align-items: center; justify-content: space-between;
    border-top: 1px solid #eef1f6; gap: 10px; flex-wrap: wrap;
}
.iman-page .bulk-save-bar .bulk-info { color: #6b7a90; font-size: 13px; }

.iman-page .empty-state {
    text-align: center; padding: 60px 20px; color: #6b7a90;
}
.iman-page .empty-state i { font-size: 50px; opacity: 0.3; margin-bottom: 14px; }

@media (max-width: 768px) {
    .iman-page .page-hero { padding: 20px 22px; }
    .iman-page .hero-title { font-size: 20px; }
    .iman-page .iman-table th, .iman-page .iman-table td { padding: 8px 6px; font-size: 12px; }
    .iman-page .check-cell { width: 32px; height: 32px; }
    .iman-page .number-cell { width: 70px; }
}
</style>
@endpush

@section('content')
@php
    $today = \Carbon\Carbon::today()->toDateString();
    $progressPercent = $stats['week_total'] > 0 ? round(($stats['week_completed'] / $stats['week_total']) * 100) : 0;
@endphp

<div class="iman-page">
    <div class="page-hero">
        <div class="hero-content">
            <div class="hero-icon"><i class="fas fa-star-and-crescent"></i></div>
            <div>
                <h1 class="hero-title">البرنامج الإيماني</h1>
                <p class="hero-sub">حدد معاييرك اليومية وتابع التزامك على مدار الأسبوع</p>
            </div>
            <div class="hero-actions">
                <a href="{{ route('student.iman-program.create') }}" class="btn-gold">
                    <i class="fas fa-plus-circle"></i> برنامج جديد
                </a>
            </div>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon navy"><i class="fas fa-bookmark"></i></div>
            <div>
                <div class="stat-label">عدد البرامج</div>
                <div class="stat-value">{{ $stats['programs_count'] }}</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon gold"><i class="fas fa-list-check"></i></div>
            <div>
                <div class="stat-label">إجمالي المعايير</div>
                <div class="stat-value">{{ $stats['criteria_count'] }}</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon success"><i class="fas fa-circle-check"></i></div>
            <div>
                <div class="stat-label">منجز هذا الأسبوع</div>
                <div class="stat-value">{{ $stats['week_completed'] }} / {{ $stats['week_total'] }}</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon info"><i class="fas fa-chart-pie"></i></div>
            <div>
                <div class="stat-label">نسبة الإنجاز</div>
                <div class="stat-value">{{ $progressPercent }}%</div>
            </div>
        </div>
    </div>

    <div class="week-nav">
        <div class="week-buttons">
            <a href="{{ route('student.iman-program.index', ['week' => $weekStart->copy()->subWeek()->toDateString()]) }}" class="btn-week">
                <i class="fas fa-chevron-right"></i> الأسبوع السابق
            </a>
        </div>
        <div class="week-label">
            <i class="fas fa-calendar-week" style="color: var(--gold);"></i>
            {{ $weekStart->format('Y-m-d') }} → {{ $weekEnd->format('Y-m-d') }}
        </div>
        <div class="week-buttons">
            <a href="{{ route('student.iman-program.index', ['week' => $weekStart->copy()->addWeek()->toDateString()]) }}" class="btn-week">
                الأسبوع التالي <i class="fas fa-chevron-left"></i>
            </a>
            <a href="{{ route('student.iman-program.index') }}" class="btn-week">
                <i class="fas fa-rotate-left"></i> الحالي
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @forelse($programs as $program)
        <div class="program-card" id="program-{{ $program->id }}">
            <div class="program-header">
                <div class="program-title">
                    <div class="prog-icon"><i class="fas fa-seedling"></i></div>
                    <div>
                        <h4>{{ $program->name }}</h4>
                        <small>
                            @if($program->creator_id === $program->owner_id)
                                أنشأته بنفسك
                            @else
                                أضافه: {{ $program->creator->name ?? '—' }}
                            @endif
                            @if($program->start_date) • يبدأ {{ $program->start_date->format('Y-m-d') }} @endif
                        </small>
                    </div>
                </div>
                <div class="program-actions">
                    <a href="{{ route('student.iman-program.edit', $program) }}">
                        <i class="fas fa-pen"></i> تعديل
                    </a>
                    <form method="POST" action="{{ route('student.iman-program.destroy', $program) }}" onsubmit="return confirm('هل أنت متأكد من حذف هذا البرنامج؟');" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="danger"><i class="fas fa-trash"></i> حذف</button>
                    </form>
                </div>
            </div>

            @if($program->description)
                <div style="padding: 14px 22px; color: #6b7a90; font-size: 14px; border-bottom: 1px solid #eef1f6;">
                    {{ $program->description }}
                </div>
            @endif

            @if($program->criteria->isEmpty())
                <div class="empty-state">
                    <i class="fas fa-clipboard-list"></i>
                    <p>لم يتم إضافة معايير بعد</p>
                    <a href="{{ route('student.iman-program.edit', $program) }}" class="btn-gold" style="margin-top: 12px;">
                        <i class="fas fa-plus"></i> أضف معايير
                    </a>
                </div>
            @else
                <div class="table-responsive">
                    <table class="iman-table">
                        <thead>
                            <tr>
                                <th class="criterion-head">المعيار</th>
                                @foreach($weekDates as $date)
                                    <th class="day-head {{ $date->toDateString() === $today ? 'today' : '' }}">
                                        <span class="day-label">{{ $thisDayName($date) }}</span>
                                        <span class="day-date">{{ $date->format('m-d') }}</span>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($program->criteria as $criterion)
                                <tr>
                                    <td class="criterion-cell">
                                        <div class="criterion-name">{{ $criterion->name }}</div>
                                        <div class="criterion-meta">
                                            @if($criterion->type === 'check')
                                                <i class="fas fa-check-square"></i> علامة (تم/لم يتم)
                                            @else
                                                <i class="fas fa-hashtag"></i> رقم — الهدف: {{ $criterion->target_value ?? 0 }}
                                            @endif
                                        </div>
                                    </td>
                                    @foreach($weekDates as $date)
                                        @php
                                            $dateKey = $date->toDateString();
                                            $key = $program->id . '|' . $criterion->id . '|' . $dateKey;
                                            $entry = $entries->get($key)?->first();
                                            $isToday = $dateKey === $today;
                                        @endphp
                                        <td class="{{ $isToday ? 'today-cell' : '' }}" data-program="{{ $program->id }}" data-criterion="{{ $criterion->id }}" data-date="{{ $dateKey }}">
                                            @if($criterion->type === 'check')
                                                <button type="button" class="check-cell {{ $entry && $entry->is_checked ? 'is-checked' : '' }}"
                                                        data-url="{{ route('student.iman-program.toggle-entry', $program) }}">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            @else
                                                @php
                                                    $currentValue = $entry?->value ?? 0;
                                                    $isDone = $entry && (int) $entry->value >= (int) ($criterion->target_value ?? 0) && (int) $criterion->target_value > 0;
                                                @endphp
                                                <input type="number" min="0" class="number-cell {{ $isDone ? 'is-done' : '' }}"
                                                       value="{{ $currentValue }}"
                                                       data-url="{{ route('student.iman-program.toggle-entry', $program) }}"
                                                       data-target="{{ $criterion->target_value ?? 0 }}">
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    @empty
        <div class="program-card">
            <div class="empty-state">
                <i class="fas fa-star-and-crescent"></i>
                <h5 style="color: var(--navy);">لا توجد برامج إيمانية بعد</h5>
                <p>ابدأ بإنشاء أول برنامج إيماني وحدد معايير التزامك اليومي</p>
                <a href="{{ route('student.iman-program.create') }}" class="btn-gold" style="margin-top: 14px;">
                    <i class="fas fa-plus-circle"></i> أنشئ برنامجك الأول
                </a>
            </div>
        </div>
    @endforelse
</div>
@endsection

@push('scripts')
<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

document.querySelectorAll('.check-cell').forEach(function (btn) {
    btn.addEventListener('click', function () {
        const td = btn.closest('td');
        const url = btn.dataset.url;
        const data = {
            criterion_id: td.dataset.criterion,
            date: td.dataset.date,
            is_checked: !btn.classList.contains('is-checked'),
            _token: csrfToken,
        };
        btn.classList.add('is-saving');
        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: JSON.stringify(data),
        })
        .then(r => r.json())
        .then(json => {
            if (json.success) {
                btn.classList.toggle('is-checked', json.entry.is_checked);
            }
        })
        .catch(() => {})
        .finally(() => btn.classList.remove('is-saving'));
    });
});

document.querySelectorAll('.number-cell').forEach(function (input) {
    let timer = null;
    input.addEventListener('input', function () {
        clearTimeout(timer);
        const td = input.closest('td');
        const url = input.dataset.url;
        const data = {
            criterion_id: td.dataset.criterion,
            date: td.dataset.date,
            value: parseInt(input.value || 0, 10),
            _token: csrfToken,
        };
        timer = setTimeout(() => {
            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify(data),
            })
            .then(r => r.json())
            .then(json => {
                if (json.success) {
                    const target = parseInt(input.dataset.target || 0, 10);
                    input.classList.toggle('is-done', target > 0 && (json.entry.value || 0) >= target);
                }
            })
            .catch(() => {});
        }, 400);
    });
});
</script>
@endpush
