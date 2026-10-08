@extends('layouts.app')
@section('title', 'إدارة أولياء الأمور')
@section('page-title', 'إدارة أولياء الأمور')
@section('role-label', 'مدير')

@section('sidebar-menu')
@include('admin.partials.sidebar')
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item active">أولياء الأمور</li>
@endsection

@section('content')
@php
$parentsFathers = $parents->filter(function($p) { return $p->parentProfile && $p->parentProfile->relationship === 'father'; })->count();
$parentsMothers = $parents->filter(function($p) { return $p->parentProfile && $p->parentProfile->relationship === 'mother'; })->count();
@endphp
<div class="islamic-pattern-header">
    <div class="pattern-overlay"></div>
    <div class="bismillah-text">بِسْمِ اللَّهِ الرَّحْمَنِ الرَّحِيمِ</div>
</div>

{{-- Page Header --}}
<x-page-hero icon="fas fa-users">
    <h1 class="page-banner-title">إدارة أولياء الأمور</h1>
    <p class="page-banner-sub">عرض وإدارة بيانات أولياء أمور الطلاب</p>
    <x-slot:actions>
        <a href="{{ route('admin.parents.create') }}" class="btn-gold">
            <i class="fas fa-plus"></i>
            <span>إضافة ولي أمر</span>
        </a>
    </x-slot:actions>
</x-page-hero>

{{-- Stats Cards --}}
<div class="stats-grid-islamic mb-4">
    <div class="stat-card-islamic primary">
        <div class="stat-card-bg-pattern"></div>
        <div class="stat-content-wrapper">
            <div class="stat-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number">{{ $parents->total() }}</div>
                <div class="stat-label">إجمالي أولياء الأمور</div>
            </div>
        </div>
        <div class="stat-decoration">
            <i class="fas fa-user-friends"></i>
        </div>
        <div class="stat-corner-decoration"></div>
    </div>
    <div class="stat-card-islamic gold">
        <div class="stat-card-bg-pattern"></div>
        <div class="stat-content-wrapper">
            <div class="stat-icon">
                <i class="fas fa-user-tie"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number">{{ $parentsFathers }}</div>
                <div class="stat-label">آباء</div>
            </div>
        </div>
        <div class="stat-decoration">
            <i class="fas fa-male"></i>
        </div>
        <div class="stat-corner-decoration"></div>
    </div>
    <div class="stat-card-islamic emerald">
        <div class="stat-card-bg-pattern"></div>
        <div class="stat-content-wrapper">
            <div class="stat-icon">
                <i class="fas fa-female"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number">{{ $parentsMothers }}</div>
                <div class="stat-label">أمهات</div>
            </div>
        </div>
        <div class="stat-decoration">
            <i class="fas fa-female"></i>
        </div>
        <div class="stat-corner-decoration"></div>
    </div>
</div>

{{-- Filter Card --}}
<div class="filter-card-islamic mb-4">
    <div class="filter-header">
        <div class="filter-title">
            <i class="fas fa-filter"></i>
            <span>بحث وتصفية</span>
        </div>
        <div class="filter-icon-decoration">
            <i class="fas fa-search"></i>
        </div>
    </div>
    <form method="GET" class="filter-form">
        <div class="row g-3">
            <div class="col-md-4">
                <div class="input-group-islamic">
                    <i class="fas fa-user"></i>
                    <input type="text" name="search" class="form-control" placeholder="ابحث بالاسم أو اسم المستخدم..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-3">
                <div class="select-group-islamic">
                    <i class="fas fa-link"></i>
                    <select name="relationship" class="form-select">
                        <option value="">كل القرابات</option>
                        <option value="father" {{ request('relationship') == 'father' ? 'selected' : '' }}>الأب</option>
                        <option value="mother" {{ request('relationship') == 'mother' ? 'selected' : '' }}>الأم</option>
                        <option value="guardian" {{ request('relationship') == 'guardian' ? 'selected' : '' }}>ولي أمر</option>
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary-islamic">
                    <i class="fas fa-search me-2"></i>بحث
                </button>
            </div>
            <div class="col-md-2">
                <a href="{{ route('admin.parents.index') }}" class="btn btn-outline-islamic">
                    <i class="fas fa-redo"></i>
                </a>
            </div>
        </div>
    </form>
</div>

{{-- Table Card --}}
<div class="table-card-islamic">
    <div class="table-header">
        <div class="table-title">
            <div class="table-icon">
                <i class="fas fa-list"></i>
            </div>
            <span>قائمة أولياء الأمور</span>
        </div>
        <div class="table-actions">
            <span class="records-count-islamic">
                <i class="fas fa-database"></i>
                {{ $parents->total() }} سجل
            </span>
        </div>
    </div>
    <div class="table-body p-0">
        @if($parents->isEmpty())
            <div class="empty-state-islamic">
                <div class="empty-icon-wrapper">
                    <div class="empty-icon">
                        <i class="fas fa-users-slash"></i>
                    </div>
                    <div class="empty-icon-glow"></div>
                </div>
                <h5>لا يوجد أولياء أمور</h5>
                <p>ابدأ بإضافة ولي أمر جديد للبدء</p>
                <a href="{{ route('admin.parents.create') }}" class="btn btn-primary-islamic">
                    <i class="fas fa-plus me-2"></i>إضافة ولي أمر
                </a>
            </div>
        @else
            <div class="table-responsive">
                <table class="data-table-islamic">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>اسم ولي الأمر</th>
                            <th>رقم الهاتف</th>
                            <th>البريد الإلكتروني</th>
                            <th>صلة القرابة</th>
                            <th>عدد الطلاب</th>
                            <th>الحالة</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($parents as $index => $parent)
                        @php $isActive = (bool) $parent->is_active; @endphp
                        <tr class="{{ $isActive ? '' : 'row-inactive' }}">
                            <td class="index-cell">{{ $parents->firstItem() + $index }}</td>
                            <td>
                                <div class="user-cell">
                                    <div class="user-avatar">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <div class="user-info">
                                        <span class="user-name">{{ $parent->name }}</span>
                                        <span class="user-username">{{ $parent->username }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="phone-cell">
                                <i class="fas fa-phone me-1" style="color:#d4a030"></i>
                                {{ $parent->phone ?? '-' }}
                            </td>
                            <td class="email-cell">{{ $parent->email ?? '-' }}</td>
                            <td>
                                @if($parent->parentProfile)
                                    <span class="badge-islamic {{ $parent->parentProfile->relationship === 'father' ? 'primary' : ($parent->parentProfile->relationship === 'mother' ? 'emerald' : 'info') }}">
                                        @if($parent->parentProfile->relationship === 'father')
                                            <i class="fas fa-male me-1"></i>الأب
                                        @elseif($parent->parentProfile->relationship === 'mother')
                                            <i class="fas fa-female me-1"></i>الأم
                                        @else
                                            <i class="fas fa-user me-1"></i>{{ $parent->parentProfile->relationship }}
                                        @endif
                                    </span>
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                @if($parent->parentProfile)
                                    <span class="student-count-badge">
                                        <i class="fas fa-user-graduate"></i>
                                        {{ $parent->parentProfile->students_count ?? 0 }} طالب
                                    </span>
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                @if($isActive)
                                    <span class="badge-status active"><i class="fas fa-circle-check"></i>مفعّل</span>
                                @else
                                    <span class="badge-status inactive"><i class="fas fa-circle-pause"></i>معطّل</span>
                                @endif
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <a href="{{ route('admin.parents.show', $parent->id) }}" class="action-btn-islamic view" title="عرض">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.parents.edit', $parent->id) }}" class="action-btn-islamic edit" title="تعديل">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.parents.toggleActive', $parent->id) }}" method="POST" class="d-inline"
                                          @if($isActive) onsubmit="return confirm('تعطيل حساب هذا ولي الأمر؟ سيتم منعه من الدخول واستبعاده من التقارير والإحصائيات.')" @endif>
                                        @csrf
                                        <button type="submit" class="action-btn-islamic {{ $isActive ? 'deactivate' : 'activate' }}" title="{{ $isActive ? 'تعطيل' : 'تفعيل' }}">
                                            <i class="fas fa-{{ $isActive ? 'ban' : 'check' }}"></i>
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.parents.destroy', $parent->id) }}" method="POST" class="d-inline" onsubmit="return confirm('هل أنت متأكد من حذف هذا ولي الأمر؟')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="action-btn-islamic delete" title="حذف">
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
                {{ $parents->withQueryString()->links() }}
            </div>
        @endif
    </div>
</div>

<style>
.islamic-pattern-header {
    position: relative;
    height: 80px;
    margin-bottom: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    border-radius: 13px;
    background: linear-gradient(135deg, #182B48 0%, #2A4570 100%);
}
.pattern-overlay {
    position: absolute;
    inset: 0;
    background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23d4a030' fill-opacity='0.1'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    opacity: 0.5;
}
.bismillah-text {
    font-family: 'Amiri', serif;
    font-size: 24px;
    color: #d4a030;
    text-shadow: 0 2px 10px rgba(212,160,48,0.3);
    position: relative;
    z-index: 1;
    letter-spacing: 2px;
}
.header-icon-wrapper {
    position: relative;
}
.header-icon-glow {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 90px;
    height: 90px;
    background: radial-gradient(circle, rgba(212,160,48,0.2) 0%, transparent 70%);
    border-radius: 50%;
    z-index: 0;
}
.add-btnIslamic {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 14px 16px;
    background: linear-gradient(135deg, #d4a030, #B4802C);
    color: #fff;
    border-radius: 13px;
    text-decoration: none;
    font-weight: 700;
    font-size: 14px;
    transition: all 0.4s ease;
    box-shadow: 0 4px 20px rgba(212,160,48,0.3);
    border: none;
}
.add-btnIslamic:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 30px rgba(212,160,48,0.4);
    color: #fff;
}
.stats-grid-islamic {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
}
@media (max-width: 992px) {
.stats-grid-islamic { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 576px) {
.stats-grid-islamic { grid-template-columns: 1fr; }
}
.stat-card-islamic {
    position: relative;
    padding: 16px;
    border-radius: 13px;
    overflow: hidden;
    transition: all 0.4s ease;
}
.stat-card-islamic:hover {
    transform: translateY(-8px);
    box-shadow: 0 20px 50px rgba(0,0,0,0.15);
}
.stat-card-islamic.primary { background: linear-gradient(135deg, #182B48 0%, #2A4570 100%); }
.stat-card-islamic.gold { background: linear-gradient(135deg, #d4a030 0%, #B4802C 100%); }
.stat-card-islamic.emerald { background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%); }
.stat-card-bg-pattern {
    position: absolute;
    top: -40px;
    right: -40px;
    width: 150px;
    height: 150px;
    border-radius: 50%;
    background: rgba(255,255,255,0.1);
}
.stat-content-wrapper {
    display: flex;
    align-items: flex-start;
    gap: 15px;
    position: relative;
    z-index: 1;
}
.stat-content { flex: 1; }
.stat-corner-decoration {
    position: absolute;
    bottom: 0;
    right: 0;
    width: 80px;
    height: 80px;
    background: radial-gradient(circle at bottom right, rgba(255,255,255,0.1) 0%, transparent 70%);
    border-radius: 50% 0 0 0;
}
.filter-card-islamic {
    background: #fff;
    border-radius: 13px;
    box-shadow: 0 4px 25px rgba(0,0,0,0.05);
    border: 1px solid rgba(212,160,48,0.1);
    overflow: hidden;
    margin-bottom: 16px;
}
.filter-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px 16px;
    background: linear-gradient(135deg, rgba(24,43,72,0.03), rgba(42,69,112,0.03));
    border-bottom: 2px solid rgba(212,160,48,0.1);
}
.filter-title {
    display: flex;
    align-items: center;
    gap: 12px;
    font-weight: 700;
    color: #182B48;
    font-size: 16px;
}
.filter-title i { color: #d4a030; font-size: 18px; }
.filter-icon-decoration {
    width: 45px;
    height: 45px;
    background: linear-gradient(135deg, rgba(212,160,48,0.1), rgba(180,128,44,0.1));
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #d4a030;
    font-size: 18px;
}
.input-group-islamic, .select-group-islamic {
    position: relative;
}
.input-group-islamic i, .select-group-islamic i {
    position: absolute;
    right: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: #d4a030;
    z-index: 1;
}
.input-group-islamic .form-control, .select-group-islamic .form-select {
    padding-right: 45px;
    border-radius: 12px;
    border: 2px solid #e9ecef;
    padding: 12px 16px;
    transition: all 0.3s;
}
.input-group-islamic .form-control:focus, .select-group-islamic .form-select:focus {
    border-color: #d4a030;
    box-shadow: 0 0 0 4px rgba(212,160,48,0.15);
}
.btn-primary-islamic {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 12px 16px;
    background: linear-gradient(135deg, #182B48, #2A4570);
    color: #fff;
    border: none;
    border-radius: 12px;
    font-weight: 600;
    font-size: 14px;
    transition: all 0.3s ease;
    cursor: pointer;
    width: 100%;
}
.btn-primary-islamic:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 20px rgba(24,43,72,0.3);
    color: #fff;
}
.btn-outline-islamic {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 12px 16px;
    background: transparent;
    color: #7f8c8d;
    border: 2px solid #e9ecef;
    border-radius: 12px;
    font-weight: 600;
    font-size: 14px;
    transition: all 0.3s ease;
    cursor: pointer;
    width: 100%;
}
.btn-outline-islamic:hover { border-color: #d4a030; color: #d4a030; }
.table-card-islamic {
    background: #fff;
    border-radius: 13px;
    box-shadow: 0 4px 25px rgba(0,0,0,0.05);
    border: 1px solid rgba(212,160,48,0.1);
    overflow: hidden;
}
.table-icon {
    width: 45px;
    height: 45px;
    background: linear-gradient(135deg, #182B48, #1a3356);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #d4a030;
    font-size: 18px;
}
.records-count-islamic {
    display: flex;
    align-items: center;
    gap: 8px;
    background: linear-gradient(135deg, rgba(212,160,48,0.1), rgba(180,128,44,0.1));
    padding: 8px 16px;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 600;
    color: #B4802C;
}
.records-count-islamic i { color: #d4a030; }
.data-table-islamic { width: 100%; border-collapse: collapse; }
.data-table-islamic thead { background: linear-gradient(135deg, rgba(24,43,72,0.03), rgba(42,69,112,0.03)); }
.data-table-islamic th {
    padding: 16px 16px;
    font-size: 12px;
    font-weight: 700;
    color: #182B48;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-bottom: 2px solid rgba(212,160,48,0.15);
}
.data-table-islamic td {
    padding: 16px 16px;
    font-size: 14px;
    color: #2c3e50;
    border-bottom: 1px solid #f0f4f8;
    vertical-align: middle;
}
.data-table-islamic tbody tr { transition: all 0.3s; }
.data-table-islamic tbody tr:hover { background: linear-gradient(135deg, rgba(212,160,48,0.03), rgba(180,128,44,0.03)); }
.index-cell { color: #7f8c8d; font-weight: 700; }
.user-cell { display: flex; align-items: center; gap: 15px; }
.user-avatar {
    width: 48px;
    height: 48px;
    background: linear-gradient(135deg, #182B48, #2A4570);
    border-radius: 13px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #d4a030;
    font-size: 20px;
}
.user-info { display: flex; flex-direction: column; }
.email-cell { color: #7f8c8d; }
.badge-islamic {
    padding: 7px 14px;
    border-radius: 10px;
    font-size: 13px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
}
.badge-islamic.primary { background: rgba(24,43,72,0.1); color: #182B48; }
.badge-islamic.emerald { background: rgba(39,174,96,0.1); color: #27ae60; }
.badge-islamic.info { background: rgba(52,152,219,0.1); color: #3498db; }
.student-count-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: linear-gradient(135deg, rgba(212,160,48,0.1), rgba(180,128,44,0.1));
    padding: 6px 14px;
    border-radius: 13px;
    font-size: 13px;
    font-weight: 600;
    color: #B4802C;
}
.student-count-badge i { color: #d4a030; }
.action-btn-islamic {
    width: 40px;
    height: 40px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
    text-decoration: none;
}
.action-btn-islamic.view { background: rgba(52,152,219,0.1); color: #3498db; }
.action-btn-islamic.view:hover { background: #3498db; color: #fff; transform: scale(1.05); }
.action-btn-islamic.edit { background: rgba(212,160,48,0.1); color: #d4a030; }
.action-btn-islamic.edit:hover { background: #d4a030; color: #fff; transform: scale(1.05); }
.action-btn-islamic.delete { background: rgba(192,57,43,0.1); color: #c0392b; }
.action-btn-islamic.delete:hover { background: #c0392b; color: #fff; transform: scale(1.05); }
.action-btn-islamic.activate { background: rgba(39,174,96,0.1); color: #27ae60; }
.action-btn-islamic.activate:hover { background: #27ae60; color: #fff; transform: scale(1.05); }
.action-btn-islamic.deactivate { background: rgba(230,126,34,0.1); color: #e67e22; }
.action-btn-islamic.deactivate:hover { background: #e67e22; color: #fff; transform: scale(1.05); }
.table-footer .pagination { margin: 0; gap: 4px; }
.table-footer .page-link {
    padding: 6px 11px;
    border-radius: 7px;
    font-size: 12.5px;
    border: 2px solid #e9ecef;
    color: #182B48;
    transition: all 0.3s;
}
.table-footer .page-item.active .page-link {
    background: linear-gradient(135deg, #182B48, #2A4570);
    border-color: #182B48;
    color: #fff;
}
.empty-state-islamic {
    text-align: center;
    padding: 40px 16px;
}
.empty-icon-wrapper {
    position: relative;
    display: inline-block;
    margin-bottom: 16px;
}
.empty-icon-glow {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 130px;
    height: 130px;
    background: radial-gradient(circle, rgba(212,160,48,0.15) 0%, transparent 70%);
    border-radius: 50%;
    z-index: 0;
}
.empty-state-islamic h5 { color: #182B48; font-size: 18px; font-weight: 700; margin-bottom: 10px; }
.empty-state-islamic p { color: #7f8c8d; margin-bottom: 16px; font-size: 15px; }
</style>
@endsection