@extends('layouts.app')

@section('title', 'جلسة المراجعة')
@section('page-title', 'جلسة المراجعة')
@section('role-label', 'طالب')

@section('sidebar-menu')
    @include('student.partials.sidebar')
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fas fa-book-quran me-2"></i>
                مراجعة سورة {{ $reviewItem->surah }} - آيات {{ $reviewItem->verse_start }} : {{ $reviewItem->verse_end }}
            </h5>
            <a href="{{ route('student.reviews.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-arrow-right me-1"></i>العودة
            </a>
        </div>
    </div>
    <div class="card-body">
        <!-- Verse Display -->
        <div class="text-center mb-4">
            <div class="p-4 rounded bg-light" style="font-size: 1.8rem; line-height: 2.2; direction: rtl;">
                {{ $verseText }}
            </div>
            @if(isset($verseMeta['surah_name']))
            <small class="text-muted mt-2">{{ $verseMeta['surah_name'] }} - آية رقم {{ $reviewItem->verse_start }}</small>
            @endif
        </div>

        <!-- Info Cards -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="card text-center">
                    <div class="card-body py-2">
                        <div class="text-muted small">التكرار</div>
                        <div class="fw-bold">{{ $reviewItem->repetitions }}</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card text-center">
                    <div class="card-body py-2">
                        <div class="text-muted small">معامل السهولة</div>
                        <div class="fw-bold">{{ number_format($reviewItem->ease_factor, 2) }}</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card text-center">
                    <div class="card-body py-2">
                        <div class="text-muted small">الفترة</div>
                        <div class="fw-bold">{{ $reviewItem->interval }} {{ $reviewItem->interval == 1 ? 'يوم' : 'أيام' }}</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card text-center">
                    <div class="card-body py-2">
                        <div class="text-muted small">آخر مراجعة</div>
                        <div class="fw-bold">{{ $reviewItem->last_reviewed_at?->diffForHumans() ?? 'الأولى' }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- SM-2 Quality Rating -->
        <form action="{{ route('student.reviews.submit', $reviewItem) }}" method="POST">
            @csrf
            <div class="card bg-light">
                <div class="card-body">
                    <h6 class="mb-3 text-center"><i class="fas fa-star me-2"></i>كيف كانت تلاوة المراجعة؟</h6>

                    <div class="d-flex justify-content-center gap-2 flex-wrap mb-3">
                        @foreach([1, 2, 3, 4, 5] as $rating)
                        <label class="quality-btn btn btn-lg {{ $rating <= 2 ? 'btn-danger' : ($rating <= 3 ? 'btn-warning' : 'btn-success') }}">
                            <input type="radio" name="quality" value="{{ $rating }}" class="d-none" required>
                            <div>
                                <div class="fw-bold">{{ $rating }}</div>
                                <small>
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
                                </small>
                            </div>
                        </label>
                        @endforeach
                    </div>

                    @error('quality')
                    <div class="text-danger text-center small">{{ $message }}</div>
                    @enderror

                    <div class="d-flex justify-content-center gap-2 mt-4">
                        <button type="submit" name="continue" value="1" class="btn btn-primary">
                            <i class="fas fa-arrow-left me-1"></i>تسليم ومتابعة
                        </button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-check me-1"></i>تسليم وإنهاء
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
@parent
<script>
document.querySelectorAll('.quality-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.quality-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
    });
});
</script>
@endsection
