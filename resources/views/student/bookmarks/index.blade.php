@extends('layouts.app')

@section('title', 'علاماتي')
@section('page-title', 'علاماتي المحفوظة')
@section('role-label', 'طالب')

@section('sidebar-menu')
    @include('student.partials.sidebar')
@endsection

@section('content')
<!-- Header Actions -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0"><i class="fas fa-bookmark me-2"></i>علاماتي ({{ $bookmarks->count() }})</h5>
    <div>
        <a href="{{ route('student.bookmarks.export', ['format' => 'csv']) }}" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-download me-1"></i>تصدير CSV
        </a>
    </div>
</div>

<!-- Category Filter -->
@if($categories->count() > 0)
<div class="card mb-3">
    <div class="card-body py-2">
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('student.bookmarks') }}"
               class="btn btn-sm {{ !$categoryId ? 'btn-primary' : 'btn-outline-secondary' }}">
                الكل
            </a>
            @foreach($categories as $cat)
                <a href="{{ route('student.bookmarks', ['category' => $cat->id]) }}"
                   class="btn btn-sm {{ $categoryId == $cat->id ? 'btn-primary' : 'btn-outline-secondary' }}"
                   style="border-color: {{ $cat->color }}; color: {{ $cat->color }};">
                    <i class="fas fa-{{ $cat->icon ?? 'bookmark' }}"></i>
                    {{ $cat->name }} ({{ $cat->bookmarks_count }})
                </a>
            @endforeach
        </div>
    </div>
</div>
@endif

<!-- Bookmarks List -->
@if($bookmarks->count() > 0)
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>الصفحة</th>
                            <th>السورة</th>
                            <th>العنوان</th>
                            <th>الملاحظة</th>
                            <th>الفئة</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($bookmarks as $bookmark)
                            <tr>
                                <td>
                                    <a href="{{ route('student.quran.page', ['page' => $bookmark->page_number]) }}"
                                       class="btn btn-sm btn-outline-primary">
                                        صفحة {{ $bookmark->page_number }}
                                    </a>
                                </td>
                                <td>{{ $bookmark->surah_name ?? '-' }}</td>
                                <td>{{ $bookmark->title ?? '-' }}</td>
                                <td>
                                    <small class="text-muted">{{ Str::limit($bookmark->note, 50) }}</small>
                                </td>
                                <td>
                                    @if($bookmark->category)
                                        <span class="badge" style="background: {{ $bookmark->category->color }}20; color: {{ $bookmark->category->color }};">
                                            <i class="fas fa-{{ $bookmark->category->icon ?? 'bookmark' }}"></i>
                                            {{ $bookmark->category->name }}
                                        </span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-outline-danger"
                                            onclick="deleteBookmark({{ $bookmark->id }})">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@else
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="fas fa-bookmark fa-4x text-muted opacity-25 mb-3"></i>
            <h5 class="text-muted">لا توجد علامات محفوظة</h5>
            <p class="text-muted mb-3">احفظ آياتك المفضلة أثناء القراءة</p>
            <a href="{{ route('student.quran') }}" class="btn btn-primary">
                <i class="fas fa-book-quran me-1"></i>اقرأ القرآن الآن
            </a>
        </div>
    </div>
@endif
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
