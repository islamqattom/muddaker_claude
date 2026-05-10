@extends('layouts.app')

@section('title', 'تفاصيل الجلسة')
@section('page-title', 'تفاصيل جلسة التسميع')
@section('role-label', 'مشرف')

@section('sidebar-menu')
    @include('supervisor.partials.sidebar')
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('supervisor.dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route('supervisor.recitation.history') }}">سجل الجلسات</a></li>
    <li class="breadcrumb-item active">تفاصيل الجلسة</li>
@endsection

@section('content')
<div class="row">
    <!-- Student Info Card -->
    <div class="col-md-4 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-user-graduate me-2"></i>
                    معلومات الطالب
                </h6>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="user-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <div>
                        <div class="fw-semibold">{{ $session->student->user->name }}</div>
                        <div class="text-muted small">رقم الملف: {{ $session->student->id }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Session Overview Card -->
    <div class="col-md-4 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-info-circle me-2"></i>
                    معلومات الجلسة
                </h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="text-muted small">نوع الجلسة</label>
                    <div class="fw-semibold">{{ $session->type_label }}</div>
                </div>
                <div class="mb-3">
                    <label class="text-muted small">التاريخ</label>
                    <div class="fw-semibold">{{ $session->session_date->format('Y/m/d') }}</div>
                </div>
                <div class="mb-3">
                    <label class="text-muted small">الحالة</label>
                    <div>
                        <span class="badge bg-{{ $session->status_color }}">{{ $session->status_label }}</span>
                    </div>
                </div>
                @if($session->supervisor)
                <div>
                    <label class="text-muted small">المشرف</label>
                    <div class="fw-semibold">{{ $session->supervisor->name }}</div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Statistics Card -->
    <div class="col-md-4 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-chart-pie me-2"></i>
                    الإحصائيات
                </h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label class="text-muted small">إجمالي الصفحات</label>
                    <div class="fs-4 fw-bold text-primary">{{ $session->total_pages }}</div>
                </div>
                <div class="mb-3">
                    <label class="text-muted small">إجمالي الأخطاء</label>
                    <div class="fs-4 fw-bold {{ $session->total_errors > 0 ? 'text-danger' : 'text-success' }}">
                        {{ $session->total_errors }}
                    </div>
                </div>
                <div class="mb-3">
                    <label class="text-muted small">إجمالي الترددات</label>
                    <div class="fs-4 fw-bold {{ $session->total_hesitations > 0 ? 'text-warning' : 'text-success' }}">
                        {{ $session->total_hesitations }}
                    </div>
                </div>
                <div>
                    <label class="text-muted small">الدرجة النهائية</label>
                    <div class="fs-4 fw-bold text-info">
                        {{ $session->final_score !== null ? number_format($session->final_score, 1) : '-' }}
                        @if($session->max_score)
                            <small class="text-muted">/ {{ $session->max_score }}</small>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Part Info Card -->
<div class="card mb-4">
    <div class="card-header">
        <h6 class="mb-0">
            <i class="fas fa-book me-2"></i>
            معلومات الجزء
        </h6>
    </div>
    <div class="card-body">
        <div class="row">
            @if($session->part)
            <div class="col-md-3">
                <label class="text-muted small">الجزء</label>
                <div class="fs-4 fw-bold">جزء {{ $session->part->part_number }}</div>
            </div>
            <div class="col-md-3">
                <label class="text-muted small">اسم الجزء</label>
                <div class="fw-semibold">{{ $session->part->name }}</div>
            </div>
            @endif
            <div class="col-md-3">
                <label class="text-muted small">صفحات التسميع</label>
                <div class="fw-semibold">{{ $session->start_page }} - {{ $session->end_page }}</div>
            </div>
            @if($session->unmastered_pages_count !== null)
            <div class="col-md-3">
                <label class="text-muted small">الصفحات غير المتقنة</label>
                <div class="fw-semibold text-warning">{{ $session->unmastered_pages_count }}</div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Pages Details Table -->
<div class="card mb-4">
    <div class="card-header">
        <h6 class="mb-0">
            <i class="fas fa-list me-2"></i>
            تفاصيل الصفحات
        </h6>
    </div>
    <div class="card-body p-0">
        @if($session->pages->isEmpty())
            <div class="text-center py-4 text-muted">
                <i class="fas fa-file-alt fa-2x mb-2 d-block"></i>
                <p>لا توجد تفاصيل للصفحات</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>رقم الصفحة</th>
                            <th>الأخطاء</th>
                            <th>الترددات</th>
                            <th>التقييم</th>
                            <th>الدرجة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($session->pages as $page)
                        <tr>
                            <td class="fw-semibold">{{ $page->page_number }}</td>
                            <td>
                                @if($page->errors > 0)
                                    <span class="text-danger fw-semibold">{{ $page->errors }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($page->hesitations > 0)
                                    <span class="text-warning fw-semibold">{{ $page->hesitations }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $page->color_class }}">{{ $page->color_label }}</span>
                            </td>
                            <td>
                                @if($page->page_score !== null)
                                    {{ number_format($page->page_score, 1) }}
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

<!-- Notes Section -->
@if($session->notes)
<div class="card">
    <div class="card-header">
        <h6 class="mb-0">
            <i class="fas fa-sticky-note me-2"></i>
            ملاحظات
        </h6>
    </div>
    <div class="card-body">
        <p class="mb-0">{{ $session->notes }}</p>
    </div>
</div>
@endif
@endsection
