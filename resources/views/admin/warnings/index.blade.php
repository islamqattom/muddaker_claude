@extends('layouts.app')
@section('title', 'الإنذارات')
@section('page-title', 'الإنذارات')
@section('role-label', 'مدير')

@section('sidebar-menu')
@include('admin.partials.sidebar')
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item active">الإنذارات</li>
@endsection

@section('content')
<div class="card mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-end">
            <div class="row g-3 align-items-end flex-grow-1">
                <div class="col-md-3">
                    <label class="form-label">نوع الإنذار</label>
                    <select class="form-select" onchange="window.location='{{ route('admin.warnings.index') }}?type='+this.value">
                        <option value="">جميع الأنواع</option>
                        <option value="attendance" {{ request('type') == 'attendance' ? 'selected' : '' }}>إنذار حضور</option>
                        <option value="recitation" {{ request('type') == 'recitation' ? 'selected' : '' }}>إنذار تسميع</option>
                        <option value="general" {{ request('type') == 'general' ? 'selected' : '' }}>إنذار عام</option>
                    </select>
                </div>
            </div>
            <div>
                <a href="{{ route('admin.warnings.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i>
                    إضافة إنذار
                </a>
            </div>
        </div>
    </div>
</div>

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
                <i class="fas fa-exclamation-circle fa-3x mb-3 d-block"></i>
                <p>لا توجد إنذارات مسجلة</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="60">#</th>
                            <th>اسم الطالب</th>
                            <th>المشرف</th>
                            <th>نوع الإنذار</th>
                            <th>العنوان</th>
                            <th>التاريخ</th>
                            <th>الرقم</th>
                            <th width="150">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($warnings as $index => $warning)
                        <tr>
                            <td>{{ $warnings->firstItem() + $index }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <img src="{{ $warning->student->user->avatar_url }}" alt="Avatar" class="rounded-circle" style="width:36px;height:36px;object-fit:cover;">
                                    <div>
                                        <div class="fw-semibold">{{ $warning->student->user->name }}</div>
                                        <small class="text-muted">{{ $warning->student->student_number }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $warning->issuedBy->name }}</td>
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
                            <td>
                                <code class="small">{{ $warning->warning_number }}</code>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="{{ route('admin.warnings.show', $warning->id) }}" class="btn btn-sm btn-outline-primary" title="عرض">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <form action="{{ route('admin.warnings.destroy', $warning->id) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من حذف هذا الإنذار؟')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="حذف">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="p-3 border-top">
                {{ $warnings->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
