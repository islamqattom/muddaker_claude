@extends('layouts.app')

@section('title', 'المواد الدراسية')
@section('page-title', 'المواد والدروس')

@php
    $role = auth()->user()->role;
    $roleLabel = $role === 'admin' ? 'مدير' : 'معلم';
@endphp

@section('role-label', $roleLabel)

@section('sidebar-menu')
    @include($role . '.partials.sidebar')
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route($role . '.dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item active">المواد الدراسية</li>
@endsection

@push('styles')
<style>
.subjects-page { font-family: 'Tajawal', sans-serif; }
.stat-icon.primary { background: linear-gradient(135deg, #182B48 0%, #2A4570 100%); }
.stat-icon.success { background: linear-gradient(135deg, #27ae60 0%, #219a52 100%); }
.stat-icon.warning { background: linear-gradient(135deg, #d4a030 0%, #B4802C 100%); }
.stat-icon.info { background: linear-gradient(135deg, #3498db 0%, #2980b9 100%); }
.stat-info h3 { margin: 0; font-size: 22px; color: #182B48; font-weight: 800; }
.stat-info span { color: #7f8c8d; font-size: 13px; font-weight: 600; }
.filter-bar {
    background: #fff;
    padding: 16px 16px;
    border-radius: 13px;
    box-shadow: 0 2px 12px rgba(24,43,72,0.05);
    margin-bottom: 16px;
    display: flex; gap: 12px; flex-wrap: wrap; align-items: center;
}
.filter-bar .form-control, .filter-bar .form-select {
    border-radius: 10px; border: 1.5px solid #e9ecef;
    padding: 8px 14px; min-width: 180px;
}
.filter-bar .form-control:focus, .filter-bar .form-select:focus {
    border-color: #d4a030; box-shadow: 0 0 0 3px rgba(212,160,48,0.10);
}
.subjects-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 20px;
}
.subject-card {
    background: #fff;
    border-radius: 13px;
    padding: 16px;
    box-shadow: 0 4px 20px rgba(24,43,72,0.07);
    border: 1px solid rgba(24,43,72,0.05);
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}
.subject-card::before {
    content: '';
    position: absolute; top: 0; right: 0; left: 0;
    height: 6px;
    background: var(--subject-color, #d4a030);
}
.subject-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 35px rgba(24,43,72,0.12);
}
.subject-header {
    display: flex; justify-content: space-between; align-items: flex-start;
    margin-bottom: 12px;
}
.subject-title {
    color: #182B48; font-weight: 800; font-size: 18px;
    margin: 0 0 4px 0;
}
.subject-desc {
    color: #7f8c8d; font-size: 13px; line-height: 1.6;
    margin-bottom: 14px;
    display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;
    overflow: hidden;
}
.subject-meta {
    display: flex; gap: 12px; flex-wrap: wrap;
    margin-bottom: 14px;
}
.meta-pill {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 5px 12px; border-radius: 13px;
    background: rgba(24,43,72,0.05);
    color: #182B48;
    font-size: 12px; font-weight: 600;
}
.meta-pill i { color: #d4a030; font-size: 11px; }
.progress-wrap {
    margin: 14px 0;
}
.progress-label {
    display: flex; justify-content: space-between;
    font-size: 12px; color: #5a6c7d;
    font-weight: 600; margin-bottom: 6px;
}
.progress {
    height: 8px; background: rgba(24,43,72,0.06);
    border-radius: 6px; overflow: hidden;
}
.progress-bar {
    background: linear-gradient(90deg, #d4a030 0%, #B4802C 100%);
    height: 100%; border-radius: 6px;
    transition: width 0.4s ease;
}
.progress-bar.completed { background: linear-gradient(90deg, #27ae60 0%, #219a52 100%); }
.subject-footer {
    display: flex; justify-content: space-between; align-items: center;
    padding-top: 14px;
    border-top: 1px dashed #e9ecef;
    margin-top: 14px;
}
.subject-creator {
    color: #7f8c8d; font-size: 12px;
    display: flex; align-items: center; gap: 6px;
}
.subject-creator i { color: #d4a030; }
.subject-actions {
    display: flex; gap: 6px;
}
.btn-icon {
    width: 34px; height: 34px;
    border-radius: 9px;
    border: none;
    display: inline-flex; align-items: center; justify-content: center;
    color: #fff; font-size: 13px;
    cursor: pointer;
    transition: all 0.2s ease;
    text-decoration: none;
}
.btn-icon:hover { transform: translateY(-2px); color: #fff; }
.btn-icon.view { background: linear-gradient(135deg, #3498db 0%, #2980b9 100%); }
.btn-icon.calendar { background: linear-gradient(135deg, #d4a030 0%, #B4802C 100%); }
.btn-icon.edit { background: linear-gradient(135deg, #8e44ad 0%, #732d91 100%); }
.btn-icon.delete { background: linear-gradient(135deg, #c0392b 0%, #a93226 100%); }
.empty-state i {
    font-size: 22px; color: rgba(212,160,48,0.4);
    margin-bottom: 16px;
}
.status-badge.active { background: rgba(39,174,96,0.12); color: #27ae60; }
.status-badge.inactive { background: rgba(127,140,141,0.15); color: #7f8c8d; }
.status-badge.overdue { background: rgba(192,57,43,0.12); color: #c0392b; }
@media (max-width: 768px) {
.subjects-grid { grid-template-columns: 1fr; }
}
</style>
@endpush

@section('content')
<div class="subjects-page">
    <div class="page-header">
        <div class="header-content">
            <div class="header-icon">
                <i class="fas fa-book"></i>
            </div>
            <div class="header-text">
                <h4>المواد الدراسية</h4>
                <p>إدارة المواد وتفريغ الدروس زمنياً ومتابعة إتمامها</p>
            </div>
        </div>
        <a href="{{ route($role . '.subjects.create') }}" class="btn btn-primary" style="background:linear-gradient(135deg, #182B48 0%, #2A4570 100%);border:none;border-radius:12px;padding:16px 22px;font-weight:700">
            <i class="fas fa-plus-circle ms-1"></i> إضافة مادة جديدة
        </a>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon primary"><i class="fas fa-book"></i></div>
            <div class="stat-info">
                <h3>{{ $stats['total'] }}</h3>
                <span>إجمالي المواد</span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon success"><i class="fas fa-check-circle"></i></div>
            <div class="stat-info">
                <h3>{{ $stats['active'] }}</h3>
                <span>المواد النشطة</span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon info"><i class="fas fa-list-check"></i></div>
            <div class="stat-info">
                <h3>{{ $stats['total_lessons'] }}</h3>
                <span>إجمالي الدروس</span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon warning"><i class="fas fa-circle-check"></i></div>
            <div class="stat-info">
                <h3>{{ $stats['completed_lessons'] }}</h3>
                <span>الدروس المنجزة</span>
            </div>
        </div>
    </div>

    <form method="GET" class="filter-bar">
        <input type="text" name="search" class="form-control" placeholder="بحث بالاسم أو الوصف..." value="{{ request('search') }}">
        <select name="is_active" class="form-select">
            <option value="all" {{ request('is_active', 'all') === 'all' ? 'selected' : '' }}>كل الحالات</option>
            <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>نشطة</option>
            <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>غير نشطة</option>
        </select>
        @if($role === 'admin' && !empty($creators))
        <select name="created_by" class="form-select">
            <option value="">كل المعلمون</option>
            @foreach($creators as $creator)
                <option value="{{ $creator->id }}" {{ request('created_by') == $creator->id ? 'selected' : '' }}>{{ $creator->name }}</option>
            @endforeach
        </select>
        @endif
        <button type="submit" class="btn btn-primary" style="background:#d4a030;border:none;border-radius:10px;padding:8px 16px;font-weight:700">
            <i class="fas fa-search"></i> بحث
        </button>
        <a href="{{ route($role . '.subjects.index') }}" class="btn btn-secondary" style="border-radius:10px;padding:8px 16px">إعادة تعيين</a>
    </form>

    @if($subjects->count() > 0)
        <div class="subjects-grid">
            @foreach($subjects as $subject)
                @php
                    $total = $subject->lessons_count ?? 0;
                    $completed = $subject->completed_lessons_count ?? 0;
                    $pending = $subject->pending_lessons_count ?? 0;
                    $progress = $total > 0 ? round(($completed / $total) * 100, 1) : 0;
                @endphp
                <div class="subject-card" style="--subject-color:{{ $subject->color }}">
                    <span class="status-badge {{ $subject->is_active ? 'active' : 'inactive' }}">
                        <i class="fas fa-{{ $subject->is_active ? 'check' : 'pause' }}"></i>
                        {{ $subject->is_active ? 'نشطة' : 'غير نشطة' }}
                    </span>
                    @if($pending > 0 && \App\Models\Lesson::where('subject_id', $subject->id)->where('status', 'pending')->where('lesson_date', '<', now()->toDateString())->exists())
                        <span class="status-badge overdue" style="left:100px">
                            <i class="fas fa-exclamation"></i> متأخرة
                        </span>
                    @endif

                    <div class="subject-header">
                        <div style="width:38px;height:38px;border-radius:10px;background:{{ $subject->color }}20;color:{{ $subject->color }};display:flex;align-items:center;justify-content:center;font-size:16px">
                            <i class="fas fa-book-open"></i>
                        </div>
                    </div>

                    <h5 class="subject-title">{{ $subject->name }}</h5>
                    @if($subject->description)
                        <p class="subject-desc">{{ $subject->description }}</p>
                    @endif

                    <div class="subject-meta">
                        <span class="meta-pill">
                            <i class="fas fa-list"></i> {{ $total }} درس
                        </span>
                        <span class="meta-pill" style="background:rgba(39,174,96,0.10);color:#27ae60">
                            <i class="fas fa-check"></i> {{ $completed }} تم
                        </span>
                        <span class="meta-pill" style="background:rgba(243,156,18,0.10);color:#f39c12">
                            <i class="fas fa-clock"></i> {{ $pending }} قادم
                        </span>
                    </div>

                    <div class="progress-wrap">
                        <div class="progress-label">
                            <span>نسبة الإنجاز</span>
                            <span style="color:#182B48;font-weight:800">{{ $progress }}%</span>
                        </div>
                        <div class="progress">
                            <div class="progress-bar {{ $progress === 100 ? 'completed' : '' }}" style="width:{{ $progress }}%"></div>
                        </div>
                    </div>

                    <div class="subject-footer">
                        <div class="subject-creator">
                            <i class="fas fa-user-tie"></i>
                            {{ $subject->creator->name ?? '-' }}
                        </div>
                        <div class="subject-actions">
                            <a href="{{ route($role . '.subjects.show', $subject->id) }}" class="btn-icon view" title="عرض الدروس"><i class="fas fa-eye"></i></a>
                            <a href="{{ route($role . '.subjects.calendar', $subject->id) }}" class="btn-icon calendar" title="تقويم الدروس"><i class="fas fa-calendar-alt"></i></a>
                            <a href="{{ route($role . '.subjects.edit', $subject->id) }}" class="btn-icon edit" title="تعديل"><i class="fas fa-edit"></i></a>
                            <button type="button" class="btn-icon delete" title="حذف" onclick="deleteSubject({{ $subject->id }}, '{{ addslashes($subject->name) }}')"><i class="fas fa-trash"></i></button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-4 d-flex justify-content-center">
            {{ $subjects->links() }}
        </div>
    @else
        <div class="empty-state">
            <i class="fas fa-book-open"></i>
            <h5>لا توجد مواد دراسية بعد</h5>
            <p>ابدأ بإضافة أول مادة دراسية لتفريغ دروسها ومتابعتها زمنياً</p>
            <a href="{{ route($role . '.subjects.create') }}" class="btn btn-primary mt-3" style="background:linear-gradient(135deg, #182B48 0%, #2A4570 100%);border:none;border-radius:10px;padding:16px 22px">
                <i class="fas fa-plus ms-1"></i> إضافة أول مادة
            </a>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
const rolePrefix = @json($role);
function deleteSubject(id, name) {
    Swal.fire({
        title: 'تأكيد الحذف',
        html: `هل تريد حذف المادة <strong>${name}</strong> وجميع دروسها؟`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'نعم، احذف',
        cancelButtonText: 'إلغاء',
        confirmButtonColor: '#c0392b',
        cancelButtonColor: '#7f8c8d',
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/${rolePrefix}/subjects/${id}`;
            form.innerHTML = `@csrf @method('DELETE')`;
            document.body.appendChild(form);
            form.submit();
        }
    });
}
</script>
@endpush
