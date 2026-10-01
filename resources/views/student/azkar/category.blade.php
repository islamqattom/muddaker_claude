@extends('layouts.app')

@section('title', $category->name)
@section('page-title', $category->name)
@section('role-label', 'طالب')

@section('sidebar-menu')
    @include('student.partials.sidebar')
@endsection

@push('styles')
<style>
.azkar-category {
    --gold: #d4a030;
    --gold-light: #e6b84d;
    --navy: #182B48;
    --navy-light: #2A4570;
    --success: #27ae60;
    --warning: #f39c12;
    --danger: #c0392b;
    --info: #3498db;
    --purple: #9b59b6;
}

.azkar-category .page-hero {
    background: linear-gradient(135deg, {{ $category->color }} 0%, {{ $category->color }}99 100%);
    border-radius: 14px;
    padding: 16px 22px;
    margin-bottom: 16px;
    position: relative;
    overflow: hidden;
}

.azkar-category .page-hero::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.1'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    pointer-events: none;
}

.azkar-category .hero-icon {
    width: 48px; height: 48px;
    background: rgba(255,255,255,0.2);
    border-radius: 13px;
    display: flex; align-items: center; justify-content: center;
    font-size: 20px; color: white;
    position: relative; z-index: 1;
    flex-shrink: 0;
}

.azkar-category .hero-title { color: white; font-size: 18px; font-weight: 700; margin-bottom: 4px; position: relative; z-index: 1; }
.azkar-category .hero-sub { color: rgba(255,255,255,0.8); font-size: 12.5px; margin: 0; position: relative; z-index: 1; }

.azkar-category .adhkar-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.azkar-category .dhikr-card {
    background: white;
    border-radius: 13px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.04);
    border: 1px solid rgba(24,43,72,0.04);
    padding: 16px;
    transition: all 0.3s ease;
}

.azkar-category .dhikr-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 30px rgba(0,0,0,0.08);
}

.azkar-category .dhikr-content {
    font-size: 15px;
    line-height: 2;
    color: var(--navy);
    font-weight: 500;
    text-align: center;
    padding: 16px;
    background: linear-gradient(135deg, rgba(24,43,72,0.02) 0%, rgba(42,69,112,0.04) 100%);
    border-radius: 13px;
    margin-bottom: 16px;
    border: 1px solid rgba(24,43,72,0.04);
}

.azkar-category .dhikr-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
}

.azkar-category .dhikr-count-badge {
    background: linear-gradient(135deg, var(--navy), var(--navy-light));
    color: var(--gold);
    padding: 6px 12px;
    border-radius: 9px;
    font-weight: 700;
    font-size: 12.5px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.azkar-category .counter-section {
    display: flex;
    align-items: center;
    gap: 12px;
}

.azkar-category .counter-btn {
    width: 38px; height: 38px;
    border-radius: 50%;
    border: none;
    cursor: pointer;
    transition: all 0.2s ease;
    display: flex; align-items: center; justify-content: center;
    font-size: 16px;
}

.azkar-category .counter-btn:active { transform: scale(0.9); }

.azkar-category .counter-btn.minus {
    background: #f8f9fa;
    color: #6c7a89;
    border: 2px solid #e9ecef;
}

.azkar-category .counter-btn.plus {
    background: linear-gradient(135deg, {{ $category->color }}, {{ $category->color }}99);
    color: white;
}

.azkar-category .counter-btn:hover { transform: scale(1.05); }

.azkar-category .counter-display {
    min-width: 60px;
    text-align: center;
    font-size: 20px;
    font-weight: 700;
    color: var(--navy);
}

.azkar-category .progress-bar-custom {
    height: 6px;
    background: rgba(24,43,72,0.08);
    border-radius: 3px;
    overflow: hidden;
    margin-top: 12px;
}

.azkar-category .progress-fill {
    height: 100%;
    background: linear-gradient(90deg, {{ $category->color }}, {{ $category->color }}99);
    border-radius: 3px;
    transition: width 0.2s ease;
}

@media (max-width: 768px) {
    .azkar-category .page-hero { padding: 14px 16px; }
    .azkar-category .hero-icon { width: 48px; height: 48px; font-size: 20px; }
    .azkar-category .hero-title { font-size: 18px; }
    .azkar-category .dhikr-content { font-size: 15px; padding: 14px; }
    .azkar-category .dhikr-footer { flex-direction: column; align-items: stretch; }
    .azkar-category .dhikr-count-badge { justify-content: center; }
    .azkar-category .counter-section { justify-content: center; }
}
</style>
@endpush

@section('content')
<div class="azkar-category">
    <x-page-hero :icon="'fas ' . $category->icon">
        <h1 class="page-banner-title">{{ $category->name }}</h1>
        <p class="page-banner-sub">{{ $category->adhkar->count() }} ذكر</p>
    </x-page-hero>

    <a href="{{ route('student.azkar') }}" class="btn btn-sm mb-4" style="background: #f8f9fa; color: #6c7a89; border-radius: 9px;">
        <i class="fas fa-arrow-right me-1"></i> رجوع للأقسام
    </a>

    <div class="adhkar-list">
        @foreach($category->adhkar as $index => $dhikr)
            <div class="dhikr-card" data-count="{{ $dhikr->count }}">
                <div class="dhikr-content">
                    "{{ $dhikr->content }}"
                </div>
                <div class="dhikr-footer">
                    <div class="dhikr-count-badge">
                        <i class="fas fa-repeat"></i>
                        {{ $dhikr->count }} مرة
                    </div>
                    <div class="counter-section">
                        <button class="counter-btn minus" onclick="decrementCounter(this)">
                            <i class="fas fa-minus"></i>
                        </button>
                        <span class="counter-display">0</span>
                        <button class="counter-btn plus" onclick="incrementCounter(this)">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>
                <div class="progress-bar-custom">
                    <div class="progress-fill" style="width: 0%"></div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection

@section('scripts')
@parent
<script>
function incrementCounter(btn) {
    const card = btn.closest('.dhikr-card');
    const targetCount = parseInt(card.dataset.count);
    const display = card.querySelector('.counter-display');
    const progress = card.querySelector('.progress-fill');

    let current = parseInt(display.textContent) || 0;
    current++;
    display.textContent = current;

    let percentage = (current / targetCount) * 100;
    progress.style.width = Math.min(percentage, 100) + '%';

    if (current >= targetCount) {
        current = 0;
        display.textContent = '0';
        progress.style.width = '0%';
    }

    fetch('/student/azkar/track', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ type: 'dhikr', count: 1 })
    });
}

function decrementCounter(btn) {
    const card = btn.closest('.dhikr-card');
    const display = card.querySelector('.counter-display');
    const progress = card.querySelector('.progress-fill');

    let current = parseInt(display.textContent) || 0;
    if (current > 0) {
        current--;
        display.textContent = current;

        const targetCount = parseInt(card.dataset.count);
        let percentage = (current / targetCount) * 100;
        progress.style.width = Math.min(percentage, 100) + '%';
    }
}
</script>
@endsection