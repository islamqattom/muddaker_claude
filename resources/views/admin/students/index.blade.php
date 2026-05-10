@extends('layouts.app')
@section('title', 'إدارة الطلاب')
@section('page-title', 'إدارة الطلاب')
@section('role-label', 'مدير')

@section('sidebar-menu')
@include('admin.partials.sidebar')
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item active">الطلاب</li>
@endsection

@section('content')

{{-- Page Header --}}
<div class="page-header mb-4">
    <div class="header-content">
        <div class="header-icon">
            <i class="fas fa-user-graduate"></i>
        </div>
        <div class="header-text">
            <h4>إدارة الطلاب</h4>
            <p>عرض وإدارة جميع طلاب مركز التسميع</p>
        </div>
    </div>
    <div class="header-stats">
        <div class="stat-pill primary">
            <i class="fas fa-users"></i>
            <span>{{ $students->total() }} طالب</span>
        </div>
    </div>
</div>

{{-- Search & Filter Card --}}
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
                <select name="level_id" class="form-select">
                    <option value="">جميع المستويات</option>
                    @foreach($levels as $level)
                        <option value="{{ $level->id }}" {{ request('level_id') == $level->id ? 'selected' : '' }}>{{ $level->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-group">
                <label class="filter-label">المشرف</label>
                <select name="supervisor_id" class="form-select">
                    <option value="">جميع المشرفين</option>
                    @foreach($supervisors as $supervisor)
                        <option value="{{ $supervisor->id }}" {{ request('supervisor_id') == $supervisor->id ? 'selected' : '' }}>{{ $supervisor->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search me-2"></i>بحث
                </button>
                <a href="{{ route('admin.students.create') }}" class="btn btn-success">
                    <i class="fas fa-plus me-2"></i>إضافة طالب
                </a>
            </div>
        </div>
    </form>
</div>

{{-- Students Table Card --}}
<div class="table-card">
    <div class="table-header">
        <div class="table-title">
            <i class="fas fa-list"></i>
            <span>قائمة الطلاب</span>
        </div>
        <div class="table-actions">
            <span class="record-count">
                <i class="fas fa-folder-open"></i>
                {{ $students->total() }} سجل
            </span>
        </div>
    </div>

    @if($students->isEmpty())
        <div class="empty-state">
            <div class="empty-icon">
                <i class="fas fa-user-graduate"></i>
            </div>
            <h5>لا يوجد طلاب</h5>
            <p>لم يتم العثور على طلاب مسجلين</p>
            <a href="{{ route('admin.students.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>إضافة طالب جديد
            </a>
        </div>
    @else
        <div class="table-responsive">
            <table class="modern-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>اسم الطالب</th>
                        <th>رقم الطالب</th>
                        <th>المستوى</th>
                        <th>المشرف</th>
                        <th>الهاتف</th>
                        <th>تاريخ التسجيل</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($students as $index => $student)
                    <tr>
                        <td class="row-num">{{ $students->firstItem() + $index }}</td>
                        <td>
                            <div class="user-info-cell">
                                <div class="user-avatar-table">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div class="user-details">
                                    <span class="user-name">{{ $student->user->name }}</span>
                                    <span class="user-username">{{ $student->username ?? '-' }}</span>
                                </div>
                            </div>
                        </td>
                        <td><span class="student-number">{{ $student->student_number }}</span></td>
                        <td>
                            @if($student->level)
                                <span class="level-badge">{{ $student->level->name }}</span>
                            @else
                                <span class="no-data">-</span>
                            @endif
                        </td>
                        <td class="supervisor-cell">{{ $student->supervisor->name ?? '-' }}</td>
                        <td class="phone-cell">{{ $student->phone ?? $student->user->phone ?? '-' }}</td>
                        <td class="date-cell">{{ $student->enrollment_date?->format('d/m/Y') ?? '-' }}</td>
                        <td>
                            <div class="action-buttons">
                                <a href="{{ route('admin.students.show', $student->id) }}" class="action-btn view" title="عرض">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.students.edit', $student->id) }}" class="action-btn edit" title="تعديل">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('admin.students.destroy', $student->id) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من حذف هذا الطالب؟')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="action-btn delete" title="حذف">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="table-footer">
            <div class="pagination-wrapper">
                {{ $students->links() }}
            </div>
        </div>
    @endif
</div>

<style>
/* Page Header */
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
}

@media (max-width: 768px) {
    .page-header { flex-direction: column; align-items: flex-start; }
}

.header-content {
    display: flex;
    align-items: center;
    gap: 20px;
}

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

.header-text h4 {
    color: #182B48;
    font-size: 22px;
    font-weight: 700;
    margin: 0 0 4px 0;
}

.header-text p {
    color: #7f8c8d;
    font-size: 14px;
    margin: 0;
}

.stat-pill {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 20px;
    border-radius: 30px;
    font-weight: 600;
    font-size: 14px;
}

.stat-pill.primary {
    background: linear-gradient(135deg, #182B48, #2A4570);
    color: #fff;
}

.stat-pill i {
    color: #d4a030;
}

/* Filter Card */
.filter-card {
    background: #fff;
    border-radius: 16px;
    padding: 25px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.05);
    border: 1px solid #f0f4f8;
}

.filter-form { margin: 0; }

.filter-grid {
    display: grid;
    grid-template-columns: 1.5fr 1fr 1fr 1.5fr;
    gap: 20px;
    align-items: end;
}

@media (max-width: 992px) {
    .filter-grid { grid-template-columns: 1fr 1fr; }
}

@media (max-width: 576px) {
    .filter-grid { grid-template-columns: 1fr; }
}

.filter-group { }

.filter-label {
    display: block;
    color: #182B48;
    font-size: 13px;
    font-weight: 600;
    margin-bottom: 8px;
}

.search-input {
    position: relative;
}

.search-input i {
    position: absolute;
    right: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: #B4802C;
}

.search-input .form-control {
    padding-right: 40px;
}

.filter-actions {
    display: flex;
    gap: 10px;
}

/* Table Card */
.table-card {
    background: #fff;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0,0,0,0.05);
    border: 1px solid #f0f4f8;
}

.table-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 25px;
    border-bottom: 1px solid #f0f4f8;
    background: linear-gradient(135deg, rgba(24,43,72,0.02), rgba(26,51,86,0.02));
}

.table-title {
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 16px;
    font-weight: 700;
    color: #182B48;
}

.table-title i {
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

.record-count {
    color: #7f8c8d;
    font-size: 13px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.record-count i {
    color: #d4a030;
}

/* Modern Table */
.modern-table {
    width: 100%;
    border-collapse: collapse;
}

.modern-table thead {
    background: #f8f9fa;
}

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

.modern-table tbody tr {
    transition: all 0.2s;
}

.modern-table tbody tr:hover {
    background: #fafbfc;
}

.row-num {
    color: #bdc3c7;
    font-weight: 600;
    font-size: 13px;
}

.user-info-cell {
    display: flex;
    align-items: center;
    gap: 14px;
}

.user-avatar-table {
    width: 42px;
    height: 42px;
    background: linear-gradient(135deg, #182B48, #2A4570);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-weight: 700;
    font-size: 16px;
}

.user-details {
    display: flex;
    flex-direction: column;
}

.user-name {
    color: #182B48;
    font-weight: 600;
    font-size: 14px;
}

.user-username {
    color: #7f8c8d;
    font-size: 12px;
}

.student-number {
    font-family: monospace;
    background: #f0f4f8;
    padding: 4px 10px;
    border-radius: 6px;
    font-size: 13px;
    color: #182B48;
}

.level-badge {
    background: linear-gradient(135deg, rgba(24,43,72,0.1), rgba(42,69,112,0.1));
    color: #182B48;
    padding: 6px 12px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 600;
}

.no-data {
    color: #bdc3c7;
}

.supervisor-cell {
    color: #7f8c8d;
    font-size: 13px;
}

.phone-cell {
    font-size: 13px;
}

.date-cell {
    color: #7f8c8d;
    font-size: 13px;
}

/* Action Buttons */
.action-buttons {
    display: flex;
    gap: 8px;
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

/* Table Footer */
.table-footer {
    padding: 20px 25px;
    border-top: 1px solid #f0f4f8;
    display: flex;
    justify-content: center;
}

.pagination-wrapper .pagination {
    margin: 0;
    gap: 5px;
}

.pagination-wrapper .page-link {
    padding: 8px 14px;
    border-radius: 8px;
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

/* Empty State */
.empty-state {
    text-align: center;
    padding: 60px 20px;
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

.empty-state h5 {
    color: #182B48;
    font-size: 18px;
    font-weight: 700;
    margin-bottom: 8px;
}

.empty-state p {
    color: #7f8c8d;
    margin-bottom: 25px;
}
</style>

@endsection