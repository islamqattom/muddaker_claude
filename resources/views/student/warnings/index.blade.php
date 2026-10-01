@extends('layouts.app')
@section('title', 'الإنذارات')
@section('page-title', 'الإنذارات')
@section('role-label', 'طالب')

@section('sidebar-menu')
    @include('student.partials.sidebar')
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item active">الإنذارات</li>
@endsection

@push('styles')
<style>
.student-warnings {
    --gold: #d4a030;
    --gold-light: #e6b84d;
    --navy: #182B48;
    --navy-light: #2A4570;
    --success: #27ae60;
    --warning: #f39c12;
    --danger: #c0392b;
    --info: #3498db;
}

.student-warnings .page-hero {
    background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
    border-radius: 14px;
    padding: 16px 22px;
    margin-bottom: 16px;
    position: relative;
    overflow: hidden;
}

.student-warnings .page-hero::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23d4a030' fill-opacity='0.06'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    pointer-events: none;
}

.student-warnings .page-hero::after {
    content: '';
    position: absolute;
    top: 0; right: 0;
    width: 100px; height: 4px;
    background: linear-gradient(90deg, var(--gold), transparent);
}

.student-warnings .hero-icon {
    width: 48px; height: 48px;
    background: linear-gradient(135deg, var(--danger) 0%, #e74c3c 100%);
    border-radius: 13px;
    display: flex; align-items: center; justify-content: center;
    font-size: 20px; color: white;
    box-shadow: 0 6px 18px rgba(192,57,43,0.35);
    flex-shrink: 0;
}

.student-warnings .hero-title { color: white; font-size: 18px; font-weight: 700; margin-bottom: 4px; }
.student-warnings .hero-sub { color: rgba(255,255,255,0.75); font-size: 12.5px; margin: 0; }

.student-warnings .warnings-card {
    background: white;
    border-radius: 13px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.04);
    border: 1px solid rgba(24,43,72,0.04);
    overflow: hidden;
}

.student-warnings .warnings-header {
    background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
    padding: 12px 16px;
    display: flex; align-items: center; justify-content: space-between;
}

.student-warnings .warnings-header h5 { color: white; font-weight: 700; margin: 0; font-size: 14px; display: flex; align-items: center; gap: 8px; }
.student-warnings .warnings-header h5 i { color: var(--gold); }

.student-warnings .warnings-table { margin: 0; }

.student-warnings .warnings-table thead th {
    background: linear-gradient(135deg, rgba(24,43,72,0.04) 0%, rgba(42,69,112,0.06) 100%);
    padding: 11px 14px;
    font-weight: 700;
    font-size: 11px;
    color: var(--navy);
    border: none;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.student-warnings .warnings-table tbody td {
    padding: 11px 14px;
    vertical-align: middle;
    border-color: rgba(24,43,72,0.04);
    font-size: 13px;
}

.student-warnings .warnings-table tbody tr { transition: all 0.2s ease; }
.student-warnings .warnings-table tbody tr:hover { background: linear-gradient(90deg, rgba(212,160,48,0.04) 0%, rgba(24,43,72,0.02) 100%); }
.student-warnings .warnings-table tbody tr:hover td:first-child { border-right: 3px solid var(--gold); }

.student-warnings .type-badge {
    padding: 3px 10px; border-radius: 7px;
    font-size: 11.5px; font-weight: 600;
}

.student-warnings .type-badge.attendance { background: rgba(243,156,18,0.15); color: var(--warning); }
.student-warnings .type-badge.recitation { background: rgba(52,152,219,0.15); color: var(--info); }
.student-warnings .type-badge.general { background: rgba(108,117,125,0.15); color: #6c757d; }

.student-warnings .warning-number {
    font-family: monospace;
    background: rgba(24,43,72,0.06);
    padding: 3px 10px;
    border-radius: 7px;
    font-size: 11.5px;
    font-weight: 600;
    color: var(--navy);
}

.student-warnings .view-btn {
    width: 30px; height: 30px;
    border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    border: 1px solid rgba(24,43,72,0.1);
    background: white;
    color: var(--navy);
    transition: all 0.2s ease;
}

.student-warnings .view-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(24,43,72,0.2);
    background: var(--navy);
    color: white;
}

.student-warnings .pagination-wrapper {
    padding: 12px 16px;
    border-top: 1px solid rgba(24,43,72,0.04);
    display: flex; justify-content: center;
}

.student-warnings .empty-state { text-align: center; padding: 40px 16px; }
.student-warnings .empty-icon {
    width: 66px; height: 66px;
    background: linear-gradient(135deg, rgba(39,174,96,0.15), rgba(46,204,113,0.15));
    border-radius: 13px;
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 14px;
}
.student-warnings .empty-icon i { font-size: 26px; color: var(--success); }

@media (max-width: 992px) {
    .student-warnings .page-hero { padding: 14px 16px; }
    .student-warnings .hero-icon { width: 48px; height: 48px; font-size: 20px; }
    .student-warnings .hero-title { font-size: 18px; }
}
</style>
@endpush

@section('content')
<div class="student-warnings">
    <x-page-hero icon="fas fa-exclamation-triangle">
        <h1 class="page-banner-title">الإنذارات</h1>
        <p class="page-banner-sub">سجل الإنذارات والتوبيخات</p>
    </x-page-hero>

    <div class="warnings-card">
        <div class="warnings-header">
            <h5><i class="fas fa-list"></i>جدول الإنذارات</h5>
            <span class="badge" style="background: rgba(255,255,255,0.2); color: white; font-size: 11.5px; padding: 3px 10px; border-radius: 999px; font-weight: 600;">
                <i class="fas fa-exclamation-circle me-1"></i>
                {{ $warnings->total() }} إنذار
            </span>
        </div>

        @if($warnings->isEmpty())
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h5 class="text-muted mb-2">لا توجد إنذارات</h5>
                <p class="text-muted small">لم يتم تسجيل أي إنذارات بحقك حتى الآن</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table warnings-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>نوع الإنذار</th>
                            <th>العنوان</th>
                            <th>التاريخ</th>
                            <th>الرقم</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($warnings as $index => $warning)
                        <tr>
                            <td><span style="color: var(--gold); font-weight: 700;">{{ $warnings->firstItem() + $index }}</span></td>
                            <td>
                                <span class="type-badge {{ $warning->type }}">
                                    @if($warning->type === 'attendance')
                                        <i class="fas fa-calendar-times me-1"></i>حضور
                                    @elseif($warning->type === 'recitation')
                                        <i class="fas fa-microphone me-1"></i>تسميع
                                    @else
                                        <i class="fas fa-exclamation-circle me-1"></i>عام
                                    @endif
                                </span>
                            </td>
                            <td class="fw-semibold" style="color: var(--navy);">{{ $warning->title }}</td>
                            <td><span class="date-val">{{ $warning->issue_date->format('Y/m/d') }}</span></td>
                            <td><span class="warning-number">{{ $warning->warning_number }}</span></td>
                            <td>
                                <a href="{{ route('student.warnings.show', $warning->id) }}" class="view-btn" title="عرض التفاصيل">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="pagination-wrapper">
                {{ $warnings->links() }}
            </div>
        @endif
    </div>
</div>
@endsection