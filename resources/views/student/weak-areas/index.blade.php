@extends('layouts.app')

@section('title', 'المناطق الضعيفة')
@section('page-title', 'المناطق الضعيفة')
@section('role-label', 'طالب')

@section('sidebar-menu')
    @include('student.partials.sidebar')
@endsection

@push('styles')
<style>
.weak-areas-page {
    --gold: #d4a030;
    --gold-light: #e6b84d;
    --navy: #182B48;
    --navy-light: #2A4570;
    --success: #27ae60;
    --warning: #f39c12;
    --danger: #c0392b;
    --info: #3498db;
}

.weak-areas-page .page-hero {
    background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
    border-radius: 20px;
    padding: 24px 28px;
    margin-bottom: 24px;
    position: relative;
    overflow: hidden;
}

.weak-areas-page .page-hero::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23d4a030' fill-opacity='0.06'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    pointer-events: none;
}

.weak-areas-page .page-hero::after {
    content: '';
    position: absolute;
    top: 0; right: 0;
    width: 100px; height: 4px;
    background: linear-gradient(90deg, var(--gold), transparent);
}

.weak-areas-page .hero-icon {
    width: 56px; height: 56px;
    background: linear-gradient(135deg, var(--warning) 0%, #e67e22 100%);
    border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    font-size: 24px; color: white;
    box-shadow: 0 6px 18px rgba(243,156,18,0.35);
}

.weak-areas-page .hero-title { color: white; font-size: 22px; font-weight: 700; margin-bottom: 4px; }
.weak-areas-page .hero-sub { color: rgba(255,255,255,0.75); font-size: 13px; margin: 0; }

.weak-areas-page .stat-card {
    background: white;
    border-radius: 20px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.04);
    border: 1px solid rgba(24,43,72,0.04);
    overflow: hidden;
    transition: all 0.3s ease;
}

.weak-areas-page .stat-card:hover { transform: translateY(-3px); box-shadow: 0 8px 30px rgba(0,0,0,0.08); }

.weak-areas-page .stat-card-body { padding: 20px; text-align: center; }

.weak-areas-page .stat-icon {
    width: 50px; height: 50px;
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 22px;
    margin: 0 auto 12px;
}

.weak-areas-page .stat-icon.primary { background: linear-gradient(135deg, var(--navy), var(--navy-light)); color: var(--gold); }
.weak-areas-page .stat-icon.success { background: linear-gradient(135deg, var(--success), #2ecc71); color: white; }
.weak-areas-page .stat-icon.warning { background: linear-gradient(135deg, var(--warning), #e67e22); color: white; }

.weak-areas-page .stat-value { font-size: 26px; font-weight: 800; color: var(--navy); line-height: 1; }
.weak-areas-page .stat-label { font-size: 11px; color: #6c7a89; font-weight: 500; margin-top: 4px; text-transform: uppercase; }

.weak-areas-page .toolbar-card {
    background: white;
    border-radius: 20px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.04);
    border: 1px solid rgba(24,43,72,0.04);
    padding: 16px 24px;
    margin-bottom: 24px;
    display: flex; align-items: center; justify-content: space-between;
    flex-wrap: wrap;
    gap: 16px;
}

.weak-areas-page .toolbar-title { font-size: 16px; font-weight: 700; color: var(--navy); display: flex; align-items: center; gap: 10px; }
.weak-areas-page .toolbar-title i { color: var(--warning); }

.weak-areas-page .btn-refresh {
    background: linear-gradient(135deg, var(--navy), var(--navy-light));
    color: white;
    border: none;
    border-radius: 10px;
    padding: 10px 18px;
    font-weight: 600;
    font-size: 13px;
    transition: all 0.2s ease;
    display: flex; align-items: center; gap: 8px;
}

.weak-areas-page .btn-refresh:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(24,43,72,0.3); color: white; }

.weak-areas-page .areas-card {
    background: white;
    border-radius: 20px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.04);
    border: 1px solid rgba(24,43,72,0.04);
    overflow: hidden;
}

.weak-areas-page .areas-table { width: 100%; border-collapse: collapse; margin: 0; }
.weak-areas-page .areas-table thead th {
    background: linear-gradient(135deg, rgba(24,43,72,0.04) 0%, rgba(42,69,112,0.06) 100%);
    padding: 14px 16px;
    font-weight: 700;
    font-size: 11px;
    color: var(--navy);
    border: none;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.weak-areas-page .areas-table tbody td {
    padding: 14px 16px;
    font-size: 14px;
    color: #5a6c7d;
    border-bottom: 1px solid rgba(24,43,72,0.04);
    vertical-align: middle;
}
.weak-areas-page .areas-table tbody tr { transition: all 0.2s ease; }
.weak-areas-page .areas-table tbody tr:hover { background: linear-gradient(90deg, rgba(212,160,48,0.04) 0%, rgba(24,43,72,0.02) 100%); }

.weak-areas-page .rate-badge {
    padding: 6px 12px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 600;
}

.weak-areas-page .action-btn {
    width: 32px; height: 32px;
    border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    border: 1px solid rgba(24,43,72,0.1);
    background: white;
    color: var(--navy);
    transition: all 0.2s ease;
}

.weak-areas-page .action-btn:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(24,43,72,0.2); }

.weak-areas-page .action-btn.success { border-color: rgba(39,174,96,0.3); color: var(--success); }
.weak-areas-page .action-btn.success:hover { background: var(--success); color: white; }

.weak-areas-page .info-card {
    background: linear-gradient(135deg, rgba(52,152,219,0.08), rgba(41,128,185,0.08));
    border: 2px solid rgba(52,152,219,0.15);
    border-radius: 16px;
    padding: 20px;
}

.weak-areas-page .info-card h6 { color: var(--navy); font-weight: 700; margin-bottom: 8px; }
.weak-areas-page .info-card p { font-size: 13px; color: #6c7a89; margin-bottom: 0; }

.weak-areas-page .empty-state { text-align: center; padding: 80px 20px; }
.weak-areas-page .empty-icon {
    width: 100px; height: 100px;
    background: linear-gradient(135deg, rgba(39,174,96,0.15), rgba(46,204,113,0.15));
    border-radius: 24px;
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 24px;
}
.weak-areas-page .empty-icon i { font-size: 40px; color: var(--success); }

@media (max-width: 992px) {
    .weak-areas-page .page-hero { padding: 20px 24px; }
    .weak-areas-page .hero-icon { width: 48px; height: 48px; font-size: 20px; }
    .weak-areas-page .hero-title { font-size: 18px; }
}
</style>
@endpush

@section('content')
<div class="weak-areas-page">
    <div class="page-hero">
        <div class="d-flex align-items-center gap-3">
            <div class="hero-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div>
                <h1 class="hero-title">المناطق الضعيفة</h1>
                <p class="hero-sub">المناطق التي تحتاج تقوية إضافية</p>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-4">
            <div class="stat-card">
                <div class="stat-card-body">
                    <div class="stat-icon primary">
                        <i class="fas fa-list"></i>
                    </div>
                    <div class="stat-value">{{ $progress['total'] }}</div>
                    <div class="stat-label">إجمالي المناطق</div>
                </div>
            </div>
        </div>
        <div class="col-4">
            <div class="stat-card">
                <div class="stat-card-body">
                    <div class="stat-icon success">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-value">{{ $progress['remediated'] }}</div>
                    <div class="stat-label">تم تحسينها</div>
                </div>
            </div>
        </div>
        <div class="col-4">
            <div class="stat-card">
                <div class="stat-card-body">
                    <div class="stat-icon warning">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="stat-value">{{ $progress['percentage'] }}%</div>
                    <div class="stat-label">نسبة التحسن</div>
                </div>
            </div>
        </div>
    </div>

    <div class="toolbar-card">
        <div class="toolbar-title">
            <i class="fas fa-exclamation-triangle"></i>
            المناطق التي تحتاج تقوية
        </div>
        <a href="{{ route('student.weak-areas.refresh') }}" class="btn-refresh">
            <i class="fas fa-sync"></i>تحديث التحليل
        </a>
    </div>

    @if($weakAreas->count() > 0)
        <div class="areas-card">
            <div class="table-responsive">
                <table class="areas-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>السورة</th>
                            <th>الصفحة</th>
                            <th>معدل الخطأ</th>
                            <th>معدل التردد</th>
                            <th>الأولوية</th>
                            <th>المحاولات</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($weakAreas as $index => $area)
                        <tr>
                            <td><span style="color: var(--gold); font-weight: 700;">{{ $index + 1 }}</span></td>
                            <td class="fw-semibold" style="color: var(--navy);">سورة {{ $area->surah }}</td>
                            <td>{{ $area->page_number ?? '-' }}</td>
                            <td>
                                @php
                                    $errorColor = $area->error_rate >= 0.5 ? 'danger' : ($area->error_rate >= 0.3 ? 'warning' : 'success');
                                @endphp
                                <span class="rate-badge bg-{{ $errorColor }}" style="color: white;">
                                    {{ round($area->error_rate * 100) }}%
                                </span>
                            </td>
                            <td>
                                @php
                                    $hesitationColor = $area->hesitation_rate >= 0.5 ? 'danger' : ($area->hesitation_rate >= 0.3 ? 'warning' : 'success');
                                @endphp
                                <span class="rate-badge bg-{{ $hesitationColor }}" style="color: white;">
                                    {{ round($area->hesitation_rate * 100) }}%
                                </span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress-bar" style="width: 60px; height: 6px; background: rgba(24,43,72,0.1); border-radius: 3px; overflow: hidden;">
                                        <div style="width: {{ min(100, $area->priority_score * 100) }}%; height: 100%; background: var(--danger);"></div>
                                    </div>
                                    <small class="text-muted">{{ round($area->priority_score, 2) }}</small>
                                </div>
                            </td>
                            <td>{{ $area->attempt_count }}</td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('student.quran.page', $area->page_number ?? 1) }}" class="action-btn" title="اذهب للقرآن">
                                        <i class="fas fa-book-quran"></i>
                                    </a>
                                    <form action="{{ route('student.weak-areas.mark', $area) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="action-btn success" title="تم التحسن" onclick="return confirm('هل تحسن هذا المستوى؟')">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="areas-card">
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h5 class="mb-2" style="color: var(--success); font-weight: 700;">لا توجد مناطق ضعيفة!</h5>
                <p class="text-muted mb-3">أحسنت! لا توجد حالياً آيات تحتاج تقوية في التسميع</p>
                <a href="{{ route('student.sessions.index') }}" class="btn-start">
                    <i class="fas fa-microphone me-1"></i>تسميع جديد
                </a>
            </div>
        </div>
    @endif

    <div class="info-card mt-4">
        <h6><i class="fas fa-info-circle me-2"></i>كيف يعمل تحليل المناطق الضعيفة؟</h6>
        <p>
            يحلل النظام بيانات تسميعك السابقة لحساب نسبة الأخطاء والترددات في كل صفحة.
            إذا كانت نسبة الخطأ أكثر من 30% أو نسبة التردد أكثر من 40%،
            يتم تصنيف الصفحة كمنطقة ضعيفة تحتاج تقوية إضافية.
        </p>
    </div>
</div>
@endsection