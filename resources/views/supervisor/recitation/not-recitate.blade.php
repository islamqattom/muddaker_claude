@extends('layouts.app')

@section('title', 'لم يسمع')
@section('page-title', 'تسجيل عدم التسميع')
@section('role-label', 'مشرف')

@section('sidebar-menu')
    @include('supervisor.partials.sidebar')
@endsection

@push('styles')
<style>
.recitation-not-recite {
    --gold: #d4a030;
    --gold-light: #e6b84d;
    --navy: #182B48;
    --navy-light: #2A4570;
}

.recitation-not-recite .student-header-card {
    background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
    border-radius: 14px;
    overflow: hidden;
    margin-bottom: 20px;
    position: relative;
}

.recitation-not-recite .student-header-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23d4a030' fill-opacity='0.06'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    pointer-events: none;
}

.recitation-not-recite .student-header-content {
    position: relative;
    z-index: 1;
    padding: 14px 18px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
}

.recitation-not-recite .student-info {
    display: flex;
    align-items: center;
    gap: 12px;
}

.recitation-not-recite .student-avatar {
    width: 48px;
    height: 48px;
    background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    color: white;
    box-shadow: 0 4px 12px rgba(108, 117, 125, 0.3);
}

.recitation-not-recite .student-name {
    color: white;
    font-size: 18px;
    font-weight: 700;
    margin-bottom: 4px;
}

.recitation-not-recite .student-meta {
    display: flex;
    align-items: center;
    gap: 8px;
}

.recitation-not-recite .quran-verse-header {
    font-family: 'Amiri', serif;
    font-size: 13px;
    color: rgba(255, 255, 255, 0.7);
    text-align: center;
    padding: 6px 16px;
    background: rgba(0,0,0,0.1);
    border-top: 1px solid rgba(212, 160, 48, 0.2);
}

.recitation-not-recite .compact-badge {
    font-size: 11px;
    padding: 4px 10px;
    border-radius: 6px;
}

.recitation-not-recite .islamic-border {
    position: relative;
}

.recitation-not-recite .islamic-border::before {
    content: '';
    position: absolute;
    top: 0;
    right: 0;
    width: 60px;
    height: 3px;
    background: linear-gradient(90deg, var(--gold), transparent);
}

.recitation-not-recite .form-card {
    background: white;
    border-radius: 14px;
    overflow: hidden;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.06);
}

.recitation-not-recite .form-header {
    background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
    padding: 14px 18px;
}

.recitation-not-recite .form-label {
    font-weight: 600;
    color: var(--navy);
    margin-bottom: 8px;
}

.recitation-not-recite .form-control {
    border-radius: 10px;
    border: 1px solid #e9ecef;
    padding: 10px 14px;
    font-size: 14px;
    transition: all 0.2s ease;
}

.recitation-not-recite .form-control:focus {
    border-color: var(--navy);
    box-shadow: 0 0 0 3px rgba(24, 43, 72, 0.1);
}

.recitation-not-recite .save-btn {
    background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
    border: none;
    border-radius: 10px;
    padding: 12px 28px;
    font-size: 14px;
    font-weight: 700;
    color: white;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(24, 43, 72, 0.3);
}

.recitation-not-recite .save-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(24, 43, 72, 0.4);
}

.recitation-not-recite .btn-cancel {
    border: 1px solid #e9ecef;
    border-radius: 10px;
    padding: 10px 18px;
    font-size: 13px;
    font-weight: 600;
    transition: all 0.3s ease;
    background: white;
    color: #6c757d;
}

.recitation-not-recite .btn-cancel:hover {
    background: #f8f9fa;
    border-color: #ced4da;
    color: #495057;
}

@media (max-width: 768px) {
    .recitation-not-recite .student-header-content {
        flex-direction: column;
        text-align: center;
    }

    .recitation-not-recite .student-info {
        flex-direction: column;
    }

    .recitation-not-recite .student-name {
        font-size: 16px;
    }
}
</style>
@endpush

@section('content')
<div class="recitation-not-recite">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="student-header-card islamic-border">
                <div class="student-header-content">
                    <div class="student-info">
                        <div class="student-avatar">
                            <i class="fas fa-user-slash"></i>
                        </div>
                        <div>
                            <h2 class="student-name">{{ $student->user->name }}</h2>
                            <div class="student-meta">
                                <span class="badge bg-white text-secondary compact-badge">
                                    <i class="fas fa-layer-group me-1"></i>
                                    {{ $student->level->name ?? '' }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="text-white text-start">
                        <span class="badge bg-secondary compact-badge px-3">
                            <i class="fas fa-times-circle me-1"></i>لم يسمع
                        </span>
                    </div>
                </div>
                <div class="quran-verse-header">
                    <i class="fas fa-book-quran me-2"></i>
                    {{ 'وَمَا أَنتَ عَلَىٰ هَذَا بِحَفِيظٍ' }}
                </div>
            </div>

            <div class="form-card islamic-border">
                <div class="form-header">
                    <h5 class="mb-0 text-white"><i class="fas fa-edit me-2"></i>تسجيل عدم التسميع</h5>
                </div>
                <div class="card-body" style="padding: 20px;">
                    <form method="POST" action="{{ route('supervisor.recitation.save-not-recitate') }}">
                        @csrf
                        <input type="hidden" name="student_id" value="{{ $student->id }}">

                        <div class="mb-3">
                            <label class="form-label">
                                <i class="fas fa-calendar-alt me-1 text-secondary"></i>
                                التاريخ <span class="text-danger">*</span>
                            </label>
                            <input type="date" name="session_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">
                                <i class="fas fa-comment-alt me-1 text-secondary"></i>
                                السبب (اختياري)
                            </label>
                            <textarea name="reason" class="form-control" rows="3" placeholder="أدخل سبب عدم التسميع..."></textarea>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="save-btn flex-grow-1">
                                <i class="fas fa-save me-2"></i>حفظ
                            </button>
                            <a href="{{ route('supervisor.recitation.index') }}" class="btn-cancel">
                                <i class="fas fa-times me-2"></i>إلغاء
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection