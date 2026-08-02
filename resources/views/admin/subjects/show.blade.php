@extends('layouts.app')

@section('title', $subject->name)
@section('page-title', 'دروس: ' . $subject->name)

@php
    $role = auth()->user()->role;
    $roleLabel = $role === 'admin' ? 'مدير' : 'معلم';
    $total = $subject->lessons_count;
    $completed = $subject->completed_lessons_count;
    $pending = $subject->pending_lessons_count;
    $overdue = $subject->overdue_lessons_count;
    $progress = $total > 0 ? round(($completed / $total) * 100, 1) : 0;
@endphp

@section('role-label', $roleLabel)

@section('sidebar-menu')
    @include($role . '.partials.sidebar')
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route($role . '.dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route($role . '.subjects.index') }}">المواد الدراسية</a></li>
    <li class="breadcrumb-item active">{{ $subject->name }}</li>
@endsection

@push('styles')
<style>
.subject-show-page { font-family: 'Tajawal', sans-serif; }
.page-header { display: flex; justify-content: space-between; align-items: center; gap: 20px; margin-bottom: 30px; flex-wrap: wrap; }
.header-content { display: flex; align-items: center; gap: 20px; flex: 1; }
.subject-color-badge { width: 60px; height: 60px; border-radius: 16px; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 26px; box-shadow: 0 8px 20px rgba(0,0,0,0.15); }
.header-text h4 { color: #182B48; font-size: 24px; font-weight: 800; margin: 0 0 4px 0; }
.header-text p { color: #7f8c8d; font-size: 14px; margin: 0; }
.header-actions { display: flex; gap: 10px; flex-wrap: wrap; }
.btn { border-radius: 12px; padding: 10px 22px; font-weight: 700; font-family: 'Tajawal', sans-serif; border: none; }

.subject-info-card { background: #fff; border-radius: 18px; padding: 24px; box-shadow: 0 4px 20px rgba(24,43,72,0.07); border: 1px solid rgba(24,43,72,0.05); margin-bottom: 20px; }
.subject-info-header { display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px; margin-bottom: 16px; padding-bottom: 16px; border-bottom: 1px dashed #e9ecef; }
.subject-meta-info { display: flex; gap: 14px; flex-wrap: wrap; }
.meta-item { display: inline-flex; align-items: center; gap: 8px; padding: 6px 14px; background: rgba(24,43,72,0.05); border-radius: 20px; font-size: 13px; color: #182B48; font-weight: 600; }
.meta-item i { color: {{ $subject->color }}; }
.subject-description { color: #5a6c7d; line-height: 1.8; }

.stats-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 14px; margin-bottom: 20px; }
.stat-card { background: #fff; border-radius: 14px; padding: 18px; box-shadow: 0 2px 12px rgba(24,43,72,0.05); border: 1px solid rgba(24,43,72,0.05); display: flex; align-items: center; gap: 14px; }
.stat-icon { width: 50px; height: 50px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 20px; color: #fff; }
.stat-icon.total { background: linear-gradient(135deg, #182B48 0%, #2A4570 100%); }
.stat-icon.completed { background: linear-gradient(135deg, #27ae60 0%, #219a52 100%); }
.stat-icon.pending { background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%); }
.stat-icon.overdue { background: linear-gradient(135deg, #c0392b 0%, #a93226 100%); }
.stat-info h3 { margin: 0; font-size: 22px; color: #182B48; font-weight: 800; }
.stat-info span { color: #7f8c8d; font-size: 12px; font-weight: 600; }

.progress-section { background: #fff; border-radius: 14px; padding: 20px; box-shadow: 0 2px 12px rgba(24,43,72,0.05); margin-bottom: 20px; }
.progress-label { display: flex; justify-content: space-between; font-weight: 700; color: #182B48; margin-bottom: 8px; }
.progress { height: 14px; background: rgba(24,43,72,0.06); border-radius: 8px; overflow: hidden; }
.progress-bar { background: linear-gradient(90deg, {{ $subject->color }} 0%, #B4802C 100%); height: 100%; border-radius: 8px; transition: width 0.4s; }

.filter-bar { background: #fff; padding: 14px 18px; border-radius: 12px; box-shadow: 0 2px 10px rgba(24,43,72,0.05); margin-bottom: 16px; display: flex; gap: 10px; flex-wrap: wrap; align-items: center; }
.filter-bar .form-control, .filter-bar .form-select { border-radius: 10px; border: 1.5px solid #e9ecef; padding: 8px 14px; }
.filter-bar .btn { padding: 8px 18px; }

.lessons-list { display: flex; flex-direction: column; gap: 12px; }
.lesson-item { background: #fff; border-radius: 14px; padding: 18px; box-shadow: 0 2px 12px rgba(24,43,72,0.05); border-right: 5px solid #e9ecef; transition: all 0.3s ease; display: flex; gap: 16px; align-items: center; }
.lesson-item:hover { box-shadow: 0 6px 20px rgba(24,43,72,0.10); transform: translateX(-2px); }
.lesson-item.status-pending { border-right-color: #f39c12; }
.lesson-item.status-completed { border-right-color: #27ae60; }
.lesson-item.status-cancelled { border-right-color: #7f8c8d; }
.lesson-item.is-overdue { border-right-color: #c0392b; background: rgba(192,57,43,0.02); }

.lesson-date-block { min-width: 80px; text-align: center; padding: 10px; background: rgba(24,43,72,0.04); border-radius: 10px; }
.lesson-day { color: #182B48; font-weight: 800; font-size: 14px; }
.lesson-date-num { color: #182B48; font-weight: 800; font-size: 22px; line-height: 1; margin: 4px 0; }
.lesson-month { color: #7f8c8d; font-size: 12px; }

.lesson-content { flex: 1; }
.lesson-title { color: #182B48; font-weight: 700; font-size: 16px; margin-bottom: 4px; }
.lesson-content-text { color: #5a6c7d; font-size: 13px; line-height: 1.5; margin-bottom: 6px; }
.lesson-meta { display: flex; gap: 12px; flex-wrap: wrap; font-size: 12px; color: #7f8c8d; }
.lesson-meta span { display: inline-flex; align-items: center; gap: 4px; }
.lesson-meta i { color: {{ $subject->color }}; }

.lesson-actions { display: flex; gap: 6px; }
.btn-icon { width: 34px; height: 34px; border-radius: 9px; border: none; display: inline-flex; align-items: center; justify-content: center; color: #fff; font-size: 13px; cursor: pointer; transition: all 0.2s; text-decoration: none; }
.btn-icon:hover { transform: translateY(-2px); color: #fff; }
.btn-icon.view { background: linear-gradient(135deg, #3498db 0%, #2980b9 100%); }
.btn-icon.edit { background: linear-gradient(135deg, #8e44ad 0%, #732d91 100%); }
.btn-icon.complete { background: linear-gradient(135deg, #27ae60 0%, #219a52 100%); }
.btn-icon.undo { background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%); }
.btn-icon.delete { background: linear-gradient(135deg, #c0392b 0%, #a93226 100%); }

.status-badge { display: inline-flex; align-items: center; gap: 5px; padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: 700; }
.status-badge.pending { background: rgba(243,156,18,0.12); color: #f39c12; }
.status-badge.completed { background: rgba(39,174,96,0.12); color: #27ae60; }
.status-badge.cancelled { background: rgba(127,140,141,0.15); color: #7f8c8d; }
.status-badge.overdue { background: rgba(192,57,43,0.12); color: #c0392b; }

.empty-state { text-align: center; padding: 50px 20px; background: #fff; border-radius: 18px; box-shadow: 0 4px 20px rgba(24,43,72,0.05); }
.empty-state i { font-size: 60px; color: rgba(212,160,48,0.4); margin-bottom: 16px; }

@media (max-width: 768px) {
    .lesson-item { flex-direction: column; align-items: stretch; }
    .lesson-date-block { display: flex; gap: 8px; align-items: center; justify-content: center; }
    .header-actions { width: 100%; }
    .header-actions .btn { flex: 1; }
}
</style>
@endpush

@section('content')
<div class="subject-show-page">
    <div class="page-header">
        <div class="header-content">
            <div class="subject-color-badge" style="background: linear-gradient(135deg, {{ $subject->color }} 0%, #B4802C 100%);">
                <i class="fas fa-book-open"></i>
            </div>
            <div class="header-text">
                <h4>{{ $subject->name }}</h4>
                <p>الدروس المجدولة والمكتملة لهذه المادة</p>
            </div>
        </div>
        <div class="header-actions">
            <a href="{{ route($role . '.subjects.calendar', $subject->id) }}" class="btn btn-primary" style="background: linear-gradient(135deg, #182B48 0%, #2A4570 100%); color: #fff;">
                <i class="fas fa-calendar-alt ms-1"></i> التقويم
            </a>
            <a href="{{ route($role . '.lessons.template', $subject->id) }}" class="btn" style="background: #16a085; color: #fff;">
                <i class="fas fa-file-excel ms-1"></i> تنزيل قالب
            </a>
            <button type="button" class="btn" style="background: #f39c12; color: #fff;" onclick="document.getElementById('importModal').classList.add('show');document.getElementById('importModal').style.display='block';">
                <i class="fas fa-file-upload ms-1"></i> استيراد من Excel
            </button>
            <a href="{{ route($role . '.lessons.create', $subject->id) }}" class="btn" style="background: {{ $subject->color }}; color: #fff;">
                <i class="fas fa-plus-circle ms-1"></i> إضافة درس
            </a>
        </div>
    </div>

    <div class="subject-info-card">
        <div class="subject-info-header">
            <div class="subject-meta-info">
                <span class="meta-item"><i class="fas fa-user-tie"></i> {{ $subject->creator->name ?? '-' }}</span>
                <span class="meta-item"><i class="fas fa-calendar"></i> أُنشئت في {{ $subject->created_at->format('Y/m/d') }}</span>
                <span class="meta-item"><i class="fas fa-{{ $subject->is_active ? 'check-circle' : 'pause-circle' }}"></i> {{ $subject->is_active ? 'نشطة' : 'غير نشطة' }}</span>
            </div>
        </div>
        @if($subject->description)
            <p class="subject-description">{{ $subject->description }}</p>
        @endif
    </div>

    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-icon total"><i class="fas fa-list"></i></div>
            <div class="stat-info"><h3>{{ $total }}</h3><span>إجمالي الدروس</span></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon completed"><i class="fas fa-check"></i></div>
            <div class="stat-info"><h3>{{ $completed }}</h3><span>تم إنجازها</span></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon pending"><i class="fas fa-clock"></i></div>
            <div class="stat-info"><h3>{{ $pending }}</h3><span>قيد الانتظار</span></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon overdue"><i class="fas fa-exclamation-triangle"></i></div>
            <div class="stat-info"><h3>{{ $overdue }}</h3><span>متأخرة</span></div>
        </div>
    </div>

    @if($total > 0)
        <div class="progress-section">
            <div class="progress-label">
                <span>نسبة الإنجاز الكلية</span>
                <span style="color: {{ $subject->color }};">{{ $progress }}%</span>
            </div>
            <div class="progress">
                <div class="progress-bar" style="width: {{ $progress }}%"></div>
            </div>
        </div>
    @endif

    <form method="GET" class="filter-bar">
        <select name="status" class="form-select" style="max-width: 200px;">
            <option value="all" {{ request('status', 'all') === 'all' ? 'selected' : '' }}>كل الحالات</option>
            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>قيد الانتظار</option>
            <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>منجزة</option>
            <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>ملغاة</option>
        </select>
        <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}" placeholder="من">
        <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}" placeholder="إلى">
        <button type="submit" class="btn btn-primary" style="background: {{ $subject->color }}; color: #fff;"><i class="fas fa-search"></i> تصفية</button>
        <a href="{{ route($role . '.subjects.show', $subject->id) }}" class="btn btn-secondary">إعادة</a>
    </form>

    @if($lessons->count() > 0)
        <div class="lessons-list">
            @foreach($lessons as $lesson)
                @php
                    $statusClass = $lesson->status;
                    if ($lesson->is_overdue) $statusClass = 'is-overdue';
                @endphp
                <div class="lesson-item status-{{ $statusClass }}">
                    <div class="lesson-date-block">
                        <div class="lesson-day">{{ $lesson->day_name }}</div>
                        <div class="lesson-date-num">{{ $lesson->lesson_date ? $lesson->lesson_date->format('d') : '-' }}</div>
                        <div class="lesson-month">{{ $lesson->lesson_date ? $lesson->lesson_date->format('M Y') : '' }}</div>
                    </div>

                    <div class="lesson-content">
                        <div class="lesson-title">
                            {{ $lesson->title }}
                            @if($lesson->is_overdue)
                                <span class="status-badge overdue"><i class="fas fa-exclamation"></i> متأخر</span>
                            @else
                                <span class="status-badge {{ $lesson->status }}">{{ $lesson->status_label }}</span>
                            @endif
                        </div>
                        @if($lesson->content)
                            <div class="lesson-content-text">{{ \Illuminate\Support\Str::limit($lesson->content, 150) }}</div>
                        @endif
                        <div class="lesson-meta">
                            @if($lesson->start_time)
                                <span><i class="fas fa-clock"></i> {{ $lesson->time_range }}</span>
                            @endif
                            @if($lesson->completed_at)
                                <span><i class="fas fa-check-circle"></i> تم في {{ $lesson->completed_at->format('Y/m/d H:i') }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="lesson-actions">
                        <a href="{{ route($role . '.lessons.show', [$subject->id, $lesson->id]) }}" class="btn-icon view" title="عرض"><i class="fas fa-eye"></i></a>
                        <a href="{{ route($role . '.lessons.edit', [$subject->id, $lesson->id]) }}" class="btn-icon edit" title="تعديل"><i class="fas fa-edit"></i></a>
                        @if($lesson->status !== 'completed')
                            <button type="button" class="btn-icon complete" title="إكمال الدرس" onclick="toggleLessonStatus({{ $subject->id }}, {{ $lesson->id }}, 'completed', this)"><i class="fas fa-check"></i></button>
                        @else
                            <button type="button" class="btn-icon undo" title="إعادة للحالة السابقة" onclick="toggleLessonStatus({{ $subject->id }}, {{ $lesson->id }}, 'pending', this)"><i class="fas fa-undo"></i></button>
                        @endif
                        <button type="button" class="btn-icon delete" title="حذف" onclick="deleteLesson({{ $subject->id }}, {{ $lesson->id }}, '{{ addslashes($lesson->title) }}')"><i class="fas fa-trash"></i></button>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-4 d-flex justify-content-center">
            {{ $lessons->links() }}
        </div>
    @else
        <div class="empty-state">
            <i class="fas fa-calendar-plus"></i>
            <h5>لا توجد دروس بعد</h5>
            <p>ابدأ بتفريغ الدروس زمنياً لهذه المادة</p>
            <a href="{{ route($role . '.lessons.create', $subject->id) }}" class="btn mt-3" style="background: {{ $subject->color }}; color: #fff; padding: 10px 24px; border-radius: 12px; font-weight: 700;">
                <i class="fas fa-plus ms-1"></i> إضافة أول درس
            </a>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
function toggleLessonStatus(subjectId, lessonId, status, btn) {
    const url = `/${role}/subjects/${subjectId}/lessons/${lessonId}/status`;
    fetch(url, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
        },
        body: JSON.stringify({ status })
    }).then(r => r.json()).then(data => {
        if (data.success) {
            toastr.success(data.message);
            setTimeout(() => location.reload(), 800);
        } else {
            toastr.error('حدث خطأ');
        }
    });
}

function deleteLesson(subjectId, lessonId, name) {
    Swal.fire({
        title: 'تأكيد الحذف',
        html: `هل تريد حذف الدرس <strong>${name}</strong>؟`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'نعم، احذف',
        cancelButtonText: 'إلغاء',
        confirmButtonColor: '#c0392b',
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/${role}/subjects/${subjectId}/lessons/${lessonId}`;
            form.innerHTML = `@csrf @method('DELETE')`;
            document.body.appendChild(form);
            form.submit();
        }
    });
}
</script>

<div class="modal fade" id="importModal" tabindex="-1" style="display: none;" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="border-radius: 16px; border: none;">
            <div class="modal-header" style="background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%); color: #fff; border-radius: 16px 16px 0 0;">
                <h5 class="modal-title" style="color: #fff; font-weight: 700;"><i class="fas fa-file-excel ms-2"></i> استيراد دروس من ملف Excel</h5>
                <button type="button" class="btn-close btn-close-white" onclick="document.getElementById('importModal').classList.remove('show');document.getElementById('importModal').style.display='none';"></button>
            </div>
            <form method="POST" action="{{ route($role . '.lessons.import', $subject->id) }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-body" style="padding: 24px;">
                    <div class="alert" style="background: rgba(212,160,48,0.10); border: 1px dashed #d4a030; border-radius: 12px; padding: 16px; color: #182B48;">
                        <h6 style="font-weight: 800; margin-bottom: 8px;"><i class="fas fa-info-circle"></i> إرشادات الاستيراد</h6>
                        <ol style="margin: 0; padding-right: 18px; line-height: 1.9; font-size: 13px;">
                            <li>قم بتنزيل القالب أولاً من زر <strong>"تنزيل قالب"</strong>.</li>
                            <li>القالب يحتوي على العناوين التالية: العنوان، المحتوى، التاريخ، وقت البداية، وقت النهاية، الحالة، الملاحظات.</li>
                            <li>الحقل الوحيد الإلزامي هو <strong>العنوان</strong>، البقية اختيارية.</li>
                            <li>صيغة التاريخ: <code>YYYY-MM-DD</code> (مثال: 2026-08-15).</li>
                            <li>صيغة الوقت: <code>HH:MM</code> (مثال: 08:30).</li>
                            <li>الحالة تكون إحدى القيم: <code>pending</code> (لم يتم) أو <code>completed</code> (تم) أو <code>cancelled</code> (ملغى).</li>
                            <li>الحد الأقصى: 10 ميجابايت.</li>
                        </ol>
                    </div>

                    <div class="mt-3">
                        <a href="{{ route($role . '.lessons.template', $subject->id) }}" class="btn" style="background: #16a085; color: #fff; border-radius: 10px;">
                            <i class="fas fa-download ms-1"></i> تنزيل القالب
                        </a>
                    </div>

                    <div class="mt-3">
                        <label class="form-label" style="color: #182B48; font-weight: 700;">اختر ملف Excel</label>
                        <input type="file" name="file" accept=".xlsx,.xls,.csv" class="form-control" required style="border: 2px dashed #d4a030; padding: 20px; border-radius: 12px; background: rgba(212,160,48,0.04);">
                        <small class="form-text text-muted">الصيغ المدعومة: xlsx, xls, csv</small>
                    </div>
                </div>
                <div class="modal-footer" style="background: #f8f9fa; border-top: 1px solid #e9ecef; border-radius: 0 0 16px 16px;">
                    <button type="button" class="btn" style="background: #f0f4f8; color: #182B48; border-radius: 10px;" onclick="document.getElementById('importModal').classList.remove('show');document.getElementById('importModal').style.display='none';">إلغاء</button>
                    <button type="submit" class="btn" style="background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%); color: #fff; border-radius: 10px;"><i class="fas fa-upload ms-1"></i> رفع واستيراد</button>
                </div>
            </form>
        </div>
    </div>
</div>

@if(session('import_errors'))
<div id="importErrorsData" data-errors='@json(session('import_errors'))'></div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const data = document.getElementById('importErrorsData');
    if (data) {
        const errors = JSON.parse(data.dataset.errors);
        if (errors.length > 0) {
            let html = '<ul style="text-align: right; margin: 0; padding-right: 20px;">';
            errors.forEach(e => { html += '<li>' + e + '</li>'; });
            html += '</ul>';
            Swal.fire({
                title: 'تفاصيل التخطي',
                html: html,
                icon: 'warning',
                confirmButtonText: 'حسناً',
                width: 600
            });
        }
    }
});
</script>
@endif
@endpush
