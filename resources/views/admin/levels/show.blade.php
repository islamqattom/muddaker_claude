@extends('layouts.app')
@section('title', 'تفاصيل المستوى')
@section('page-title', 'تفاصيل المستوى')
@section('role-label', 'مدير')

@section('sidebar-menu')
@include('admin.partials.sidebar')
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.levels.index') }}">المستويات</a></li>
    <li class="breadcrumb-item active">{{ $level->name }}</li>
@endsection

@section('content')

<div class="details-layout">
    {{-- Level Info Card --}}
    <div class="info-card">
        <div class="info-card-header">
            <div class="level-icon">
                <i class="fas fa-layer-group"></i>
            </div>
            <div class="level-title">
                <h4>{{ $level->name }}</h4>
                <span class="level-number">المستوى {{ $level->level_number }}</span>
            </div>
        </div>
        <div class="info-card-body">
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-icon">
                        <i class="fas fa-book"></i>
                    </div>
                    <div class="info-content">
                        <label>صفحات الحفظ الجديد</label>
                        <span>{{ $level->new_memorization_pages }} صفحة</span>
                    </div>
                </div>
                <div class="info-item">
                    <div class="info-icon">
                        <i class="fas fa-redo"></i>
                    </div>
                    <div class="info-content">
                        <label>صفحات المراجعة</label>
                        <span>{{ $level->review_pages }} صفحة</span>
                    </div>
                </div>
            </div>
            @if($level->description)
            <div class="description-section">
                <label>الوصف</label>
                <p>{{ $level->description }}</p>
            </div>
            @endif
        </div>
    </div>

    {{-- Students Card --}}
    <div class="students-card">
        <div class="students-card-header">
            <div class="header-title">
                <i class="fas fa-user-graduate"></i>
                <span>الطلاب في هذا المستوى</span>
            </div>
            <div class="students-count-badge">
                <i class="fas fa-users"></i>
                {{ $level->students_count }} طالب
            </div>
        </div>
        <div class="card-body-custom p-0">
            @if($level->students->isEmpty())
                <div class="empty-state">
                    <div class="empty-icon">
                        <i class="fas fa-user-slash"></i>
                    </div>
                    <h5>لا يوجد طلاب</h5>
                    <p>لم يتم إضافة طلاب لهذا المستوى بعد</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="modern-table">
                        <thead>
                            <tr>
                                <th>الطالب</th>
                                <th>رقم الطالب</th>
                                <th>المشرف</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($level->students as $student)
                            <tr>
                                <td>
                                    <div class="user-info-cell">
                                        <div class="user-avatar">
                                            <i class="fas fa-user"></i>
                                        </div>
                                        <span class="user-name">{{ $student->user->name }}</span>
                                    </div>
                                </td>
                                <td><code class="student-number">{{ $student->student_number }}</code></td>
                                <td>
                                    @if($student->supervisor)
                                        <span class="supervisor-text">{{ $student->supervisor->name }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    {{-- Actions Card --}}
    <div class="actions-card">
        <div class="actions-header">
            <i class="fas fa-cogs"></i>
            <span>الإجراءات</span>
        </div>
        <div class="actions-body">
            <a href="{{ route('admin.levels.edit', $level->id) }}" class="action-btn primary">
                <i class="fas fa-edit me-2"></i>تعديل المستوى
            </a>
            <a href="{{ route('admin.levels.index') }}" class="action-btn secondary">
                <i class="fas fa-arrow-right me-2"></i>العودة للقائمة
            </a>
            <form action="{{ route('admin.levels.destroy', $level->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف هذا المستوى؟')">
                @csrf
                @method('DELETE')
                <button type="submit" class="action-btn danger w-100">
                    <i class="fas fa-trash me-2"></i>حذف المستوى
                </button>
            </form>
        </div>
    </div>
</div>

<style>
.details-layout {
    display: grid;
    grid-template-columns: 1fr 300px;
    gap: 25px;
}

@media (max-width: 992px) { .details-layout { grid-template-columns: 1fr; } }

.info-card, .students-card, .actions-card {
    background: #fff;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0,0,0,0.05);
    border: 1px solid #f0f4f8;
}

.info-card-header {
    display: flex;
    align-items: center;
    gap: 20px;
    padding: 30px;
    background: linear-gradient(135deg, #182B48 0%, #2A4570 100%);
    position: relative;
}

.info-card-header::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #d4a030, #B4802C, #d4a030);
}

.level-icon {
    width: 70px;
    height: 70px;
    background: linear-gradient(135deg, #d4a030, #B4802C);
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 28px;
    color: #fff;
    box-shadow: 0 8px 25px rgba(212,160,48,0.4);
}

.level-title h4 { color: #fff; font-size: 22px; font-weight: 700; margin: 0 0 8px 0; }

.level-number {
    display: inline-block;
    background: rgba(255,255,255,0.15);
    padding: 5px 15px;
    border-radius: 20px;
    color: rgba(255,255,255,0.9);
    font-size: 13px;
    font-weight: 500;
}

.info-card-body { padding: 30px; }

.info-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
    margin-bottom: 25px;
}

@media (max-width: 576px) { .info-grid { grid-template-columns: 1fr; } }

.info-item {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 12px;
}

.info-icon {
    width: 50px;
    height: 50px;
    background: linear-gradient(135deg, rgba(24,43,72,0.1), rgba(42,69,112,0.1));
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #d4a030;
    font-size: 20px;
}

.info-content { display: flex; flex-direction: column; }
.info-content label { font-size: 12px; color: #7f8c8d; margin-bottom: 3px; }
.info-content span { font-size: 18px; font-weight: 700; color: #182B48; }

.description-section {
    padding-top: 20px;
    border-top: 1px solid #f0f4f8;
}

.description-section label { font-size: 12px; color: #7f8c8d; margin-bottom: 8px; display: block; }
.description-section p { color: #2c3e50; font-size: 14px; line-height: 1.6; margin: 0; }

.students-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 25px;
    border-bottom: 1px solid #f0f4f8;
    background: linear-gradient(135deg, rgba(24,43,72,0.02), rgba(26,51,86,0.02));
}

.header-title {
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 16px;
    font-weight: 700;
    color: #182B48;
}

.header-title i {
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, #182B48, #1a3356);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #d4a030;
    font-size: 16px;
}

.students-count-badge {
    display: flex;
    align-items: center;
    gap: 8px;
    background: linear-gradient(135deg, rgba(24,43,72,0.1), rgba(42,69,112,0.1));
    padding: 8px 16px;
    border-radius: 20px;
    color: #182B48;
    font-weight: 600;
    font-size: 13px;
}

.students-count-badge i { color: #d4a030; }

.card-body-custom { padding: 25px; }

.empty-state {
    text-align: center;
    padding: 50px 20px;
}

.empty-icon {
    width: 80px;
    height: 80px;
    background: linear-gradient(135deg, rgba(24,43,72,0.1), rgba(42,69,112,0.1));
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
    font-size: 32px;
    color: #d4a030;
}

.empty-state h5 { color: #182B48; font-size: 16px; font-weight: 700; margin-bottom: 5px; }
.empty-state p { color: #7f8c8d; font-size: 13px; }

.table-responsive { overflow-x: auto; }

.modern-table { width: 100%; border-collapse: collapse; }
.modern-table thead { background: #f8f9fa; }
.modern-table th {
    padding: 16px 20px;
    font-size: 11px;
    font-weight: 700;
    color: #182B48;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-bottom: 2px solid #e9ecef;
    white-space: nowrap;
}
.modern-table td {
    padding: 16px 20px;
    font-size: 14px;
    color: #2c3e50;
    border-bottom: 1px solid #f0f4f8;
    vertical-align: middle;
}
.modern-table tbody tr { transition: all 0.2s; }
.modern-table tbody tr:hover { background: #fafbfc; }

.user-info-cell { display: flex; align-items: center; gap: 12px; }

.user-avatar {
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, #182B48, #2A4570);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #d4a030;
    font-size: 16px;
}

.user-name { color: #182B48; font-weight: 600; }

.student-number {
    background: #f0f4f8;
    padding: 5px 12px;
    border-radius: 6px;
    font-size: 13px;
    color: #182B48;
}

.supervisor-text { color: #7f8c8d; font-size: 13px; }

.actions-card { grid-column: 2; grid-row: 1 / 3; height: fit-content; }

@media (max-width: 992px) { .actions-card { grid-column: 1; grid-row: auto; } }

.actions-header {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 20px 25px;
    border-bottom: 1px solid #f0f4f8;
    background: linear-gradient(135deg, rgba(24,43,72,0.02), rgba(26,51,86,0.02));
    color: #182B48;
    font-weight: 700;
    font-size: 16px;
}

.actions-header i {
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, #182B48, #1a3356);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #d4a030;
    font-size: 16px;
}

.actions-body {
    padding: 25px;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.action-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 14px 20px;
    border-radius: 12px;
    font-weight: 600;
    font-size: 14px;
    transition: all 0.3s ease;
    text-decoration: none;
    border: none;
    cursor: pointer;
}

.action-btn.primary {
    background: linear-gradient(135deg, #182B48, #2A4570);
    color: #fff;
}

.action-btn.primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(24,43,72,0.3);
}

.action-btn.secondary {
    background: transparent;
    color: #7f8c8d;
    border: 1.5px solid #dee2e6;
}

.action-btn.secondary:hover {
    background: #f8f9fa;
    color: #182B48;
}

.action-btn.danger {
    background: rgba(192,57,43,0.1);
    color: #c0392b;
}

.action-btn.danger:hover {
    background: #c0392b;
    color: #fff;
    transform: translateY(-2px);
}

.action-btn.w-100 { width: 100%; }
</style>
@endsection