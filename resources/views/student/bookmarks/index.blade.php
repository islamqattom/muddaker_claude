@extends('layouts.app')

@section('title', 'علاماتي')
@section('page-title', 'علاماتي المحفوظة')
@section('role-label', 'طالب')

@section('sidebar-menu')
    @include('student.partials.sidebar')
@endsection

@push('styles')
<style>
.bookmarks-page {
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

.bookmarks-page .page-hero {
    background: linear-gradient(135deg, var(--navy) 0%, var(--navy-light) 100%);
    border-radius: 20px;
    padding: 24px 28px;
    margin-bottom: 24px;
    position: relative;
    overflow: hidden;
}

.bookmarks-page .page-hero::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23d4a030' fill-opacity='0.06'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    pointer-events: none;
}

.bookmarks-page .page-hero::after {
    content: '';
    position: absolute;
    top: 0; right: 0;
    width: 100px; height: 4px;
    background: linear-gradient(90deg, var(--gold), transparent);
}

.bookmarks-page .hero-icon {
    width: 56px; height: 56px;
    background: linear-gradient(135deg, var(--gold) 0%, var(--gold-light) 100%);
    border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    font-size: 24px; color: white;
    box-shadow: 0 6px 18px rgba(212,160,48,0.35);
}

.bookmarks-page .hero-title { color: white; font-size: 22px; font-weight: 700; margin-bottom: 4px; }
.bookmarks-page .hero-sub { color: rgba(255,255,255,0.75); font-size: 13px; margin: 0; }
.bookmarks-page .hero-count { color: white; font-size: 32px; font-weight: 800; }

.bookmarks-page .toolbar-card {
    background: white;
    border-radius: 20px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.04);
    border: 1px solid rgba(24,43,72,0.04);
    padding: 16px 24px;
    margin-bottom: 24px;
    display: flex; align-items: center; justify-content: space-between;
    flex-wrap: wrap;
    gap: 16px;
}

.bookmarks-page .toolbar-title { font-size: 16px; font-weight: 700; color: var(--navy); display: flex; align-items: center; gap: 10px; }
.bookmarks-page .toolbar-title i { color: var(--gold); }

.bookmarks-page .btn-export {
    background: linear-gradient(135deg, var(--navy), var(--navy-light));
    color: white;
    border: none;
    border-radius: 10px;
    padding: 10px 18px;
    font-weight: 600;
    font-size: 13px;
    transition: all 0.2s ease;
}

.bookmarks-page .btn-export:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(24,43,72,0.3); color: white; }

.bookmarks-page .category-filters { display: flex; gap: 10px; flex-wrap: wrap; }

.bookmarks-page .category-btn {
    padding: 8px 16px;
    border-radius: 10px;
    font-size: 12px;
    font-weight: 600;
    transition: all 0.2s ease;
}

.bookmarks-page .category-btn.active {
    background: linear-gradient(135deg, var(--navy), var(--navy-light));
    color: white;
}

.bookmarks-page .category-btn:not(.active) {
    background: rgba(24,43,72,0.06);
    color: var(--navy);
    border: 1px solid rgba(24,43,72,0.1);
}

.bookmarks-page .category-btn:hover:not(.active) { background: rgba(24,43,72,0.1); }

.bookmarks-page .bookmarks-card {
    background: white;
    border-radius: 20px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.04);
    border: 1px solid rgba(24,43,72,0.04);
    overflow: hidden;
}

.bookmarks-page .bookmarks-table { width: 100%; border-collapse: collapse; margin: 0; }
.bookmarks-page .bookmarks-table thead th {
    background: linear-gradient(135deg, rgba(24,43,72,0.04) 0%, rgba(42,69,112,0.06) 100%);
    padding: 14px 16px;
    font-weight: 700;
    font-size: 11px;
    color: var(--navy);
    border: none;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.bookmarks-page .bookmarks-table tbody td {
    padding: 14px 16px;
    font-size: 14px;
    color: #5a6c7d;
    border-bottom: 1px solid rgba(24,43,72,0.04);
    vertical-align: middle;
}
.bookmarks-page .bookmarks-table tbody tr { transition: all 0.2s ease; }
.bookmarks-page .bookmarks-table tbody tr:hover { background: linear-gradient(90deg, rgba(212,160,48,0.04) 0%, rgba(24,43,72,0.02) 100%); }

.bookmarks-page .page-link-btn {
    padding: 6px 14px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 600;
    background: linear-gradient(135deg, var(--info), #2980b9);
    color: white;
    transition: all 0.2s ease;
}

.bookmarks-page .page-link-btn:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(52,152,219,0.3); color: white; }

.bookmarks-page .category-badge {
    padding: 6px 12px;
    border-radius: 8px;
    font-size: 11px;
    font-weight: 600;
    display: inline-flex; align-items: center; gap: 6px;
}

.bookmarks-page .delete-btn {
    width: 34px; height: 34px;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    border: 1px solid rgba(192,57,43,0.2);
    background: white;
    color: var(--danger);
    transition: all 0.2s ease;
}

.bookmarks-page .delete-btn:hover { background: var(--danger); color: white; transform: translateY(-2px); }

.bookmarks-page .empty-state { text-align: center; padding: 80px 20px; }
.bookmarks-page .empty-icon {
    width: 100px; height: 100px;
    background: linear-gradient(135deg, rgba(212,160,48,0.15), rgba(212,160,48,0.2));
    border-radius: 24px;
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 24px;
}
.bookmarks-page .empty-icon i { font-size: 40px; color: var(--gold); }

.bookmarks-page .empty-title { font-size: 20px; font-weight: 700; color: var(--navy); margin-bottom: 8px; }
.bookmarks-page .empty-desc { color: #6c7a89; margin-bottom: 24px; }

.bookmarks-page .btn-start {
    background: linear-gradient(135deg, var(--navy), var(--navy-light));
    color: white;
    border: none;
    border-radius: 12px;
    padding: 12px 24px;
    font-weight: 600;
    transition: all 0.2s ease;
}

.bookmarks-page .btn-start:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(24,43,72,0.3); color: white; }

@media (max-width: 992px) {
    .bookmarks-page .page-hero { padding: 20px 24px; }
    .bookmarks-page .hero-icon { width: 48px; height: 48px; font-size: 20px; }
    .bookmarks-page .hero-title { font-size: 18px; }
    .bookmarks-page .toolbar-card { flex-direction: column; align-items: flex-start; }
}
</style>
@endpush

@section('content')
<div class="bookmarks-page">
    <div class="page-hero">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="hero-icon">
                    <i class="fas fa-bookmark"></i>
                </div>
                <div>
                    <h1 class="hero-title">علاماتي</h1>
                    <p class="hero-sub">علاماتك المحفوظة للآيات القرآنية</p>
                </div>
            </div>
            <div class="hero-count">{{ $bookmarks->count() }}</div>
        </div>
    </div>

    <div class="toolbar-card">
        <div class="toolbar-title">
            <i class="fas fa-bookmark"></i>
            العلامات ({{ $bookmarks->count() }})
        </div>
        <div class="d-flex align-items-center gap-3 flex-wrap">
            <a href="{{ route('student.bookmarks.export', ['format' => 'csv']) }}" class="btn-export">
                <i class="fas fa-download me-1"></i>تصدير CSV
            </a>
        </div>
    </div>

    @if($categories->count() > 0)
    <div class="category-filters mb-4">
        <a href="{{ route('student.bookmarks') }}" class="category-btn {{ !$categoryId ? 'active' : '' }}">
            الكل
        </a>
        @foreach($categories as $cat)
            <a href="{{ route('student.bookmarks', ['category' => $cat->id]) }}"
               class="category-btn {{ $categoryId == $cat->id ? 'active' : '' }}"
               style="{{ $categoryId == $cat->id ? '' : 'border-color: ' . $cat->color . '; color: ' . $cat->color . ';' }}">
                <i class="fas fa-{{ $cat->icon ?? 'bookmark' }} me-1"></i>
                {{ $cat->name }} ({{ $cat->bookmarks_count }})
            </a>
        @endforeach
    </div>
    @endif

    @if($bookmarks->count() > 0)
        <div class="bookmarks-card">
            <div class="table-responsive">
                <table class="bookmarks-table">
                    <thead>
                        <tr>
                            <th>الصفحة</th>
                            <th>السورة</th>
                            <th>العنوان</th>
                            <th>الملاحظة</th>
                            <th>الفئة</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($bookmarks as $bookmark)
                            <tr>
                                <td>
                                    <a href="{{ route('student.quran.page', ['page' => $bookmark->page_number]) }}" class="page-link-btn">
                                        صفحة {{ $bookmark->page_number }}
                                    </a>
                                </td>
                                <td class="fw-semibold" style="color: var(--navy);">{{ $bookmark->surah_name ?? '-' }}</td>
                                <td>{{ $bookmark->title ?? '-' }}</td>
                                <td><small class="text-muted">{{ Str::limit($bookmark->note, 50) }}</small></td>
                                <td>
                                    @if($bookmark->category)
                                        <span class="category-badge" style="background: {{ $bookmark->category->color }}20; color: {{ $bookmark->category->color }};">
                                            <i class="fas fa-{{ $bookmark->category->icon ?? 'bookmark' }}"></i>
                                            {{ $bookmark->category->name }}
                                        </span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <button class="delete-btn" onclick="deleteBookmark({{ $bookmark->id }})">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="bookmarks-card">
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="fas fa-bookmark"></i>
                </div>
                <h5 class="empty-title">لا توجد علامات محفوظة</h5>
                <p class="empty-desc">احفظ آياتك المفضلة أثناء القراءة</p>
                <a href="{{ route('student.quran') }}" class="btn-start">
                    <i class="fas fa-book-quran me-1"></i>اقرأ القرآن الآن
                </a>
            </div>
        </div>
    @endif
</div>
@endsection

@section('scripts')
@parent
<script>
function deleteBookmark(id) {
    if (!confirm('هل أنت متأكد من حذف هذه العلامة؟')) return;

    fetch('/student/bookmarks/' + id, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) {
            location.reload();
        }
    });
}
</script>
@endsection