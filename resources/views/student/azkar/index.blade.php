@extends('layouts.app')

@section('title', 'أذكار المسلم')
@section('page-title', 'أذكار المسلم')
@section('role-label', 'طالب')

@section('sidebar-menu')
    @include('student.partials.sidebar')
@endsection

@push('styles')
<style>
.azkar-page {
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

.azkar-page .page-hero {
    background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
    border-radius: 14px;
    padding: 16px 22px;
    margin-bottom: 16px;
    position: relative;
    overflow: hidden;
}

.azkar-page .page-hero::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23d4a030' fill-opacity='0.08'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    pointer-events: none;
}

.azkar-page .page-hero::after {
    content: '';
    position: absolute;
    top: 0; right: 0;
    width: 120px; height: 4px;
    background: linear-gradient(90deg, var(--gold), transparent);
}

.azkar-page .hero-icon {
    width: 48px; height: 48px;
    background: linear-gradient(135deg, var(--gold) 0%, var(--gold-light) 100%);
    border-radius: 13px;
    display: flex; align-items: center; justify-content: center;
    font-size: 20px; color: white;
    box-shadow: 0 8px 24px rgba(212,160,48,0.4);
    position: relative; z-index: 1;
    flex-shrink: 0;
}

.azkar-page .hero-title { color: white; font-size: 18px; font-weight: 700; margin-bottom: 6px; position: relative; z-index: 1; }
.azkar-page .hero-sub { color: rgba(255,255,255,0.8); font-size: 12.5px; margin: 0; position: relative; z-index: 1; }

.azkar-page .stats-row {
    display: flex;
    gap: 12px;
    margin-top: 16px;
    position: relative; z-index: 1;
}

.azkar-page .stat-item {
    background: rgba(255,255,255,0.15);
    backdrop-filter: blur(10px);
    border-radius: 10px;
    padding: 12px 16px;
    text-align: center;
    min-width: 100px;
}

.azkar-page .stat-value { color: white; font-size: 22px; font-weight: 800; }
.azkar-page .stat-label { color: rgba(255,255,255,0.7); font-size: 12px; margin-top: 2px; }

.azkar-page .tasbeeh-card {
    background: white;
    border-radius: 13px;
    box-shadow: 0 8px 40px rgba(0,0,0,0.08);
    border: 1px solid rgba(24,43,72,0.04);
    padding: 16px;
    margin-bottom: 16px;
}

.azkar-page .tasbeeh-header {
    text-align: center;
    margin-bottom: 16px;
}

.azkar-page .tasbeeh-header h4 {
    color: var(--navy);
    font-weight: 700;
    font-size: 18px;
    margin-bottom: 4px;
}

.azkar-page .tasbeeh-header p { color: #6c7a89; font-size: 12.5px; margin: 0; }

.azkar-page .tasbeeh-display {
    text-align: center;
    margin-bottom: 16px;
}

.azkar-page .tasbeeh-count {
    font-size: 72px;
    font-weight: 800;
    color: var(--navy);
    line-height: 1;
    text-shadow: 0 4px 12px rgba(24,43,72,0.1);
}

.azkar-page .tasbeeh-target {
    font-size: 13px;
    color: #6c7a89;
    margin-top: 8px;
}

.azkar-page .tasbeeh-buttons {
    display: flex;
    gap: 10px;
    justify-content: center;
    flex-wrap: wrap;
}

.azkar-page .tasbeeh-btn {
    padding: 10px 20px;
    border-radius: 9px;
    font-size: 13px;
    font-weight: 700;
    border: none;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 10px;
}

.azkar-page .tasbeeh-btn:active { transform: scale(0.95); }

.azkar-page .tasbeeh-btn.subhan {
    background: linear-gradient(135deg, var(--navy), var(--navy-light));
    color: white;
}

.azkar-page .tasbeeh-btn.alhamd {
    background: linear-gradient(135deg, var(--success), #2ecc71);
    color: white;
}

.azkar-page .tasbeeh-btn.akbar {
    background: linear-gradient(135deg, var(--gold), var(--gold-light));
    color: white;
}

.azkar-page .tasbeeh-btn.istighfar {
    background: linear-gradient(135deg, var(--danger), #e74c3c);
    color: white;
}

.azkar-page .tasbeeh-btn.reset {
    background: #f8f9fa;
    color: #6c7a89;
    border: 2px solid #e9ecef;
}

.azkar-page .tasbeeh-btn:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(0,0,0,0.15); }

.azkar-page .categories-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 14px;
}

.azkar-page .category-card {
    background: white;
    border-radius: 13px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.04);
    border: 1px solid rgba(24,43,72,0.04);
    padding: 16px;
    transition: all 0.3s ease;
    text-decoration: none;
    display: block;
}

.azkar-page .category-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 40px rgba(0,0,0,0.1);
}

.azkar-page .category-icon {
    width: 48px; height: 48px;
    border-radius: 13px;
    display: flex; align-items: center; justify-content: center;
    font-size: 20px;
    margin-bottom: 14px;
}

.azkar-page .category-name {
    font-size: 16px;
    font-weight: 700;
    color: var(--navy);
    margin-bottom: 6px;
}

.azkar-page .category-count {
    font-size: 12.5px;
    color: #6c7a89;
}

.azkar-page .category-arrow {
    width: 30px; height: 30px;
    border-radius: 50%;
    background: rgba(24,43,72,0.06);
    display: flex; align-items: center; justify-content: center;
    color: var(--navy);
    margin-top: 12px;
    transition: all 0.2s ease;
}

.azkar-page .category-card:hover .category-arrow {
    background: var(--navy);
    color: white;
}

@media (max-width: 768px) {
    .azkar-page .page-hero { padding: 14px 16px; }
    .azkar-page .hero-icon { width: 48px; height: 48px; font-size: 20px; }
    .azkar-page .hero-title { font-size: 18px; }
    .azkar-page .tasbeeh-count { font-size: 56px; }
    .azkar-page .tasbeeh-btn { padding: 10px 20px; font-size: 13px; }
    .azkar-page .stats-row { flex-wrap: wrap; }
    .azkar-page .stat-item { min-width: 80px; padding: 10px 14px; }
    .azkar-page .stat-value { font-size: 20px; }
}
</style>
@endpush

@section('content')
<div class="azkar-page">
    <div class="page-hero">
        <div class="d-flex align-items-center gap-4">
            <div class="hero-icon">
                <i class="fas fa-quran"></i>
            </div>
            <div>
                <h1 class="hero-title">أذكار المسلم</h1>
                <p class="hero-sub">رطب لسانك بذكر الله</p>
                <div class="stats-row">
                    <div class="stat-item">
                        <div class="stat-value" id="totalCount">{{ $stats['total_dhikr'] }}</div>
                        <div class="stat-label">إجمالي الذكر</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value" id="tasbeehCount">{{ $stats['tasbeeh_count'] }}</div>
                        <div class="stat-label">التسبيح</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value" id="istighfarCount">{{ $stats['istighfar_count'] }}</div>
                        <div class="stat-label">الاستغفار</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="tasbeeh-card">
        <div class="tasbeeh-header">
            <h4>السبحة الإلكترونية</h4>
            <p>اضغط على الذكر الذي تريده</p>
        </div>
        <div class="tasbeeh-display">
            <div class="tasbeeh-count" id="tasbeehDisplay">0</div>
            <div class="tasbeeh-target">من <span id="targetCount">33</span></div>
        </div>
        <div class="tasbeeh-buttons">
            <button class="tasbeeh-btn subhan" onclick="doTasbeeh('سبحان الله', 33)">
                <i class="fas fa-hands-praying"></i> سبحان الله
            </button>
            <button class="tasbeeh-btn alhamd" onclick="doTasbeeh('الحمد لله', 33)">
                <i class="fas fa-hand-holding-heart"></i> الحمد لله
            </button>
            <button class="tasbeeh-btn akbar" onclick="doTasbeeh('الله أكبر', 34)">
                <i class="fas fa-crown"></i> الله أكبر
            </button>
            <button class="tasbeeh-btn istighfar" onclick="doTasbeeh('أستغفر الله', 100)">
                <i class="fas fa-hand-holding-heart"></i> أستغفر الله
            </button>
        </div>
        <div class="text-center mt-4">
            <button class="tasbeeh-btn reset" onclick="resetCounter()">
                <i class="fas fa-redo"></i> إعادة العداد
            </button>
        </div>
    </div>

    <h4 class="mb-4" style="color: var(--navy); font-weight: 700;">
        <i class="fas fa-book-quran me-2" style="color: var(--gold);"></i>
        أذكار المسلم
    </h4>

    <div class="categories-grid">
        @foreach($categories as $category)
            <a href="{{ route('student.azkar.category', $category->id) }}" class="category-card">
                <div class="category-icon" style="background: {{ $category->color }}20; color: {{ $category->color }};">
                    <i class="fas {{ $category->icon }}"></i>
                </div>
                <div class="category-name">{{ $category->name }}</div>
                <div class="category-count">{{ $category->adhkar_count }} ذكر</div>
                <div class="category-arrow">
                    <i class="fas fa-arrow-left"></i>
                </div>
            </a>
        @endforeach
    </div>
</div>
@endsection

@section('scripts')
@parent
<script>
let currentCount = 0;
let targetCount = 33;

function updateDisplay() {
    document.getElementById('tasbeehDisplay').textContent = currentCount;
    document.getElementById('targetCount').textContent = targetCount;
}

function doTasbeeh(text, target) {
    currentCount++;
    targetCount = target;
    updateDisplay();

    if (currentCount >= targetCount) {
        currentCount = 0;
        updateDisplay();
    }

    fetch('/student/azkar/track', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            type: text.includes('أستغفر') ? 'istighfar' : 'tasbeeh',
            count: 1
        })
    })
    .then(r => r.json())
    .then(data => {
        document.getElementById('totalCount').textContent = data.stats.total_dhikr;
        document.getElementById('tasbeehCount').textContent = data.stats.tasbeeh_count;
        document.getElementById('istighfarCount').textContent = data.stats.istighfar_count;
    });
}

function resetCounter() {
    currentCount = 0;
    targetCount = 33;
    updateDisplay();
}
</script>
@endsection