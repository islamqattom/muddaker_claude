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
                <div class="table-icon">
                    <i class="fas fa-list"></i>
                </div>
                <span>جلسات التسميع ({{ $sessions->total() }})</span>
            </div>
        </div>
        <div class="card-body-custom">
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
                                    @if($session->type === 'mafsil')
                                        @php
                                            $partNumbers = $session->pages->pluck('page_number')->unique()->sort()->values()->toArray();
                                        @endphp
                                        @if(count($partNumbers) > 0)
                                            <span class="fw-bold">أجزاء {{ implode(', ', $partNumbers) }}</span>
                                        @elseif($session->part)
                                            <span class="fw-bold">جزء {{ $session->part->part_number }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    @elseif(in_array($session->type, ['unmastered', 'review', 'new_memorization']))
                                        @if($session->start_page === $session->end_page)
                                            <span>صفحة {{ $session->start_page }}</span>
                                        @else
                                            <span>{{ $session->start_page }} - {{ $session->end_page }}</span>
                                        @endif
                                    @else
                                        @if($session->part)
                                            <span class="fw-bold">جزء {{ $session->part->part_number }}</span>
                                        @else
                                            <span>{{ $session->start_page }} - {{ $session->end_page }}</span>
                                        @endif
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
.portal-container {
    max-width: 1400px;
    margin: 0 auto;
}

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
    margin-bottom: 30px;
}

.header-content { display: flex; align-items: center; gap: 20px; }

.header-icon {
    width: 70px;
    height: 70px;
    border-radius: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 30px;
    box-shadow: 0 10px 30px rgba(24,43,72,0.2);
}

.header-icon.primary {
    background: linear-gradient(135deg, #182B48, #2A4570);
    color: #d4a030;
}

.header-text h4 {
    color: #182B48;
    font-size: 24px;
    font-weight: 700;
    margin: 0 0 4px 0;
}

.header-text p { color: #95a5a6; font-size: 14px; margin: 0; }

.header-stats { display: flex; gap: 12px; }

.stat-pill {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 22px;
    border-radius: 30px;
    font-weight: 600;
    font-size: 14px;
}

.stat-pill.primary {
    background: linear-gradient(135deg, #182B48, #2A4570);
    color: #fff;
}

.stat-pill i { color: #d4a030; }

.filter-card {
    background: #fff;
    border-radius: 20px;
    padding: 28px;
    box-shadow: 0 4px 25px rgba(0,0,0,0.04);
    border: 1px solid #f0f4f8;
    margin-bottom: 30px;
}

.filter-grid {
    display: grid;
    grid-template-columns: 1.5fr 1fr 1fr 1fr 1fr 1fr auto;
    gap: 18px;
    align-items: end;
}

@media (max-width: 992px) { .filter-grid { grid-template-columns: 1fr 1fr 1fr; } }
@media (max-width: 576px) { .filter-grid { grid-template-columns: 1fr; } }

.filter-field { display: flex; flex-direction: column; }

.filter-label {
    color: #182B48;
    font-size: 13px;
    font-weight: 600;
    margin-bottom: 8px;
}

.filter-actions { display: flex; gap: 10px; }

.table-card {
    background: #fff;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 4px 25px rgba(0,0,0,0.04);
    border: 1px solid #f0f4f8;
}

.table-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 22px 28px;
    border-bottom: 1px solid #f0f4f8;
    background: linear-gradient(135deg, rgba(24,43,72,0.02), rgba(26,51,86,0.02));
}

.table-title {
    display: flex;
    align-items: center;
    gap: 14px;
    font-size: 17px;
    font-weight: 700;
    color: #182B48;
}

.table-icon {
    width: 45px;
    height: 45px;
    background: linear-gradient(135deg, #182B48, #1a3356);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #d4a030;
    font-size: 18px;
}

.card-body-custom { padding: 0; }

.empty-state {
    text-align: center;
    padding: 70px 30px;
}

.empty-illustration {
    width: 100px;
    height: 100px;
    background: linear-gradient(135deg, rgba(24,43,72,0.08), rgba(42,69,112,0.08));
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 25px;
    font-size: 40px;
    color: #d4a030;
}

.empty-state h5 {
    color: #182B48;
    font-size: 20px;
    font-weight: 700;
    margin-bottom: 10px;
}

.empty-state p { color: #95a5a6; }

.table-responsive { overflow-x: auto; }

.modern-table { width: 100%; border-collapse: collapse; }
.modern-table thead { background: #f8f9fa; }

.modern-table th {
    padding: 18px 22px;
    font-size: 11px;
    font-weight: 700;
    color: #182B48;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-bottom: 2px solid #e9ecef;
    white-space: nowrap;
}

.modern-table td {
    padding: 18px 22px;
    font-size: 14px;
    color: #2c3e50;
    border-bottom: 1px solid #f0f4f8;
    vertical-align: middle;
}

.modern-table tbody tr { transition: all 0.2s; }
.modern-table tbody tr:hover { background: #fafbfc; }

.row-num { color: #bdc3c7; font-weight: 700; font-size: 13px; }

.date-cell {
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 600;
    color: #182B48;
}

.date-cell i { color: #d4a030; }

.day-badge {
    background: #f0f4f8;
    color: #7f8c8d;
    padding: 5px 12px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 600;
}

.user-info-cell { display: flex; align-items: center; gap: 14px; }

.user-avatar {
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, #182B48, #2A4570);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #d4a030;
    font-size: 16px;
}

.user-name { color: #182B48; font-weight: 600; }

.type-badge {
    display: inline-block;
    padding: 6px 14px;
    border-radius: 10px;
    font-size: 12px;
    font-weight: 600;
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
    padding: 5px 12px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 700;
}

.hesitations-badge {
    display: inline-block;
    background: rgba(243,156,18,0.12);
    color: #d48806;
    padding: 5px 12px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 700;
}

.score-cell { display: flex; align-items: baseline; gap: 4px; }
.score-value { font-weight: 700; color: #182B48; font-size: 15px; }
.score-max { color: #7f8c8d; font-size: 12px; }

.status-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 14px;
    border-radius: 10px;
    font-size: 12px;
    font-weight: 600;
}

.status-badge.success { background: rgba(39,174,96,0.12); color: #16835a; }
.status-badge.danger { background: rgba(192,57,43,0.12); color: #a82e1c; }
.status-badge.pending { background: rgba(127,140,141,0.12); color: #7f8c8d; }

.action-btn {
    width: 40px;
    height: 40px;
    border-radius: 12px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: rgba(52,152,219,0.1);
    color: #3498db;
    transition: all 0.3s ease;
    text-decoration: none;
}

.action-btn:hover {
    background: #3498db;
    color: #fff;
    transform: scale(1.05);
}

.table-footer {
    padding: 20px 28px;
    border-top: 1px solid #f0f4f8;
    display: flex;
    justify-content: center;
}

.pagination-wrapper .pagination { margin: 0; gap: 6px; }
.pagination-wrapper .page-link {
    padding: 10px 16px;
    border-radius: 10px;
    font-size: 14px;
    border: 1px solid #e9ecef;
    color: #182B48;
}
.pagination-wrapper .page-item.active .page-link {
    background: linear-gradient(135deg, #182B48, #2A4570);
    border-color: #182B48;
    color: #fff;
}
.pagination-wrapper .page-item.disabled .page-link { color: #bdc3c7; }
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