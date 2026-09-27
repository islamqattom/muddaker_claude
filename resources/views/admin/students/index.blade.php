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
/* Shared listing styles live in public/css/page.css */
</style>

@endsection