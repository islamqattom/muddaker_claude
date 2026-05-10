@extends('layouts.app')

@section('title', 'لم يسمع')
@section('page-title', 'تسجيل عدم التسميع')
@section('role-label', 'مشرف')

@section('sidebar-menu')
    @include('supervisor.partials.sidebar')
@endsection

@push('styles')
<style>
.rec-session {
    --gold: #d4a030;
    --gold-light: #e6b84d;
    --navy: #182B48;
    --navy-light: #2A4570;
    --success: #27ae60;
    --warning: #f39c12;
    --danger: #c0392b;
    --info: #3498db;
    --purple: #8e44ad;
}

.rec-session .page-hero {
    background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
    border-radius: 20px;
    padding: 20px 28px;
    margin-bottom: 24px;
    position: relative;
    overflow: hidden;
}

.rec-session .page-hero::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23d4a030' fill-opacity='0.06'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    pointer-events: none;
}

.rec-session .hero-content { position: relative; z-index: 1; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px; }

.rec-session .hero-icon {
    width: 56px; height: 56px;
    background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
    border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    font-size: 24px; color: white;
    box-shadow: 0 6px 20px rgba(108,117,125,0.35);
}

.rec-session .student-info { display: flex; align-items: center; gap: 14px; }

.rec-session .student-avatar {
    width: 50px; height: 50px;
    background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
    border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    font-size: 20px; color: white;
    box-shadow: 0 4px 12px rgba(108,117,125,0.3);
}

.rec-session .student-name { font-size: 20px; font-weight: 700; color: white; margin-bottom: 4px; }

.rec-session .hero-meta { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }

.rec-session .compact-badge {
    font-size: 11px; padding: 5px 12px; border-radius: 8px;
}

.rec-session .type-badge {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 8px 16px; border-radius: 12px;
    font-size: 13px; font-weight: 600;
}

.rec-session .type-badge.not-recitate { background: rgba(108,117,125,0.25); color: #6c757d; }

.rec-session .quran-verse {
    font-family: 'Amiri', serif;
    font-size: 13px;
    color: rgba(255,255,255,0.6);
    text-align: center;
    padding: 10px 20px;
    background: rgba(0,0,0,0.1);
    border-top: 1px solid rgba(212,160,48,0.2);
    margin-top: 16px;
    position: relative; z-index: 1;
}

.rec-session .page-card {
    border: 1px solid rgba(24,43,72,0.08);
    border-radius: 16px;
    overflow: hidden;
    transition: all 0.25s ease;
    box-shadow: 0 2px 12px rgba(0,0,0,0.04);
    margin-bottom: 16px;
}

.rec-session .page-card-header {
    background: linear-gradient(135deg, rgba(24,43,72,0.03) 0%, rgba(42,69,112,0.05) 100%);
    padding: 14px 18px;
    display: flex; align-items: center; justify-content: space-between;
    border-bottom: 1px solid rgba(24,43,72,0.04);
}

.rec-session .save-btn {
    background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
    border: none;
    border-radius: 12px;
    padding: 12px 32px;
    font-size: 14px; font-weight: 700;
    color: white;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(24,43,72,0.3);
}

.rec-session .save-btn:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(24,43,72,0.4); }

.rec-session .btn-cancel {
    border: 1px solid rgba(24,43,72,0.1);
    border-radius: 12px;
    padding: 12px 20px;
    font-size: 13px; font-weight: 600;
    transition: all 0.3s ease;
    background: white;
    color: #6c757d;
}

.rec-session .btn-cancel:hover { background: rgba(24,43,72,0.05); border-color: var(--navy); color: var(--navy); }

.rec-session .notes-textarea {
    border: 2px solid rgba(24,43,72,0.08);
    border-radius: 12px;
    padding: 12px 16px;
    font-size: 14px;
    transition: all 0.3s ease;
}

.rec-session .notes-textarea:focus {
    border-color: var(--gold);
    box-shadow: 0 0 0 3px rgba(212,160,48,0.15);
}

@media (max-width: 768px) {
    .rec-session .hero-content { flex-direction: column; text-align: center; }
    .rec-session .student-info { flex-direction: column; }
    .rec-session .hero-meta { justify-content: center; }
}
</style>
@endpush

@section('content')
<div class="rec-session">
    <div class="page-hero">
        <div class="hero-content">
            <div class="student-info">
                <div class="student-avatar">
                    <i class="fas fa-user-slash"></i>
                </div>
                <div>
                    <h2 class="student-name">{{ $student->user->name }}</h2>
                    <div class="hero-meta">
                        <span class="compact-badge bg-warning text-dark">
                            <i class="fas fa-layer-group me-1"></i>
                            {{ $student->level->name ?? '' }}
                        </span>
                        @if($student->student_number)
                        <span class="text-white opacity-75 small">
                            <i class="fas fa-id-card me-1"></i>
                            {{ $student->student_number }}
                        </span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="d-flex align-items-center gap-3 flex-wrap">
                <span class="type-badge not-recitate">
                    <i class="fas fa-user-slash"></i>لم يسمع
                </span>
            </div>
        </div>
        <div class="quran-verse">
            <i class="fas fa-book-quran me-2"></i>
            {{ 'وَمَا أَنتَ عَلَىٰ هَذَا بِحَفِيظٍ' }}
        </div>
    </div>

    <div class="page-card">
        <div class="page-card-header">
            <h6 class="mb-0" style="color: var(--navy);">
                <i class="fas fa-edit me-2" style="color: #6c757d;"></i>
                تسجيل عدم التسميع
            </h6>
        </div>
        <div class="page-card-body">
            <form method="POST" action="{{ route('supervisor.recitation.save-not-recitate') }}">
                @csrf
                <input type="hidden" name="student_id" value="{{ $student->id }}">

                <div class="mb-4">
                    <label class="form-label fw-bold mb-2" style="color: var(--navy);">
                        <i class="fas fa-calendar-alt me-2" style="color: var(--gold);"></i>
                        التاريخ <span class="text-danger">*</span>
                    </label>
                    <input type="date" name="session_date" class="form-control notes-textarea" value="{{ date('Y-m-d') }}" required style="height: 48px;">
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold mb-2" style="color: var(--navy);">
                        <i class="fas fa-comment-alt me-2" style="color: var(--gold);"></i>
                        السبب (اختياري)
                    </label>
                    <textarea name="reason" class="form-control notes-textarea" rows="3" placeholder="أدخل سبب عدم التسميع..."></textarea>
                </div>

                <div class="d-flex gap-3">
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
@endsection