@extends('layouts.app')
@section('title', 'إدارة المستويات')
@section('page-title', 'إدارة المستويات')
@section('role-label', 'مدير')

@section('sidebar-menu')
@include('admin.partials.sidebar')
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item active">المستويات</li>
@endsection

@section('content')

{{-- Page Header --}}
<div class="page-header">
    <div class="header-content">
        <div class="header-icon">
            <i class="fas fa-layer-group"></i>
        </div>
        <div class="header-text">
            <h4>إدارة المستويات</h4>
            <p>إضافة وتعديل مستويات حفظ القرآن الكريم</p>
        </div>
    </div>
    <a href="{{ route('admin.levels.create') }}" class="add-btn">
        <i class="fas fa-plus me-2"></i>إضافة مستوى
    </a>
</div>

{{-- Levels Cards --}}
@if($levels->isEmpty())
    <div class="empty-state-card">
        <div class="empty-icon">
            <i class="fas fa-layer-group"></i>
        </div>
        <h5>لا توجد مستويات مسجلة</h5>
        <p>ابدأ بإضافة مستويات جديدة لإدارة الطلاب</p>
        <a href="{{ route('admin.levels.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>إضافة مستوى
        </a>
    </div>
@else
    <div class="levels-grid">
        @foreach($levels as $level)
        <div class="level-card">
            <div class="level-card-header">
                <div class="level-number">{{ $level->level_number }}</div>
                <div class="level-info">
                    <h5>{{ $level->name }}</h5>
                    <span class="students-count">
                        <i class="fas fa-user-graduate"></i>
                        {{ $level->students_count }} طالب
                    </span>
                </div>
            </div>
            <div class="level-card-body">
                <div class="level-stats">
                    <div class="stat-item">
                        <i class="fas fa-book"></i>
                        <span>{{ $level->new_memorization_pages }} صفحة</span>
                        <small>حفظ جديد</small>
                    </div>
                    <div class="stat-item">
                        <i class="fas fa-redo"></i>
                        <span>{{ $level->review_pages }} صفحة</span>
                        <small>مراجعة</small>
                    </div>
                </div>
                @if($level->description)
                <p class="level-description">{{ $level->description }}</p>
                @endif
            </div>
            <div class="level-card-actions">
                <a href="{{ route('admin.levels.show', $level->id) }}" class="action-btn view" title="عرض">
                    <i class="fas fa-eye"></i>
                </a>
                <a href="{{ route('admin.levels.edit', $level->id) }}" class="action-btn edit" title="تعديل">
                    <i class="fas fa-edit"></i>
                </a>
                <form action="{{ route('admin.levels.destroy', $level->id) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من حذف هذا المستوى؟')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="action-btn delete" title="حذف">
                        <i class="fas fa-trash"></i>
                    </button>
                </form>
            </div>
        </div>
        @endforeach
    </div>

    <div class="pagination-section">
        {{ $levels->links() }}
    </div>
@endif

<style>
/* Shared listing styles live in public/css/page.css */
.empty-state-card h5 { color: #182B48; font-size: 15px; font-weight: 700; margin-bottom: 8px; }
.empty-state-card p { color: #7f8c8d; margin-bottom: 16px; }
.levels-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 16px;
    margin-bottom: 16px;
}
.level-card {
    background: #fff;
    border-radius: 13px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(24, 43, 72, 0.04);
    border: 1px solid #eef2f7;
    transition: all .25s ease;
}
.level-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 22px rgba(24, 43, 72, 0.1);
}
.level-card-header {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 16px;
    background: linear-gradient(135deg, #182B48 0%, #2A4570 100%);
    position: relative;
}
.level-card-header::after {
    content: '';
    position: absolute;
    bottom: 0; left: 0; right: 0;
    height: 3px;
    background: linear-gradient(90deg, #d4a030, #B4802C, #d4a030);
}
.level-number {
    width: 48px; height: 48px;
    background: linear-gradient(135deg, #d4a030, #B4802C);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    font-weight: 800;
    color: #fff;
    flex-shrink: 0;
}
.level-info h5 { color: #fff; font-size: 16px; font-weight: 700; margin: 0 0 6px; }
.students-count {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: rgba(255, 255, 255, 0.15);
    padding: 4px 11px;
    border-radius: 999px;
    color: rgba(255, 255, 255, 0.9);
    font-size: 11.5px;
    font-weight: 500;
}
.students-count i { color: #d4a030; }
.level-card-body { padding: 16px 16px; }
.level-stats { display: flex; gap: 12px; margin-bottom: 12px; }
.stat-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
    flex: 1;
    padding: 11px;
    background: #f8f9fa;
    border-radius: 10px;
}
.stat-item i { font-size: 17px; color: #d4a030; }
.stat-item span { font-size: 15px; font-weight: 700; color: #182B48; }
.stat-item small { font-size: 10.5px; color: #7f8c8d; }
.level-description { color: #7f8c8d; font-size: 12.5px; line-height: 1.6; margin: 0; }
.level-card-actions {
    display: flex;
    gap: 8px;
    padding: 14px 16px;
    border-top: 1px solid #f0f4f8;
    background: #fafbfc;
}
.pagination-section { display: flex; justify-content: center; margin-top: 16px; }
</style>
@endsection