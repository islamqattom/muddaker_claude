@extends('layouts.app')
@section('title', 'تقرير طالب')
@section('page-title', 'تقرير طالب')
@section('role-label', 'مدير')

@section('sidebar-menu')
@include('admin.partials.sidebar')
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.reports.index') }}">التقارير</a></li>
    <li class="breadcrumb-item active">{{ $student->user->name }}</li>
@endsection

@section('content')
@include('admin.reports._styles')

@php
    $pdfUrl = route('admin.reports.student', array_merge([$student->id], request()->except('export'))) . '?export=pdf';
@endphp

<div class="rpt-filter">
    <div class="rpt-filter-header">
        <i class="fas fa-calendar"></i>
        <span>تحديد الفترة</span>
    </div>
    <div class="rpt-filter-body">
        <form method="GET">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">من تاريخ</label>
                    <input type="date" name="from_date" class="form-control" value="{{ $fromDate->format('Y-m-d') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">إلى تاريخ</label>
                    <input type="date" name="to_date" class="form-control" value="{{ $toDate->format('Y-m-d') }}">
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="rpt-btn rpt-btn-primary flex-fill"><i class="fas fa-search"></i> تطبيق</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="rpt-header">
    <div class="rpt-header-content">
        <div class="rpt-header-icon"><i class="fas fa-user-graduate"></i></div>
        <div class="rpt-header-text">
            <h4>{{ $student->user->name }}</h4>
            <p>{{ $student->level->name ?? '—' }} • {{ $student->student_number }}@if($student->supervisor) • المشرف: {{ $student->supervisor->name ?? '—' }}@endif</p>
        </div>
    </div>
    <div class="rpt-actions">
        <a href="{{ $pdfUrl }}" target="_blank" class="rpt-btn rpt-btn-danger"><i class="fas fa-file-pdf"></i> PDF</a>
    </div>
</div>

<div class="rpt-kpi-grid">
    <div class="rpt-kpi" style="--kpi-color:#182B48;--kpi-bg:rgba(24,43,72,0.10)">
        <div class="rpt-kpi-icon"><i class="fas fa-list"></i></div>
        <div class="rpt-kpi-value">{{ $sessionStats['total'] }}</div>
        <div class="rpt-kpi-label">إجمالي الجلسات</div>
    </div>
    <div class="rpt-kpi" style="--kpi-color:#27ae60;--kpi-bg:rgba(39,174,96,0.10)">
        <div class="rpt-kpi-icon"><i class="fas fa-check-circle"></i></div>
        <div class="rpt-kpi-value">{{ $sessionStats['passed'] }}</div>
        <div class="rpt-kpi-label">ناجح</div>
    </div>
    <div class="rpt-kpi" style="--kpi-color:#c0392b;--kpi-bg:rgba(192,57,43,0.10)">
        <div class="rpt-kpi-icon"><i class="fas fa-times-circle"></i></div>
        <div class="rpt-kpi-value">{{ $sessionStats['failed'] }}</div>
        <div class="rpt-kpi-label">راسب</div>
    </div>
    <div class="rpt-kpi" style="--kpi-color:#d4a030;--kpi-bg:rgba(212,160,48,0.10)">
        <div class="rpt-kpi-icon"><i class="fas fa-percentage"></i></div>
        <div class="rpt-kpi-value">{{ $sessionStats['pass_rate'] }}%</div>
        <div class="rpt-kpi-label">نسبة النجاح</div>
    </div>
    <div class="rpt-kpi" style="--kpi-color:#059669;--kpi-bg:rgba(5,150,105,0.10)">
        <div class="rpt-kpi-icon"><i class="fas fa-calendar-check"></i></div>
        <div class="rpt-kpi-value">{{ $attendanceStats['attendance_rate'] }}%</div>
        <div class="rpt-kpi-label">نسبة الحضور</div>
    </div>
    <div class="rpt-kpi" style="--kpi-color:#059669;--kpi-bg:rgba(5,150,105,0.10)">
        <div class="rpt-kpi-icon"><i class="fas fa-calendar-day"></i></div>
        <div class="rpt-kpi-value">{{ $dayStats['att_days'] }}@if(!is_null($dayStats['att_days_total'])) من {{ $dayStats['att_days_total'] }}@endif</div>
        <div class="rpt-kpi-label">أيام الحضور</div>
    </div>
    <div class="rpt-kpi" style="--kpi-color:#16a085;--kpi-bg:rgba(22,160,133,0.10)">
        <div class="rpt-kpi-icon"><i class="fas fa-microphone-alt"></i></div>
        <div class="rpt-kpi-value">{{ $dayStats['rec_days'] }}@if(!is_null($dayStats['rec_days_total'])) من {{ $dayStats['rec_days_total'] }}@endif</div>
        <div class="rpt-kpi-label">أيام التسميع</div>
    </div>
</div>

<div class="rpt-table-card" style="margin-bottom:16px">
    <div class="rpt-table-header">
        <h5><i class="fas fa-microphone"></i> جلسات التسميع</h5>
        <span class="rpt-count-badge">{{ $sessions->count() }} جلسة</span>
    </div>
    @if($sessions->isEmpty())
        <div class="rpt-empty"><p>لا توجد جلسات في الفترة المحددة</p></div>
    @else
        <div class="table-responsive">
            <table class="rpt-table">
                <thead>
                    <tr>
                        <th data-sort>النوع</th>
                        <th data-sort>الجزء</th>
                        <th data-sort="num">الصفحات</th>
                        <th data-sort="num">الأخطاء</th>
                        <th data-sort="num">الترددات</th>
                        <th data-sort="num">الدرجة</th>
                        <th data-sort>الحالة</th>
                        <th data-sort>التاريخ</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sessions as $s)
                    <tr>
                        <td data-sort-value="{{ $s->type_label }}"><span class="rpt-badge" style="background:rgba(24,43,72,0.06);color:var(--primary)">{{ $s->type_label }}</span></td>
                        <td data-sort-value="{{ $s->parts_label }}" style="font-weight:600">{{ $s->parts_label }}</td>
                        <td data-sort-value="{{ $s->total_pages ?? 0 }}">{{ $s->total_pages ?? '—' }}</td>
                        <td data-sort-value="{{ $s->total_errors ?? 0 }}" style="color:#c0392b;font-weight:700">{{ $s->total_errors ?? 0 }}</td>
                        <td data-sort-value="{{ $s->total_hesitations ?? 0 }}" style="color:#d97706;font-weight:700">{{ $s->total_hesitations ?? 0 }}</td>
                        <td data-sort-value="{{ $s->final_score ?? -1 }}">
                            @if($s->final_score !== null)
                                @php $sc = $s->final_score; @endphp
                                <span class="rpt-score {{ $sc >= 90 ? 'high' : ($sc >= 75 ? 'medium' : 'low') }}">{{ number_format($sc, 1) }}%</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td><span class="rpt-badge {{ $s->status }}">{{ $s->status_label }}</span></td>
                        <td data-sort-value="{{ $s->session_date?->format('Y-m-d') }}" style="font-weight:600">{{ $s->session_date->format('Y/m/d') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

<div class="rpt-table-card">
    <div class="rpt-table-header">
        <h5><i class="fas fa-calendar-alt"></i> سجلات الحضور</h5>
        <span class="rpt-count-badge">{{ $attendances->count() }} سجل</span>
    </div>
    @if($attendances->isEmpty())
        <div class="rpt-empty"><p>لا توجد سجلات حضور في الفترة</p></div>
    @else
        <div class="table-responsive">
            <table class="rpt-table">
                <thead>
                    <tr>
                        <th data-sort>الحالة</th>
                        <th data-sort>وقت الحضور</th>
                        <th data-sort>ملاحظة</th>
                        <th data-sort>التاريخ</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($attendances as $a)
                    <tr>
                        <td><span class="rpt-badge {{ $a->status }}">{{ ['present'=>'حاضر','absent'=>'غائب','late'=>'متأخر','excused'=>'معذور','holiday'=>'عطلة'][$a->status] ?? $a->status }}</span></td>
                        <td>{{ $a->arrival_time ?? '—' }}</td>
                        <td>{{ $a->notes ?? '—' }}</td>
                        <td data-sort-value="{{ $a->date?->format('Y-m-d') }}" style="font-weight:600">{{ $a->date->format('Y/m/d') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
