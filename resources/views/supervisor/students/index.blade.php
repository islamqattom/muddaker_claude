@extends('layouts.app')

@section('title', 'قائمة الطلاب')
@section('page-title', 'قائمة الطلاب')
@section('role-label', 'مشرف')

@section('sidebar-menu')
    @include('supervisor.partials.sidebar')
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('supervisor.dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item active">الطلاب</li>
@endsection

@section('content')

<div class="page-header-modern">
    <div class="header-content">
        <div class="header-icon islamic">
            <i class="fas fa-users"></i>
        </div>
        <div class="header-text">
            <h4>إدارة الطلاب</h4>
            <p>عرض وإدارة بيانات الطلاب في حلقات التحفيظ</p>
        </div>
    </div>
    <div class="header-stats">
        <div class="stat-pill primary">
            <i class="fas fa-user-graduate"></i>
            <span>{{ $students->total() }} طالب</span>
        </div>
    </div>
</div>

<div class="filter-card mb-4">
    <form method="GET" class="filter-form">
        <div class="filter-grid">
            <div class="filter-group">
                <label class="filter-label">بحث</label>
                <div class="search-input">
                    <i class="fas fa-search"></i>
                    <input type="text" name="search" class="form-control" placeholder="الاسم أو رقم الهاتف..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="filter-group">
                <label class="filter-label">المستوى</label>
                <select name="level_id" class="form-select" onchange="this.form.submit()">
                    <option value="">جميع المستويات</option>
                    @foreach(\App\Models\Level::all() as $level)
                        <option value="{{ $level->id }}" {{ request('level_id') == $level->id ? 'selected' : '' }}>{{ $level->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search me-2"></i>بحث
                </button>
                <a href="{{ route('supervisor.students.index') }}" class="btn btn-outline-primary">
                    <i class="fas fa-redo me-2"></i>إعادة تعيين
                </a>
            </div>
        </div>
    </form>
</div>

@if($students->isEmpty())
    <div class="empty-state-card">
        <div class="empty-illustration">
            <div class="empty-icon-circle">
                <i class="fas fa-user-graduate"></i>
            </div>
            <div class="islamic-star s1"><i class="fas fa-star"></i></div>
            <div class="islamic-star s2"><i class="fas fa-star"></i></div>
            <div class="islamic-star s3"><i class="fas fa-star"></i></div>
        </div>
        <h5>لا يوجد طلاب</h5>
        <p>لم يتم العثور على طلاب مسجلين حالياً</p>
        <a href="{{ route('supervisor.students.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>إضافة طالب جديد
        </a>
    </div>
@else
    <div class="students-grid">
        @foreach($students as $index => $student)
        <div class="student-card {{ $student->attendance_rate < 50 ? 'needs-attention' : '' }}">
            <div class="student-card-header">
                <div class="student-avatar-lg">
                    <i class="fas fa-user"></i>
                </div>
                <div class="student-info">
                    <h6 class="student-name">{{ $student->user->name }}</h6>
                    <span class="student-number">{{ $student->student_number }}</span>
                </div>
                <div class="card-actions">
                    <a href="{{ route('supervisor.students.show', $student->id) }}" class="action-btn view" title="عرض">
                        <i class="fas fa-eye"></i>
                    </a>
                    <a href="{{ route('supervisor.students.edit', $student->id) }}" class="action-btn edit" title="تعديل">
                        <i class="fas fa-edit"></i>
                    </a>
                    <button type="button" class="action-btn delete" title="حذف" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $student->id }}">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
            <div class="student-card-body">
                <div class="info-row">
                    <span class="info-label"><i class="fas fa-layer-group"></i> المستوى</span>
                    @if($student->level)
                        <span class="level-badge">{{ $student->level->name }}</span>
                    @else
                        <span class="no-data">-</span>
                    @endif
                </div>
                <div class="info-row">
                    <span class="info-label"><i class="fas fa-phone"></i> الهاتف</span>
                    <span class="info-value">{{ $student->phone ?? '-' }}</span>
                </div>
                <div class="progress-section">
                    <div class="progress-item">
                        <div class="progress-header">
                            <span class="progress-label"><i class="fas fa-calendar-check"></i> نسبة الحضور</span>
                            <span class="progress-value {{ $student->attendance_rate < 50 ? 'text-danger' : 'text-success' }}">{{ $student->attendance_rate }}%</span>
                        </div>
                        <div class="progress-bar-container">
                            <div class="progress-bar success" style="width: {{ $student->attendance_rate }}%"></div>
                        </div>
                    </div>
                    <div class="progress-item">
                        <div class="progress-header">
                            <span class="progress-label"><i class="fas fa-award"></i> نسبة النجاح</span>
                            <span class="progress-value text-info">{{ $student->pass_rate }}%</span>
                        </div>
                        <div class="progress-bar-container">
                            <div class="progress-bar info" style="width: {{ $student->pass_rate }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="student-card-footer">
                <a href="{{ route('supervisor.student-parts.index', $student->id) }}" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-book-quran me-1"></i>الأجزاء
                </a>
                <a href="{{ route('supervisor.reports.student', $student->id) }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-chart-bar me-1"></i>التقرير
                </a>
            </div>
        </div>

        <div class="modal fade" id="deleteModal{{ $student->id }}" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <div class="modal-icon-wrapper">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <h5 class="modal-title">حذف الطالب</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>هل أنت متأكد من حذف الطالب <strong>{{ $student->user->name }}</strong>؟</p>
                        <p class="text-danger small mb-0"><i class="fas fa-warning me-1"></i>سيتم حذف جميع بيانات الطالب بشكل نهائي.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                        <form action="{{ route('supervisor.students.destroy', $student->id) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-trash me-2"></i>حذف
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="pagination-wrapper">
        {{ $students->links() }}
    </div>
@endif

<style>
.page-header-modern {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
    margin-bottom: 25px;
}

@media (max-width: 768px) {
    .page-header-modern {
        flex-direction: column;
        align-items: flex-start;
    }
}

.page-header-modern .header-content {
    display: flex;
    align-items: center;
    gap: 20px;
}

.page-header-modern .header-icon {
    width: 70px;
    height: 70px;
    background: linear-gradient(135deg, #182B48, #2A4570);
    border-radius: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 30px;
    color: #d4a030;
    box-shadow: 0 10px 30px rgba(24,43,72,0.3);
    position: relative;
}

.page-header-modern .header-icon::after {
    content: '';
    position: absolute;
    inset: -3px;
    border-radius: 21px;
    background: linear-gradient(135deg, #d4a030, #B4802C);
    z-index: -1;
    opacity: 0.3;
}

.page-header-modern .header-icon.islamic {
    background: linear-gradient(135deg, #182B48, #1a3356);
}

.page-header-modern .header-text h4 {
    color: #182B48;
    font-size: 24px;
    font-weight: 700;
    margin: 0 0 6px 0;
}

.page-header-modern .header-text p {
    color: #7f8c8d;
    font-size: 14px;
    margin: 0;
}

.page-header-modern .stat-pill {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 24px;
    border-radius: 30px;
    font-weight: 600;
    font-size: 14px;
    background: linear-gradient(135deg, #182B48, #2A4570);
    color: #fff;
    box-shadow: 0 4px 15px rgba(24,43,72,0.2);
}

.page-header-modern .stat-pill i {
    color: #d4a030;
}

.quranic-verse-header {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 20px;
    padding: 20px;
    background: linear-gradient(135deg, rgba(24,43,72,0.03), rgba(42,69,112,0.05));
    border-radius: 12px;
    margin-bottom: 30px;
}

.quranic-verse-header .verse-decoration {
    flex: 1;
    height: 1px;
    background: linear-gradient(90deg, transparent, #d4a030, transparent);
    max-width: 100px;
}

.quranic-verse-header .quranic-verse {
    font-family: 'Amiri', serif;
    font-size: 18px;
    color: #182B48;
    margin: 0;
    text-shadow: 0 1px 2px rgba(212,160,48,0.2);
}

.filter-card {
    background: #fff;
    border-radius: 20px;
    padding: 25px;
    box-shadow: 0 4px 25px rgba(0,0,0,0.04);
    border: 1px solid rgba(212,160,48,0.1);
    margin-bottom: 25px;
}

.filter-form { margin: 0; }

.filter-grid {
    display: grid;
    grid-template-columns: 1.5fr 1fr 1.5fr;
    gap: 20px;
    align-items: end;
}

@media (max-width: 992px) {
    .filter-grid {
        grid-template-columns: 1fr 1fr;
    }
}

@media (max-width: 576px) {
    .filter-grid {
        grid-template-columns: 1fr;
    }
}

.filter-label {
    display: block;
    color: #182B48;
    font-size: 13px;
    font-weight: 600;
    margin-bottom: 8px;
}

.search-input { position: relative; }

.search-input i {
    position: absolute;
    right: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: #B4802C;
}

.search-input .form-control { padding-right: 40px; }

.filter-actions { display: flex; gap: 10px; }

.students-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
    gap: 20px;
    margin-bottom: 25px;
}

@media (max-width: 576px) {
    .students-grid {
        grid-template-columns: 1fr;
    }
}

.student-card {
    background: #fff;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0,0,0,0.06);
    border: 1px solid #f0f4f8;
    transition: all 0.3s ease;
}

.student-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 40px rgba(24,43,72,0.12);
}

.student-card.needs-attention {
    border-color: rgba(231,76,60,0.3);
}

.student-card.needs-attention .student-card-header {
    background: linear-gradient(135deg, rgba(231,76,60,0.08), rgba(192,57,43,0.05));
}

.student-card-header {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 20px;
    background: linear-gradient(135deg, rgba(24,43,72,0.04), rgba(42,69,112,0.04));
    border-bottom: 1px solid #f0f4f8;
}

.student-avatar-lg {
    width: 55px;
    height: 55px;
    background: linear-gradient(135deg, #182B48, #2A4570);
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #d4a030;
    font-size: 22px;
    flex-shrink: 0;
    box-shadow: 0 4px 12px rgba(24,43,72,0.2);
}

.student-info {
    flex: 1;
    min-width: 0;
}

.student-info .student-name {
    color: #182B48;
    font-weight: 700;
    font-size: 16px;
    margin: 0 0 4px 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.student-info .student-number {
    font-family: monospace;
    font-size: 12px;
    color: #7f8c8d;
    background: #f0f4f8;
    padding: 3px 8px;
    border-radius: 6px;
}

.card-actions {
    display: flex;
    gap: 6px;
}

.action-btn {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: none;
    transition: all 0.3s ease;
    cursor: pointer;
    text-decoration: none;
}

.action-btn.view { background: rgba(52,152,219,0.1); color: #3498db; }
.action-btn.view:hover { background: #3498db; color: #fff; transform: scale(1.08); }

.action-btn.edit { background: rgba(243,156,18,0.1); color: #f39c12; }
.action-btn.edit:hover { background: #f39c12; color: #fff; transform: scale(1.08); }

.action-btn.delete { background: rgba(192,57,43,0.1); color: #c0392b; }
.action-btn.delete:hover { background: #c0392b; color: #fff; transform: scale(1.08); }

.student-card-body {
    padding: 20px;
}

.info-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 0;
    border-bottom: 1px solid #f8f9fa;
}

.info-row:last-of-type {
    border-bottom: none;
}

.info-label {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #7f8c8d;
    font-size: 13px;
}

.info-label i {
    color: #d4a030;
    font-size: 12px;
}

.info-value {
    color: #182B48;
    font-weight: 600;
    font-size: 14px;
}

.level-badge {
    background: linear-gradient(135deg, rgba(24,43,72,0.1), rgba(42,69,112,0.1));
    color: #182B48;
    padding: 5px 12px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 600;
}

.no-data {
    color: #bdc3c7;
    font-size: 13px;
}

.progress-section {
    margin-top: 15px;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.progress-item {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.progress-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.progress-label {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    color: #7f8c8d;
}

.progress-label i {
    color: #d4a030;
    font-size: 11px;
}

.progress-value {
    font-size: 13px;
    font-weight: 700;
}

.progress-value.text-success { color: #27ae60; }
.progress-value.text-danger { color: #c0392b; }
.progress-value.text-info { color: #3498db; }

.progress-bar-container {
    height: 6px;
    background: #f0f4f8;
    border-radius: 3px;
    overflow: hidden;
}

.progress-bar {
    height: 100%;
    border-radius: 3px;
    transition: width 0.5s ease;
}

.progress-bar.success { background: linear-gradient(90deg, #27ae60, #2ecc71); }
.progress-bar.info { background: linear-gradient(90deg, #3498db, #2980b9); }

.student-card-footer {
    display: flex;
    gap: 10px;
    padding: 15px 20px;
    background: #fafbfc;
    border-top: 1px solid #f0f4f8;
}

.student-card-footer .btn {
    flex: 1;
    font-size: 12px;
    padding: 8px 12px;
    border-radius: 8px;
}

.empty-state-card {
    text-align: center;
    padding: 60px 30px;
    background: #fff;
    border-radius: 20px;
    box-shadow: 0 4px 25px rgba(0,0,0,0.04);
    border: 1px solid #f0f4f8;
}

.empty-illustration {
    position: relative;
    width: 120px;
    height: 120px;
    margin: 0 auto 25px;
}

.empty-icon-circle {
    width: 100px;
    height: 100px;
    background: linear-gradient(135deg, rgba(24,43,72,0.1), rgba(42,69,112,0.1));
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
    font-size: 40px;
    color: #d4a030;
}

.islamic-star {
    position: absolute;
    color: #d4a030;
    opacity: 0.3;
}

.islamic-star.s1 {
    top: 5px;
    right: 5px;
    font-size: 14px;
    animation: starPulse 2s infinite;
}

.islamic-star.s2 {
    bottom: 15px;
    left: 0;
    font-size: 10px;
    animation: starPulse 2s infinite 0.5s;
}

.islamic-star.s3 {
    top: 20px;
    left: 10px;
    font-size: 8px;
    animation: starPulse 2s infinite 1s;
}

@keyframes starPulse {
    0%, 100% { opacity: 0.3; transform: scale(1); }
    50% { opacity: 0.6; transform: scale(1.1); }
}

.empty-state-card h5 {
    color: #182B48;
    font-size: 20px;
    font-weight: 700;
    margin-bottom: 10px;
}

.empty-state-card p {
    color: #7f8c8d;
    margin-bottom: 25px;
    font-size: 14px;
}

.pagination-wrapper {
    display: flex;
    justify-content: center;
    margin-top: 30px;
}

.pagination-wrapper .pagination {
    margin: 0;
    gap: 6px;
}

.pagination-wrapper .page-link {
    padding: 10px 16px;
    border-radius: 10px;
    font-size: 14px;
    border: 1px solid #e9ecef;
    color: #182B48;
}

.pagination-wrapper .page-item.active .page-link {
    background: linear-gradient(135deg, #182B48, #2A4570);
    border-color: #182B48;
    color: #fff;
}

.pagination-wrapper .page-item.disabled .page-link {
    color: #bdc3c7;
}

.modal-header {
    background: linear-gradient(135deg, rgba(24,43,72,0.05), rgba(42,69,112,0.05));
    border-bottom: 2px solid #f0f4f8;
    padding: 20px 25px;
    display: flex;
    align-items: center;
    gap: 15px;
}

.modal-icon-wrapper {
    width: 45px;
    height: 45px;
    background: linear-gradient(135deg, rgba(192,57,43,0.1), rgba(231,76,60,0.1));
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #c0392b;
    font-size: 20px;
}

.modal-header .modal-title {
    color: #182B48;
    font-weight: 700;
    font-size: 18px;
    margin: 0;
}

.modal-body {
    padding: 25px;
}

.modal-body p {
    color: #2c3e50;
    font-size: 15px;
    margin-bottom: 15px;
}

.modal-footer {
    padding: 15px 25px 20px;
    background: #fafbfc;
    border-top: 1px solid #f0f4f8;
    display: flex;
    gap: 10px;
    justify-content: flex-start;
}
</style>

@endsection