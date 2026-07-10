@extends('layouts.app')

@section('title', 'البرنامج الإيماني')
@section('page-title', $scope === 'self' ? 'برنامجي الإيماني' : 'البرنامج الإيماني - ' . ($student?->user->name ?? 'اختر طالباً'))
@section('role-label', 'مشرف')

@section('sidebar-menu')
    @include('supervisor.partials.sidebar')
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
    .number-cell.is-saving { opacity: 0.6; pointer-events: none; }
    .save-table-btn:hover { opacity: 0.85; }
    .save-table-btn:active { transform: scale(0.95); }
    .save-table-btn.is-saving { opacity: 0.6; pointer-events: none; }
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
@php
    $today = \Carbon\Carbon::today()->toDateString();
    $progressPercent = $stats['week_total'] > 0 ? round(($stats['week_completed'] / $stats['week_total']) * 100) : 0;
@endphp

<div style="background:linear-gradient(135deg,#182B48 0%,#2A4570 100%);border-radius:16px;padding:24px 30px;margin-bottom:24px;color:#fff;display:flex;align-items:center;gap:18px;flex-wrap:wrap;">
    <div style="width:60px;height:60px;background:linear-gradient(135deg,#d4a030,#e6b84d);border-radius:14px;display:flex;align-items:center;justify-content:center;font-size:26px;">
        <i class="fas fa-star-and-crescent"></i>
    </div>
    <div>
        <h1 style="margin:0;font-size:22px;font-weight:700;">{{ $scope === 'self' ? 'برنامجي الإيماني' : 'البرنامج الإيماني للطلاب' }}</h1>
        <p style="margin:0;color:rgba(255,255,255,0.8);font-size:13px;">{{ $scope === 'self' ? 'حدد معاييرك الشخصية وتابع التزامك اليومي' : 'اختر طالباً لإضافة/متابعة برنامجه الإيماني' }}</p>
    </div>
    @if($scope === 'self' || $student)
        <div style="margin-right:auto;display:flex;gap:10px;flex-wrap:wrap;">
            @if($scope === 'student' && $student)
                <a href="{{ route('supervisor.iman-program.student-report', $student->id) }}"
                   style="background:rgba(255,255,255,0.15);color:#fff;border:1px solid rgba(255,255,255,0.25);padding:10px 18px;border-radius:10px;font-weight:700;text-decoration:none;display:inline-flex;align-items:center;gap:8px;">
                    <i class="fas fa-chart-pie"></i> تقرير الطالب
                </a>
            @endif
            <a href="{{ $scope === 'self' ? route('supervisor.iman-program.create', ['scope' => 'self']) : route('supervisor.iman-program.create', ['scope' => 'student', 'student_id' => $student->id]) }}"
               style="background:linear-gradient(135deg,#d4a030,#e6b84d);color:#fff;border:none;padding:10px 22px;border-radius:12px;font-weight:700;text-decoration:none;display:inline-flex;align-items:center;gap:8px;">
                <i class="fas fa-plus-circle"></i> برنامج جديد
            </a>
        </div>
    @endif
</div>

<div style="background:#fff;border-radius:16px;padding:14px 22px;box-shadow:0 2px 10px rgba(24,43,72,0.05);margin-bottom:20px;display:flex;gap:8px;flex-wrap:wrap;">
    <a href="{{ route('supervisor.iman-program.index', ['scope' => 'self']) }}"
       style="padding:10px 20px;border-radius:10px;font-weight:700;text-decoration:none;display:inline-flex;align-items:center;gap:8px;{{ $scope === 'self' ? 'background:linear-gradient(135deg,#182B48,#2A4570);color:#fff;' : 'background:#f0f4f8;color:#182B48;' }}">
        <i class="fas fa-user"></i> برنامجي الشخصي
    </a>
    <a href="{{ route('supervisor.iman-program.index', ['scope' => 'student']) }}"
       style="padding:10px 20px;border-radius:10px;font-weight:700;text-decoration:none;display:inline-flex;align-items:center;gap:8px;{{ $scope === 'student' ? 'background:linear-gradient(135deg,#182B48,#2A4570);color:#fff;' : 'background:#f0f4f8;color:#182B48;' }}">
        <i class="fas fa-user-graduate"></i> برامج الطلاب
    </a>
    <a href="{{ route('supervisor.iman-program.reports.students') }}"
       style="padding:10px 20px;border-radius:10px;font-weight:700;text-decoration:none;display:inline-flex;align-items:center;gap:8px;background:#f0f4f8;color:#182B48;">
        <i class="fas fa-chart-column"></i> تقرير الطلاب
    </a>
    <a href="{{ route('supervisor.iman-program.reports.self') }}"
       style="padding:10px 20px;border-radius:10px;font-weight:700;text-decoration:none;display:inline-flex;align-items:center;gap:8px;background:#f0f4f8;color:#182B48;">
        <i class="fas fa-user-check"></i> تقريري
    </a>
</div>

@if($scope === 'student')
    <div style="background:#fff;border-radius:16px;padding:18px 22px;box-shadow:0 2px 10px rgba(24,43,72,0.05);margin-bottom:20px;">
        <form method="GET" action="{{ route('supervisor.iman-program.index') }}" class="row g-3 align-items-end">
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
    <div style="background:#fff;border-radius:16px;padding:18px 22px;box-shadow:0 2px 10px rgba(24,43,72,0.05);margin-bottom:20px;">
        <form method="GET" action="{{ route('supervisor.iman-program.index') }}" class="row g-3 align-items-end">
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

@if($scope === 'self' || $student)
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:14px;margin-bottom:20px;">
        <div style="background:#fff;border-radius:16px;padding:18px;box-shadow:0 2px 10px rgba(24,43,72,0.05);display:flex;align-items:center;gap:14px;">
            <div style="width:50px;height:50px;background:linear-gradient(135deg,#182B48,#2A4570);border-radius:12px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:22px;"><i class="fas fa-bookmark"></i></div>
            <div><div style="font-size:12px;color:#6b7a90;">البرامج</div><div style="font-size:22px;font-weight:800;color:#182B48;">{{ $stats['programs_count'] }}</div></div>
        </div>
        <div style="background:#fff;border-radius:16px;padding:18px;box-shadow:0 2px 10px rgba(24,43,72,0.05);display:flex;align-items:center;gap:14px;">
            <div style="width:50px;height:50px;background:linear-gradient(135deg,#d4a030,#e6b84d);border-radius:12px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:22px;"><i class="fas fa-list-check"></i></div>
            <div><div style="font-size:12px;color:#6b7a90;">المعايير</div><div style="font-size:22px;font-weight:800;color:#182B48;">{{ $stats['criteria_count'] }}</div></div>
        </div>
        <div style="background:#fff;border-radius:16px;padding:18px;box-shadow:0 2px 10px rgba(24,43,72,0.05);display:flex;align-items:center;gap:14px;">
            <div style="width:50px;height:50px;background:linear-gradient(135deg,#27ae60,#2ecc71);border-radius:12px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:22px;"><i class="fas fa-circle-check"></i></div>
            <div><div style="font-size:12px;color:#6b7a90;">منجز هذا الأسبوع</div><div style="font-size:22px;font-weight:800;color:#182B48;">{{ $stats['week_completed'] }} / {{ $stats['week_total'] }}</div></div>
        </div>
        <div style="background:#fff;border-radius:16px;padding:18px;box-shadow:0 2px 10px rgba(24,43,72,0.05);display:flex;align-items:center;gap:14px;">
            <div style="width:50px;height:50px;background:linear-gradient(135deg,#3498db,#5dade2);border-radius:12px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:22px;"><i class="fas fa-chart-pie"></i></div>
            <div><div style="font-size:12px;color:#6b7a90;">نسبة الإنجاز</div><div style="font-size:22px;font-weight:800;color:#182B48;">{{ $progressPercent }}%</div></div>
        </div>
    </div>
@endif

@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

@if($scope === 'student' && !$student)
    <div style="background:#fff;border-radius:16px;padding:60px 20px;text-align:center;color:#6b7a90;box-shadow:0 2px 10px rgba(24,43,72,0.05);">
        <i class="fas fa-user-graduate" style="font-size:50px;opacity:0.3;"></i>
        <h5 style="color:#182B48;margin-top:14px;">اختر طالباً للبدء</h5>
        <p>قم باختيار طالب من القائمة أعلاه لإضافة/متابعة برنامجه الإيماني.</p>
    </div>
@else
    @forelse($programs as $program)
        <x-iman-program-card
            :program="$program"
            :week-dates="$weekDates"
            :entries="$entries"
            :today="$today"
            :edit-route="route('supervisor.iman-program.edit', $program)"
            :delete-route="route('supervisor.iman-program.destroy', $program)"
            :toggle-route="route('supervisor.iman-program.toggle-entry', $program)"
            :criteria-route="$program->owner_type === 'student' ? route('supervisor.iman-program.manage-criteria', $program) : null"
            :can-manage="true"
            :can-edit="true"
            :show-save-button="true" />
    @empty
        <div style="background:#fff;border-radius:16px;padding:60px 20px;text-align:center;color:#6b7a90;box-shadow:0 2px 10px rgba(24,43,72,0.05);">
            <i class="fas fa-star-and-crescent" style="font-size:50px;opacity:0.3;"></i>
            <h5 style="color:#182B48;margin-top:14px;">{{ $scope === 'self' ? 'لا توجد برامج شخصية بعد' : 'لا توجد برامج إيمانية لهذا الطالب' }}</h5>
            <a href="{{ $scope === 'self' ? route('supervisor.iman-program.create', ['scope' => 'self']) : route('supervisor.iman-program.create', ['scope' => 'student', 'student_id' => $student->id]) }}"
               style="display:inline-block;margin-top:14px;background:linear-gradient(135deg,#d4a030,#e6b84d);color:#fff;padding:12px 24px;border-radius:12px;font-weight:700;text-decoration:none;">
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
        const willBeChecked = !btn.classList.contains('is-checked');
        btn.classList.toggle('is-checked', willBeChecked);
        btn.classList.add('is-saving');
        const data = { criterion_id: td.dataset.criterion, date: td.dataset.date, is_checked: willBeChecked };
        fetch(btn.dataset.url, { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }, body: JSON.stringify(data) })
        .then(r => r.json()).then(json => {
            if (json.success) btn.classList.toggle('is-checked', json.entry.is_checked);
        })
        .catch(() => { btn.classList.toggle('is-checked', !willBeChecked); })
        .finally(() => btn.classList.remove('is-saving'));
    });
});

function saveNumberCell(input) {
    clearTimeout(input._saveTimer);
    const td = input.closest('td');
    const data = { criterion_id: td.dataset.criterion, date: td.dataset.date, value: parseInt(input.value || 0, 10) };
    input.classList.add('is-saving');
    fetch(input.dataset.url, { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }, body: JSON.stringify(data) })
    .then(r => r.json()).then(json => {
        if (json.success) {
            const target = parseInt(input.dataset.target || 0, 10);
            input.classList.toggle('is-done', target > 0 && (json.entry.value || 0) >= target);
        }
    }).catch(() => {}).finally(() => input.classList.remove('is-saving'));
}

document.querySelectorAll('.number-cell').forEach(function (input) {
    input._saveTimer = null;
    input.addEventListener('input', function () {
        clearTimeout(input._saveTimer);
        input._saveTimer = setTimeout(() => saveNumberCell(input), 400);
    });
    input.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
            saveNumberCell(input);
        }
    });
});

document.querySelectorAll('.save-table-btn').forEach(function (btn) {
    btn.addEventListener('click', function () {
        document.querySelectorAll('.save-table-btn').forEach(function (b) { b.classList.add('is-saving'); });
        const inputs = document.querySelectorAll('.number-cell');
        inputs.forEach(function (input) {
            clearTimeout(input._saveTimer);
            const td = input.closest('td');
            const data = { criterion_id: td.dataset.criterion, date: td.dataset.date, value: parseInt(input.value || 0, 10) };
            fetch(input.dataset.url, { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }, body: JSON.stringify(data), keepalive: true }).catch(() => {});
        });
        location.reload();
    });
});
</script>
@endpush
