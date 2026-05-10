@extends('layouts.app')

@section('title', 'محاضر الاجتماعات')
@section('page-title', 'محاضر الاجتماعات')
@section('role-label', 'مدير')

@section('sidebar-menu')
    @include('admin.partials.sidebar')
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item active">محاضر الاجتماعات</li>
@endsection

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="fas fa-calendar-minus me-2"></i>
            محاضر الاجتماعات
        </h5>
        <a href="{{ route('admin.meeting-minutes.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-1"></i> إضافة محضر
        </a>
    </div>
    <div class="card-body">
        <form method="GET" class="mb-4">
            <div class="row g-3">
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">جميع الحالات</option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>مسودة</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>معتمد</option>
                        <option value="archived" {{ request('status') == 'archived' ? 'selected' : '' }}>مؤرشف</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}" placeholder="من تاريخ">
                </div>
                <div class="col-md-3">
                    <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}" placeholder="إلى تاريخ">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-secondary">
                        <i class="fas fa-search me-1"></i> بحث
                    </button>
                    <a href="{{ route('admin.meeting-minutes.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-redo"></i>
                    </a>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>التاريخ</th>
                        <th>الوقت</th>
                        <th>الحالة</th>
                        <th>المنشئ</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($meetingMinutes as $minute)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $minute->meeting_date->format('Y/m/d') }}</td>
                        <td>{{ $minute->meeting_time ?? '-' }}</td>
                        <td>
                            <span class="badge bg-{{ $minute->status_color }}">{{ $minute->status_label }}</span>
                        </td>
                        <td>{{ $minute->creator->name ?? '-' }}</td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('admin.meeting-minutes.show', $minute->id) }}" class="btn btn-outline-primary" title="عرض">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.meeting-minutes.edit', $minute->id) }}" class="btn btn-outline-secondary" title="تعديل">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @if($minute->status === 'draft')
                                    <form method="POST" action="{{ route('admin.meeting-minutes.destroy', $minute->id) }}" class="d-inline" onsubmit="return confirm('هل تريد حذف هذا المحضر؟')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" title="حذف">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">
                            <i class="fas fa-calendar-minus fa-2x mb-2 d-block"></i>
                            لا توجد محاضر اجتماعات
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($meetingMinutes->hasPages())
        <div class="mt-4">
            {{ $meetingMinutes->links() }}
        </div>
        @endif
    </div>
</div>
@endsection