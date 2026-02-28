@extends('layouts.app')

@section('title', 'قائمة الطلاب')
@section('page-title', 'قائمة الطلاب')
@section('role-label', 'مشرف')

@section('sidebar-menu')
    @include('supervisor.partials.sidebar')
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('supervisor.dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item active">الطلاب</li>
@endsection

@section('content')
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label">بحث بالاسم</label>
                <input type="text" name="search" class="form-control" placeholder="ابحث بالاسم..." value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">المستوى</label>
                <select name="level_id" class="form-select" onchange="this.form.submit()">
                    <option value="">جميع المستويات</option>
                    @foreach(\App\Models\Level::all() as $level)
                        <option value="{{ $level->id }}" {{ request('level_id') == $level->id ? 'selected' : '' }}>
                            {{ $level->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search me-1"></i> بحث
                </button>
            </div>
            <div class="col-md-2">
                <a href="{{ route('supervisor.students.index') }}" class="btn btn-outline-secondary w-100">
                    <i class="fas fa-redo me-1"></i> إعادة
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0">
            <i class="fas fa-users me-2"></i>
            طلاب ({{ $students->total() }})
        </h6>
        <a href="{{ route('supervisor.students.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-1"></i>إضافة طالب
        </a>
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
                            <th>نسبة الحضور</th>
                            <th>نسبة النجاح</th>
                            <th width="100">الإجراءات</th>
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
                                        <small class="text-muted">{{ $student->national_id }}</small>
                                    </div>
                                </div>
                            </td>
                            <td><code>{{ $student->student_number }}</code></td>
                            <td>
                                @if($student->level)
                                    <span class="badge bg-secondary">{{ $student->level->name }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress flex-grow-1" style="height:6px;width:60px;">
                                        <div class="progress-bar bg-success" style="width: {{ $student->attendance_rate }}%"></div>
                                    </div>
                                    <small class="text-muted">{{ $student->attendance_rate }}%</small>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress flex-grow-1" style="height:6px;width:60px;">
                                        <div class="progress-bar bg-info" style="width: {{ $student->pass_rate }}%"></div>
                                    </div>
                                    <small class="text-muted">{{ $student->pass_rate }}%</small>
                                </div>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="{{ route('supervisor.students.show', $student->id) }}" class="btn btn-sm btn-outline-primary" title="عرض">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('supervisor.students.edit', $student->id) }}" class="btn btn-sm btn-outline-warning" title="تعديل">
                                        <i class="fas fa-edit"></i>
                                    </a>
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
