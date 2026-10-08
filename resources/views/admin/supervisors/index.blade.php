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
<x-page-hero icon="fas fa-user-tie">
    <h1 class="page-banner-title">إدارة المشرفين</h1>
    <p class="page-banner-sub">عرض وإدارة جميع مشرفي مركز التسميع</p>
    <x-slot:actions>
        <a href="{{ route('admin.supervisors.create') }}" class="btn-gold">
            <i class="fas fa-plus me-2"></i>إضافة مشرف
        </a>
    </x-slot:actions>
</x-page-hero>

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
        @php $isActive = (bool) $supervisor->is_active; @endphp
        <div class="supervisor-card {{ $isActive ? '' : 'card-inactive' }}">
            <div class="supervisor-header-card">
                <div class="supervisor-avatar-card">
                    <i class="fas fa-user-tie"></i>
                </div>
                <div class="supervisor-info-card">
                    <h5>{{ $supervisor->name }}</h5>
                    <div class="d-flex align-items-center gap-2">
                        <span class="role-tag">مشرف</span>
                        @if($isActive)
                            <span class="badge-status active"><i class="fas fa-circle-check"></i>مفعّل</span>
                        @else
                            <span class="badge-status inactive"><i class="fas fa-circle-pause"></i>معطّل</span>
                        @endif
                    </div>
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
                <form action="{{ route('admin.supervisors.toggleActive', $supervisor->id) }}" method="POST" class="d-inline"
                      style="flex:1;" @if($isActive) onsubmit="return confirm('تعطيل حساب هذا المشرف؟ سيتم منعه من الدخول واستبعاده من التقارير والإحصائيات.')" @endif>
                    @csrf
                    <button type="submit" class="action-btn-card {{ $isActive ? 'deactivate' : 'activate' }}" style="width:100%;">
                        <i class="fas fa-{{ $isActive ? 'ban' : 'check' }}"></i>
                        <span>{{ $isActive ? 'تعطيل' : 'تفعيل' }}</span>
                    </button>
                </form>
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
/* Shared listing styles live in public/css/page.css */
.header-icon.supervisors {
    background: linear-gradient(135deg, #8e44ad, #9b59b6);
    box-shadow: 0 6px 18px rgba(142, 68, 173, 0.25);
}
.header-btn {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 16px;
    border-radius: 10px;
    font-weight: 600;
}
.supervisors-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 16px;
    margin-bottom: 16px;
}
.supervisor-card {
    background: #fff;
    border-radius: 13px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(24, 43, 72, 0.04);
    border: 1px solid #eef2f7;
    transition: all .25s ease;
}
.supervisor-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 30px rgba(24, 43, 72, 0.1);
    border-color: rgba(212, 160, 48, 0.3);
}
.supervisor-header-card {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 16px 16px;
    background: #fbfcfe;
    border-bottom: 1px solid #f0f4f8;
}
.supervisor-avatar-card {
    width: 48px; height: 48px;
    background: linear-gradient(135deg, #8e44ad, #9b59b6);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #d4a030;
    font-size: 20px;
    flex-shrink: 0;
}
.supervisor-info-card { flex: 1; min-width: 0; }
.supervisor-info-card h5 { color: #182B48; font-size: 15.5px; font-weight: 700; margin: 0 0 5px; }
.role-tag {
    background: rgba(142, 68, 173, 0.1);
    color: #8e44ad;
    padding: 3px 11px;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 600;
}
.student-count-badge {
    background: linear-gradient(135deg, #182B48, #2A4570);
    color: #fff;
    padding: 8px 13px;
    border-radius: 9px;
    display: flex;
    align-items: center;
    gap: 7px;
    font-size: 13px;
    font-weight: 700;
}
.student-count-badge i { color: #d4a030; }
.supervisor-details { padding: 14px 16px; }
.detail-row {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 0;
    border-bottom: 1px solid #f0f4f8;
}
.detail-row:last-child { border-bottom: none; }
.detail-row i { width: 18px; color: #d4a030; font-size: 12px; }
.detail-row span { color: #7f8c8d; font-size: 12.5px; width: 78px; }
.detail-row strong { color: #182B48; font-size: 12.5px; flex: 1; }
.supervisor-actions { display: flex; gap: 8px; padding: 14px 16px; background: #f8f9fa; }
.action-btn-card {
    flex: 1;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 7px;
    padding: 10px;
    border-radius: 9px;
    font-size: 12.5px;
    font-weight: 600;
    text-decoration: none;
    transition: all .2s ease;
    border: none;
    cursor: pointer;
}
.action-btn-card.view { background: rgba(52, 152, 219, 0.12); color: #3498db; }
.action-btn-card.view:hover { background: #3498db; color: #fff; }
.action-btn-card.edit { background: rgba(243, 156, 18, 0.12); color: #f39c12; }
.action-btn-card.edit:hover { background: #f39c12; color: #fff; }
.action-btn-card.delete { background: rgba(192, 57, 43, 0.12); color: #c0392b; }
.action-btn-card.delete:hover { background: #c0392b; color: #fff; }
.action-btn-card.activate { background: rgba(39, 174, 96, 0.12); color: #27ae60; }
.action-btn-card.activate:hover { background: #27ae60; color: #fff; }
.action-btn-card.deactivate { background: rgba(230, 126, 34, 0.12); color: #e67e22; }
.action-btn-card.deactivate:hover { background: #e67e22; color: #fff; }
.card-inactive { opacity: .6; }
.pagination-container { display: flex; justify-content: center; }
</style>

@endsection