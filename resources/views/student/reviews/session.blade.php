@extends('layouts.app')

@section('title', 'جلسة المراجعة')
@section('page-title', 'جلسة المراجعة')
@section('role-label', 'طالب')

@section('sidebar-menu')
    @include('student.partials.sidebar')
@endsection

@push('styles')
<style>
.review-session {
    --gold: #d4a030;
    --gold-light: #e6b84d;
    --navy: #182B48;
    --navy-light: #2A4570;
}

.review-session .page-header {
    background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
    border-radius: 16px;
    padding: 24px 28px;
    margin-bottom: 24px;
    position: relative;
    overflow: hidden;
}

.review-session .page-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23d4a030' fill-opacity='0.06'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    pointer-events: none;
}

.review-session .page-header-content {
    position: relative;
    z-index: 1;
    display: flex;
    align-items: center;
    gap: 16px;
}

.review-session .page-icon {
    width: 56px;
    height: 56px;
    background: linear-gradient(135deg, var(--gold) 0%, var(--gold-light) 100%);
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    color: white;
    box-shadow: 0 6px 18px rgba(212, 160, 48, 0.35);
}

.review-session .page-title {
    color: white;
    font-size: 22px;
    font-weight: 700;
    margin-bottom: 4px;
}

.review-session .page-subtitle {
    color: rgba(255, 255, 255, 0.8);
    font-size: 13px;
    margin: 0;
}

.review-session .verse-card {
    background: white;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
    overflow: hidden;
    border: 1px solid rgba(24, 43, 72, 0.04);
}

.review-session .verse-header {
    background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
    padding: 16px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.review-session .verse-header h5 {
    color: white;
    font-weight: 700;
    margin: 0;
    font-size: 15px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.review-session .verse-header h5 i {
    color: var(--gold);
}

.review-session .verse-header .surah-info {
    color: rgba(255, 255, 255, 0.9);
    font-size: 13px;
}

.review-session .verse-body {
    padding: 32px;
    text-align: center;
    background: linear-gradient(135deg, rgba(24, 43, 72, 0.02) 0%, rgba(42, 69, 112, 0.03) 100%);
}

.review-session .verse-text {
    font-family: 'Amiri', 'Scheherazade', serif;
    font-size: 32px;
    line-height: 2.4;
    color: var(--navy);
    direction: rtl;
    padding: 40px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.04);
    border: 1px solid rgba(24, 43, 72, 0.04);
}

.review-session .verse-meta {
    margin-top: 16px;
    font-size: 14px;
    color: #6b7280;
}

.review-session .verse-meta i {
    color: var(--gold);
    margin-left: 4px;
}

.review-session .stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    margin-bottom: 24px;
}

.review-session .stat-item {
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
    padding: 20px;
    text-align: center;
    border: 1px solid rgba(24, 43, 72, 0.04);
    transition: all 0.3s ease;
}

.review-session .stat-item:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
}

.review-session .stat-item-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 12px;
    font-size: 20px;
}

.review-session .stat-item-value {
    font-size: 28px;
    font-weight: 800;
    margin-bottom: 4px;
}

.review-session .stat-item-label {
    font-size: 12px;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.review-session .rating-card {
    background: white;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
    overflow: hidden;
    border: 1px solid rgba(24, 43, 72, 0.04);
}

.review-session .rating-header {
    background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
    padding: 16px 20px;
    text-align: center;
}

.review-session .rating-header h5 {
    color: white;
    font-weight: 700;
    margin: 0;
    font-size: 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
}

.review-session .rating-header h5 i {
    color: var(--gold);
}

.review-session .rating-body {
    padding: 32px;
}

.review-session .rating-question {
    text-align: center;
    margin-bottom: 24px;
    font-size: 18px;
    font-weight: 600;
    color: var(--navy);
}

.review-session .rating-options {
    display: flex;
    justify-content: center;
    gap: 12px;
    flex-wrap: wrap;
}

.review-session .quality-btn {
    min-width: 120px;
    padding: 16px 20px;
    border-radius: 12px;
    border: 2px solid transparent;
    transition: all 0.3s ease;
    cursor: pointer;
    text-align: center;
}

.review-session .quality-btn:hover {
    transform: translateY(-3px);
}

.review-session .quality-btn.selected {
    border-color: var(--gold);
    box-shadow: 0 6px 20px rgba(212, 160, 48, 0.25);
}

.review-session .quality-btn input {
    display: none;
}

.review-session .quality-value {
    font-size: 24px;
    font-weight: 800;
    margin-bottom: 4px;
}

.review-session .quality-label {
    font-size: 12px;
    opacity: 0.8;
}

.review-session .rating-buttons {
    display: flex;
    justify-content: center;
    gap: 12px;
    margin-top: 32px;
}

.review-session .btn-submit {
    padding: 14px 32px;
    border-radius: 10px;
    font-weight: 600;
    font-size: 15px;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.review-session .btn-submit:hover {
    transform: translateY(-2px);
}

.review-session .btn-continue {
    background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
    border: none;
    color: white;
}

.review-session .btn-continue:hover {
    box-shadow: 0 6px 20px rgba(24, 43, 72, 0.35);
}

.review-session .btn-finish {
    background: linear-gradient(135deg, #059669 0%, #10b981 100%);
    border: none;
    color: white;
}

.review-session .btn-finish:hover {
    box-shadow: 0 6px 20px rgba(5, 150, 105, 0.35);
}

.review-session .bismillah {
    font-family: 'Amiri', serif;
    font-size: 32px;
    color: var(--navy);
    text-align: center;
    margin-bottom: 24px;
    opacity: 0.6;
}

.review-session .back-btn {
    background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
    border: none;
    border-radius: 10px;
    padding: 10px 20px;
    font-weight: 600;
    color: white;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    text-decoration: none;
}

.review-session .back-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(24, 43, 72, 0.35);
    color: white;
}

.review-session .error-message {
    text-align: center;
    color: #dc2626;
    font-size: 13px;
    margin-top: 12px;
}

@media (max-width: 768px) {
    .review-session .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }

    .review-session .verse-text {
        font-size: 24px;
        padding: 24px;
    }

    .review-session .rating-options {
        flex-direction: column;
    }

    .review-session .quality-btn {
        width: 100%;
    }

    .review-session .rating-buttons {
        flex-direction: column;
    }
}
</style>
<link href="https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&family=Scheherazade:wght@400;700&family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
@endpush

@section('content')
<div class="review-session">
    <div class="bismillah">بِسْمِ اللَّهِ الرَّحْمَنِ الرَّحِيمِ</div>

    <x-page-hero icon="fas fa-book-quran">
    </x-page-hero>

    <div class="row">
        <div class="col-12">
            <div class="verse-card mb-4">
                <div class="verse-header">
                    <h5>
                        <i class="fas fa-book-open"></i>
                        الآيات المطلوبة للمراجعة
                    </h5>
                    <span class="surah-info">{{ $reviewItem->surah }}</span>
                </div>
                <div class="verse-body">
                    <div class="verse-text">
                        {{ $verseText }}
                    </div>
                    @if(isset($verseMeta['surah_name']))
                    <div class="verse-meta">
                        <i class="fas fa-book"></i>
                        {{ $verseMeta['surah_name'] }} - آية رقم {{ $reviewItem->verse_start }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-item">
            <div class="stat-item-icon" style="background: linear-gradient(135deg, rgba(212, 160, 48, 0.15), rgba(230, 184, 77, 0.2)); color: var(--gold);">
                <i class="fas fa-redo"></i>
            </div>
            <div class="stat-item-value" style="color: var(--gold);">{{ $reviewItem->repetitions }}</div>
            <div class="stat-item-label">التكرار</div>
        </div>
        <div class="stat-item">
            <div class="stat-item-icon" style="background: linear-gradient(135deg, rgba(24, 43, 72, 0.1), rgba(42, 69, 112, 0.15)); color: var(--navy);">
                <i class="fas fa-sliders-h"></i>
            </div>
            <div class="stat-item-value" style="color: var(--navy);">{{ number_format($reviewItem->ease_factor, 2) }}</div>
            <div class="stat-item-label">معامل السهولة</div>
        </div>
        <div class="stat-item">
            <div class="stat-item-icon" style="background: linear-gradient(135deg, rgba(5, 150, 105, 0.1), rgba(16, 185, 129, 0.15)); color: #059669;">
                <i class="fas fa-calendar-alt"></i>
            </div>
            <div class="stat-item-value" style="color: #059669;">{{ $reviewItem->interval }}</div>
            <div class="stat-item-label">الفترة (أيام)</div>
        </div>
        <div class="stat-item">
            <div class="stat-item-icon" style="background: linear-gradient(135deg, rgba(139, 92, 246, 0.1), rgba(167, 139, 250, 0.15)); color: #8b5cf6;">
                <i class="fas fa-history"></i>
            </div>
            <div class="stat-item-value" style="color: #8b5cf6;">{{ $reviewItem->last_reviewed_at?->diffForHumans() ?? 'الأولى' }}</div>
            <div class="stat-item-label">آخر مراجعة</div>
        </div>
    </div>

    <form action="{{ route('student.reviews.submit', $reviewItem) }}" method="POST">
        @csrf
        <div class="rating-card">
            <div class="rating-header">
                <h5>
                    <i class="fas fa-star"></i>
                    كيف كانت تلاوة المراجعة؟
                </h5>
            </div>
            <div class="rating-body">
                <div class="rating-question">الرجاء تقييم أدائك في تلاوة الآيات</div>

                <div class="rating-options">
                    @foreach([1, 2, 3, 4, 5] as $rating)
                    <label class="quality-btn btn {{ $rating <= 2 ? 'btn-danger' : ($rating <= 3 ? 'btn-warning' : 'btn-success') }}" data-rating="{{ $rating }}">
                        <input type="radio" name="quality" value="{{ $rating }}" class="d-none" required>
                        <div class="quality-value">{{ $rating }}</div>
                        <div class="quality-label">
                            @if($rating == 1)
                                لم أتذكر
                            @elseif($rating == 2)
                                صعب جداً
                            @elseif($rating == 3)
                                صحيح بصعوبة
                            @elseif($rating == 4)
                                جيد
                            @else
                                ممتاز
                            @endif
                        </div>
                    </label>
                    @endforeach
                </div>

                @error('quality')
                <div class="error-message">{{ $message }}</div>
                @enderror

                <div class="rating-buttons">
                    <button type="submit" name="continue" value="1" class="btn-submit btn-continue">
                        <i class="fas fa-arrow-left"></i>
                        تسليم ومتابعة
                    </button>
                    <button type="submit" class="btn-submit btn-finish">
                        <i class="fas fa-check"></i>
                        تسليم وإنهاء
                    </button>
                </div>
            </div>
        </div>
    </form>

    <div class="mt-4 text-start">
        <a href="{{ route('student.reviews.index') }}" class="back-btn">
            <i class="fas fa-arrow-right"></i>
            العودة للمراجعة
        </a>
    </div>
</div>
@endsection

@section('scripts')
@parent
<script>
document.querySelectorAll('.quality-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.quality-btn').forEach(b => {
            b.classList.remove('selected');
            b.style.transform = '';
        });
        this.classList.add('selected');
        this.style.transform = 'translateY(-4px) scale(1.05)';
    });
});
</script>
@endsection