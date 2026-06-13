@extends('layouts.app')

@section('title', 'تفاصيل الجلسة')
@section('page-title', 'تفاصيل جلسة التسميع')
@section('role-label', 'مدير')

@section('sidebar-menu')
    @include('admin.partials.sidebar')
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.sessions.index') }}">جلسات التسميع</a></li>
    <li class="breadcrumb-item active">تفاصيل الجلسة</li>
@endsection

@section('content')
<style>
:root {
    --primary: #182B48;
    --primary-light: #2A4570;
    --primary-lighter: #3D5A80;
    --gold: #d4a030;
    --gold-dark: #B4802C;
    --gold-light: #E8C45A;
    --success: #27ae60;
    --warning: #f39c12;
    --danger: #c0392b;
    --info: #3498db;
    --purple: #8e44ad;
    --bg-main: #f8fafc;
    --card-shadow: 0 4px 25px rgba(24, 43, 72, 0.08);
    --card-shadow-hover: 0 12px 40px rgba(24, 43, 72, 0.15);
}

body { background: var(--bg-main); font-family: 'Tajawal', sans-serif; }

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
    margin-bottom: 30px;
    flex-wrap: wrap;
}
.header-content { display: flex; align-items: center; gap: 20px; }
.header-icon {
    width: 70px; height: 70px;
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
    border-radius: 18px;
    display: flex; align-items: center; justify-content: center;
    font-size: 30px; color: var(--gold);
    box-shadow: 0 8px 25px rgba(24,43,72,0.25);
}
.header-text h4 { color: var(--primary); font-size: 24px; font-weight: 700; margin: 0 0 4px 0; }
.header-text p { color: #6c7a89; font-size: 14px; margin: 0; }
.header-text p::before { content: '✦'; color: var(--gold); margin-left: 8px; }

.btn-back {
    display: inline-flex; align-items: center; gap: 10px;
    padding: 14px 28px;
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
    color: #fff; border-radius: 14px;
    text-decoration: none; font-weight: 600; font-size: 14px;
    transition: all 0.3s ease;
    border: 2px solid transparent;
    box-shadow: 0 4px 15px rgba(24,43,72,0.25);
}
.btn-back:hover { transform: translateY(-3px); box-shadow: 0 8px 25px rgba(24,43,72,0.35); color: #fff; }
.btn-back i { color: var(--gold); }

.info-card {
    background: #fff; border-radius: 20px;
    box-shadow: var(--card-shadow); border: 1px solid rgba(24,43,72,0.05);
    overflow: hidden;
    margin-bottom: 25px;
}
.info-header {
    display: flex; align-items: center; gap: 22px;
    padding: 28px;
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
    position: relative;
}
.info-header::after {
    content: '';
    position: absolute; bottom: 0; left: 0; right: 0;
    height: 5px;
    background: linear-gradient(90deg, var(--gold), var(--gold-dark), var(--gold));
}
.info-icon {
    width: 65px; height: 65px;
    background: linear-gradient(135deg, var(--gold) 0%, var(--gold-dark) 100%);
    border-radius: 16px; display: flex; align-items: center; justify-content: center;
    font-size: 28px; color: #fff;
    box-shadow: 0 4px 15px rgba(212,160,48,0.35);
}
.info-content h5 { color: #fff; font-size: 20px; font-weight: 700; margin: 0 0 12px 0; }
.session-meta { display: flex; gap: 12px; flex-wrap: wrap; }
.meta-badge {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 8px 16px; border-radius: 20px;
    font-size: 14px; font-weight: 500;
    background: rgba(255,255,255,0.15);
    color: #fff;
    backdrop-filter: blur(5px);
}
.meta-badge i { color: var(--gold); }
.type-badge {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 8px 18px; border-radius: 20px;
    font-size: 13px; font-weight: 600;
}
.type-badge.new_memorization { background: rgba(39,174,96,0.25); color: #2ecc71; border: 1px solid rgba(39,174,96,0.3); }
.type-badge.review { background: rgba(52,152,219,0.25); color: #5dade2; border: 1px solid rgba(52,152,219,0.3); }
.type-badge.unmastered { background: rgba(243,156,18,0.25); color: #f1c40f; border: 1px solid rgba(243,156,18,0.3); }
.type-badge.recitation { background: rgba(155,89,182,0.25); color: #bb8fce; border: 1px solid rgba(155,89,182,0.3); }
.type-badge.mafsil { background: rgba(231,76,60,0.25); color: #e74c3c; border: 1px solid rgba(231,76,60,0.3); }
.type-badge i { font-size: 12px; }

.section-card {
    background: #fff; border-radius: 20px;
    box-shadow: var(--card-shadow); border: 1px solid rgba(24,43,72,0.05);
    overflow: hidden; margin-bottom: 25px;
    transition: all 0.3s ease;
}
.section-card:hover { transform: translateY(-3px); box-shadow: 0 8px 35px rgba(24,43,72,0.1); }
.section-header {
    display: flex; align-items: center; gap: 12px;
    padding: 20px 28px;
    background: linear-gradient(135deg, rgba(24,43,72,0.03) 0%, rgba(26,51,86,0.05) 100%);
    border-bottom: 1px solid rgba(24,43,72,0.05);
    font-weight: 700; font-size: 17px; color: var(--primary);
}
.section-header i { color: var(--gold); font-size: 20px; }
.section-body { padding: 25px 28px; }

.student-info-card {
    background: linear-gradient(135deg, rgba(24,43,72,0.04) 0%, rgba(42,69,112,0.06) 100%);
    border: 2px solid rgba(24,43,72,0.08);
    border-radius: 16px;
    padding: 20px;
    text-align: center;
}
.student-avatar {
    width: 80px; height: 80px;
    background: linear-gradient(135deg, var(--purple) 0%, #7d3c98 100%);
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 15px;
    font-size: 32px; color: #fff;
    box-shadow: 0 4px 15px rgba(142,68,173,0.3);
}
.student-name { font-size: 20px; font-weight: 700; color: var(--primary); margin-bottom: 5px; }
.student-number { font-size: 14px; color: #95a5a6; }

.stats-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; }
.stat-card {
    background: linear-gradient(135deg, rgba(24,43,72,0.04) 0%, rgba(42,69,112,0.06) 100%);
    border: 1px solid rgba(24,43,72,0.06);
    border-radius: 14px;
    padding: 20px;
    text-align: center;
    transition: all 0.3s ease;
}
.stat-card:hover { transform: translateY(-3px); border-color: var(--gold); }
.stat-card .stat-number { font-size: 32px; font-weight: 800; color: var(--primary); line-height: 1; margin-bottom: 8px; }
.stat-card .stat-number.success { color: var(--success); }
.stat-card .stat-number.warning { color: var(--warning); }
.stat-card .stat-number.danger { color: var(--danger); }
.stat-card .stat-number.gold { color: var(--gold); }
.stat-card .stat-label { font-size: 13px; color: #6c7a89; font-weight: 500; }

.part-info {
    display: flex; gap: 15px; flex-wrap: wrap;
}
.part-item {
    flex: 1;
    background: linear-gradient(135deg, rgba(24,43,72,0.04) 0%, rgba(42,69,112,0.06) 100%);
    border: 1px solid rgba(24,43,72,0.06);
    border-radius: 12px;
    padding: 18px;
    text-align: center;
}
.part-item .part-label { font-size: 12px; color: #6c7a89; margin-bottom: 5px; }
.part-item .part-value { font-size: 18px; font-weight: 700; color: var(--primary); }

.pages-table {
    width: 100%; border-collapse: collapse;
}
.pages-table thead {
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
    color: #fff;
}
.pages-table th {
    padding: 14px 18px;
    font-size: 12px; font-weight: 700;
    text-align: center;
    border: none;
}
.pages-table td {
    padding: 14px 18px;
    font-size: 14px; color: #5a6c7d;
    border-bottom: 1px solid rgba(24,43,72,0.05);
    text-align: center;
}
.pages-table tbody tr { transition: all 0.3s ease; }
.pages-table tbody tr:hover { background: linear-gradient(135deg, rgba(212,160,48,0.05) 0%, rgba(180,128,44,0.08) 100%); }
.pages-table tbody tr:last-child td { border-bottom: none; }

.page-score { font-weight: 700; color: var(--primary); }

.color-indicator {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 6px 14px; border-radius: 20px;
    font-size: 12px; font-weight: 600;
}
.color-indicator.excellent { background: rgba(39,174,96,0.15); color: var(--success); }
.color-indicator.good { background: rgba(52,152,219,0.15); color: var(--info); }
.color-indicator.acceptable { background: rgba(243,156,18,0.15); color: var(--warning); }
.color-indicator.poor { background: rgba(192,57,43,0.15); color: var(--danger); }
.color-indicator::before { content: ''; width: 8px; height: 8px; border-radius: 50%; background: currentColor; }

.notes-card {
    background: linear-gradient(135deg, rgba(212,160,48,0.08) 0%, rgba(180,128,44,0.12) 100%);
    border: 2px solid rgba(212,160,48,0.15);
    border-radius: 16px;
    padding: 22px;
}
.notes-card p { font-size: 15px; color: #5a6c7d; line-height: 1.8; margin: 0; }

@media (max-width: 992px) {
    .page-header { flex-direction: column; align-items: flex-start; }
    .header-content { margin-bottom: 15px; }
    .stats-grid { grid-template-columns: 1fr; }
}
</style>

<div class="page-header">
    <div class="header-content">
        <div class="header-icon">
            <i class="fas fa-quran"></i>
        </div>
        <div class="header-text">
            <h4>تفاصيل جلسة التسميع</h4>
            <p>بيانات وتقرير جلسة تحفيظ القرآن الكريم</p>
        </div>
    </div>
    <a href="{{ route('admin.sessions.index') }}" class="btn-back">
        <i class="fas fa-arrow-right"></i>العودة للقائمة
    </a>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="info-card">
            <div class="info-header">
                <div class="info-icon">
                    <i class="fas fa-calendar-day"></i>
                </div>
                <div class="info-content">
                    <h5>معلومات الجلسة</h5>
                    <div class="session-meta">
                        <span class="meta-badge date">
                            <i class="fas fa-calendar-alt"></i>
                            {{ $session->session_date->format('Y/m/d') }}
                        </span>
                        <span class="type-badge {{ $session->type }}">
                            @if($session->type === 'new_memorization')
                                <i class="fas fa-star"></i>حفظ جديد
                            @elseif($session->type === 'review')
                                <i class="fas fa-redo"></i>مراجعة
                            @elseif($session->type === 'unmastered')
                                <i class="fas fa-exclamation-circle"></i>غير متقن
                            @elseif($session->type === 'recitation')
                                <i class="fas fa-book-open"></i>سرد
                            @else
                                <i class="fas fa-layer-group"></i>مفصل
                            @endif
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="section-card">
            <div class="section-header">
                <i class="fas fa-chart-pie"></i>
                <span>إحصائيات الجلسة</span>
            </div>
            <div class="section-body">
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-number gold">{{ $session->total_pages }}</div>
                        <div class="stat-label">إجمالي الصفحات</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number {{ $session->total_errors > 0 ? 'danger' : 'success' }}">{{ $session->total_errors }}</div>
                        <div class="stat-label">إجمالي الأخطاء</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number {{ $session->total_hesitations > 0 ? 'warning' : 'success' }}">{{ $session->total_hesitations }}</div>
                        <div class="stat-label">إجمالي الترددات</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number gold">{{ $session->final_score !== null ? number_format($session->final_score, 1) : '-' }}/{{ $session->max_score }}</div>
                        <div class="stat-label">الدرجة النهائية</div>
                    </div>
                </div>
            </div>
        </div>

        @if($session->part || $session->start_page)
        <div class="section-card">
            <div class="section-header">
                <i class="fas fa-book"></i>
                <span>معلومات الجزء والصفحات</span>
            </div>
            <div class="section-body">
                <div class="part-info">
                    @if($session->part)
                    <div class="part-item">
                        <div class="part-label">الجزء</div>
                        <div class="part-value">جزء {{ $session->part->part_number }}</div>
                    </div>
                    <div class="part-item">
                        <div class="part-label">اسم الجزء</div>
                        <div class="part-value">{{ $session->part->name }}</div>
                    </div>
                    @endif
                    <div class="part-item">
                        <div class="part-label">صفحات التسميع</div>
                        <div class="part-value">{{ $session->start_page }} - {{ $session->end_page }}</div>
                    </div>
                    @if($session->unmastered_pages_count !== null)
                    <div class="part-item">
                        <div class="part-label">الصفحات غير المتقنة</div>
                        <div class="part-value" style="color: var(--warning);">{{ $session->unmastered_pages_count }}</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endif

        @if($session->pages->isNotEmpty())
        <div class="section-card">
            <div class="section-header">
                <i class="fas fa-list"></i>
                <span>تفاصيل الصفحات</span>
            </div>
            <div class="section-body" style="padding: 0;">
                <table class="pages-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>رقم الصفحة</th>
                            <th>الموضع</th>
                            <th>الأخطاء</th>
                            <th>الترددات</th>
                            <th>التقييم</th>
                            <th>الدرجة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($session->pages as $index => $page)
                        <tr>
                            <td><span style="color: var(--gold); font-weight: 700;">{{ $index + 1 }}</span></td>
                            <td class="page-score">{{ $page->page_number }}</td>
                            <td>{{ $page->position_label ?? '—' }}</td>
                            <td>
                                @if($page->errors > 0)
                                    <span style="color: var(--danger); font-weight: 700;">{{ $page->errors }}</span>
                                @else
                                    <span style="color: #ccc;">—</span>
                                @endif
                            </td>
                            <td>
                                @if($page->hesitations > 0)
                                    <span style="color: var(--warning); font-weight: 700;">{{ $page->hesitations }}</span>
                                @else
                                    <span style="color: #ccc;">—</span>
                                @endif
                            </td>
                            <td>
                                <span class="color-indicator {{ $page->color_class }}">
                                    {{ $page->color_label }}
                                </span>
                            </td>
                            <td class="page-score">{{ $page->page_score !== null ? number_format($page->page_score, 1) : '—' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        @if($session->notes)
        <div class="section-card">
            <div class="section-header">
                <i class="fas fa-sticky-note"></i>
                <span>ملاحظات</span>
            </div>
            <div class="section-body">
                <div class="notes-card">
                    <p>{{ $session->notes }}</p>
                </div>
            </div>
        </div>
        @endif
    </div>

    <div class="col-lg-4">
        <div class="section-card" style="position: sticky; top: 20px;">
            <div class="section-header">
                <i class="fas fa-user-graduate"></i>
                <span>معلومات الطالب</span>
            </div>
            <div class="section-body" style="text-align: center;">
                <div class="student-info-card">
                    <div class="student-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="student-name">{{ $session->student->user->name }}</div>
                    <div class="student-number">{{ $session->student->student_number }}</div>
                </div>

                @if($session->supervisor)
                <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid rgba(24,43,72,0.08);">
                    <div style="display: flex; align-items: center; justify-content: center; gap: 12px;">
                        <div style="width: 45px; height: 45px; background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%); border-radius: 12px; display: flex; align-items: center; justify-content: center; color: var(--gold);">
                            <i class="fas fa-user-tie"></i>
                        </div>
                        <div style="text-align: right;">
                            <div style="font-weight: 700; color: var(--primary);">{{ $session->supervisor->name }}</div>
                            <div style="font-size: 12px; color: #95a5a6;">المشرف</div>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection