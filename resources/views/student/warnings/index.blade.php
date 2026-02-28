@extends('layouts.app')
@section('title', 'الإنذارات')
@section('page-title', 'الإنذارات')
@section('role-label', 'طالب')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item active">الإنذارات</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h6 class="mb-0">
            <i class="fas fa-exclamation-triangle me-2"></i>
            سجل الإنذارات
        </h6>
    </div>
    <div class="card-body p-0">
        @if($warnings->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="fas fa-check-circle fa-3x mb-3 d-block text-success"></i>
                <p>لا توجد إنذارات مسجلة بحقك</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>نوع الإنذار</th>
                            <th>العنوان</th>
                            <th>التاريخ</th>
                            <th>الرقم</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($warnings as $index => $warning)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>
                                @if($warning->type === 'attendance')
                                    <span class="badge bg-warning text-dark">
                                        <i class="fas fa-calendar-times me-1"></i>حضور
                                    </span>
                                @elseif($warning->type === 'recitation')
                                    <span class="badge bg-info">
                                        <i class="fas fa-microphone me-1"></i>تسميع
                                    </span>
                                @else
                                    <span class="badge bg-secondary">
                                        <i class="fas fa-exclamation-circle me-1"></i>عام
                                    </span>
                                @endif
                            </td>
                            <td>{{ $warning->title }}</td>
                            <td>{{ $warning->issue_date->format('d/m/Y') }}</td>
                            <td><code class="small">{{ $warning->warning_number }}</code></td>
                            <td>
                                <a href="{{ route('student.warnings.show', $warning->id) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye"></i> عرض
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
