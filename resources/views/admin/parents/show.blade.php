@extends('layouts.app')
@section('title', 'بيانات ولي الأمر')
@section('page-title', 'بيانات ولي الأمر')
@section('role-label', 'مدير')

@section('sidebar-menu')
@include('admin.partials.sidebar')
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.parents.index') }}">أولياء الأمور</a></li>
    <li class="breadcrumb-item active">{{ $parent->name }}</li>
@endsection

@section('content')

{{-- Page Header --}}
<div class="page-header-islamic">
    <div class="header-content">
        <div class="header-icon-wrapper">
            <div class="header-icon">
                <i class="fas fa-user"></i>
            </div>
            <div class="header-icon-glow"></div>
        </div>
        <div class="header-text">
            <h4>بيانات ولي الأمر</h4>
            <p>معلومات وإحصائيات ولي الأمر</p>
        </div>
    </div>
    <div class="header-actions">
        <a href="{{ route('admin.parents.edit', $parent->id) }}" class="edit-btn-islamic">
            <i class="fas fa-edit"></i>
            <span>تعديل</span>
        </a>
        <a href="{{ route('admin.parents.index') }}" class="back-btn-islamic">
            <i class="fas fa-arrow-right"></i>
            <span>القائمة</span>
        </a>
    </div>
</div>

<div class="row g-4">
    {{-- Main Content --}}
    <div class="col-lg-8">
        {{-- Profile Card --}}
        <div class="profile-card-islamic mb-4">
            <div class="profile-header">
                <div class="profile-avatar-wrapper">
                    <div class="profile-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="profile-avatar-glow"></div>
                </div>
                <div class="profile-info">
                    <h3>{{ $parent->name }}</h3>
                    <span class="username">{{ $parent->username }}</span>
                    @if($parent->parentProfile)
                        <span class="relationship-badge-islamic">
                            @if($parent->parentProfile->relationship === 'father')
                                <i class="fas fa-male me-1"></i>الأب
                            @elseif($parent->parentProfile->relationship === 'mother')
                                <i class="fas fa-female me-1"></i>الأم
                            @else
                                <i class="fas fa-user me-1"></i>{{ $parent->parentProfile->relationship }}
                            @endif
                        </span>
                    @endif
                </div>
                <div class="profile-header-decoration">
                    <i class="fas fa-moon"></i>
                </div>
            </div>
            <div class="profile-body">
                <div class="info-grid-islamic">
                    <div class="info-item-islamic">
                        <div class="info-icon">
                            <i class="fas fa-phone"></i>
                        </div>
                        <div class="info-content">
                            <span class="info-label">رقم الهاتف</span>
                            <span class="info-value">{{ $parent->phone ?? '-' }}</span>
                        </div>
                    </div>
                    <div class="info-item-islamic">
                        <div class="info-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="info-content">
                            <span class="info-label">البريد الإلكتروني</span>
                            <span class="info-value">{{ $parent->email ?? '-' }}</span>
                        </div>
                    </div>
                    @if($parent->parentProfile && $parent->parentProfile->national_id)
                    <div class="info-item-islamic">
                        <div class="info-icon">
                            <i class="fas fa-id-card"></i>
                        </div>
                        <div class="info-content">
                            <span class="info-label">الرقم الوطني</span>
                            <span class="info-value">{{ $parent->parentProfile->national_id }}</span>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Linked Students --}}
        @if($parent->parentProfile && $parent->parentProfile->students->count() > 0)
        <div class="students-card-islamic">
            <div class="card-header-islamic">
                <div class="card-title">
                    <div class="card-icon">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <span>الطلاب المرتبطون</span>
                </div>
                <span class="students-count-islamic">
                    <i class="fas fa-users"></i>
                    {{ $parent->parentProfile->students->count() }} طالب
                </span>
            </div>
            <div class="card-body-islamic p-0">
                <div class="table-responsive">
                    <table class="data-table-islamic">
                        <thead>
                            <tr>
                                <th>اسم الطالب</th>
                                <th>رقم الطالب</th>
                                <th>المستوى</th>
                                <th>المشرف</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($parent->parentProfile->students as $student)
                            <tr>
                                <td>
                                    <div class="user-cell">
                                        <div class="user-avatar">
                                            <i class="fas fa-user-graduate"></i>
                                        </div>
                                        <span class="user-name">{{ $student->user->name }}</span>
                                    </div>
                                </td>
                                <td><code class="student-number-islamic">{{ $student->student_number }}</code></td>
                                <td>
                                    @if($student->level)
                                        <span class="level-badge-islamic">{{ $student->level->name }}</span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="supervisor-cell">{{ $student->supervisor->name ?? '-' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif
    </div>

    {{-- Sidebar --}}
    <div class="col-lg-4">
        <div class="actions-card-islamic">
            <div class="card-header-islamic">
                <div class="card-title">
                    <div class="card-icon">
                        <i class="fas fa-cogs"></i>
                    </div>
                    <span>الإجراءات</span>
                </div>
            </div>
            <div class="card-body-islamic">
                <div class="action-buttons-islamic">
                    <a href="{{ route('admin.parents.edit', $parent->id) }}" class="action-btn-primary-islamic">
                        <i class="fas fa-edit"></i>
                        <span>تعديل البيانات</span>
                    </a>
                    <a href="{{ route('admin.parents.index') }}" class="action-btn-secondary-islamic">
                        <i class="fas fa-list"></i>
                        <span>العودة للقائمة</span>
                    </a>
                    <form action="{{ route('admin.parents.destroy', $parent->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف هذا ولي الأمر؟')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="action-btn-danger-islamic w-100">
                            <i class="fas fa-trash"></i>
                            <span>حذف ولي الأمر</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Quick Info Card --}}
        <div class="quick-info-card mt-4">
            <div class="card-header-islamic">
                <div class="card-title">
                    <div class="card-icon">
                        <i class="fas fa-info-circle"></i>
                    </div>
                    <span>معلومات سريعة</span>
                </div>
            </div>
            <div class="card-body-islamic">
                <div class="quick-info-item">
                    <span class="quick-info-label">المعرف</span>
                    <span class="quick-info-value">#{{ $parent->id }}</span>
                </div>
                <div class="quick-info-item">
                    <span class="quick-info-label">تاريخ التسجيل</span>
                    <span class="quick-info-value">{{ $parent->created_at->format('d/m/Y') }}</span>
                </div>
                <div class="quick-info-item">
                    <span class="quick-info-label">عدد الطلاب</span>
                    <span class="quick-info-value">{{ $parent->parentProfile->students_count ?? 0 }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.page-header-islamic {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
    margin-bottom: 25px;
    padding: 25px 30px;
    background: #fff;
    border-radius: 20px;
    box-shadow: 0 4px 25px rgba(24,43,72,0.08);
    border: 1px solid rgba(212,160,48,0.1);
}

.header-content { display: flex; align-items: center; gap: 20px; }

.header-icon-wrapper { position: relative; }

.header-icon {
    width: 70px;
    height: 70px;
    background: linear-gradient(135deg, #182B48, #2A4570);
    border-radius: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 30px;
    color: #d4a030;
    box-shadow: 0 8px 30px rgba(24,43,72,0.25);
    position: relative;
    z-index: 1;
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

.header-text h4 { color: #182B48; font-size: 24px; font-weight: 700; margin: 0 0 4px 0; }
.header-text p { color: #7f8c8d; font-size: 14px; margin: 0; }

.header-actions { display: flex; gap: 12px; }

.edit-btn-islamic {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 12px 24px;
    background: linear-gradient(135deg, #d4a030, #B4802C);
    color: #fff;
    border-radius: 12px;
    text-decoration: none;
    font-weight: 700;
    font-size: 14px;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(212,160,48,0.3);
}

.edit-btn-islamic:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(212,160,48,0.4);
    color: #fff;
}

.back-btn-islamic {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 12px 24px;
    background: #f8f9fa;
    color: #182B48;
    border-radius: 12px;
    text-decoration: none;
    font-weight: 600;
    font-size: 14px;
    transition: all 0.3s ease;
}

.back-btn-islamic:hover { background: #e9ecef; color: #182B48; }

.profile-card-islamic {
    background: #fff;
    border-radius: 20px;
    box-shadow: 0 4px 25px rgba(0,0,0,0.05);
    border: 1px solid rgba(212,160,48,0.1);
    overflow: hidden;
}

.profile-header {
    display: flex;
    align-items: center;
    gap: 25px;
    padding: 35px;
    background: linear-gradient(135deg, #182B48 0%, #2A4570 100%);
    position: relative;
    overflow: hidden;
}

.profile-header::before {
    content: '';
    position: absolute;
    inset: 0;
    background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23d4a030' fill-opacity='0.05'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    opacity: 0.5;
}

.profile-header::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 5px;
    background: linear-gradient(90deg, #d4a030, #B4802C, #d4a030);
}

.profile-avatar-wrapper { position: relative; }

.profile-avatar {
    width: 100px;
    height: 100px;
    background: linear-gradient(135deg, #d4a030, #B4802C);
    border-radius: 22px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 45px;
    color: #fff;
    box-shadow: 0 8px 30px rgba(212,160,48,0.4);
    position: relative;
    z-index: 1;
}

.profile-avatar-glow {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 130px;
    height: 130px;
    background: radial-gradient(circle, rgba(212,160,48,0.3) 0%, transparent 70%);
    border-radius: 50%;
    z-index: 0;
}

.profile-info { position: relative; z-index: 1; }
.profile-info h3 { color: #fff; font-size: 24px; font-weight: 700; margin: 0 0 8px 0; }
.username { color: rgba(255,255,255,0.7); font-size: 14px; display: block; margin-bottom: 12px; }

.relationship-badge-islamic {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: rgba(255,255,255,0.15);
    padding: 8px 16px;
    border-radius: 20px;
    color: #fff;
    font-size: 14px;
    font-weight: 500;
    backdrop-filter: blur(5px);
}

.profile-header-decoration {
    position: absolute;
    top: 20px;
    left: 20px;
    font-size: 30px;
    color: #d4a030;
    opacity: 0.2;
}

.profile-body { padding: 30px; }

.info-grid-islamic {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 20px;
}

.info-item-islamic {
    display: flex;
    align-items: center;
    gap: 18px;
    padding: 20px;
    background: linear-gradient(135deg, #f8f9fa, #f0f4f8);
    border-radius: 16px;
    transition: all 0.3s;
}

.info-item-islamic:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.08);
}

.info-icon {
    width: 50px;
    height: 50px;
    background: linear-gradient(135deg, #182B48, #2A4570);
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #d4a030;
    font-size: 20px;
    flex-shrink: 0;
}

.info-content { display: flex; flex-direction: column; }
.info-label { font-size: 12px; color: #7f8c8d; margin-bottom: 4px; font-weight: 500; }
.info-value { font-size: 15px; font-weight: 700; color: #182B48; direction: ltr; text-align: right; }

.students-card-islamic {
    background: #fff;
    border-radius: 20px;
    box-shadow: 0 4px 25px rgba(0,0,0,0.05);
    border: 1px solid rgba(212,160,48,0.1);
    overflow: hidden;
}

.card-header-islamic {
    padding: 20px 25px;
    border-bottom: 2px solid rgba(212,160,48,0.1);
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: linear-gradient(135deg, rgba(24,43,72,0.02), rgba(26,51,86,0.02));
}

.card-title {
    display: flex;
    align-items: center;
    gap: 15px;
    font-size: 17px;
    font-weight: 700;
    color: #182B48;
}

.card-icon {
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

.students-count-islamic {
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

.students-count-islamic i { color: #d4a030; }

.card-body-islamic { padding: 0; }

.data-table-islamic { width: 100%; border-collapse: collapse; }
.data-table-islamic thead { background: linear-gradient(135deg, rgba(24,43,72,0.03), rgba(42,69,112,0.03)); }
.data-table-islamic th {
    padding: 16px 20px;
    font-size: 12px;
    font-weight: 700;
    color: #182B48;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-bottom: 2px solid rgba(212,160,48,0.15);
}
.data-table-islamic td {
    padding: 18px 20px;
    font-size: 14px;
    color: #2c3e50;
    border-bottom: 1px solid #f0f4f8;
    vertical-align: middle;
}
.data-table-islamic tbody tr { transition: all 0.3s; }
.data-table-islamic tbody tr:hover { background: linear-gradient(135deg, rgba(212,160,48,0.03), rgba(180,128,44,0.03)); }

.user-cell { display: flex; align-items: center; gap: 15px; }
.user-avatar {
    width: 45px;
    height: 45px;
    background: linear-gradient(135deg, #182B48, #2A4570);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #d4a030;
    font-size: 18px;
}
.user-name { font-weight: 700; color: #182B48; font-size: 15px; }

.student-number-islamic {
    background: linear-gradient(135deg, rgba(212,160,48,0.1), rgba(180,128,44,0.1));
    padding: 5px 12px;
    border-radius: 8px;
    font-size: 13px;
    color: #B4802C;
    font-weight: 600;
}

.level-badge-islamic {
    background: rgba(52,152,219,0.1);
    padding: 5px 12px;
    border-radius: 8px;
    font-size: 13px;
    color: #3498db;
    font-weight: 600;
}

.supervisor-cell { color: #7f8c8d; }

.actions-card-islamic {
    background: #fff;
    border-radius: 20px;
    box-shadow: 0 4px 25px rgba(0,0,0,0.05);
    border: 1px solid rgba(212,160,48,0.1);
    overflow: hidden;
    position: sticky;
    top: 20px;
}

.action-buttons-islamic { display: flex; flex-direction: column; gap: 12px; padding: 20px; }

.action-btn-primary-islamic, .action-btn-secondary-islamic, .action-btn-danger-islamic {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 15px 20px;
    border-radius: 14px;
    text-decoration: none;
    font-weight: 600;
    font-size: 14px;
    transition: all 0.3s ease;
}

.action-btn-primary-islamic {
    background: linear-gradient(135deg, #182B48, #2A4570);
    color: #fff;
    box-shadow: 0 4px 15px rgba(24,43,72,0.2);
}

.action-btn-primary-islamic:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(24,43,72,0.3);
    color: #fff;
}

.action-btn-secondary-islamic {
    background: #f8f9fa;
    color: #182B48;
}

.action-btn-secondary-islamic:hover { background: #e9ecef; color: #182B48; }

.action-btn-danger-islamic {
    background: rgba(192,57,43,0.1);
    color: #c0392b;
    border: none;
    cursor: pointer;
    width: 100%;
    justify-content: center;
}

.action-btn-danger-islamic:hover { background: #c0392b; color: #fff; }

.action-btn-primary-islamic i, .action-btn-secondary-islamic i, .action-btn-danger-islamic i { font-size: 18px; width: 24px; }

.quick-info-card {
    background: #fff;
    border-radius: 20px;
    box-shadow: 0 4px 25px rgba(0,0,0,0.05);
    border: 1px solid rgba(212,160,48,0.1);
    overflow: hidden;
}

.quick-info-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 20px;
    border-bottom: 1px solid #f0f4f8;
}

.quick-info-item:last-child { border-bottom: none; }
.quick-info-label { color: #7f8c8d; font-size: 14px; }
.quick-info-value { font-weight: 700; color: #182B48; font-size: 14px; }
</style>
@endsection