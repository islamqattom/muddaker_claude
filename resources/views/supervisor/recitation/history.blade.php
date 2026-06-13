@extends('layouts.app')
@section('title', 'سجل الجلسات')
@section('page-title', 'سجل الجلسات')
@section('sidebar-menu') @include('supervisor.partials.sidebar') @endsection

@push('styles')
<style>
.session-history {
    --gold: #d4a030;
    --gold-light: #e6b84d;
    --navy: #182B48;
    --navy-light: #2A4570;
    --success: #27ae60;
    --warning: #f39c12;
    --danger: #c0392b;
    --info: #3498db;
}

.session-history .page-hero {
    background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
    border-radius: 20px;
    padding: 28px 32px;
    margin-bottom: 28px;
    position: relative;
    overflow: hidden;
}

.session-history .page-hero::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23d4a030' fill-opacity='0.06'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    pointer-events: none;
}

.session-history .page-hero::after {
    content: '';
    position: absolute;
    top: 0; right: 0;
    width: 120px; height: 4px;
    background: linear-gradient(90deg, var(--gold), transparent);
}

.session-history .hero-icon {
    width: 64px; height: 64px;
    background: linear-gradient(135deg, var(--gold) 0%, var(--gold-light) 100%);
    border-radius: 16px;
    display: flex; align-items: center; justify-content: center;
    font-size: 28px; color: white;
    box-shadow: 0 8px 24px rgba(212, 160, 48, 0.35);
    flex-shrink: 0;
}

.session-history .hero-title {
    color: white;
    font-size: 24px; font-weight: 700;
    margin-bottom: 4px;
}

.session-history .hero-subtitle {
    color: rgba(255, 255, 255, 0.75);
    font-size: 14px;
    margin: 0;
}

.session-history .quran-verse {
    font-family: 'Amiri', serif;
    font-size: 14px;
    color: rgba(255, 255, 255, 0.6);
    text-align: center;
    padding: 10px 20px;
    background: rgba(0,0,0,0.1);
    border-top: 1px solid rgba(212, 160, 48, 0.2);
    margin-top: 20px;
}

.session-history .filter-card {
    background: white;
    border-radius: 20px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
    border: 1px solid rgba(24, 43, 72, 0.04);
    overflow: hidden;
    margin-bottom: 24px;
}

.session-history .filter-header {
    background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
    padding: 16px 24px;
    display: flex; align-items: center; gap: 12px;
}

.session-history .filter-header i { color: var(--gold); font-size: 20px; }
.session-history .filter-header h5 { color: white; font-weight: 700; margin: 0; font-size: 16px; }

.session-history .filter-body { padding: 20px 24px; }

.session-history .form-control, .session-history .form-select {
    border: 2px solid rgba(24, 43, 72, 0.08);
    border-radius: 12px;
    padding: 10px 16px;
    font-size: 14px;
    transition: all 0.3s ease;
}

.session-history .form-control:focus, .session-history .form-select:focus {
    border-color: var(--gold);
    box-shadow: 0 0 0 3px rgba(212, 160, 48, 0.15);
}

.session-history .btn-apply {
    background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
    border: none;
    border-radius: 12px;
    padding: 10px 24px;
    font-weight: 600;
    color: white;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(24, 43, 72, 0.25);
}

.session-history .btn-apply:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(24, 43, 72, 0.35);
}

.session-history .stats-bar {
    background: white;
    border-radius: 16px;
    padding: 16px 24px;
    margin-bottom: 20px;
    display: flex; align-items: center; gap: 16px;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.04);
    border: 1px solid rgba(24, 43, 72, 0.04);
    flex-wrap: wrap;
}

.session-history .stat-pill {
    display: flex; align-items: center; gap: 8px;
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 13px; font-weight: 600;
}

.session-history .stat-pill.total {
    background: linear-gradient(135deg, rgba(24, 43, 72, 0.1), rgba(42, 69, 112, 0.15));
    color: var(--navy);
}

.session-history .stat-pill.student {
    background: linear-gradient(135deg, rgba(52, 152, 219, 0.15), rgba(41, 128, 185, 0.2));
    color: #2980b9;
}

.session-history .sessions-table-card {
    background: white;
    border-radius: 20px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
    border: 1px solid rgba(24, 43, 72, 0.04);
    overflow: hidden;
}

.session-history .table-header {
    background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
    padding: 16px 24px;
    display: flex; align-items: center; justify-content: space-between;
}

.session-history .table-header h5 {
    color: white; font-weight: 700; margin: 0;
    font-size: 15px; display: flex; align-items: center; gap: 10px;
}

.session-history .table-header h5 i { color: var(--gold); }

.session-history .table-header .badge {
    background: rgba(255,255,255,0.2);
    color: white;
    font-size: 12px; padding: 6px 14px;
    border-radius: 20px;
}

.session-history .sessions-table {
    margin: 0;
}

.session-history .sessions-table thead th {
    background: linear-gradient(135deg, rgba(24, 43, 72, 0.04) 0%, rgba(42, 69, 112, 0.06) 100%);
    padding: 14px 16px;
    font-weight: 700;
    font-size: 12px;
    color: var(--navy);
    border: none;
    text-align: center;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.session-history .sessions-table thead th:first-child { text-align: center; }

.session-history .sessions-table tbody td {
    padding: 16px;
    vertical-align: middle;
    border-color: rgba(24, 43, 72, 0.04);
    font-size: 14px;
    text-align: center;
}

.session-history .sessions-table tbody tr {
    transition: all 0.2s ease;
}

.session-history .sessions-table tbody tr:hover {
    background: linear-gradient(90deg, rgba(212, 160, 48, 0.04) 0%, rgba(24, 43, 72, 0.02) 100%);
}

.session-history .sessions-table tbody tr:hover td:first-child {
    border-right: 3px solid var(--gold);
}

.session-history .row-index {
    width: 32px; height: 32px;
    background: linear-gradient(135deg, rgba(24, 43, 72, 0.08), rgba(42, 69, 112, 0.12));
    border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    font-weight: 700;
    color: var(--navy);
    font-size: 12px;
    margin: 0 auto;
}

.session-history .student-info {
    display: flex; align-items: center; gap: 10px;
}

.session-history .student-avatar {
    width: 36px; height: 36px;
    background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    color: var(--gold);
    font-size: 14px;
    flex-shrink: 0;
}

.session-history .student-name { font-weight: 600; color: var(--navy); }

.session-history .type-badge {
    padding: 6px 14px;
    border-radius: 10px;
    font-size: 12px; font-weight: 600;
}

.session-history .type-badge.new_memorization { background: rgba(39,174,96,0.15); color: var(--success); }
.session-history .type-badge.review { background: rgba(52,152,219,0.15); color: var(--info); }
.session-history .type-badge.unmastered { background: rgba(243,156,18,0.15); color: var(--warning); }
.session-history .type-badge.recitation { background: rgba(155,89,182,0.15); color: #8e44ad; }
.session-history .type-badge.mafsil { background: rgba(231,76,60,0.15); color: var(--danger); }
.session-history .type-badge.not_recitate { background: rgba(127,140,141,0.15); color: #7f8c8d; }

.session-history .part-badge {
    background: linear-gradient(135deg, rgba(24, 43, 72, 0.08), rgba(42, 69, 112, 0.12));
    color: var(--navy);
    padding: 4px 12px;
    border-radius: 8px;
    font-size: 12px; font-weight: 600;
}

.session-history .pages-badge {
    background: var(--navy);
    color: white;
    padding: 4px 10px;
    border-radius: 8px;
    font-size: 11px; font-weight: 600;
}

.session-history .errors-val { color: var(--danger); font-weight: 700; }
.session-history .hesitations-val { color: var(--warning); font-weight: 700; }

.session-history .score-badge {
    padding: 6px 14px;
    border-radius: 10px;
    font-size: 13px; font-weight: 700;
}

.session-history .status-badge {
    padding: 6px 14px;
    border-radius: 10px;
    font-size: 12px; font-weight: 600;
}

.session-history .day-badge {
    background: rgba(24, 43, 72, 0.06);
    color: var(--navy);
    padding: 4px 12px;
    border-radius: 8px;
    font-size: 11px; font-weight: 600;
}

.session-history .date-badge {
    font-size: 12px;
    color: #6c7a89;
}

.session-history .action-btn {
    width: 34px; height: 34px;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    border: 1px solid rgba(24, 43, 72, 0.1);
    background: white;
    transition: all 0.2s ease;
}

.session-history .action-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.session-history .action-btn.view { color: var(--navy); border-color: var(--navy); }
.session-history .action-btn.view:hover { background: var(--navy); color: white; }

.session-history .action-btn.delete { color: var(--danger); border-color: var(--danger); }
.session-history .action-btn.delete:hover { background: var(--danger); color: white; }

.session-history .empty-state {
    text-align: center;
    padding: 60px 20px;
}

.session-history .empty-icon {
    width: 90px; height: 90px;
    background: linear-gradient(135deg, rgba(24, 43, 72, 0.06), rgba(42, 69, 112, 0.08));
    border-radius: 22px;
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 20px;
}

.session-history .empty-icon i { font-size: 36px; color: var(--navy); opacity: 0.4; }

.session-history .pagination-wrapper {
    padding: 16px 20px;
    border-top: 1px solid rgba(24, 43, 72, 0.04);
    display: flex; justify-content: center;
}

.session-history .delete-modal .modal-header {
    background: linear-gradient(135deg, var(--danger) 0%, #e74c3c 100%);
    padding: 16px 24px;
}

.session-history .delete-modal .modal-header h5 { color: white; }

.session-history .delete-modal .modal-body {
    padding: 24px;
    text-align: center;
}

.session-history .delete-modal .modal-body .warning-icon {
    width: 60px; height: 60px;
    background: rgba(231, 76, 60, 0.1);
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 16px;
    font-size: 28px;
    color: var(--danger);
}

.session-history .delete-modal .modal-footer {
    padding: 16px 24px;
    border-top: 1px solid rgba(24, 43, 72, 0.04);
}

@media (max-width: 992px) {
    .session-history .page-hero { padding: 20px 24px; }
    .session-history .hero-icon { width: 52px; height: 52px; font-size: 22px; }
    .session-history .hero-title { font-size: 20px; }
    .session-history .stats-bar { padding: 12px 16px; }
    .session-history .sessions-table { font-size: 12px; }
    .session-history .sessions-table thead th, .session-history .sessions-table tbody td { padding: 10px 8px; }
}
</style>
@endpush

@section('content')
<div class="session-history">
    <div class="page-hero">
        <div class="d-flex align-items-center gap-4">
            <div class="hero-icon">
                <i class="fas fa-history"></i>
            </div>
            <div>
                <h1 class="hero-title">سجل جلسات التسميع</h1>
                <p class="hero-subtitle">عرض وتصفية سجلات جلسات التحفيظ للطلاب</p>
            </div>
        </div>
        <div class="quran-verse">
            <i class="fas fa-book-quran me-2"></i>
            {{ 'وَرَتِّلِ الْقُرْآنَ تَرْتِيلًا' }}
        </div>
    </div>

    <div class="filter-card">
        <div class="filter-header">
            <i class="fas fa-filter"></i>
            <h5>تصفية السجل</h5>
        </div>
        <div class="filter-body">
            <form method="GET" action="{{ route('supervisor.recitation.history') }}" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label fw-bold mb-2">الطالب</label>
                    <select name="student_id" class="form-select">
                        <option value="">-- جميع الطلاب --</option>
                        @foreach($students as $student)
                            <option value="{{ $student->id }}" {{ $studentId == $student->id ? 'selected' : '' }}>
                                {{ $student->user->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold mb-2">من تاريخ</label>
                    <input type="date" name="from_date" class="form-control" value="{{ request('from_date', $fromDate->toDateString()) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold mb-2">إلى تاريخ</label>
                    <input type="date" name="to_date" class="form-control" value="{{ request('to_date', $toDate->toDateString()) }}">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-apply w-100">
                        <i class="fas fa-search me-2"></i>بحث
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="stats-bar">
        <div class="stat-pill total">
            <i class="fas fa-list"></i>
            <span>عدد النتائج: <strong>{{ $sessions->total() }}</strong></span>
        </div>
        @if($studentId)
            @php $selectedStudent = $students->firstWhere('id', $studentId) @endphp
            @if($selectedStudent)
                <div class="stat-pill student">
                    <i class="fas fa-user-graduate"></i>
                    <span>{{ $selectedStudent->user->name }}</span>
                </div>
            @endif
        @endif
    </div>

    <div class="sessions-table-card">
        <div class="table-header">
            <h5><i class="fas fa-table"></i>جدول الجلسات</h5>
            <span class="badge">
                <i class="fas fa-calendar-alt me-1"></i>
                {{ $fromDate->format('Y/m/d') }} - {{ $toDate->format('Y/m/d') }}
            </span>
        </div>

        @if($sessions->isEmpty())
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="fas fa-inbox"></i>
                </div>
                <h5 class="text-muted mb-2">لا توجد جلسات</h5>
                <p class="text-muted small">لا توجد جلسات تسميع في الفترة المحددة</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table sessions-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>الطالب</th>
                            <th>نوع الجلسة</th>
                            <th>الجزء</th>
                            <th>الصفحات</th>
                            <th>الأخطاء</th>
                            <th>الترددات</th>
                            <th>العلامة</th>
                            <th>الحالة</th>
                            <th>التاريخ</th>
                            <th>اليوم</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sessions as $index => $session)
                            <tr>
                                <td>
                                    <div class="row-index">{{ $sessions->firstItem() + $index }}</div>
                                </td>
                                <td>
                                    <div class="student-info">
                                        <div class="student-avatar">
                                            <i class="fas fa-user"></i>
                                        </div>
                                        <span class="student-name">{{ $session->student->user->name }}</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="type-badge {{ $session->type }}">
                                        {{ $session->type_label }}
                                    </span>
                                </td>
                                <td>
                                    @if($session->type === 'mafsil')
                                        @php $partNumbers = $session->pages->pluck('page_number')->unique()->sort()->values()->toArray(); @endphp
                                        @if(count($partNumbers) > 0)
                                            <span class="part-badge">أجزاء {{ implode(', ', $partNumbers) }}</span>
                                        @elseif($session->part)
                                            <span class="part-badge">جزء {{ $session->part->part_number }}</span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    @elseif(in_array($session->type, ['unmastered', 'review', 'new_memorization']))
                                        @if($session->start_page === $session->end_page)
                                            <span class="part-badge">صفحة {{ $session->start_page }}</span>
                                        @else
                                            <span class="part-badge">{{ $session->start_page }} - {{ $session->end_page }}</span>
                                        @endif
                                    @else
                                        @if($session->part)
                                            <span class="part-badge">الجزء {{ $session->part->part_number }}</span>
                                        @else
                                            <span class="part-badge">{{ $session->start_page }} - {{ $session->end_page }}</span>
                                        @endif
                                    @endif
                                </td>
                                <td>
                                    @if($session->total_pages)
                                        <span class="pages-badge">{{ $session->total_pages }}</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if($session->total_errors > 0)
                                        <span class="errors-val">{{ $session->total_errors }}</span>
                                    @else
                                        <span class="text-muted">0</span>
                                    @endif
                                </td>
                                <td>
                                    @if($session->total_hesitations > 0)
                                        <span class="hesitations-val">{{ $session->total_hesitations }}</span>
                                    @else
                                        <span class="text-muted">0</span>
                                    @endif
                                </td>
                                <td>
                                    @if($session->final_score !== null)
                                        @php
                                            $scoreClass = $session->final_score >= 90 ? 'bg-success text-white' : ($session->final_score >= 80 ? 'bg-warning text-dark' : 'bg-danger text-white');
                                        @endphp
                                        <span class="score-badge {{ $scoreClass }}">{{ number_format($session->final_score, 1) }}%</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="status-badge bg-{{ $session->status_color }}">{{ $session->status_label }}</span>
                                </td>
                                <td>
                                    <span class="date-badge">{{ $session->session_date->format('Y/m/d') }}</span>
                                </td>
                                <td>
                                    <span class="day-badge">{{ $session->day }}</span>
                                </td>
                                <td>
                                    <div class="d-flex gap-2 justify-content-center">
                                        <a href="{{ route('supervisor.sessions.show', $session->id) }}" class="action-btn view" title="عرض">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <button type="button" class="action-btn delete" title="حذف" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $session->id }}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>

                                    <div class="modal fade delete-modal" id="deleteModal{{ $session->id }}" tabindex="-1">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i>حذف جلسة التسميع</h5>
                                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="warning-icon">
                                                        <i class="fas fa-trash"></i>
                                                    </div>
                                                    <p class="mb-2">هل أنت متأكد من حذف جلسة التسميع للطالب:</p>
                                                    <strong class="text-primary">{{ $session->student->user->name }}</strong>
                                                    <p class="text-muted small mt-2 mb-0">بتاريخ: {{ $session->session_date->format('Y/m/d') }}</p>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                                                    <form action="{{ route('supervisor.recitation.destroy', $session->id) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger">
                                                            <i class="fas fa-trash me-1"></i>حذف
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($sessions->hasPages())
                <div class="pagination-wrapper">
                    {{ $sessions->appends(request()->query())->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
@endsection