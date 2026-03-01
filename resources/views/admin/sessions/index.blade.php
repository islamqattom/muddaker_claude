@extends('layouts.app')

@section('title', 'جلسات التسميع')
@section('page-title', 'جلسات التسميع')
@section('role-label', 'مدير')

@section('sidebar-menu')
    @include('admin.partials.sidebar')
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item active">جلسات التسميع</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0">
            <i class="fas fa-microphone me-2"></i>
            جلسات التسميع ({{ $sessions->total() }})
        </h6>
    </div>
    <div class="card-body p-0">
        @if($sessions->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="fas fa-microphone-alt fa-3x mb-3 d-block"></i>
                <p>لا توجد جلسات تسميع مسجلة</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>التاريخ</th>
                            <th>الطالب</th>
                            <th>المشرف</th>
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
                            <td>{{ $sessions->firstItem() + $index }}</td>
                            <td class="fw-semibold">{{ $session->session_date->format('Y/m/d') }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div style="width:32px;height:32px;border-radius:50%;background:#182B48;display:flex;align-items:center;justify-content:center;color:#fff;font-size:12px;font-weight:bold;">
                                        {{ mb_substr($session->student->user->name, 0, 1) }}
                                    </div>
                                    <span>{{ $session->student->user->name }}</span>
                                </div>
                            </td>
                            <td>
                                @if($session->supervisor)
                                    <span>{{ $session->supervisor->name }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-info">{{ $session->type_label }}</span>
                            </td>
                            <td>
                                @if($session->part)
                                    <span class="fw-semibold">جزء {{ $session->part->part_number }}</span>
                                @else
                                    <span>{{ $session->start_page }} - {{ $session->end_page }}</span>
                                    <span class="text-muted">(صفحات)</span>
                                @endif
                            </td>
                            <td>
                                @if($session->total_errors > 0)
                                    <span class="text-danger fw-semibold">{{ $session->total_errors }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($session->total_hesitations > 0)
                                    <span class="text-warning fw-semibold">{{ $session->total_hesitations }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($session->final_score !== null)
                                    <span class="fw-semibold">{{ number_format($session->final_score, 1) }}</span>
                                    <span class="text-muted">/ {{ $session->max_score }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ $session->status_color }}">{{ $session->status_label }}</span>
                            </td>
                            <td>
                                <a href="{{ route('admin.sessions.show', $session->id) }}" class="btn btn-sm btn-outline-primary" title="عرض التفاصيل">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="p-3 border-top">
                {{ $sessions->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
