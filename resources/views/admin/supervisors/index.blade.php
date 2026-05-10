@extends('layouts.app')
@section('title', 'إدارة المشرفين')
@section('page-title', 'إدارة المشرفين')
@section('role-label', 'مدير')

@section('sidebar-menu')
@include('admin.partials.sidebar')
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item active">المشرفين</li>
@endsection

@section('content')

{{-- Page Header --}}
<div class="page-header mb-4">
    <div class="header-content">
        <div class="header-icon supervisors">
            <i class="fas fa-user-tie"></i>
        </div>
        <div class="header-text">
            <h4>إدارة المشرفين</h4>
            <p>عرض وإدارة جميع مشرفي مركز التسميع</p>
        </div>
    </div>
    <div class="header-stats">
        <a href="{{ route('admin.supervisors.create') }}" class="btn btn-success header-btn">
            <i class="fas fa-plus me-2"></i>إضافة مشرف
        </a>
    </div>
</div>

{{-- Supervisors Cards Grid --}}
@if($supervisors->isEmpty())
    <div class="table-card">
        <div class="empty-state">
            <div class="empty-icon">
                <i class="fas fa-user-tie"></i>
            </div>
            <h5>لا يوجد مشرفين</h5>
            <p>لم يتم العثور على مشرفين مسجلين</p>
            <a href="{{ route('admin.supervisors.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>إضافة مشرف جديد
            </a>
        </div>
    </div>
@else
    <div class="supervisors-grid">
        @foreach($supervisors as $supervisor)
        <div class="supervisor-card">
            <div class="supervisor-header-card">
                <div class="supervisor-avatar-card">
                    <i class="fas fa-user-tie"></i>
                </div>
                <div class="supervisor-info-card">
                    <h5>{{ $supervisor->name }}</h5>
                    <span class="role-tag">مشرف</span>
                </div>
                <div class="student-count-badge">
                    <i class="fas fa-users"></i>
                    {{ $supervisor->supervisedStudents->count() }}
                </div>
            </div>

            <div class="supervisor-details">
                <div class="detail-row">
                    <i class="fas fa-id-badge"></i>
                    <span>رقم الموظف:</span>
                    <strong>{{ $supervisor->supervisor->employee_number ?? '-' }}</strong>
                </div>
                <div class="detail-row">
                    <i class="fas fa-phone"></i>
                    <span>الهاتف:</span>
                    <strong>{{ $supervisor->phone ?? '-' }}</strong>
                </div>
                <div class="detail-row">
                    <i class="fas fa-envelope"></i>
                    <span>البريد:</span>
                    <strong>{{ $supervisor->email ?? '-' }}</strong>
                </div>
                <div class="detail-row">
                    <i class="fas fa-book"></i>
                    <span>التخصص:</span>
                    <strong>{{ $supervisor->supervisor->specialization ?? '-' }}</strong>
                </div>
            </div>

            <div class="supervisor-actions">
                <a href="{{ route('admin.supervisors.show', $supervisor->id) }}" class="action-btn-card view">
                    <i class="fas fa-eye"></i>
                    <span>عرض</span>
                </a>
                <a href="{{ route('admin.supervisors.edit', $supervisor->id) }}" class="action-btn-card edit">
                    <i class="fas fa-edit"></i>
                    <span>تعديل</span>
                </a>
                <form action="{{ route('admin.supervisors.destroy', $supervisor->id) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من حذف هذا المشرف؟')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="action-btn-card delete">
                        <i class="fas fa-trash"></i>
                        <span>حذف</span>
                    </button>
                </form>
            </div>
        </div>
        @endforeach
    </div>

    <div class="pagination-container">
        {{ $supervisors->links() }}
    </div>
@endif

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

.header-icon.supervisors {
    background: linear-gradient(135deg, #8e44ad, #9b59b6);
    box-shadow: 0 8px 25px rgba(142,68,173,0.25);
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

.header-btn {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 12px 24px;
    border-radius: 12px;
    font-weight: 600;
}

/* Supervisors Grid */
.supervisors-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
    gap: 25px;
    margin-bottom: 25px;
}

@media (max-width: 576px) {
    .supervisors-grid { grid-template-columns: 1fr; }
}

/* Supervisor Card */
.supervisor-card {
    background: #fff;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0,0,0,0.05);
    border: 1px solid #f0f4f8;
    transition: all 0.3s ease;
}

.supervisor-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 40px rgba(0,0,0,0.1);
    border-color: rgba(212,160,48,0.3);
}

.supervisor-header-card {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 25px;
    background: linear-gradient(135deg, rgba(24,43,72,0.03), rgba(42,69,112,0.03));
    border-bottom: 1px solid #f0f4f8;
}

.supervisor-avatar-card {
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, #8e44ad, #9b59b6);
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #d4a030;
    font-size: 24px;
    box-shadow: 0 4px 15px rgba(142,68,173,0.3);
}

.supervisor-info-card {
    flex: 1;
}

.supervisor-info-card h5 {
    color: #182B48;
    font-size: 17px;
    font-weight: 700;
    margin: 0 0 6px 0;
}

.role-tag {
    background: linear-gradient(135deg, rgba(142,68,173,0.1), rgba(155,89,182,0.1));
    color: #8e44ad;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
}

.student-count-badge {
    background: linear-gradient(135deg, #182B48, #2A4570);
    color: #fff;
    padding: 10px 15px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    font-weight: 700;
}

.student-count-badge i {
    color: #d4a030;
}

/* Supervisor Details */
.supervisor-details {
    padding: 20px 25px;
}

.detail-row {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 0;
    border-bottom: 1px solid #f0f4f8;
}

.detail-row:last-child {
    border-bottom: none;
}

.detail-row i {
    width: 20px;
    color: #d4a030;
    font-size: 12px;
}

.detail-row span {
    color: #7f8c8d;
    font-size: 13px;
    width: 80px;
}

.detail-row strong {
    color: #182B48;
    font-size: 13px;
    flex: 1;
}

/* Supervisor Actions */
.supervisor-actions {
    display: flex;
    gap: 10px;
    padding: 20px 25px;
    background: #f8f9fa;
}

.action-btn-card {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 12px;
    border-radius: 10px;
    font-size: 13px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
}

.action-btn-card.view {
    background: rgba(52,152,219,0.1);
    color: #3498db;
}

.action-btn-card.view:hover {
    background: #3498db;
    color: #fff;
}

.action-btn-card.edit {
    background: rgba(243,156,18,0.1);
    color: #f39c12;
}

.action-btn-card.edit:hover {
    background: #f39c12;
    color: #fff;
}

.action-btn-card.delete {
    background: rgba(192,57,43,0.1);
    color: #c0392b;
}

.action-btn-card.delete:hover {
    background: #c0392b;
    color: #fff;
}

/* Pagination */
.pagination-container {
    display: flex;
    justify-content: center;
}

.pagination-container .pagination {
    margin: 0;
    gap: 5px;
}

.pagination-container .page-link {
    padding: 10px 16px;
    border-radius: 10px;
    font-size: 14px;
    border: 1px solid #e9ecef;
    color: #182B48;
}

.pagination-container .page-item.active .page-link {
    background: linear-gradient(135deg, #182B48, #2A4570);
    border-color: #182B48;
    color: #fff;
}

.pagination-container .page-item.disabled .page-link {
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
    background: linear-gradient(135deg, rgba(142,68,173,0.1), rgba(155,89,182,0.1));
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
    font-size: 36px;
    color: #8e44ad;
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

/* Table Card (backup) */
.table-card {
    background: #fff;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0,0,0,0.05);
    border: 1px solid #f0f4f8;
}
</style>

@endsection