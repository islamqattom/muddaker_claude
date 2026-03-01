@extends('layouts.app')

@section('title', 'الملف الشخصي')
@section('page-title', 'الملف الشخصي')
@section('role-label', 'ولي أمر')

@section('sidebar-menu')
<span class="nav-section-title">الرئيسية</span>
<a href="{{ route('parent.dashboard') }}" class="nav-link {{ request()->routeIs('parent.dashboard') ? 'active' : '' }}">
    <i class="fas fa-home"></i> الصفحة الرئيسية
</a>
<span class="nav-section-title mt-2">المتابعة</span>
<a href="{{ route('parent.sessions.index') }}" class="nav-link"><i class="fas fa-microphone"></i> جلسات التسميع</a>
<a href="{{ route('parent.attendance.index') }}" class="nav-link"><i class="fas fa-calendar-check"></i> سجل الحضور</a>
<a href="{{ route('parent.warnings.index') }}" class="nav-link">
    <i class="fas fa-exclamation-triangle"></i> الإنذارات
    @php $unread = \App\Models\Warning::whereHas('student', fn($q)=>$q->whereHas('parents',fn($p)=>$p->where('user_id',auth()->id())))->where('is_read_by_parent',false)->count(); @endphp
    @if($unread > 0)<span class="badge bg-danger">{{ $unread }}</span>@endif
</a>
<a href="{{ route('parent.reports.index') }}" class="nav-link"><i class="fas fa-chart-bar"></i> التقارير</a>
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('parent.dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item active">الملف الشخصي</li>
@endsection

@section('content')
<div class="row">
    <!-- Parent Information Card -->
    <div class="col-md-4 mb-4">
        <div class="card h-100">
            <div class="card-body text-center">
                <div style="width:120px;height:120px;border-radius:50%;background:#182B48;display:flex;align-items:center;justify-content:center;color:#fff;font-size:48px;font-weight:bold;margin:0 auto;">
                    {{ mb_substr($parent->user->name, 0, 1) }}
                </div>
                <h4 class="mt-3 mb-1">{{ $parent->user->name }}</h4>
                <p class="text-muted mb-3">{{ $parent->user->email }}</p>

                <hr>

                <div class="text-start">
                    <div class="mb-3">
                        <label class="text-muted small d-block">رقم الهاتف</label>
                        <span class="fw-semibold">{{ $parent->phone ?? '-' }}</span>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small d-block">رقم الهوية</label>
                        <span class="fw-semibold">{{ $parent->national_id ?? '-' }}</span>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small d-block">العنوان</label>
                        <span class="fw-semibold">{{ $parent->address ?? '-' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Form -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-user-edit me-2"></i>
                    تعديل البيانات الشخصية
                </h6>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle me-2"></i>
                        {{ session('success') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('parent.profile.update') }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="col-12 mb-3">
                        <label class="form-label">صورة الملف الشخصي</label>
                        <div class="d-flex align-items-center gap-3">
                            <img src="{{ $parent->user->avatar_url }}" alt="Avatar" class="rounded-circle" style="width:80px;height:80px;object-fit:cover;">
                            <div>
                                <input type="file" name="image" class="form-control @error('image') is-invalid @enderror" accept="image/*">
                                <small class="text-muted">الصيغ المدعومة: jpeg, png, jpg, gif, webp</small>
                            </div>
                        </div>
                        @error('image')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">الاسم</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ $parent->user->name }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">البريد الإلكتروني</label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ $parent->user->email }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">رقم الهاتف</label>
                        <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ $parent->phone }}" placeholder="05xxxxxxxx">
                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>حفظ التغييرات
                    </button>
                </form>
            </div>
        </div>

        <!-- Linked Students -->
        <div class="card mt-4">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-users me-2"></i>
                    الأبناء المرتبطون ({{ $parent->students->count() }})
                </h6>
            </div>
            <div class="card-body p-0">
                @if($parent->students->isEmpty())
                    <div class="text-center py-4 text-muted">
                        <i class="fas fa-users fa-2x mb-2 d-block"></i>
                        <p>لا يوجد أبناء مرتبطون بحسابك</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>الطالب</th>
                                    <th>المستوى</th>
                                    <th>المشرف</th>
                                    <th>الجزء الحالي</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($parent->students as $student)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div style="width:36px;height:36px;border-radius:50%;background:#182B48;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:bold;">
                                                {{ mb_substr($student->user->name, 0, 1) }}
                                            </div>
                                            <span class="fw-semibold">{{ $student->user->name }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        @if($student->level)
                                            <span class="badge bg-secondary">{{ $student->level->name }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($student->supervisor)
                                            {{ $student->supervisor->name }}
                                        @else
                                            <span class="text-muted">غير محدد</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($student->current_part)
                                            <span class="badge bg-primary">جزء {{ $student->current_part }}</span>
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
    </div>
</div>
@endsection
