@extends('layouts.app')

@section('title', 'إنشاء تحدي')
@section('page-title', 'إنشاء تحدي جديد')
@section('role-label', 'طالب')

@section('sidebar-menu')
    @include('student.partials.sidebar')
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-swords me-2"></i>إنشاء تحدي جديد</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('student.challenges.store') }}" method="POST">
            @csrf

            <!-- Opponent Selection -->
            <div class="mb-4">
                <label class="form-label">اختر المنافس</label>
                @if($opponents->count() > 0)
                <div class="row g-2">
                    @foreach($opponents as $opponent)
                    <div class="col-md-6 col-lg-4">
                        <label class="card selectable-card {{ old('opponent_id') == $opponent->id ? 'selected' : '' }}">
                            <div class="card-body py-2">
                                <input type="radio" name="opponent_id" value="{{ $opponent->id }}" class="d-none"
                                       {{ old('opponent_id') == $opponent->id ? 'checked' : '' }} required>
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>{{ $opponent->name }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $opponent->level?->name ?? '-' }}</small>
                                    </div>
                                    <div class="text-end">
                                        <i class="fas fa-fire text-warning"></i>
                                        {{ $opponent->current_streak }} يوم
                                    </div>
                                </div>
                            </div>
                        </label>
                    </div>
                    @endforeach
                </div>
                @error('opponent_id')
                <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
                @else
                <div class="alert alert-info mb-0">
                    <i class="fas fa-info-circle me-1"></i>
                    لا يوجد منافسون متاحون حالياً. حاول لاحقاً!
                </div>
                @endif
            </div>

            <!-- Challenge Details -->
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">السورة</label>
                    <input type="number" name="surah" class="form-control" min="1" max="114"
                           value="{{ old('surah') }}" required>
                    @error('surah')
                    <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">آية البداية</label>
                    <input type="number" name="verse_start" class="form-control" min="1"
                           value="{{ old('verse_start') }}" required>
                    @error('verse_start')
                    <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">آية النهاية</label>
                    <input type="number" name="verse_end" class="form-control" min="1"
                           value="{{ old('verse_end') }}" required>
                    @error('verse_end')
                    <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="mt-3">
                <label class="form-label">تاريخ الهدف</label>
                <input type="date" name="target_date" class="form-control"
                       min="{{ \Carbon\Carbon::tomorrow()->format('Y-m-d') }}"
                       value="{{ old('target_date') }}" required>
                <small class="text-muted">يجب أن يكون بعد اليوم</small>
                @error('target_date')
                <div class="text-danger small">{{ $message }}</div>
                @enderror
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary btn-lg w-100" {{ $opponents->isEmpty() ? 'disabled' : '' }}>
                    <i class="fas fa-paper-plane me-2"></i>إرسال التحدي
                </button>
            </div>
        </form>
    </div>
</div>

<div class="text-center mt-3">
    <a href="{{ route('student.challenges.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-right me-1"></i>العودة للتحديات
    </a>
</div>
@endsection

@section('scripts')
@parent
<script>
document.querySelectorAll('.selectable-card').forEach(card => {
    card.addEventListener('click', function() {
        document.querySelectorAll('.selectable-card').forEach(c => c.classList.remove('selected'));
        this.classList.add('selected');
        const radio = this.querySelector('input[type="radio"]');
        radio.checked = true;
    });
});
</script>
@endsection

@section('styles')
@parent
<style>
.selectable-card {
    cursor: pointer;
    border: 2px solid #dee2e6;
    transition: all 0.2s;
}
.selectable-card:hover {
    border-color: #0d6efd;
}
.selectable-card.selected {
    border-color: #0d6efd;
    background: #f8f9ff;
}
</style>
</parent>
@endsection
