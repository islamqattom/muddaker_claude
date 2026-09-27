@extends('layouts.app')

@section('title', 'تفاصيل الجلسة')
@section('page-title', 'تفاصيل جلسة التسميع')
@section('role-label', 'ولي أمر')

@section('sidebar-menu')
    @include('parent.partials.sidebar')
@endsection

@push('styles')
<style>
.session-detail {
    --gold: #d4a030;
    --gold-light: #e6b84d;
    --navy: #182B48;
    --navy-light: #2A4570;
    --success: #27ae60;
    --warning: #f39c12;
    --danger: #c0392b;
    --info: #3498db;
    --purple: #8e44ad;
}

.session-detail .page-hero {
    background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
    border-radius: 14px;
    padding: 16px 22px;
    margin-bottom: 16px;
    position: relative;
    overflow: hidden;
}

.session-detail .page-hero::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23d4a030' fill-opacity='0.06'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    pointer-events: none;
}

.session-detail .hero-content { position: relative; z-index: 1; }

.session-detail .hero-icon {
    width: 48px; height: 48px;
    background: linear-gradient(135deg, var(--gold) 0%, var(--gold-light) 100%);
    border-radius: 13px;
    display: flex; align-items: center; justify-content: center;
    font-size: 20px; color: white;
    box-shadow: 0 6px 20px rgba(212, 160, 48, 0.35);
}

.session-detail .hero-title { color: white; font-size: 18px; font-weight: 700; }
.session-detail .hero-sub { color: rgba(255,255,255,0.75); font-size: 12.5px; }

.session-detail .quran-verse {
    font-family: 'Amiri', serif;
    font-size: 13px;
    color: rgba(255, 255, 255, 0.6);
    text-align: center;
    padding: 10px 20px;
    background: rgba(0,0,0,0.1);
    border-top: 1px solid rgba(212, 160, 48, 0.2);
    margin-top: 20px;
    position: relative; z-index: 1;
}

.session-detail .info-card {
    background: white;
    border-radius: 13px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
    border: 1px solid rgba(24, 43, 72, 0.04);
    overflow: hidden;
    margin-bottom: 16px;
    transition: all 0.3s ease;
    position: relative;
}

.session-detail .info-card:hover { transform: translateY(-3px); box-shadow: 0 8px 30px rgba(0,0,0,0.08); }

.session-detail .card-accent {
    position: absolute;
    top: 0; right: 0;
    width: 80px; height: 4px;
    background: linear-gradient(90deg, var(--gold), transparent);
}

.session-detail .info-header {
    background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
    padding: 12px 16px;
    display: flex; align-items: center; gap: 10px;
}

.session-detail .info-header i { color: var(--gold); font-size: 16px; }
.session-detail .info-header h5 { color: white; font-weight: 700; margin: 0; font-size: 14px; }

.session-detail .info-body { padding: 16px; }

.session-detail .student-info-box {
    background: linear-gradient(135deg, rgba(24, 43, 72, 0.04) 0%, rgba(42, 69, 112, 0.06) 100%);
    border: 2px solid rgba(24, 43, 72, 0.08);
    border-radius: 13px;
    padding: 16px;
    text-align: center;
}

.session-detail .student-avatar {
    width: 60px; height: 60px;
    background: linear-gradient(135deg, var(--purple) 0%, #7d3c98 100%);
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 12px;
    font-size: 24px; color: #fff;
    box-shadow: 0 4px 15px rgba(142, 68, 173, 0.3);
}

.session-detail .student-name { font-size: 18px; font-weight: 700; color: var(--navy); margin-bottom: 6px; }
.session-detail .student-number { font-size: 12.5px; color: #95a5a6; }

.session-detail .stat-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; }

.session-detail .stat-box {
    background: linear-gradient(135deg, rgba(24, 43, 72, 0.04) 0%, rgba(42, 69, 112, 0.06) 100%);
    border: 1px solid rgba(24, 43, 72, 0.06);
    border-radius: 13px;
    padding: 14px;
    text-align: center;
    transition: all 0.3s ease;
}

.session-detail .stat-box:hover { transform: translateY(-2px); border-color: var(--gold); }

.session-detail .stat-box .stat-value {
    font-size: 22px; font-weight: 800; line-height: 1;
    margin-bottom: 3px;
}

.session-detail .stat-box .stat-value.primary { color: var(--navy); }
.session-detail .stat-box .stat-value.success { color: var(--success); }
.session-detail .stat-box .stat-value.danger { color: var(--danger); }
.session-detail .stat-box .stat-value.warning { color: var(--warning); }

.session-detail .stat-box .stat-label { font-size: 12px; color: #6c7a89; font-weight: 500; }

.session-detail .part-info-grid {
    display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 14px;
}

.session-detail .part-item {
    background: linear-gradient(135deg, rgba(24, 43, 72, 0.04) 0%, rgba(42, 69, 112, 0.06) 100%);
    border: 1px solid rgba(24, 43, 72, 0.06);
    border-radius: 13px;
    padding: 14px;
    text-align: center;
}

.session-detail .part-item .part-label { font-size: 11px; color: #6c7a89; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.5px; }
.session-detail .part-item .part-value { font-size: 16px; font-weight: 700; color: var(--navy); }

.session-detail .pages-table {
    width: 100%; border-collapse: collapse;
    margin: 0;
}

.session-detail .pages-table thead {
    background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
    color: #fff;
}

.session-detail .pages-table th {
    padding: 11px 14px;
    font-size: 11px; font-weight: 700;
    text-align: center;
    border: none;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.session-detail .pages-table tbody tr { transition: all 0.2s ease; }
.session-detail .pages-table tbody tr:hover { background: linear-gradient(135deg, rgba(212, 160, 48, 0.05) 0%, rgba(180, 128, 44, 0.08) 100%); }
.session-detail .pages-table tbody tr:last-child td { border-bottom: none; }

.session-detail .pages-table td {
    padding: 11px 14px;
    font-size: 13px;
    color: #5a6c7d;
    border-bottom: 1px solid rgba(24, 43, 72, 0.04);
    text-align: center;
}

.session-detail .color-indicator {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 3px 10px; border-radius: 7px;
    font-size: 11.5px; font-weight: 600;
}

.session-detail .color-indicator.excellent { background: rgba(39,174,96,0.15); color: var(--success); }
.session-detail .color-indicator.good { background: rgba(52,152,219,0.15); color: var(--info); }
.session-detail .color-indicator.acceptable { background: rgba(243,156,18,0.15); color: var(--warning); }
.session-detail .color-indicator.poor { background: rgba(192,57,43,0.15); color: var(--danger); }
.session-detail .color-indicator::before { content: ''; width: 8px; height: 8px; border-radius: 50%; background: currentColor; }

.session-detail .notes-box {
    background: linear-gradient(135deg, rgba(212, 160, 48, 0.08) 0%, rgba(180, 128, 44, 0.12) 100%);
    border: 2px solid rgba(212, 160, 48, 0.15);
    border-radius: 13px;
    padding: 16px;
}

.session-detail .notes-box p { font-size: 13px; color: #5a6c7d; line-height: 1.7; margin: 0; }

.session-detail .type-badge {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 3px 10px; border-radius: 7px;
    font-size: 11.5px; font-weight: 600;
}

.session-detail .type-badge.new_memorization { background: rgba(39,174,96,0.15); color: var(--success); }
.session-detail .type-badge.review { background: rgba(52,152,219,0.15); color: var(--info); }
.session-detail .type-badge.unmastered { background: rgba(243,156,18,0.15); color: var(--warning); }
.session-detail .type-badge.recitation { background: rgba(155,89,182,0.15); color: var(--purple); }
.session-detail .type-badge.mafsil { background: rgba(231,76,60,0.15); color: var(--danger); }

.session-detail .meta-date {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 6px 12px; border-radius: 999px;
    background: rgba(255,255,255,0.15);
    color: #fff;
    font-size: 12.5px;
    font-weight: 500;
}

.session-detail .meta-date i { color: var(--gold); }

.session-detail .empty-state { text-align: center; padding: 40px 16px; }
.session-detail .empty-state i { font-size: 26px; color: rgba(24, 43, 72, 0.3); }

@media (max-width: 992px) {
    .session-detail .stat-grid { grid-template-columns: repeat(2, 1fr); }
    .session-detail .hero-icon { width: 48px; height: 48px; font-size: 20px; }
    .session-detail .hero-title { font-size: 18px; }
}
</style>
@endpush

@section('content')
<div class="session-detail">
    <div class="page-hero">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div class="hero-content d-flex align-items-center gap-3">
                <div class="hero-icon">
                    <i class="fas fa-quran"></i>
                </div>
                <div>
                    <h1 class="hero-title">تفاصيل جلسة التسميع</h1>
                    <p class="hero-sub">بيانات وتقرير جلسة التحفيظ للطالب</p>
                </div>
            </div>
            <div class="d-flex align-items-center gap-3">
                <span class="meta-date">
                    <i class="fas fa-calendar-alt"></i>
                    {{ $session->session_date->format('Y/m/d') }}
                </span>
                <span class="type-badge {{ $session->type }}">
                    {{ $session->type_label }}
                </span>
            </div>
        </div>
        <div class="quran-verse">
            <i class="fas fa-book-quran me-2"></i>
            {{ 'وَرَتِّلِ الْقُرْآنَ تَرْتِيلًا' }}
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="info-card">
                <div class="card-accent"></div>
                <div class="info-header">
                    <i class="fas fa-chart-pie"></i>
                    <h5>إحصائيات الجلسة</h5>
                </div>
                <div class="info-body">
                    <div class="stat-grid">
                        <div class="stat-box">
                            <div class="stat-value primary">{{ $session->total_pages }}</div>
                            <div class="stat-label">إجمالي الصفحات</div>
                        </div>
                        <div class="stat-box">
                            <div class="stat-value {{ $session->total_errors > 0 ? 'danger' : 'success' }}">{{ $session->total_errors }}</div>
                            <div class="stat-label">الأخطاء</div>
                        </div>
                        <div class="stat-box">
                            <div class="stat-value {{ $session->total_hesitations > 0 ? 'warning' : 'success' }}">{{ $session->total_hesitations }}</div>
                            <div class="stat-label">الترددات</div>
                        </div>
                        <div class="stat-box">
                            <div class="stat-value primary">{{ $session->final_score !== null ? number_format($session->final_score, 1) : '-' }}</div>
                            <div class="stat-label">العلامة</div>
                        </div>
                    </div>
                </div>
            </div>

            @if($session->part || $session->start_page)
            <div class="info-card">
                <div class="card-accent"></div>
                <div class="info-header">
                    <i class="fas fa-book"></i>
                    <h5>معلومات الجزء</h5>
                </div>
                <div class="info-body">
                    <div class="part-info-grid">
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
                            <div class="part-label">غير المتقنة</div>
                            <div class="part-value" style="color: var(--warning);">{{ $session->unmastered_pages_count }}</div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            @if($session->pages->isNotEmpty())
            <div class="info-card">
                <div class="card-accent"></div>
                <div class="info-header">
                    <i class="fas fa-list"></i>
                    <h5>تفاصيل الصفحات</h5>
                </div>
                <div class="info-body" style="padding: 0;">
                    <div class="table-responsive">
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
                                    <td class="fw-bold" style="color: var(--navy);">{{ $page->page_number }}</td>
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
                                    <td class="fw-bold" style="color: var(--navy);">
                                        {{ $page->page_score !== null ? number_format($page->page_score, 1) : '—' }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif

            @if($session->notes)
            <div class="info-card">
                <div class="card-accent"></div>
                <div class="info-header">
                    <i class="fas fa-sticky-note"></i>
                    <h5>ملاحظات</h5>
                </div>
                <div class="info-body">
                    <div class="notes-box">
                        <p>{{ $session->notes }}</p>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <div class="col-lg-4">
            <div class="info-card">
                <div class="card-accent"></div>
                <div class="info-header">
                    <i class="fas fa-user-graduate"></i>
                    <h5>معلومات الطالب</h5>
                </div>
                <div class="info-body" style="text-align: center;">
                    <div class="student-info-box">
                        <div class="student-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="student-name">{{ $session->student->user->name }}</div>
                        @if($session->student->student_number)
                            <div class="student-number">{{ $session->student->student_number }}</div>
                        @endif
                    </div>

                    @if($session->supervisor)
                    <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid rgba(24,43,72,0.06);">
                        <div style="display: flex; align-items: center; justify-content: center; gap: 12px;">
                            <div style="width: 38px; height: 38px; background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%); border-radius: 10px; display: flex; align-items: center; justify-content: center; color: var(--gold);">
                                <i class="fas fa-user-tie"></i>
                            </div>
                            <div style="text-align: right;">
                                <div style="font-weight: 700; color: var(--navy);">{{ $session->supervisor->name }}</div>
                                <div style="font-size: 12px; color: #95a5a6;">المشرف</div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection