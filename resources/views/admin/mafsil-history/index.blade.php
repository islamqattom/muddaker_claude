@extends('layouts.app')

@section('title', 'سجل اختبار الأجزاء')
@section('page-title', 'سجل اختبار الأجزاء')
@section('role-label', 'مدير')

@section('sidebar-menu')
    @include('admin.partials.sidebar')
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item active">سجل اختبار الأجزاء</li>
@endsection

@section('content')
<style>
:root {
        --primary: #182B48;
        --primary-light: #2A4570;
        --gold: #d4a030;
        --gold-light: #E8C45A;
        --success: #27ae60;
        --success-soft: #dcfce7;
        --warning: #f39c12;
        --warning-soft: #fef3c7;
        --danger: #c0392b;
        --danger-soft: #fee2e2;
        --info: #3498db;
        --info-soft: #e0f2fe;
        --bg-main: #f8fafc;
        --card-shadow: 0 4px 25px rgba(24, 43, 72, 0.08);
    }
body { background: var(--bg-main); font-family: 'Tajawal', sans-serif; }
.stat-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 14px;
        margin-bottom: 16px;
    }
.stat-card .icon {
        width: 48px; height: 48px;
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 20px;
    }
.stat-card.total .icon { background: var(--info-soft); color: var(--info); }
.stat-card.passed .icon { background: var(--success-soft); color: var(--success); }
.stat-card.failed .icon { background: var(--danger-soft); color: var(--danger); }
.stat-card .label { font-size: 12px; color: #6c7a89; margin: 0 0 2px 0; font-weight: 600; }
.stat-card .value { font-size: 22px; font-weight: 800; color: var(--primary); margin: 0; line-height: 1; }
.filter-header {
        padding: 14px 16px;
        background: linear-gradient(135deg, rgba(24,43,72,0.03) 0%, rgba(42,69,112,0.05) 100%);
        border-bottom: 1px solid rgba(24,43,72,0.05);
        font-weight: 700;
        color: var(--primary);
        display: flex; align-items: center; gap: 10px;
    }
.filter-header i { color: var(--gold); }
.filter-body { padding: 16px 16px; }
.filter-body .form-control, .filter-body .form-select {
        border-radius: 10px;
        border: 1.5px solid #e9ecef;
        padding: 9px 12px;
        font-size: 14px;
    }
.filter-body .form-control:focus, .filter-body .form-select:focus {
        border-color: var(--gold);
        box-shadow: 0 0 0 3px rgba(212,160,48,0.12);
    }
.filter-body label {
        font-size: 12px;
        color: #6c7a89;
        font-weight: 600;
        margin-bottom: 4px;
        display: block;
    }
.data-card {
        background: white;
        border-radius: 13px;
        box-shadow: var(--card-shadow);
        border: 1px solid rgba(24,43,72,0.05);
        overflow: hidden;
    }
.data-card-header {
        padding: 16px 16px;
        border-bottom: 1px solid #f0f3f7;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
    }
.data-card-header h6 {
        margin: 0;
        color: var(--primary);
        font-weight: 700;
        font-size: 16px;
    }
.table-modern {
        width: 100%;
        border-collapse: collapse;
    }
.table-modern thead th {
        background: #f8fafc;
        color: #6c7a89;
        font-size: 12px;
        font-weight: 700;
        padding: 12px 16px;
        text-align: right;
        border-bottom: 1px solid #eef1f6;
    }
.table-modern tbody td {
        padding: 14px 16px;
        border-bottom: 1px solid #f3f5f9;
        font-size: 14px;
        color: #2d3a4a;
        vertical-align: middle;
    }
.table-modern tbody tr:hover {
        background: #fafbfd;
    }
.table-modern tbody tr:last-child td { border-bottom: none; }
.student-cell { display: flex; align-items: center; gap: 10px; }
.student-avatar {
        width: 38px; height: 38px;
        border-radius: 10px;
        background: linear-gradient(135deg, #f1c40f 0%, var(--gold) 100%);
        color: var(--primary);
        display: flex; align-items: center; justify-content: center;
        font-weight: 700;
        font-size: 14px;
        flex-shrink: 0;
    }
.status-badge.passed { background: var(--success-soft); color: var(--success); }
.status-badge.failed { background: var(--danger-soft); color: var(--danger); }
.status-badge.incomplete { background: #f1f5f9; color: #64748b; }
.status-badge.in_progress { background: var(--warning-soft); color: var(--warning); }
.status-badge.failed_continued { background: var(--warning-soft); color: var(--warning); }
.status-badge.not_recitate { background: #f1f5f9; color: #64748b; }
.score-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 10px;
        border-radius: 8px;
        font-weight: 700;
        font-size: 13px;
    }
.score-pill.passed { background: var(--success-soft); color: var(--success); }
.score-pill.failed { background: var(--danger-soft); color: var(--danger); }
.empty-state i { font-size: 22px; margin-bottom: 14px; color: #cbd5e1; }
.btn-action {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 7px 14px;
        border-radius: 10px;
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.2s;
        border: 1px solid transparent;
    }
.btn-action.view {
        background: var(--primary);
        color: white;
    }
.btn-action.view:hover {
        background: var(--primary-light);
        color: white;
    }
.btn-action.danger {
        background: var(--danger);
        color: white;
        border: none;
        cursor: pointer;
        font-family: inherit;
    }
.btn-action.danger:hover {
        background: #a82d22;
        color: white;
    }
.actions-wrap {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        flex-wrap: wrap;
    }
.actions-wrap form { display: inline; margin: 0; }
.btn-reset {
        background: white;
        color: #6c7a89;
        border: 1.5px solid #e9ecef;
        padding: 8px 16px;
        border-radius: 10px;
        font-size: 13px;
        font-weight: 600;
        text-decoration: none;
    }
.btn-reset:hover { background: #f8fafc; color: #6c7a89; }
.pagination-wrap {
        padding: 16px 16px;
        border-top: 1px solid #f0f3f7;
    }
.source-tabs {
        display: flex;
        gap: 6px;
        margin-bottom: 16px;
        background: white;
        padding: 6px;
        border-radius: 13px;
        box-shadow: var(--card-shadow);
        border: 1px solid rgba(24,43,72,0.05);
        flex-wrap: wrap;
    }
.source-tab {
        flex: 1;
        min-width: 140px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 11px 16px;
        border-radius: 10px;
        font-size: 14px;
        font-weight: 700;
        color: #6c7a89;
        text-decoration: none;
        transition: all 0.2s;
        background: transparent;
    }
.source-tab:hover {
        background: #f8fafc;
        color: var(--primary);
    }
.source-tab.active {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
        color: white;
        box-shadow: 0 4px 12px rgba(24,43,72,0.25);
    }
.source-tab .tab-count {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 24px;
        height: 22px;
        padding: 0 8px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 800;
        background: rgba(24,43,72,0.1);
        color: var(--primary);
    }
.source-tab.active .tab-count {
        background: var(--gold);
        color: var(--primary);
    }
.source-tab i { font-size: 14px; }
.header-actions { display: flex; gap: 10px; align-items: center; }
.btn-mafsil-guest {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 11px 18px;
    background: linear-gradient(135deg, #d4a030, #B4802C);
    color: #fff;
    border-radius: 12px;
    font-weight: 700;
    font-size: 13.5px;
    font-family: 'Tajawal', sans-serif;
    text-decoration: none;
    box-shadow: 0 4px 14px rgba(212,160,48,0.35);
    transition: all 0.3s ease;
}
.btn-mafsil-guest:hover {
    background: linear-gradient(135deg, #B4802C, #d4a030);
    color: #fff;
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(212,160,48,0.45);
}
</style>

<x-page-hero icon="fas fa-book-quran">
    <h1 class="page-banner-title">سجل اختبار الأجزاء</h1>
    <p class="page-banner-sub">عرض جميع جلسات اختبار الأجزاء المسجلة</p>
    <x-slot:actions>
        <div class="header-actions">
            <a href="{{ route('public.recitation.mafsil') }}" target="_blank" rel="noopener" class="btn-mafsil-guest">
                <i class="fas fa-external-link-alt"></i>
                <span>فتح اختبار الأجزاء (صفحة الزائر)</span>
            </a>
        </div>
    </x-slot:actions>
</x-page-hero>

<div class="source-tabs">
    <a href="{{ route('admin.mafsil-history.index', array_merge(request()->except('source', 'page'), ['source' => 'all'])) }}"
       class="source-tab {{ ($source ?? 'all') === 'all' ? 'active' : '' }}">
        <i class="fas fa-layer-group"></i>
        <span>جميع الجلسات</span>
        <span class="tab-count">{{ number_format($stats['public_count'] + $stats['supervised_count']) }}</span>
    </a>
    <a href="{{ route('admin.mafsil-history.index', array_merge(request()->except('source', 'page'), ['source' => 'supervised'])) }}"
       class="source-tab {{ ($source ?? 'all') === 'supervised' ? 'active' : '' }}">
        <i class="fas fa-user-tie"></i>
        <span>جلسات المشرفين</span>
        <span class="tab-count">{{ number_format($stats['supervised_count']) }}</span>
    </a>
    <a href="{{ route('admin.mafsil-history.index', array_merge(request()->except('source', 'page'), ['source' => 'public'])) }}"
       class="source-tab {{ ($source ?? 'all') === 'public' ? 'active' : '' }}">
        <i class="fas fa-globe"></i>
        <span>جلسات الزوار (الصفحة العامة)</span>
        <span class="tab-count">{{ number_format($stats['public_count']) }}</span>
    </a>
</div>

<div class="stat-grid">
    <div class="stat-card total">
        <div class="icon"><i class="fas fa-list"></i></div>
        <div>
            <p class="label">إجمالي الجلسات</p>
            <p class="value">{{ number_format($stats['total']) }}</p>
        </div>
    </div>
    <div class="stat-card passed">
        <div class="icon"><i class="fas fa-check-circle"></i></div>
        <div>
            <p class="label">الناجحون</p>
            <p class="value">{{ number_format($stats['passed']) }}</p>
        </div>
    </div>
    <div class="stat-card failed">
        <div class="icon"><i class="fas fa-times-circle"></i></div>
        <div>
            <p class="label">الراسبون</p>
            <p class="value">{{ number_format($stats['failed']) }}</p>
        </div>
    </div>
</div>

<div class="filter-card">
    <div class="filter-header">
        <i class="fas fa-filter"></i>
        <span>تصفية النتائج</span>
    </div>
    <div class="filter-body">
        <form method="GET" action="{{ route('admin.mafsil-history.index') }}">
            <input type="hidden" name="source" value="{{ $source ?? 'all' }}">
            <div class="row g-3">
                <div class="col-md-3">
                    <label>اسم الطالب</label>
                    <input type="text" name="student_name" class="form-control" value="{{ request('student_name') }}" placeholder="بحث...">
                </div>
                <div class="col-md-3">
                    <label>اسم المحضن</label>
                    <input type="text" name="mahd_name" class="form-control" value="{{ request('mahd_name') }}" placeholder="بحث...">
                </div>
                <div class="col-md-2">
                    <label>عدد الأجزاء</label>
                    <input type="number" name="mafsil_type" class="form-control" min="1" max="30" value="{{ request('mafsil_type') }}" placeholder="1-30">
                </div>
                <div class="col-md-2">
                    <label>الحالة</label>
                    <select name="status" class="form-select">
                        <option value="">الكل</option>
                        <option value="passed" {{ request('status') == 'passed' ? 'selected' : '' }}>ناجح</option>
                        <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>راسب</option>
                        <option value="incomplete" {{ request('status') == 'incomplete' ? 'selected' : '' }}>لم يكمل</option>
                        <option value="failed_continued" {{ request('status') == 'failed_continued' ? 'selected' : '' }}>راسب ومكمل</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label>من تاريخ</label>
                    <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
                </div>
                <div class="col-md-2">
                    <label>إلى تاريخ</label>
                    <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
                </div>
                <div class="col-md-12 d-flex align-items-end gap-2">
                    <button type="submit" class="btn-action view" style="padding:9px 16px">
                        <i class="fas fa-search"></i> بحث
                    </button>
                    <a href="{{ route('admin.mafsil-history.index', ['source' => $source ?? 'all']) }}" class="btn-reset">
                        <i class="fas fa-eraser"></i> مسح
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="data-card">
    <div class="data-card-header">
        <h6><i class="fas fa-list-ul me-1" style="color:var(--gold)"></i> الجلسات ({{ $sessions->total() }})</h6>
    </div>

    @if($sessions->isEmpty())
    <div class="empty-state">
        <i class="fas fa-inbox"></i>
        <h5>لا توجد جلسات</h5>
        <p>لم يتم تسجيل أي جلسة اختبار أجزاء بعد، أو لا توجد نتائج تطابق معايير التصفية.</p>
    </div>
    @else
    <div class="table-responsive">
        <table class="table-modern">
            <thead>
                <tr>
                    <th>#</th>
                    <th>الطالب</th>
                    <th>المحضن</th>
                    <th>التاريخ</th>
                    <th>عدد الأجزاء</th>
                    <th>العلامة</th>
                    <th>الحالة</th>
                    <th>إجراءات</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sessions as $i => $session)
                @php
                    $studentName = $session->student_name ?? ($session->student->user->name ?? 'غير معروف');
                    $isPassed = $session->status === 'passed';
                @endphp
                <tr>
                    <td>{{ $sessions->firstItem() + $i }}</td>
                    <td>
                        <div class="student-cell">
                            <div class="student-avatar">{{ mb_substr($studentName, 0, 1) }}</div>
                            <div>
                                <div style="font-weight:700;color:var(--primary)">{{ $studentName }}</div>
                                @if($session->student && $session->student->user)
                                <div style="font-size:12px;color:#94a3b8">طالب مسجل</div>
                                @else
                                <div style="font-size:12px;color:#94a3b8">زائر</div>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td>
                        @if($session->mahd_name)
                        <span style="display:inline-flex;align-items:center;gap:6px;color:#2d3a4a">
                            <i class="fas fa-mosque" style="color:var(--gold);font-size:13px"></i>
                            {{ $session->mahd_name }}
                        </span>
                        @else
                        <span style="color:#cbd5e1">—</span>
                        @endif
                    </td>
                    <td>
                        <div style="font-weight:600">{{ $session->session_date->format('Y-m-d') }}</div>
                        <div style="font-size:12px;color:#94a3b8">{{ $session->session_date->translatedFormat('l') }}</div>
                    </td>
                    <td>
                        <span style="display:inline-flex;align-items:center;gap:6px;background:#f8fafc;padding:5px 10px;border-radius:8px;font-weight:700;color:var(--primary)">
                            <i class="fas fa-layer-group" style="color:var(--gold);font-size:12px"></i>
                            {{ $session->mafsil_type }} أجزاء
                        </span>
                    </td>
                    <td>
                        @if($session->final_score !== null)
                        <span class="score-pill {{ $isPassed ? 'passed' : 'failed' }}">
                            {{ number_format((float) $session->final_score, 1) }}%
                        </span>
                        @else
                        <span style="color:#cbd5e1">—</span>
                        @endif
                    </td>
                    <td>
                        <span class="status-badge {{ $session->status }}">
                            @switch($session->status)
                                @case('passed') <i class="fas fa-check-circle"></i> ناجح @break
                                @case('failed') <i class="fas fa-times-circle"></i> راسب @break
                                @case('failed_continued') <i class="fas fa-exclamation-circle"></i> راسب ومكمل @break
                                @case('incomplete') <i class="fas fa-pause-circle"></i> لم يكمل @break
                                @case('in_progress') <i class="fas fa-spinner"></i> جارٍ @break
                                @default <i class="fas fa-info-circle"></i> {{ $session->status }}
                            @endswitch
                        </span>
                    </td>
                    <td>
                        <div class="actions-wrap">
                            <a href="{{ route('admin.mafsil-history.show', $session) }}" class="btn-action view">
                                <i class="fas fa-eye"></i> عرض
                            </a>
                            <form action="{{ route('admin.mafsil-history.destroy', ['sessionId' => $session->id, 'source' => $source]) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف هذا السجل؟ لا يمكن التراجع عن هذه العملية.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-action danger" title="حذف">
                                    <i class="fas fa-trash-alt"></i> حذف
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="pagination-wrap">
        {{ $sessions->links() }}
    </div>
    @endif
</div>
@endsection
