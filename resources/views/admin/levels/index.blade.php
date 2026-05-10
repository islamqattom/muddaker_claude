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
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
    margin-bottom: 25px;
}

.header-content { display: flex; align-items: center; gap: 20px; }

.header-icon {
    width: 65px;
    height: 65px;
    background: linear-gradient(135deg, #182B48, #2A4570);
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 28px;
    color: #d4a030;
    box-shadow: 0 8px 25px rgba(24,43,72,0.25);
}

.header-text h4 { color: #182B48; font-size: 22px; font-weight: 700; margin: 0 0 4px 0; }
.header-text p { color: #7f8c8d; font-size: 14px; margin: 0; }

.add-btn {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 12px 25px;
    background: linear-gradient(135deg, #182B48, #2A4570);
    color: #fff;
    border-radius: 12px;
    text-decoration: none;
    font-weight: 600;
    font-size: 14px;
    transition: all 0.3s ease;
}

.add-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(24,43,72,0.3);
    color: #fff;
}

.empty-state-card {
    text-align: center;
    padding: 60px 20px;
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.05);
}

.empty-icon {
    width: 90px;
    height: 90px;
    background: linear-gradient(135deg, rgba(24,43,72,0.1), rgba(42,69,112,0.1));
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
    font-size: 36px;
    color: #d4a030;
}

.empty-state-card h5 { color: #182B48; font-size: 18px; font-weight: 700; margin-bottom: 8px; }
.empty-state-card p { color: #7f8c8d; margin-bottom: 20px; }

.levels-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 20px;
    margin-bottom: 25px;
}

.level-card {
    background: #fff;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0,0,0,0.05);
    border: 1px solid #f0f4f8;
    transition: all 0.3s ease;
}

.level-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}

.level-card-header {
    display: flex;
    align-items: center;
    gap: 20px;
    padding: 25px;
    background: linear-gradient(135deg, #182B48 0%, #2A4570 100%);
    position: relative;
}

.level-card-header::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #d4a030, #B4802C, #d4a030);
}

.level-number {
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, #d4a030, #B4802C);
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    font-weight: 800;
    color: #fff;
    box-shadow: 0 4px 15px rgba(212,160,48,0.4);
}

.level-info h5 { color: #fff; font-size: 18px; font-weight: 700; margin: 0 0 8px 0; }

.students-count {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: rgba(255,255,255,0.15);
    padding: 5px 12px;
    border-radius: 20px;
    color: rgba(255,255,255,0.9);
    font-size: 12px;
    font-weight: 500;
}

.students-count i { color: #d4a030; }

.level-card-body { padding: 25px; }

.level-stats {
    display: flex;
    gap: 20px;
    margin-bottom: 15px;
}

.stat-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 5px;
    flex: 1;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 12px;
}

.stat-item i { font-size: 20px; color: #d4a030; }
.stat-item span { font-size: 16px; font-weight: 700; color: #182B48; }
.stat-item small { font-size: 11px; color: #7f8c8d; }

.level-description {
    color: #7f8c8d;
    font-size: 13px;
    line-height: 1.6;
    margin: 0;
}

.level-card-actions {
    display: flex;
    gap: 10px;
    padding: 20px 25px;
    border-top: 1px solid #f0f4f8;
    background: #fafbfc;
}

.action-btn {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
    text-decoration: none;
}

.action-btn.view {
    background: rgba(52,152,219,0.1);
    color: #3498db;
}

.action-btn.view:hover {
    background: #3498db;
    color: #fff;
    transform: scale(1.05);
}

.action-btn.edit {
    background: rgba(243,156,18,0.1);
    color: #f39c12;
}

.action-btn.edit:hover {
    background: #f39c12;
    color: #fff;
    transform: scale(1.05);
}

.action-btn.delete {
    background: rgba(192,57,43,0.1);
    color: #c0392b;
}

.action-btn.delete:hover {
    background: #c0392b;
    color: #fff;
    transform: scale(1.05);
}

.pagination-section {
    display: flex;
    justify-content: center;
    margin-top: 25px;
}

.pagination-section .pagination { margin: 0; gap: 5px; }
.pagination-section .page-link { padding: 8px 14px; border-radius: 8px; font-size: 14px; border: 1px solid #e9ecef; color: #182B48; }
.pagination-section .page-item.active .page-link { background: linear-gradient(135deg, #182B48, #2A4570); border-color: #182B48; color: #fff; }
.pagination-section .page-item.disabled .page-link { color: #bdc3c7; }
</style>
@endsection