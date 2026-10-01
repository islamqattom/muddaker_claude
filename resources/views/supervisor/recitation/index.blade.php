@extends('layouts.app')

@section('title', 'التسميع')
@section('page-title', 'جلسة تسميع جديدة')
@section('role-label', 'مشرف')

@section('sidebar-menu')
    @include('supervisor.partials.sidebar')
@endsection

@php
$sessionTypes = [
    ['value' => 'new_memorization', 'label' => 'حفظ جديد', 'icon' => 'fa-book-open', 'color' => 'primary', 'desc' => 'تسميع حفظ جديد للطالب'],
    ['value' => 'review', 'label' => 'مراجعة', 'icon' => 'fa-redo', 'color' => 'success', 'desc' => 'مراجعة ما تم حفظه سابقاً'],
    ['value' => 'unmastered', 'label' => 'حفظ غير متقن', 'icon' => 'fa-exclamation-triangle', 'color' => 'warning', 'desc' => 'إعادة التسميع للصفحات الضعيفة'],
    ['value' => 'recitation', 'label' => 'سرد', 'icon' => 'fa-scroll', 'color' => 'info', 'desc' => 'تلاوة الجزء كاملاً من أوله لآخره'],
    ['value' => 'mafsil', 'label' => 'مفصل', 'icon' => 'fa-star', 'color' => 'danger', 'desc' => 'اختبار المفصل (3-30 جزء)'],
    ['value' => 'not_recitate', 'label' => 'لم يسمع', 'icon' => 'fa-times-circle', 'color' => 'secondary', 'desc' => 'تسجيل عدم التسميع'],
];
@endphp

@push('styles')
<style>
.recitation-index {
    --gold: #d4a030;
    --gold-light: #e6b84d;
    --navy: #182B48;
    --navy-light: #2A4570;
}

.recitation-index .hero-section {
    background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
    border-radius: 14px;
    padding: 16px 22px;
    margin-bottom: 16px;
    position: relative;
    overflow: hidden;
}

.recitation-index .hero-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23d4a030' fill-opacity='0.06'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    pointer-events: none;
}

.recitation-index .hero-content {
    position: relative;
    z-index: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 16px;
}

.recitation-index .hero-content > .hero-icon {
    position: absolute;
    right: 22px;
}

.recitation-index .hero-content > div:not(.hero-icon) {
    flex: 1;
    text-align: center;
}

.recitation-index .hero-icon {
    width: 48px;
    height: 48px;
    background: linear-gradient(135deg, var(--gold) 0%, var(--gold-light) 100%);
    border-radius: 13px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    color: white;
    box-shadow: 0 6px 18px rgba(212, 160, 48, 0.35);
    flex-shrink: 0;
}

.recitation-index .hero-title {
    color: white;
    font-size: 18px;
    font-weight: 700;
    margin-bottom: 4px;
}

.recitation-index .hero-subtitle {
    color: rgba(255, 255, 255, 0.8);
    font-size: 12.5px;
    margin: 0;
}

.recitation-index .session-type-card {
    border: 1px solid rgba(24, 43, 72, 0.06);
    border-radius: 13px;
    padding: 16px 12px;
    text-align: center;
    cursor: pointer;
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    background: white;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    min-height: 120px;
}

.recitation-index .session-type-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
    border-color: var(--gold);
}

.recitation-index .session-type-card.active {
    border-color: var(--navy);
    background: linear-gradient(135deg, rgba(24, 43, 72, 0.03) 0%, rgba(42, 69, 112, 0.05) 100%);
}

.recitation-index .session-type-card .type-icon {
    width: 48px;
    height: 48px;
    border-radius: 13px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    margin-bottom: 10px;
    flex-shrink: 0;
}

.recitation-index .session-type-card:hover .type-icon {
    transform: scale(1.08);
}

.recitation-index .session-type-card .type-label {
    font-size: 14px;
    font-weight: 700;
    color: var(--navy);
    margin-bottom: 4px;
}

.recitation-index .session-type-card .type-desc {
    font-size: 11.5px;
    color: #7f8c8d;
    margin: 0;
    line-height: 1.3;
}

.recitation-index .form-card {
    background: white;
    border-radius: 13px;
    box-shadow: 0 3px 12px rgba(0, 0, 0, 0.04);
    overflow: hidden;
}

.recitation-index .form-card .card-header {
    background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
    padding: 12px 16px;
    border: none;
}

.recitation-index .form-card .card-header h5 {
    color: white;
    font-weight: 700;
    margin: 0;
    font-size: 14px;
}

.recitation-index .student-card {
    background: linear-gradient(135deg, rgba(24, 43, 72, 0.03) 0%, rgba(42, 69, 112, 0.05) 100%);
    border: 1px solid rgba(24, 43, 72, 0.06);
    border-radius: 13px;
    padding: 14px;
    margin-bottom: 16px;
}

.recitation-index .student-stats {
    display: flex;
    gap: 12px;
}

.recitation-index .student-stat {
    text-align: center;
    flex: 1;
}

.recitation-index .student-stat .stat-value {
    font-size: 22px;
    font-weight: 800;
    line-height: 1;
    margin-bottom: 2px;
}

.recitation-index .student-stat .stat-label {
    font-size: 12px;
    color: #7f8c8d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.recitation-index .options-panel {
    background: linear-gradient(135deg, rgba(24, 43, 72, 0.02) 0%, rgba(42, 69, 112, 0.03) 100%);
    border: 1px solid rgba(24, 43, 72, 0.06);
    border-radius: 13px;
    padding: 16px;
    margin-top: 14px;
}

.recitation-index .color-guide-card {
    background: white;
    border-radius: 13px;
    box-shadow: 0 3px 12px rgba(0, 0, 0, 0.04);
    overflow: hidden;
}

.recitation-index .color-guide-card .card-header {
    background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%);
    padding: 12px 16px;
}

.recitation-index .color-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 12px;
    border-bottom: 1px solid #f0f4f8;
    transition: background 0.2s;
}

.recitation-index .color-item:last-child {
    border-bottom: none;
}

.recitation-index .color-item:hover {
    background: #fafbfc;
}

.recitation-index .color-dot {
    width: 30px;
    height: 30px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
    color: white;
    flex-shrink: 0;
}

.recitation-index .sessions-list {
    background: white;
    border-radius: 13px;
    box-shadow: 0 3px 12px rgba(0, 0, 0, 0.04);
    overflow: hidden;
}

.recitation-index .sessions-list .card-header {
    background: linear-gradient(135deg, var(--gold) 0%, var(--gold-light) 100%);
    padding: 12px 16px;
}

.recitation-index .session-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 14px;
    border-bottom: 1px solid #f0f4f8;
    transition: all 0.2s;
}

.recitation-index .session-item:last-child {
    border-bottom: none;
}

.recitation-index .session-item:hover {
    background: linear-gradient(135deg, rgba(24, 43, 72, 0.02) 0%, rgba(42, 69, 112, 0.03) 100%);
}

.recitation-index .session-avatar {
    width: 30px;
    height: 30px;
    background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--gold);
    font-size: 12px;
}

.recitation-index .session-info h6 {
    font-weight: 600;
    color: var(--navy);
    margin: 0 0 2px;
    font-size: 13px;
}

.recitation-index .session-info small {
    color: #7f8c8d;
    font-size: 11.5px;
}

.recitation-index .quran-verse {
    font-family: 'Amiri', serif;
    font-size: 12.5px;
    color: rgba(255, 255, 255, 0.8);
    margin-top: 8px;
    text-align: center;
}

.recitation-index .start-btn {
    background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
    border: none;
    border-radius: 9px;
    padding: 8px 16px;
    font-size: 13px;
    font-weight: 700;
    color: white;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(24, 43, 72, 0.3);
}

.recitation-index .start-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 18px rgba(24, 43, 72, 0.35);
}

.recitation-index .compact-badge {
    font-size: 11.5px;
    padding: 3px 10px;
    border-radius: 7px;
}

@media (max-width: 768px) {
    .recitation-index .hero-section {
        padding: 14px 16px;
    }

    .recitation-index .hero-title {
        font-size: 17px;
    }

    .recitation-index .hero-icon {
        width: 44px;
        height: 44px;
        font-size: 18px;
    }

    .recitation-index .student-stats {
        flex-wrap: wrap;
        gap: 10px;
    }

    .recitation-index .student-stat {
        flex: 1 1 70px;
    }

    .recitation-index .student-stat .stat-value {
        font-size: 18px;
    }
}
</style>
@endpush

@section('content')
<div class="recitation-index">
    <div class="row g-3">
        <div class="col-lg-5">
            <x-page-hero icon="fas fa-book-quran">
                <h1 class="page-banner-title">جلسة تسميع</h1>
                <p class="page-banner-sub">ابدأ جلسة جديدة مع طالب</p>
            </x-page-hero>

            <div class="form-card">
                <div class="card-header">
                    <h5><i class="fas fa-plus-circle me-2"></i>معلومات الجلسة</h5>
                </div>
                <div class="card-body p-3">
                    <form id="sessionStartForm">
                        <div class="mb-3">
                            <label class="form-label fw-bold mb-2 small">
                                <i class="fas fa-user-graduate text-primary me-1"></i>
                                اختر الطالب
                            </label>
                            <select name="student_id" id="studentSelect" class="form-select form-select-lg select2" required onchange="loadStudentInfo()">
                                <option value="">-- ابحث عن طالب --</option>
                                @foreach($students as $student)
                                    <option value="{{ $student->id }}"
                                        data-level="{{ $student->level->name ?? '' }}"
                                        data-memorization="{{ $student->level->new_memorization_pages ?? 3 }}"
                                        data-review="{{ $student->level->review_pages ?? 6 }}"
                                        data-part="{{ $student->current_part }}"
                                        data-page="{{ $student->current_page }}">
                                        {{ $student->user->name }}
                                        @if($student->student_number)
                                            ({{ $student->student_number }})
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div id="studentInfo" class="student-card d-none">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <div class="session-avatar">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1" id="si-name">-</h6>
                                    <span class="badge bg-primary compact-badge" id="si-level">-</span>
                                </div>
                            </div>
                            <div class="student-stats">
                                <div class="student-stat">
                                    <div class="stat-value text-primary" id="si-memorization">-</div>
                                    <div class="stat-label">صفحة حفظ</div>
                                </div>
                                <div class="student-stat">
                                    <div class="stat-value text-success" id="si-review">-</div>
                                    <div class="stat-label">صفحة مراجعة</div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold mb-2 small">
                                <i class="fas fa-calendar-alt text-primary me-1"></i>
                                تاريخ التسميع
                            </label>
                            <input type="date" name="session_date" id="sessionDate" class="form-control" value="{{ date('Y-m-d') }}">
                        </div>

                        <div class="mb-2">
                            <label class="form-label fw-bold mb-2 small">
                                <i class="fas fa-list-ul text-primary me-1"></i>
                                نوع التسميع
                            </label>
                            <div class="row g-2" id="sessionTypes">
                                <div class="col-6">
                                    <input type="radio" class="btn-check" name="type" id="type-new_memorization" value="new_memorization" onchange="showTypeOptions('new_memorization')">
                                    <label class="session-type-card" for="type-new_memorization">
                                        <div class="type-icon bg-primary bg-opacity-10 text-primary">
                                            <i class="fas fa-book-open"></i>
                                        </div>
                                        <div class="type-label">حفظ جديد</div>
                                        <p class="type-desc">تسميع حفظ جديد للطالب</p>
                                    </label>
                                </div>
                                <div class="col-6">
                                    <input type="radio" class="btn-check" name="type" id="type-review" value="review" onchange="showTypeOptions('review')">
                                    <label class="session-type-card" for="type-review">
                                        <div class="type-icon bg-success bg-opacity-10 text-success">
                                            <i class="fas fa-redo"></i>
                                        </div>
                                        <div class="type-label">مراجعة</div>
                                        <p class="type-desc">مراجعة ما تم حفظه سابقاً</p>
                                    </label>
                                </div>
                                <div class="col-6">
                                    <input type="radio" class="btn-check" name="type" id="type-unmastered" value="unmastered" onchange="showTypeOptions('unmastered')">
                                    <label class="session-type-card" for="type-unmastered">
                                        <div class="type-icon bg-warning bg-opacity-10 text-warning">
                                            <i class="fas fa-exclamation-triangle"></i>
                                        </div>
                                        <div class="type-label">حفظ غير متقن</div>
                                        <p class="type-desc">إعادة التسميع للصفحات الضعيفة</p>
                                    </label>
                                </div>
                                <div class="col-6">
                                    <input type="radio" class="btn-check" name="type" id="type-recitation" value="recitation" onchange="showTypeOptions('recitation')">
                                    <label class="session-type-card" for="type-recitation">
                                        <div class="type-icon bg-info bg-opacity-10 text-info">
                                            <i class="fas fa-scroll"></i>
                                        </div>
                                        <div class="type-label">سرد</div>
                                        <p class="type-desc">تلاوة الجزء كاملاً من أوله لآخره</p>
                                    </label>
                                </div>
                                <div class="col-6">
                                    <input type="radio" class="btn-check" name="type" id="type-mafsil" value="mafsil" onchange="showTypeOptions('mafsil')">
                                    <label class="session-type-card" for="type-mafsil">
                                        <div class="type-icon bg-danger bg-opacity-10 text-danger">
                                            <i class="fas fa-star"></i>
                                        </div>
                                        <div class="type-label">مفصل</div>
                                        <p class="type-desc">اختبار المفصل (3-30 جزء)</p>
                                    </label>
                                </div>
                                <div class="col-6">
                                    <input type="radio" class="btn-check" name="type" id="type-not_recitate" value="not_recitate" onchange="showTypeOptions('not_recitate')">
                                    <label class="session-type-card" for="type-not_recitate">
                                        <div class="type-icon bg-secondary bg-opacity-10 text-secondary">
                                            <i class="fas fa-times-circle"></i>
                                        </div>
                                        <div class="type-label">لم يسمع</div>
                                        <p class="type-desc">تسجيل عدم التسميع</p>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div id="options-new_memorization" class="options-panel session-options d-none">
                            <h6 class="mb-2"><i class="fas fa-book-open text-primary me-1"></i>خيارات الحفظ الجديد</h6>
                            <div class="row g-2 mb-2">
                                <div class="col-8">
                                    <label class="form-label small fw-bold">الجزء</label>
                                    <select name="part_id_new" class="form-select form-select-sm" onchange="updatePartInfo('new', this.value)">
                                        @foreach($parts as $part)
                                            <option value="{{ $part->id }}" data-start-page="{{ $part->start_page }}" data-end-page="{{ $part->end_page }}">
                                                الجزء {{ $part->part_number }} (ص {{ $part->start_page }} - {{ $part->end_page }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-4">
                                    <label class="form-label small fw-bold">الصفحة</label>
                                    <select name="page_number_new" class="form-select form-select-sm" id="page_number_new" required onchange="checkRemainingPages('new')">
                                    </select>
                                </div>
                            </div>
                            <div id="continuation_part_new_wrapper" class="row g-2 d-none">
                                <div class="col-12">
                                    <label class="form-label small text-info fw-bold">الجزء المكمل</label>
                                    <select name="continuation_part_new" class="form-select form-select-sm" id="continuation_part_new">
                                        <option value="">-- اختر --</option>
                                        @foreach($parts as $part)
                                            <option value="{{ $part->id }}">الجزء {{ $part->part_number }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="small text-muted mt-1" id="suggestion-new"></div>
                            <div class="small mt-1" id="repeated-new"></div>
                            <div class="d-flex gap-2 mt-2">
                                <button type="button" class="btn btn-outline-primary btn-sm" onclick="openAddPagesModal('new_memorization')">
                                    <i class="fas fa-plus me-1"></i>إضافة
                                </button>
                                <button type="button" class="btn btn-outline-success btn-sm" onclick="openRepeatedPagesModal('new_memorization')">
                                    <i class="fas fa-redo me-1"></i>معادة
                                </button>
                            </div>
                        </div>

                        <div id="options-review" class="options-panel session-options d-none">
                            <h6 class="mb-3"><i class="fas fa-redo text-success me-2"></i>خيارات المراجعة</h6>
                            <div class="row g-3 mb-3">
                                <div class="col-8">
                                    <label class="form-label small fw-bold">الجزء</label>
                                    <select name="part_id_review" class="form-select" onchange="updatePartInfo('review', this.value)">
                                        @foreach($parts as $part)
                                            <option value="{{ $part->id }}" data-start-page="{{ $part->start_page }}" data-end-page="{{ $part->end_page }}">
                                                الجزء {{ $part->part_number }} (ص {{ $part->start_page }} - {{ $part->end_page }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-4">
                                    <label class="form-label small fw-bold">رقم الصفحة</label>
                                    <select name="page_number_review" class="form-select" id="page_number_review" required onchange="checkRemainingPages('review')">
                                    </select>
                                </div>
                            </div>
                            <div id="continuation_part_review_wrapper" class="row g-3 d-none">
                                <div class="col-12">
                                    <label class="form-label small text-info fw-bold">الجزء المكمل</label>
                                    <select name="continuation_part_review" class="form-select" id="continuation_part_review">
                                        <option value="">-- اختر الجزء المكمل --</option>
                                        @foreach($parts as $part)
                                            <option value="{{ $part->id }}">الجزء {{ $part->part_number }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="small text-muted mt-1" id="suggestion-review"></div>
                            <div class="small mt-1" id="repeated-review"></div>
                            <div class="d-flex gap-2 mt-3">
                                <button type="button" class="btn btn-outline-primary btn-sm" onclick="openAddPagesModal('review')">
                                    <i class="fas fa-plus me-1"></i>إضافة صفحات
                                </button>
                                <button type="button" class="btn btn-outline-success btn-sm" onclick="openRepeatedPagesModal('review')">
                                    <i class="fas fa-redo me-1"></i>صفحات معادة
                                </button>
                            </div>
                        </div>

                        <div id="options-unmastered" class="options-panel session-options d-none">
                            <h6 class="mb-3"><i class="fas fa-exclamation-triangle text-warning me-2"></i>خيارات الحفظ غير المتقن</h6>
                            <div class="row g-3 mb-3">
                                <div class="col-8">
                                    <label class="form-label small fw-bold">الجزء</label>
                                    <select name="part_id_unmastered" class="form-select" onchange="updatePartInfo('unmastered', this.value)">
                                        @foreach($parts as $part)
                                            <option value="{{ $part->id }}" data-start-page="{{ $part->start_page }}" data-end-page="{{ $part->end_page }}">
                                                الجزء {{ $part->part_number }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-4">
                                    <label class="form-label small fw-bold">رقم الصفحة</label>
                                    <select name="page_number_unmastered" class="form-select" id="page_number_unmastered" required>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold">عدد الصفحات</label>
                                <div class="btn-group w-100">
                                    @foreach([5, 10, 15, 20] as $count)
                                    <input type="radio" class="btn-check" name="unmastered_count" id="uc-{{ $count }}" value="{{ $count }}" {{ $count === 5 ? 'checked' : '' }}>
                                    <label class="btn btn-outline-secondary" for="uc-{{ $count }}">{{ $count }}</label>
                                    @endforeach
                                </div>
                            </div>
                            <div class="small text-muted mt-1" id="suggestion-unmastered"></div>
                            <div class="small mt-1" id="repeated-unmastered"></div>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-outline-primary btn-sm" onclick="openAddPagesModal('unmastered')">
                                    <i class="fas fa-plus me-1"></i>إضافة صفحات
                                </button>
                                <button type="button" class="btn btn-outline-success btn-sm" onclick="openRepeatedPagesModal('unmastered')">
                                    <i class="fas fa-redo me-1"></i>صفحات معادة
                                </button>
                            </div>
                        </div>

                        <div id="options-recitation" class="options-panel session-options d-none">
                            <h6 class="mb-3"><i class="fas fa-scroll text-info me-2"></i>خيارات السرد</h6>
                            <div class="mb-3">
                                <label class="form-label small fw-bold">اختر الجزء</label>
                                <select name="part_id_recitation" class="form-select">
                                    @foreach($parts as $part)
                                        <option value="{{ $part->id }}">الجزء {{ $part->part_number }} (ص {{ $part->start_page }} - {{ $part->end_page }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="small text-muted" id="suggestion-recitation"></div>
                        </div>

                        <div id="options-mafsil" class="options-panel session-options d-none">
                            <h6 class="mb-3"><i class="fas fa-star text-danger me-2"></i>خيارات المفصل</h6>
                            <label class="form-label small fw-bold mb-2">نوع المفصل</label>
                            <div class="row g-2">
                                @php $mafsilTypes = [[3, '90%'], [6, '90%'], [10, '80%'], [15, '80%'], [20, '75%'], [25, '75%'], [30, '75%']]; @endphp
                                @foreach($mafsilTypes as [$num, $score])
                                <div class="col-4">
                                    <input type="radio" class="btn-check" name="mafsil_type" id="mt-{{ $num }}" value="{{ $num }}">
                                    <label class="btn btn-outline-danger w-100 py-3" for="mt-{{ $num }}">
                                        <div class="fw-bold fs-4">{{ $num }}</div>
                                        <small>الرسوب &lt; {{ $score }}</small>
                                    </label>
                                </div>
                                @endforeach
                            </div>
                        </div>

                        <div id="options-not_recitate" class="options-panel session-options d-none">
                            <div class="alert alert-secondary mb-0">
                                <i class="fas fa-info-circle me-2"></i>
                                سيتم تسجيل الطالب على أنه لم يسمع بدون تقييم أي صفحات
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="button" class="start-btn w-100" onclick="startSession()">
                                <i class="fas fa-play me-2"></i>
                                بدء الجلسة
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="color-guide-card mb-3">
                <div class="card-header">
                    <h5 class="mb-0 text-white"><i class="fas fa-palette me-2"></i>دليل التقييم</h5>
                </div>
                <div class="card-body p-0">
                    <div class="row g-0">
                        <div class="col-md-6">
                            <div class="color-item">
                                <div class="color-dot" style="background: linear-gradient(135deg, #27ae60, #2ecc71);">
                                    <i class="fas fa-check"></i>
                                </div>
                                <div>
                                    <div class="fw-bold" style="color: #27ae60; font-size: 13px;">ممتاز</div>
                                    <small class="text-muted">0 خطأ أو ترددين كحد أقصى</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="color-item">
                                <div class="color-dot" style="background: linear-gradient(135deg, #f39c12, #e67e22);">
                                    <i class="fas fa-minus"></i>
                                </div>
                                <div>
                                    <div class="fw-bold" style="color: #f39c12; font-size: 13px;">جيد</div>
                                    <small class="text-muted">1 خطأ أو 3-5 ترددات</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="color-item">
                                <div class="color-dot" style="background: linear-gradient(135deg, #e74c3c, #c0392b);">
                                    <i class="fas fa-times"></i>
                                </div>
                                <div>
                                    <div class="fw-bold" style="color: #e74c3c; font-size: 13px;">ضعيف</div>
                                    <small class="text-muted">2 خطأ بدون ترددات</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="color-item">
                                <div class="color-dot" style="background: linear-gradient(135deg, #2c3e50, #34495e);">
                                    <i class="fas fa-ban"></i>
                                </div>
                                <div>
                                    <div class="fw-bold" style="color: #2c3e50; font-size: 13px;">راسب</div>
                                    <small class="text-muted">2 خطأ + تردد أو 3+ أخطاء</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="p-3 bg-light border-top">
                        <h6 class="text-primary mb-2" style="font-size: 12px;"><i class="fas fa-star me-1"></i>المفاصل وعلاماتها:</h6>
                        <div class="row g-2 text-center">
                            <div class="col-4">
                                <div class="p-2 bg-white rounded shadow-sm">
                                    <strong class="text-danger" style="font-size: 11px;">مفصل 3 و 6</strong>
                                    <div class="text-success fw-bold mt-1" style="font-size: 11px;">الرسوب أقل من 90%</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-2 bg-white rounded shadow-sm">
                                    <strong class="text-danger" style="font-size: 11px;">مفصل 10 و 15</strong>
                                    <div class="text-warning fw-bold mt-1" style="font-size: 11px;">الرسوب أقل من 80%</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-2 bg-white rounded shadow-sm">
                                    <strong class="text-danger" style="font-size: 11px;">مفصل 20 و 25 و 30</strong>
                                    <div class="text-danger fw-bold mt-1" style="font-size: 11px;">الرسوب أقل من 75%</div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-2 text-center text-muted small">
                            <i class="fas fa-calculator me-1"></i>
                            خطأ = -3 علامات | كل 3 ترددات = -1 علامة
                        </div>
                    </div>
                </div>
            </div>

            <div class="sessions-list">
                <div class="card-header">
                    <h5 class="mb-0 text-white"><i class="fas fa-list me-2"></i>جلسات اليوم</h5>
                </div>
                @php
                    $todaySessions = \App\Models\RecitationSession::whereHas('student', fn($q) => $q->where('supervisor_id', auth()->id()))
                        ->whereDate('session_date', today())
                        ->with(['student.user', 'part'])
                        ->latest()
                        ->take(8)
                        ->get();
                @endphp

                @forelse($todaySessions as $session)
                <div class="session-item">
                    <div class="d-flex align-items-center gap-3">
                        <div class="session-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="session-info">
                            <h6>{{ $session->student->user->name }}</h6>
                            <small>
                                {{ $session->type_label }}
                                @if($session->part) - الجزء {{ $session->part->part_number }} @endif
                            </small>
                        </div>
                    </div>
                    <div class="text-end">
                        <span class="badge rounded-pill bg-{{ $session->status_color }}">{{ $session->status_label }}</span>
                        <div class="mt-1">
                            <span class="text-danger small">{{ $session->total_errors }} خطأ</span>
                            <span class="text-warning small me-2">{{ $session->total_hesitations }} تردد</span>
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center py-5">
                    <i class="fas fa-calendar-times fa-3x text-muted mb-3 d-block opacity-50"></i>
                    <p class="text-muted mb-0">لا توجد جلسات اليوم</p>
                </div>
                @endforelse

                @if($todaySessions->count() > 0)
                <div class="p-3 text-center border-top">
                    <a href="{{ route('supervisor.recitation.history') }}" class="btn btn-outline-primary btn-sm">
                        عرض كل الجلسات <i class="fas fa-arrow-left me-1"></i>
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Not Recitate Modal -->
<div class="modal fade" id="notRecitateModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title"><i class="fas fa-times-circle text-secondary me-2"></i>لم يسمع</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="notRecitateForm" method="POST" action="{{ route('supervisor.recitation.save-not-recitate') }}">
                @csrf
                <div class="modal-body pt-2">
                    <input type="hidden" name="student_id" id="notRecitateStudentId">
                    <input type="hidden" name="session_date" id="notRecitateSessionDate">
                    <div class="mb-3">
                        <label class="form-label">السبب (اختياري)</label>
                        <textarea name="reason" class="form-control" rows="3" placeholder="أدخل سبب عدم التسميع"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary">حفظ</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Pages Modal -->
<div class="modal fade" id="addPagesModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-plus text-primary me-2"></i>إضافة صفحات</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="additionalPagesContainer"></div>
                <button type="button" class="btn btn-outline-primary btn-sm mt-2" onclick="addAdditionalPageRow()">
                    <i class="fas fa-plus me-1"></i>إضافة صف
                </button>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                <button type="button" class="btn btn-primary" onclick="applyAdditionalPages()">تطبيق</button>
            </div>
        </div>
    </div>
</div>

<!-- Repeated Pages Modal -->
<div class="modal fade" id="repeatedPagesModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-redo text-success me-2"></i>صفحات معادة</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="repeatedPagesContainer"></div>
                <button type="button" class="btn btn-outline-success btn-sm mt-2" onclick="addRepeatedPageRow(currentSessionType)">
                    <i class="fas fa-plus me-1"></i>إضافة صف
                </button>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                <button type="button" class="btn btn-success" onclick="applyRepeatedPages()">تطبيق</button>
            </div>
        </div>
    </div>
</div>

<!-- Session Alerts Modal -->
<div class="modal fade" id="sessionAlertsModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 16px; overflow: hidden; border: none;">
            <div class="modal-header" style="background: linear-gradient(135deg, #182B48 0%, #2A4570 100%); border: none;">
                <h5 class="modal-title text-white">
                    <i class="fas fa-bell me-2" style="color: #d4a030;"></i>
                    تنبيهات الجلسة
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" onclick="acknowledgeMemorizationAlerts()"></button>
            </div>
            <div class="modal-body p-4">
                <div id="memorizationAlertSection" class="d-none"></div>
                <div id="failedPagesAlertSection" class="d-none"></div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-primary px-4" data-bs-dismiss="modal" onclick="acknowledgeMemorizationAlerts()">
                    <i class="fas fa-check me-1"></i>تم
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let pendingMemorizationAlertIds = [];
let sessionAlertsModal = null;

function acknowledgeMemorizationAlerts() {
    if (!pendingMemorizationAlertIds.length) return;
    const ids = pendingMemorizationAlertIds;
    pendingMemorizationAlertIds = [];
    fetch('{{ route('supervisor.alerts.acknowledge') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
        },
        body: JSON.stringify({ ids: ids }),
    }).catch(() => {});
}

function selectedSessionType() {
    const el = document.querySelector('input[name="type"]:checked');
    return el ? el.value : '';
}

function escapeHtml(value) {
    const div = document.createElement('div');
    div.textContent = value == null ? '' : String(value);
    return div.innerHTML;
}

function renderMemorizationSection(studentName, alerts) {
    const section = document.getElementById('memorizationAlertSection');
    if (!alerts || !alerts.length) {
        section.classList.add('d-none');
        section.innerHTML = '';
        return false;
    }

    pendingMemorizationAlertIds = alerts.map(a => a.id);
    section.innerHTML = alerts.map(function(alert) {
        const part = alert.part_number ? ('الجزء ' + alert.part_number) : '';
        return '<div class="d-flex align-items-center gap-3 p-3 mb-2 rounded" style="background: rgba(24,43,72,0.04); border: 1px solid rgba(24,43,72,0.08);">' +
            '<div style="width:40px;height:40px;border-radius:10px;background:linear-gradient(135deg,#d4a030,#e6b84d);display:flex;align-items:center;justify-content:center;color:#fff;flex-shrink:0;">' +
            '<i class="fas fa-book-open"></i></div>' +
            '<div><div class="fw-bold" style="color:#182B48;">' + escapeHtml(studentName) + '</div>' +
            '<small class="text-muted">أتمّ حفظ ' + escapeHtml(part) + ' كاملاً ويجب أن يقوم بسرده</small></div></div>';
    }).join('');
    section.classList.remove('d-none');
    return true;
}

function renderFailedPagesSection(payload) {
    const section = document.getElementById('failedPagesAlertSection');
    const data = payload && payload.data ? payload.data : null;

    if (!data || !data.pages || !data.pages.length) {
        section.classList.add('d-none');
        section.innerHTML = '';
        return false;
    }

    const studentName = payload.student_name || '';
    const date = data.session_date || '';
    const typeLabel = data.type_label || data.type || '';
    const pages = data.pages.map(function(page) {
        const tags = [];
        if (page.surah_name) tags.push(page.surah_name);
        if (page.is_completed === false) tags.push('لم يكمل');
        const suffix = tags.length ? (' (' + tags.join(' - ') + ')') : '';
        return 'صفحة ' + page.page_number + suffix;
    }).join('، ');

    section.innerHTML =
        '<div class="p-3 rounded" style="background: rgba(192,57,43,0.06); border: 1px solid rgba(192,57,43,0.18);">' +
        '<div class="d-flex align-items-center gap-2 mb-2">' +
        '<div style="width:40px;height:40px;border-radius:10px;background:linear-gradient(135deg,#e74c3c,#c0392b);display:flex;align-items:center;justify-content:center;color:#fff;flex-shrink:0;">' +
        '<i class="fas fa-exclamation-triangle"></i></div>' +
        '<div class="fw-bold" style="color:#182B48;">الطالب ' + escapeHtml(studentName) + '</div>' +
        '</div>' +
        '<div class="text-muted small mb-1">لديه صفحات راسبة في آخر جلسة ' + escapeHtml(typeLabel) + ' بتاريخ ' + escapeHtml(date) + ':</div>' +
        '<div class="fw-bold" style="color:#c0392b;">' + escapeHtml(pages) + '</div>' +
        '</div>';
    section.classList.remove('d-none');
    return true;
}

function loadSessionAlerts() {
    const select = document.getElementById('studentSelect');
    if (!select || !select.value) return;

    const studentId = select.value;
    const type = selectedSessionType();

    const partUrl = '{{ route('supervisor.alerts.student', ['student' => '__ID__']) }}'.replace('__ID__', studentId);
    const partPromise = fetch(partUrl, {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
    }).then(response => response.ok ? response.json() : null).catch(() => null);

    let failedPromise = Promise.resolve(null);
    let suggestionPromise = Promise.resolve(null);
    if (type) {
        const failedUrl = '{{ route('supervisor.alerts.failed-pages', ['student' => '__ID__']) }}'.replace('__ID__', studentId) + '?type=' + encodeURIComponent(type);
        failedPromise = fetch(failedUrl, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        }).then(response => response.ok ? response.json() : null).catch(() => null);

        const suggestionUrl = '{{ route('supervisor.alerts.suggestion', ['student' => '__ID__']) }}'.replace('__ID__', studentId) + '?type=' + encodeURIComponent(type);
        suggestionPromise = fetch(suggestionUrl, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        }).then(response => response.ok ? response.json() : null).catch(() => null);
    }

    Promise.all([partPromise, failedPromise, suggestionPromise]).then(function(results) {
        const partData = results[0];
        const failedData = results[1];
        const suggestionData = results[2];

        applyStartSuggestion(type, suggestionData);
        applyFailedPagesToRepeated(type, failedData);

        pendingMemorizationAlertIds = [];
        const hasMemorization = renderMemorizationSection(partData ? partData.student_name : '', partData ? partData.alerts : []);
        const hasFailedPages = renderFailedPagesSection(failedData);

        if (hasMemorization || hasFailedPages) {
            if (!sessionAlertsModal) {
                sessionAlertsModal = new bootstrap.Modal(document.getElementById('sessionAlertsModal'));
            }
            sessionAlertsModal.show();
        }
    });
}

function repeatedTypeKey(type) {
    return type === 'new_memorization' ? 'new' : type;
}

function applyFailedPagesToRepeated(type, payload) {
    if (!['new_memorization', 'review', 'unmastered'].includes(type)) {
        return;
    }

    const data = payload && payload.data ? payload.data : null;
    const pages = data && data.pages ? data.pages : [];

    // The failed / not-completed pages of the last session are added to the
    // "معادة" list so the session starts from the end of the previous session.
    repeatedPagesData = pages.map(function(page) {
        return { part_id: page.part_id || null, page_number: page.page_number, not_completed: page.is_completed === false };
    });

    renderRepeatedChips(type);
}

function renderRepeatedChips(type) {
    const el = document.getElementById('repeated-' + repeatedTypeKey(type));
    if (!el) return;

    if (!repeatedPagesData.length) {
        el.innerHTML = '';
        return;
    }

    const chips = repeatedPagesData.map(function(item) {
        const label = 'صفحة ' + item.page_number + (item.not_completed ? ' (لم يكمل)' : '');
        return '<span class="badge bg-success bg-opacity-10 text-success me-1 mb-1" style="font-size:11px;">' + label + '</span>';
    }).join('');

    el.innerHTML = '<i class="fas fa-redo me-1"></i>معادة: ' + chips;
}

function applyStartSuggestion(type, payload) {
    const key = type === 'new_memorization' ? 'new' : type;
    const reasonEl = document.getElementById('suggestion-' + key);
    if (!reasonEl) return;

    const data = payload && payload.data ? payload.data : null;
    if (!data) {
        reasonEl.textContent = '';
        return;
    }

    reasonEl.textContent = data.reason || '';

    if (data.completed) {
        return;
    }

    if (type === 'recitation') {
        const partSelect = document.querySelector('select[name="part_id_recitation"]');
        if (partSelect && data.part_id) {
            partSelect.value = data.part_id;
        }
        return;
    }

    const partSelect = document.querySelector('select[name="part_id_' + key + '"]');
    const pageSelect = document.getElementById('page_number_' + key);

    if (partSelect && data.part_id) {
        partSelect.value = data.part_id;
        if (typeof updatePartInfo === 'function') {
            updatePartInfo(key, data.part_id);
        }
    }

    if (pageSelect && data.page_number) {
        pageSelect.value = data.page_number;
        if (typeof checkRemainingPages === 'function') {
            checkRemainingPages(key);
        }
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const studentSelect = document.getElementById('studentSelect');
    if (studentSelect) {
        $(studentSelect).on('change', loadSessionAlerts);
    }
    $('input[name="type"]').on('change', loadSessionAlerts);

    ['new', 'review', 'unmastered'].forEach(function(type) {
        const select = document.querySelector(`select[name="part_id_${type}"]`);
        if (select) updatePartInfo(type, select.value);
    });
});

function updatePartInfo(type, partId) {
    const partSelect = document.querySelector(`select[name="part_id_${type}"]`);
    const pageSelect = document.getElementById(`page_number_${type}`);
    if (!partSelect || !pageSelect) return;

    const selectedOption = partSelect.options[partSelect.selectedIndex];
    const startPage = parseInt(selectedOption.dataset.startPage);
    const endPage = parseInt(selectedOption.dataset.endPage);

    pageSelect.innerHTML = '';
    for (let page = startPage; page <= endPage; page++) {
        const option = document.createElement('option');
        option.value = page;
        option.textContent = 'صفحة ' + page;
        pageSelect.appendChild(option);
    }

    pageSelect.dataset.minPage = startPage;
    pageSelect.dataset.maxPage = endPage;
    checkRemainingPages(type);
}

function checkRemainingPages(type) {
    const studentSelect = document.getElementById('studentSelect');
    const selectedOption = studentSelect.options[studentSelect.selectedIndex];
    if (!studentSelect.value) return;

    let requiredPages = 0;
    if (type === 'new') requiredPages = parseInt(selectedOption.dataset.memorization) || 3;
    else if (type === 'review') requiredPages = parseInt(selectedOption.dataset.review) || 6;

    const partSelect = document.querySelector(`select[name="part_id_${type}"]`);
    const pageSelect = document.getElementById(`page_number_${type}`);
    const continuationWrapper = document.getElementById(`continuation_part_${type}_wrapper`);

    if (!partSelect || !pageSelect || !continuationWrapper) return;

    const selectedPartOption = partSelect.options[partSelect.selectedIndex];
    const endPage = parseInt(selectedPartOption.dataset.endPage);
    const selectedPage = parseInt(pageSelect.value);
    const remainingPages = endPage - selectedPage + 1;

    if (remainingPages < requiredPages) continuationWrapper.classList.remove('d-none');
    else {
        continuationWrapper.classList.add('d-none');
        const continuationSelect = document.getElementById(`continuation_part_${type}`);
        if (continuationSelect) continuationSelect.value = '';
    }
}

function loadStudentInfo() {
    const select = document.getElementById('studentSelect');
    const option = select.options[select.selectedIndex];
    const info = document.getElementById('studentInfo');

    if (select.value) {
        document.getElementById('si-name').textContent = option.text;
        document.getElementById('si-level').textContent = option.dataset.level || '-';
        document.getElementById('si-memorization').textContent = option.dataset.memorization + ' ';
        document.getElementById('si-review').textContent = option.dataset.review + ' ';
        info.classList.remove('d-none');
    } else {
        info.classList.add('d-none');
    }
}

function showTypeOptions(type) {
    document.querySelectorAll('.session-options').forEach(el => el.classList.add('d-none'));
    const opt = document.getElementById('options-' + type);
    if (opt) {
        opt.classList.remove('d-none');
        if (type === 'new_memorization') updatePartInfo('new', document.querySelector('select[name="part_id_new"]').value);
        else if (type === 'review') updatePartInfo('review', document.querySelector('select[name="part_id_review"]').value);
        else if (type === 'unmastered') updatePartInfo('unmastered', document.querySelector('select[name="part_id_unmastered"]').value);
    }
}

let additionalPagesData = [];
let repeatedPagesData = [];
let currentSessionType = '';
const partsData = @json($parts);

function openAddPagesModal(type) {
    currentSessionType = type;
    additionalPagesData = [];
    const container = document.getElementById('additionalPagesContainer');
    container.innerHTML = '';
    addAdditionalPageRow();
    new bootstrap.Modal(document.getElementById('addPagesModal')).show();
}

function addAdditionalPageRow() {
    const container = document.getElementById('additionalPagesContainer');
    const rowId = 'add-page-row-' + container.children.length;
    const row = document.createElement('div');
    row.className = 'row g-2 mb-2 align-items-end additional-page-row';
    row.id = rowId;

    let partOptions = '<option value="">-- اختر الجزء --</option>';
    partsData.forEach(part => {
        partOptions += `<option value="${part.id}" data-start-page="${part.start_page}" data-end-page="${part.end_page}">الجزء ${part.part_number}</option>`;
    });

    row.innerHTML = `
        <div class="col-5">
            <select name="part_id_add" class="form-select form-select-sm" onchange="updatePageOptions('${rowId}', this.value)">
                ${partOptions}
            </select>
        </div>
        <div class="col-4">
            <select name="page_number_add" class="form-select form-select-sm" id="page_${rowId}">
                <option value="">-- اختر الصفحة --</option>
            </select>
        </div>
        <div class="col-2">
            <button type="button" class="btn btn-outline-danger btn-sm" onclick="removePageRow('${rowId}')">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    container.appendChild(row);
}

function updatePageOptions(rowId, partId) {
    const row = document.getElementById(rowId);
    const partSelect = row.querySelector('select[name="part_id_add"]');
    const pageSelect = document.getElementById('page_' + rowId);

    if (!partId) {
        pageSelect.innerHTML = '<option value="">-- اختر الجزء أولاً --</option>';
        return;
    }

    const selectedOption = partSelect.options[partSelect.selectedIndex];
    const startPage = parseInt(selectedOption.dataset.startPage);
    const endPage = parseInt(selectedOption.dataset.endPage);

    pageSelect.innerHTML = '<option value="">-- اختر الصفحة --</option>';
    for (let page = startPage; page <= endPage; page++) {
        const option = document.createElement('option');
        option.value = page;
        option.textContent = 'صفحة ' + page;
        pageSelect.appendChild(option);
    }
}

function removePageRow(rowId) {
    document.getElementById(rowId).remove();
}

function applyAdditionalPages() {
    const rows = document.querySelectorAll('.additional-page-row');
    additionalPagesData = [];

    rows.forEach(row => {
        const partId = row.querySelector('select[name="part_id_add"]').value;
        const pageNumber = row.querySelector('select[name="page_number_add"]').value;
        if (partId && pageNumber) additionalPagesData.push({ part_id: partId, page_number: parseInt(pageNumber) });
    });

    if (additionalPagesData.length === 0) {
        toastr.warning('يرجى اختيار صفحة واحدة على الأقل');
        return;
    }

    bootstrap.Modal.getInstance(document.getElementById('addPagesModal')).hide();
    toastr.success('تمت إضافة ' + additionalPagesData.length + ' صفحة');
}

function openRepeatedPagesModal(type) {
    currentSessionType = type;
    const container = document.getElementById('repeatedPagesContainer');
    container.innerHTML = '';

    // Pre-fill with the pages already in the "معادة" list (e.g. the failed
    // pages of the last session) so the supervisor can review/remove them.
    repeatedPagesData.slice().forEach(function(item) {
        addRepeatedPageRow(type, item.part_id, item.page_number);
    });
    addRepeatedPageRow(type);

    new bootstrap.Modal(document.getElementById('repeatedPagesModal')).show();
}

function addRepeatedPageRow(type, preselectedPartId, preselectedPage) {
    const container = document.getElementById('repeatedPagesContainer');
    const rowId = 'repeated-page-row-' + container.children.length;
    const row = document.createElement('div');
    row.className = 'row g-2 mb-2 align-items-end repeated-page-row';
    row.id = rowId;

    let currentPartId = preselectedPartId || '';
    if (!currentPartId) {
        if (type === 'review') currentPartId = document.querySelector('select[name="part_id_review"]')?.value || '';
        else if (type === 'new_memorization') currentPartId = document.querySelector('select[name="part_id_new"]')?.value || '';
        else if (type === 'unmastered') currentPartId = document.querySelector('select[name="part_id_unmastered"]')?.value || '';
    }

    let partOptions = '<option value="">-- اختر الجزء --</option>';
    partsData.forEach(part => {
        const selected = part.id == currentPartId ? 'selected' : '';
        partOptions += `<option value="${part.id}" data-start-page="${part.start_page}" data-end-page="${part.end_page}" ${selected}>الجزء ${part.part_number}</option>`;
    });

    row.innerHTML = `
        <div class="col-5">
            <select name="part_id_repeat" class="form-select form-select-sm" onchange="updateRepeatedPageOptions('${rowId}', this.value)">
                ${partOptions}
            </select>
        </div>
        <div class="col-4">
            <select name="page_number_repeat" class="form-select form-select-sm" id="page_repeat_${rowId}">
                <option value="">-- اختر الصفحة --</option>
            </select>
        </div>
        <div class="col-2">
            <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeRepeatedPageRow('${rowId}')">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    container.appendChild(row);

    if (currentPartId) updateRepeatedPageOptions(rowId, currentPartId);

    if (preselectedPage) {
        const pageSelect = document.getElementById('page_repeat_' + rowId);
        if (pageSelect) pageSelect.value = preselectedPage;
    }
}

function updateRepeatedPageOptions(rowId, partId) {
    const row = document.getElementById(rowId);
    const partSelect = row.querySelector('select[name="part_id_repeat"]');
    const pageSelect = document.getElementById('page_repeat_' + rowId);

    if (!partId) {
        pageSelect.innerHTML = '<option value="">-- اختر الجزء أولاً --</option>';
        return;
    }

    const selectedOption = partSelect.options[partSelect.selectedIndex];
    const startPage = parseInt(selectedOption.dataset.startPage);
    const endPage = parseInt(selectedOption.dataset.endPage);

    pageSelect.innerHTML = '<option value="">-- اختر الصفحة --</option>';
    for (let page = startPage; page <= endPage; page++) {
        const option = document.createElement('option');
        option.value = page;
        option.textContent = 'صفحة ' + page;
        pageSelect.appendChild(option);
    }
}

function removeRepeatedPageRow(rowId) {
    document.getElementById(rowId).remove();
}

function applyRepeatedPages() {
    const rows = document.querySelectorAll('.repeated-page-row');
    repeatedPagesData = [];

    rows.forEach(row => {
        const partId = row.querySelector('select[name="part_id_repeat"]').value;
        const pageNumber = row.querySelector('select[name="page_number_repeat"]').value;
        if (partId && pageNumber) repeatedPagesData.push({ part_id: partId, page_number: parseInt(pageNumber) });
    });

    if (repeatedPagesData.length === 0) {
        toastr.warning('يرجى اختيار صفحة واحدة على الأقل');
        return;
    }

    bootstrap.Modal.getInstance(document.getElementById('repeatedPagesModal')).hide();
    renderRepeatedChips(currentSessionType);
    toastr.success('تمت إضافة ' + repeatedPagesData.length + ' صفحة معادة');
}

function startSession() {
    const studentId = document.getElementById('studentSelect').value;
    const typeEl = document.querySelector('input[name="type"]:checked');
    const sessionDateInput = document.getElementById('sessionDate');
    const sessionDate = sessionDateInput.value || new Date().toISOString().split('T')[0];

    if (!studentId) { toastr.warning('يرجى اختيار الطالب'); return; }
    if (!typeEl) { toastr.warning('يرجى اختيار نوع التسميع'); return; }

    const type = typeEl.value;
    let url = '';
    let additionalPagesParam = additionalPagesData.length > 0 ? '&additional_pages=' + encodeURIComponent(JSON.stringify(additionalPagesData)) : '';
    let repeatedPagesParam = repeatedPagesData.length > 0 && (type === 'review' || type === 'new_memorization' || type === 'unmastered') ? '&repeated_pages=' + encodeURIComponent(JSON.stringify(repeatedPagesData)) : '';

    if (type === 'new_memorization') {
        const partId = document.querySelector('select[name="part_id_new"]').value;
        const pageNumber = document.getElementById('page_number_new').value;
        const pageSelect = document.getElementById('page_number_new');
        const page = parseInt(pageNumber);
        if (page < parseInt(pageSelect.dataset.minPage) || page > parseInt(pageSelect.dataset.maxPage)) {
            toastr.warning('رقم الصفحة يجب أن يكون بين ' + pageSelect.dataset.minPage + ' و ' + pageSelect.dataset.maxPage);
            return;
        }
        const continuationPart = document.querySelector('select[name="continuation_part_new"]')?.value;
        let continuationParam = continuationPart ? `&continuation_part_id=${continuationPart}` : '';
        url = `{{ route('supervisor.recitation.new-memorization') }}?student_id=${studentId}&part_id=${partId}&page_number=${pageNumber}&session_date=${sessionDate}${continuationParam}${additionalPagesParam}${repeatedPagesParam}`;
    } else if (type === 'review') {
        const partId = document.querySelector('select[name="part_id_review"]').value;
        const pageNumber = document.getElementById('page_number_review').value;
        const pageSelect = document.getElementById('page_number_review');
        const page = parseInt(pageNumber);
        if (page < parseInt(pageSelect.dataset.minPage) || page > parseInt(pageSelect.dataset.maxPage)) {
            toastr.warning('رقم الصفحة يجب أن يكون بين ' + pageSelect.dataset.minPage + ' و ' + pageSelect.dataset.maxPage);
            return;
        }
        const continuationPartReview = document.querySelector('select[name="continuation_part_review"]')?.value;
        let continuationParamReview = continuationPartReview ? `&continuation_part_id=${continuationPartReview}` : '';
        url = `{{ route('supervisor.recitation.review') }}?student_id=${studentId}&part_id=${partId}&page_number=${pageNumber}&session_date=${sessionDate}${continuationParamReview}${additionalPagesParam}${repeatedPagesParam}`;
    } else if (type === 'unmastered') {
        const partId = document.querySelector('select[name="part_id_unmastered"]').value;
        const pageNumber = document.getElementById('page_number_unmastered').value;
        const count = document.querySelector('input[name="unmastered_count"]:checked')?.value || 5;
        const pageSelect = document.getElementById('page_number_unmastered');
        const page = parseInt(pageNumber);
        if (page < parseInt(pageSelect.dataset.minPage) || page > parseInt(pageSelect.dataset.maxPage)) {
            toastr.warning('رقم الصفحة يجب أن يكون بين ' + pageSelect.dataset.minPage + ' و ' + pageSelect.dataset.maxPage);
            return;
        }
        url = `{{ route('supervisor.recitation.unmastered') }}?student_id=${studentId}&part_id=${partId}&pages_count=${count}&page_number=${pageNumber}&session_date=${sessionDate}${additionalPagesParam}${repeatedPagesParam}`;
    } else if (type === 'recitation') {
        const partId = document.querySelector('select[name="part_id_recitation"]').value;
        url = `{{ route('supervisor.recitation.recitation') }}?student_id=${studentId}&part_id=${partId}&session_date=${sessionDate}`;
    } else if (type === 'mafsil') {
        const mafsilType = document.querySelector('input[name="mafsil_type"]:checked')?.value;
        if (!mafsilType) { toastr.warning('يرجى اختيار نوع المفصل'); return; }
        url = `{{ route('supervisor.recitation.mafsil') }}?student_id=${studentId}&mafsil_type=${mafsilType}&session_date=${sessionDate}`;
    } else if (type === 'not_recitate') {
        document.getElementById('notRecitateStudentId').value = studentId;
        document.getElementById('notRecitateSessionDate').value = sessionDate;
        new bootstrap.Modal(document.getElementById('notRecitateModal')).show();
        return;
    }

    if (url) window.location.href = url;
}
</script>
@endpush