@extends('layouts.app')
@section('title', 'جلسات التسميع')
@section('page-title', 'جلسات التسميع')
@section('role-label', 'ولي أمر')
@section('sidebar-menu')
    @include('parent.partials.sidebar')
@endsection
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('parent.dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item active">جلسات التسميع</li>
@endsection
@section('content')

<div class="portal-container">
    <div class="page-header">
        <div class="header-content">
            <div class="header-icon primary">
                <i class="fas fa-microphone"></i>
            </div>
            <div class="header-text">
                <h4>جلسات التسميع</h4>
                <p>متابعة جلسات التسميع لأبنائك</p>
            </div>
        </div>
        <div class="header-stats">
            <div class="stat-pill primary">
                <i class="fas fa-list"></i>
                <span>{{ $sessions->total() }} جلسة</span>
            </div>
        </div>
    </div>

    <div class="filter-card">
        <form method="GET" action="{{ route('parent.sessions.index') }}" class="filter-grid">
            <div class="filter-field">
                <label class="filter-label">الطالب</label>
                <select name="student_id" class="form-select">
                    <option value="">كل الطلاب</option>
                    @foreach($students as $student)
                        <option value="{{ $student->id }}" {{ request('student_id') == $student->id ? 'selected' : '' }}>
                            {{ $student->user->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="filter-field">
                <label class="filter-label">نوع الجلسة</label>
                <select name="type" class="form-select">
                    <option value="all" {{ request('type', 'all') == 'all' ? 'selected' : '' }}>الكل</option>
                    <option value="new_memorization" {{ request('type') == 'new_memorization' ? 'selected' : '' }}>حفظ جديد</option>
                    <option value="review" {{ request('type') == 'review' ? 'selected' : '' }}>مراجعة</option>
                    <option value="unmastered" {{ request('type') == 'unmastered' ? 'selected' : '' }}>غير متقن</option>
                    <option value="recitation" {{ request('type') == 'recitation' ? 'selected' : '' }}>تلاوة</option>
                    <option value="mafsil" {{ request('type') == 'mafsil' ? 'selected' : '' }}>مفصل</option>
                </select>
            </div>
            <div class="filter-field">
                <label class="filter-label">الفترة</label>
                <select name="period" class="form-select" id="periodSelect" onchange="handlePeriodChange()">
                    <option value="all" {{ request('period', 'all') == 'all' ? 'selected' : '' }}>الكل</option>
                    <option value="today" {{ request('period') == 'today' ? 'selected' : '' }}>اليوم</option>
                    <option value="week" {{ request('period') == 'week' ? 'selected' : '' }}>الأسبوع</option>
                    <option value="month" {{ request('period') == 'month' ? 'selected' : '' }}>الشهر</option>
                    <option value="custom" {{ request('period') == 'custom' ? 'selected' : '' }}>مخصص</option>
                </select>
            </div>
            <div class="filter-field custom-date-range" style="display: {{ request('period') == 'custom' ? 'flex' : 'none' }}; flex-direction: column;">
                <label class="filter-label">من تاريخ</label>
                <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
            </div>
            <div class="filter-field custom-date-range" style="display: {{ request('period') == 'custom' ? 'flex' : 'none' }}; flex-direction: column;">
                <label class="filter-label">إلى تاريخ</label>
                <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
            </div>
            <div class="filter-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-filter"></i>
                </button>
                <a href="{{ route('parent.sessions.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-redo"></i>
                </a>
            </div>
        </form>
    </div>

    <div class="table-card">
        <div class="table-header">
            <div class="table-title">
                <i class="fas fa-list"></i>
                <span>جلسات التسميع ({{ $sessions->total() }})</span>
            </div>
        </div>
        <div class="card-body-custom p-0">
            @if($sessions->isEmpty())
                <div class="empty-state">
                    <div class="empty-illustration">
                        <i class="fas fa-microphone-alt"></i>
                    </div>
                    <h5>لا توجد جلسات تسميع</h5>
                    <p>لم يتم تسجيل جلسات تسميع في هذه الفترة</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="modern-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>التاريخ</th>
                                <th>اليوم</th>
                                <th>الطالب</th>
                                <th>نوع الجلسة</th>
                                <th>الجزء/الصفحات</th>
                                <th>الأخطاء</th>
                                <th>الترددات</th>
                                <th>الدرجة</th>
                                <th>الحالة</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sessions as $index => $session)
                            <tr>
                                <td class="row-num">{{ $sessions->firstItem() + $index }}</td>
                                <td>
                                    <div class="date-cell">
                                        <i class="fas fa-calendar"></i>
                                        {{ $session->session_date->format('Y/m/d') }}
                                    </div>
                                </td>
                                <td><span class="day-badge">{{ $session->day }}</span></td>
                                <td>
                                    <div class="user-info-cell">
                                        <div class="user-avatar">
                                            <i class="fas fa-user"></i>
                                        </div>
                                        <span class="user-name">{{ $session->student->user->name }}</span>
                                    </div>
                                </td>
                                <td>
                                    @switch($session->type)
                                        @case('new_memorization')
                                            <span class="type-badge new">حفظ جديد</span>
                                            @break
                                        @case('review')
                                            <span class="type-badge review">مراجعة</span>
                                            @break
                                        @case('unmastered')
                                            <span class="type-badge unmastered">غير متقن</span>
                                            @break
                                        @case('recitation')
                                            <span class="type-badge recitation">تلاوة</span>
                                            @break
                                        @case('mafsil')
                                            <span class="type-badge mafsil">مفصل</span>
                                            @break
                                        @default
                                            <span class="type-badge default">{{ $session->type_label }}</span>
                                    @endswitch
                                </td>
                                <td>
                                    @if($session->parts_label !== '-')
                                        <span class="fw-bold">{{ $session->parts_label }}</span>
                                    @elseif($session->start_page && $session->end_page)
                                        @if($session->start_page === $session->end_page)
                                            <span>صفحة {{ $session->start_page }}</span>
                                        @else
                                            <span>{{ $session->start_page }} - {{ $session->end_page }}</span>
                                        @endif
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($session->total_errors > 0)
                                        <span class="errors-badge">{{ $session->total_errors }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($session->total_hesitations > 0)
                                        <span class="hesitations-badge">{{ $session->total_hesitations }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($session->final_score !== null)
                                        <div class="score-cell">
                                            <span class="score-value">{{ number_format($session->final_score, 1) }}</span>
                                            <span class="score-max">/ {{ $session->max_score }}</span>
                                        </div>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @switch($session->status)
                                        @case('passed')
                                            <span class="status-badge success">
                                                <i class="fas fa-check"></i>
                                                <span>ناجح</span>
                                            </span>
                                            @break
                                        @case('failed')
                                            <span class="status-badge danger">
                                                <i class="fas fa-times"></i>
                                                <span>راسب</span>
                                            </span>
                                            @break
                                        @default
                                            <span class="status-badge pending">
                                                <i class="fas fa-clock"></i>
                                                <span>معلق</span>
                                            </span>
                                    @endswitch
                                </td>
                                <td>
                                    <a href="{{ route('parent.sessions.show', $session->id) }}" class="action-btn view" title="عرض التفاصيل">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="table-footer">
                    <div class="pagination-wrapper">
                        {{ $sessions->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<style>
.header-stats { display: flex; gap: 12px; }

.filter-field { display: flex; flex-direction: column; }

.empty-illustration {
    width: 66px;
    height: 66px;
    background: linear-gradient(135deg, rgba(24,43,72,0.08), rgba(42,69,112,0.08));
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 14px;
    font-size: 26px;
    color: #d4a030;
}

.date-cell {
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 600;
    color: #182B48;
    font-size: 12.5px;
}

.date-cell i { color: #d4a030; }

.day-badge {
    background: #f0f4f8;
    color: #7f8c8d;
}

.type-badge.new { background: rgba(39,174,96,0.12); color: #16835a; }
.type-badge.review { background: rgba(52,152,219,0.12); color: #2475a8; }
.type-badge.unmastered { background: rgba(230,126,34,0.12); color: #d48806; }
.type-badge.recitation { background: rgba(142,68,173,0.12); color: #7d3c98; }
.type-badge.mafsil { background: rgba(24,43,72,0.12); color: #182B48; }
.type-badge.default { background: rgba(127,140,141,0.12); color: #7f8c8d; }

.errors-badge {
    display: inline-block;
    background: rgba(192,57,43,0.12);
    color: #a82e1c;
    padding: 3px 10px;
    border-radius: 7px;
    font-size: 11.5px;
    font-weight: 700;
}

.hesitations-badge {
    display: inline-block;
    background: rgba(243,156,18,0.12);
    color: #d48806;
    padding: 3px 10px;
    border-radius: 7px;
    font-size: 11.5px;
    font-weight: 700;
}

.score-cell { display: flex; align-items: baseline; gap: 4px; }
.score-value { font-weight: 700; color: #182B48; font-size: 13px; }
.score-max { color: #7f8c8d; font-size: 12px; }

.status-badge.success { background: rgba(39,174,96,0.12); color: #16835a; }
.status-badge.danger { background: rgba(192,57,43,0.12); color: #a82e1c; }
.status-badge.pending { background: rgba(127,140,141,0.12); color: #7f8c8d; }
</style>

@push('scripts')
<script>
function handlePeriodChange() {
    var period = document.getElementById('periodSelect').value;
    var customDateFields = document.querySelectorAll('.custom-date-range');

    if (period === 'custom') {
        customDateFields.forEach(function(field) {
            field.style.display = 'flex';
        });
    } else {
        customDateFields.forEach(function(field) {
            field.style.display = 'none';
        });
    }
}
</script>
@endpush
@endsection