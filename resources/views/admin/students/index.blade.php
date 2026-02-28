@extends('layouts.app')
@section('title', 'إدارة الطلاب')
@section('page-title', 'إدارة الطلاب')
@section('role-label', 'مدير')

@section('sidebar-menu')
@include('admin.partials.sidebar')
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item active">الطلاب</li>
@endsection

@section('content')
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">بحث</label>
                <input type="text" name="search" class="form-control" placeholder="الاسم أو رقم الهاتف" value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">المستوى</label>
                <select name="level_id" class="form-select">
                    <option value="">جميع المستويات</option>
                    @foreach($levels as $level)
                        <option value="{{ $level->id }}" {{ request('level_id') == $level->id ? 'selected' : '' }}>{{ $level->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">المشرف</label>
                <select name="supervisor_id" class="form-select">
                    <option value="">جميع المشرفين</option>
                    @foreach($supervisors as $supervisor)
                        <option value="{{ $supervisor->id }}" {{ request('supervisor_id') == $supervisor->id ? 'selected' : '' }}>{{ $supervisor->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search me-1"></i>بحث
                </button>
            </div>
            <div class="col-md-3 text-end">
                <a href="{{ route('admin.students.create') }}" class="btn btn-success">
                    <i class="fas fa-plus me-1"></i>إضافة طالب
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0">
            <i class="fas fa-users me-2"></i>
            قائمة الطلاب
        </h6>
        <span class="badge bg-info">{{ $students->total() }} طالب</span>
    </div>
    <div class="card-body p-0">
        @if($students->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="fas fa-user-graduate fa-3x mb-3 d-block"></i>
                <p>لا يوجد طلاب مسجلين</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="60">#</th>
                            <th>اسم الطالب</th>
                            <th>رقم الطالب</th>
                            <th>المستوى</th>
                            <th>المشرف</th>
                            <th>الهاتف</th>
                            <th>تاريخ التسجيل</th>
                            <th width="150">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($students as $index => $student)
                        <tr>
                            <td>{{ $students->firstItem() + $index }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <img src="{{ $student->user->avatar_url }}" alt="Avatar" class="rounded-circle" style="width:36px;height:36px;object-fit:cover;">
                                    <div>
                                        <div class="fw-semibold">{{ $student->user->name }}</div>
                                        <small class="text-muted">{{ $student->username ?? '-' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td><code>{{ $student->student_number }}</code></td>
                            <td>
                                @if($student->level)
                                    <span class="badge bg-primary">{{ $student->level->name }}</span>
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{ $student->supervisor->name ?? '-' }}</td>
                            <td>{{ $student->phone ?? $student->user->phone }}</td>
                            <td>{{ $student->enrollment_date?->format('d/m/Y') ?? '-' }}</td>
                            <td>
                                <div class="btn-group">
                                    <a href="{{ route('admin.students.show', $student->id) }}" class="btn btn-sm btn-outline-primary" title="عرض">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.students.edit', $student->id) }}" class="btn btn-sm btn-outline-secondary" title="تعديل">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.students.destroy', $student->id) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من حذف هذا الطالب؟')">
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
                {{ $students->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
