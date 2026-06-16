@extends('layouts.app')
@section('title', 'إدارة الرسوم')
@section('page-title', 'إدارة الرسوم الشهرية')
@section('role-label', 'مدير')

@section('sidebar-menu')
@include('admin.partials.sidebar')
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item active">الرسوم الشهرية</li>
@endsection

@section('content')
@php
$stats = [
    'total_unpaid' => $fees->where('status', 'unpaid')->sum('fee_amount'),
    'total_paid' => $fees->where('status', 'paid')->sum('fee_amount'),
    'unpaid_count' => $fees->where('status', 'unpaid')->count(),
    'paid_count' => $fees->where('status', 'paid')->count(),
];
@endphp
<script>
window.__STUDENTS_FOR_BULK__ = @json($studentsForBulk);
</script>

{{-- Islamic Pattern Header --}}
<div class="islamic-fees-header">
    <div class="header-pattern-overlay"></div>
    <div class="header-bismillah">بِسْمِ اللَّهِ الرَّحْمَنِ الرَّحِيمِ</div>
    <div class="header-decoration-left">
        <i class="fas fa-moon"></i>
    </div>
    <div class="header-decoration-right">
        <i class="fas fa-star"></i>
    </div>
</div>

{{-- Page Header --}}
<div class="page-header-fees">
    <div class="header-content">
        <div class="header-icon-wrapper">
            <div class="header-icon">
                <i class="fas fa-coins"></i>
            </div>
            <div class="header-icon-glow"></div>
        </div>
        <div class="header-text">
            <h4>إدارة الرسوم الشهرية</h4>
            <p>متابعة وتحصيل رسوم الطلاب الشهرية</p>
        </div>
    </div>
    <div class="header-actions">
        <a href="{{ route('admin.billing.feeCategories') }}" class="btn-action-header">
            <i class="fas fa-tags"></i>
            <span>فئات الرسوم</span>
        </a>
        <a href="{{ route('admin.billing.history') }}" class="btn-action-header">
            <i class="fas fa-history"></i>
            <span>سجل المدفوعات</span>
        </a>
    </div>
</div>

{{-- Stats Cards --}}
<div class="stats-grid-fees mb-4">
    <div class="stat-card-fees primary">
        <div class="stat-card-bg-pattern"></div>
        <div class="stat-content-wrapper">
            <div class="stat-icon">
                <i class="fas fa-money-bill"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number">{{ number_format($stats['total_unpaid'], 0) }}</div>
                <div class="stat-label">إجمالي غير المدفوع</div>
            </div>
        </div>
        <div class="stat-decoration">
            <i class="fas fa-wallet"></i>
        </div>
        <div class="stat-corner-decoration"></div>
    </div>
    <div class="stat-card-fees success">
        <div class="stat-card-bg-pattern"></div>
        <div class="stat-content-wrapper">
            <div class="stat-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number">{{ number_format($stats['total_paid'], 0) }}</div>
                <div class="stat-label">إجمالي المدفوع</div>
            </div>
        </div>
        <div class="stat-decoration">
            <i class="fas fa-hand-holding-usd"></i>
        </div>
        <div class="stat-corner-decoration"></div>
    </div>
    <div class="stat-card-fees warning">
        <div class="stat-card-bg-pattern"></div>
        <div class="stat-content-wrapper">
            <div class="stat-icon">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number">{{ $stats['unpaid_count'] }}</div>
                <div class="stat-label">سجلات غير مدفوعة</div>
            </div>
        </div>
        <div class="stat-decoration">
            <i class="fas fa-hourglass-half"></i>
        </div>
        <div class="stat-corner-decoration"></div>
    </div>
    <div class="stat-card-fees gold">
        <div class="stat-card-bg-pattern"></div>
        <div class="stat-content-wrapper">
            <div class="stat-icon">
                <i class="fas fa-calendar-check"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number">{{ $stats['paid_count'] }}</div>
                <div class="stat-label">سجلات مدفوعة</div>
            </div>
        </div>
        <div class="stat-decoration">
            <i class="fas fa-receipt"></i>
        </div>
        <div class="stat-corner-decoration"></div>
    </div>
</div>

{{-- Filter Card --}}
<div class="filter-card-fees mb-4">
    <div class="filter-header">
        <div class="filter-title">
            <i class="fas fa-filter"></i>
            <span>بحث وتصفية</span>
        </div>
        <div class="filter-decoration">
            <i class="fas fa-search"></i>
        </div>
    </div>
    <form method="GET" class="filter-form">
        <div class="row g-3">
            <div class="col-md-2">
                <select name="year" class="form-select-fe">
                    @foreach($years as $year)
                        <option value="{{ $year }}" {{ request('year') == $year ? 'selected' : '' }}>{{ $year }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="month" class="form-select-fe">
                    <option value="">الكل</option>
                    @foreach($months as $num => $name)
                        <option value="{{ $num }}" {{ request('month') == $num ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="parent_id" class="form-select-fe">
                    <option value="">جميع أولياء الأمور</option>
                    @foreach($parents as $parent)
                        <option value="{{ $parent->id }}" {{ request('parent_id') == $parent->id ? 'selected' : '' }}>
                            {{ $parent->user->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select-fe">
                    <option value="">الكل</option>
                    <option value="unpaid" {{ request('status') == 'unpaid' ? 'selected' : '' }}>غير مدفوع</option>
                    <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>مدفوع</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="fee_category_id" class="form-select-fe">
                    <option value="">جميع الفئات</option>
                    @foreach($feeCategories as $cat)
                        <option value="{{ $cat->id }}" {{ request('fee_category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-1">
                <button type="submit" class="btn-search-fe">
                    <i class="fas fa-search"></i>
                </button>
            </div>
            <div class="col-md-1">
                <a href="{{ route('admin.billing.index') }}" class="btn-reset-fe">
                    <i class="fas fa-redo"></i>
                </a>
            </div>
        </div>
    </form>
</div>

{{-- Actions Card --}}
<div class="actions-bar-fees mb-4">
    <div class="actions-right">
        <button type="button" class="btn-action-fees success" onclick="openAddPaymentModal()">
            <i class="fas fa-plus"></i>
            <span>تسجيل دفعة</span>
        </button>
        <button type="button" class="btn-action-fees warning" onclick="openBulkCreateModal()">
            <i class="fas fa-layer-group"></i>
            <span>إنشاء رسوم جماعية</span>
        </button>
        <button type="button" class="btn-action-fees primary" onclick="markSelectedPaid()">
            <i class="fas fa-check"></i>
            <span>تعيين كمدفوع</span>
        </button>
    </div>
    <div class="actions-left">
        <span class="selected-count-fe" id="selectedCount">0 محدد</span>
    </div>
</div>

{{-- Table Card --}}
<div class="table-card-fees">
    <form id="bulkForm" method="POST" action="{{ route('admin.billing.markPaidBulk') }}">
        @csrf
        <div class="table-header">
            <div class="table-title">
                <div class="table-icon">
                    <i class="fas fa-list"></i>
                </div>
                <span>سجلات الرسوم</span>
            </div>
            <div class="table-actions">
                <span class="records-count-fe">
                    <i class="fas fa-database"></i>
                    {{ $fees->total() }} سجل
                </span>
            </div>
        </div>
        <div class="table-body p-0">
            @if($fees->isEmpty())
                <div class="empty-state-fees">
                    <div class="empty-icon-wrapper">
                        <div class="empty-icon">
                            <i class="fas fa-receipt"></i>
                        </div>
                        <div class="empty-icon-glow"></div>
                    </div>
                    <h5>لا توجد سجلات</h5>
                    <p>ابدأ بإنشاء سجلات رسوم جديدة</p>
                    <button type="button" class="btn-action-fees warning" onclick="openBulkCreateModal()">
                        <i class="fas fa-plus me-2"></i>إنشاء رسوم جماعية
                    </button>
                </div>
            @else
                <div class="table-responsive">
                    <table class="data-table-fees">
                        <thead>
                            <tr>
                                <th width="45"><input type="checkbox" id="selectAll"></th>
                                <th>ولي الأمر</th>
                                <th>الطالب</th>
                                <th>الفئة</th>
                                <th>المبلغ</th>
                                <th>الشهر</th>
                                <th>الحالة</th>
                                <th>تاريخ الدفع</th>
                                <th>الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($fees as $fee)
                            <tr>
                                <td><input type="checkbox" name="fee_ids[]" value="{{ $fee->id }}" class="fee-checkbox"></td>
                                <td>
                                    <div class="user-cell">
                                        <div class="user-avatar gold">
                                            <i class="fas fa-user"></i>
                                        </div>
                                        <span class="user-name">{{ $fee->parent->user->name ?? '-' }}</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="student-cell">
                                        <i class="fas fa-user-graduate"></i>
                                        {{ $fee->student->user->name ?? '-' }}
                                    </div>
                                </td>
                                <td>
                                    @if($fee->feeCategory)
                                        <span class="category-badge">{{ $fee->feeCategory->name }}</span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="amount-cell">
                                    @if($fee->fee_amount)
                                        <span class="amount">{{ number_format($fee->fee_amount, 0) }}</span>
                                        <span class="currency">د.ع</span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>{{ $months[$fee->month] ?? $fee->month }} / {{ $fee->year }}</td>
                                <td>
                                    @if($fee->status === 'paid')
                                        <span class="status-badge paid">
                                            <i class="fas fa-check-circle me-1"></i>مدفوع
                                        </span>
                                    @else
                                        <span class="status-badge unpaid">
                                            <i class="fas fa-times-circle me-1"></i>غير مدفوع
                                        </span>
                                    @endif
                                </td>
                                <td class="date-cell">
                                    @if($fee->paid_at)
                                        {{ $fee->paid_at->format('d/m/Y') }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        @if($fee->status === 'unpaid')
                                            <button type="button" class="action-btn-fees success" title="تعيين كمدفوع" onclick="markPaid({{ $fee->id }})">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        @endif
                                        <button type="button" class="action-btn-fees delete" title="حذف" onclick="deleteFee({{ $fee->id }})">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-5">
                                    <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                    لا توجد سجلات
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="table-footer">
                    {{ $fees->withQueryString()->links() }}
                </div>
            @endif
        </div>
    </form>
</div>

{{-- Add Payment Modal --}}
<div class="modal fade" id="addPaymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addPaymentModalTitle">
                    <i class="fas fa-plus-circle me-2"></i>تسجيل دفعة
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addPaymentForm">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">ولي الأمر <span class="text-danger">*</span></label>
                        <select name="parent_id" id="payment_parent_id" class="form-select" required>
                            <option value="">اختر ولي الأمر</option>
                            @foreach($parents as $parent)
                                <option value="{{ $parent->id }}">{{ $parent->user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">الطالب <span class="text-danger">*</span></label>
                        <select name="student_id" id="payment_student_id" class="form-select" required>
                            <option value="">اختر الطالب</option>
                        </select>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">السنة <span class="text-danger">*</span></label>
                                <select name="year" class="form-select" required>
                                    @foreach($years as $year)
                                        <option value="{{ $year }}" {{ $year == now()->year ? 'selected' : '' }}>{{ $year }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">الشهر <span class="text-danger">*</span></label>
                                <select name="month" class="form-select" required>
                                    @foreach($months as $num => $name)
                                        <option value="{{ $num }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-check mb-3">
                        <input type="checkbox" class="form-check-input" id="is_paid" name="is_paid" value="1" checked>
                        <label class="form-check-label" for="is_paid">تسجيل كمدفوع</label>
                    </div>
                    <div class="form-group" id="paid_at_container">
                        <label class="form-label">تاريخ الدفع</label>
                        <input type="date" name="paid_at" id="paid_at" class="form-control" max="{{ date('Y-m-d') }}">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>حفظ
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Bulk Create Modal --}}
<div class="modal fade" id="bulkCreateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-layer-group me-2"></i>إنشاء رسوم جماعية
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="bulkCreateForm">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">السنة <span class="text-danger">*</span></label>
                                <select name="year" id="bulk_year" class="form-select" required>
                                    @foreach($years as $year)
                                        <option value="{{ $year }}" {{ $year == now()->year ? 'selected' : '' }}>{{ $year }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">الشهر <span class="text-danger">*</span></label>
                                <select name="month" id="bulk_month" class="form-select" required>
                                    @foreach($months as $num => $name)
                                        <option value="{{ $num }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group mt-3">
                        <label class="form-label">الطلاب</label>
                        <div class="form-check mb-2">
                            <input type="checkbox" class="form-check-input" id="bulk_all_students" checked>
                            <label class="form-check-label" for="bulk_all_students">جميع الطلاب</label>
                        </div>
                        <div id="bulk_student_search_container" style="display:none;">
                            <input type="text" id="bulk_student_search" class="form-control mb-2" placeholder="ابحث عن طالب...">
                            <div id="bulk_student_list" class="border rounded p-2" style="max-height: 200px; overflow-y: auto;"></div>
                            <div id="bulk_student_count" class="form-text text-muted mt-1"></div>
                        </div>
                    </div>
                    <div class="alert alert-info" id="bulk_summary" style="display:none;">
                        <i class="fas fa-info-circle me-1"></i>
                        <span id="bulk_summary_text"></span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-plus me-2"></i>إنشاء السجلات
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.islamic-fees-header {
    position: relative;
    height: 70px;
    margin-bottom: 25px;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    border-radius: 16px;
    background: linear-gradient(135deg, #182B48 0%, #2A4570 100%);
}

.header-pattern-overlay {
    position: absolute;
    inset: 0;
    background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23d4a030' fill-opacity='0.1'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    opacity: 0.5;
}

.header-bismillah {
    font-family: 'Amiri', serif;
    font-size: 22px;
    color: #d4a030;
    text-shadow: 0 2px 10px rgba(212,160,48,0.3);
    position: relative;
    z-index: 1;
    letter-spacing: 2px;
}

.header-decoration-left {
    position: absolute;
    right: 30px;
    font-size: 28px;
    color: #d4a030;
    opacity: 0.15;
}

.header-decoration-right {
    position: absolute;
    left: 30px;
    font-size: 20px;
    color: #d4a030;
    opacity: 0.15;
}

.page-header-fees {
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

.btn-action-header {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 12px 20px;
    background: #fff;
    color: #182B48;
    border: 2px solid rgba(212,160,48,0.2);
    border-radius: 12px;
    font-weight: 600;
    font-size: 13px;
    transition: all 0.3s ease;
    text-decoration: none;
}

.btn-action-header:hover {
    border-color: #d4a030;
    color: #d4a030;
    transform: translateY(-2px);
}

.btn-action-header i { color: #d4a030; }

.stats-grid-fees {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
}

@media (max-width: 992px) { .stats-grid-fees { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 576px) { .stats-grid-fees { grid-template-columns: 1fr; } }

.stat-card-fees {
    position: relative;
    padding: 25px;
    border-radius: 20px;
    overflow: hidden;
    transition: all 0.4s ease;
}

.stat-card-fees:hover {
    transform: translateY(-8px);
    box-shadow: 0 20px 50px rgba(0,0,0,0.15);
}

.stat-card-fees.primary { background: linear-gradient(135deg, #182B48 0%, #2A4570 100%); }
.stat-card-fees.success { background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%); }
.stat-card-fees.warning { background: linear-gradient(135deg, #d4a030 0%, #B4802C 100%); }
.stat-card-fees.gold { background: linear-gradient(135deg, #8e44ad 0%, #9b59b6 100%); }

.stat-card-bg-pattern {
    position: absolute;
    top: -40px;
    right: -40px;
    width: 150px;
    height: 150px;
    border-radius: 50%;
    background: rgba(255,255,255,0.1);
}

.stat-content-wrapper { display: flex; align-items: flex-start; gap: 15px; position: relative; z-index: 1; }

.stat-icon {
    width: 60px;
    height: 60px;
    background: rgba(255,255,255,0.2);
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 26px;
    color: #fff;
    backdrop-filter: blur(5px);
    flex-shrink: 0;
}

.stat-content { flex: 1; }
.stat-number { font-size: 36px; font-weight: 800; color: #fff; line-height: 1; margin-bottom: 8px; }
.stat-label { font-size: 14px; color: rgba(255,255,255,0.85); font-weight: 500; }
.stat-decoration { position: absolute; bottom: 15px; left: 20px; font-size: 50px; opacity: 0.12; color: #fff; }
.stat-corner-decoration {
    position: absolute;
    bottom: 0;
    right: 0;
    width: 80px;
    height: 80px;
    background: radial-gradient(circle at bottom right, rgba(255,255,255,0.1) 0%, transparent 70%);
    border-radius: 50% 0 0 0;
}

.filter-card-fees {
    background: #fff;
    border-radius: 20px;
    box-shadow: 0 4px 25px rgba(0,0,0,0.05);
    border: 1px solid rgba(212,160,48,0.1);
    overflow: hidden;
    margin-bottom: 25px;
}

.filter-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 25px;
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

.filter-decoration {
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

.filter-form { padding: 25px; }

.form-select-fe {
    width: 100%;
    padding: 12px 16px;
    border-radius: 12px;
    border: 2px solid #e9ecef;
    font-size: 14px;
    transition: all 0.3s;
    background: #fff;
}

.form-select-fe:focus {
    border-color: #d4a030;
    box-shadow: 0 0 0 4px rgba(212,160,48,0.15);
    outline: none;
}

.btn-search-fe {
    width: 100%;
    padding: 12px;
    background: linear-gradient(135deg, #182B48, #2A4570);
    color: #fff;
    border: none;
    border-radius: 12px;
    font-size: 16px;
    transition: all 0.3s;
    cursor: pointer;
}

.btn-search-fe:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(24,43,72,0.3); }

.btn-reset-fe {
    width: 100%;
    padding: 12px;
    background: #f8f9fa;
    color: #7f8c8d;
    border: 2px solid #e9ecef;
    border-radius: 12px;
    font-size: 14px;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    justify-content: center;
}

.btn-reset-fe:hover { border-color: #d4a030; color: #d4a030; }

.actions-bar-fees {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 25px;
    background: #fff;
    border-radius: 20px;
    box-shadow: 0 4px 25px rgba(0,0,0,0.05);
    border: 1px solid rgba(212,160,48,0.1);
    margin-bottom: 25px;
}

.actions-right { display: flex; gap: 12px; }
.actions-left .selected-count-fe {
    background: linear-gradient(135deg, rgba(212,160,48,0.1), rgba(180,128,44,0.1));
    padding: 10px 20px;
    border-radius: 12px;
    font-size: 14px;
    font-weight: 600;
    color: #B4802C;
}

.btn-action-fees {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 12px 20px;
    border-radius: 12px;
    font-weight: 600;
    font-size: 14px;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
}

.btn-action-fees.success { background: linear-gradient(135deg, #27ae60, #2ecc71); color: #fff; }
.btn-action-fees.success:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(39,174,96,0.3); }

.btn-action-fees.warning { background: linear-gradient(135deg, #d4a030, #B4802C); color: #fff; }
.btn-action-fees.warning:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(212,160,48,0.3); }

.btn-action-fees.primary { background: linear-gradient(135deg, #182B48, #2A4570); color: #fff; }
.btn-action-fees.primary:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(24,43,72,0.3); }

.table-card-fees {
    background: #fff;
    border-radius: 20px;
    box-shadow: 0 4px 25px rgba(0,0,0,0.05);
    border: 1px solid rgba(212,160,48,0.1);
    overflow: hidden;
}

.table-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 25px;
    border-bottom: 2px solid rgba(212,160,48,0.1);
    background: linear-gradient(135deg, rgba(24,43,72,0.02), rgba(26,51,86,0.02));
}

.table-title {
    display: flex;
    align-items: center;
    gap: 15px;
    font-size: 17px;
    font-weight: 700;
    color: #182B48;
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

.records-count-fe {
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

.records-count-fe i { color: #d4a030; }

.data-table-fees { width: 100%; border-collapse: collapse; }
.data-table-fees thead { background: linear-gradient(135deg, rgba(24,43,72,0.03), rgba(42,69,112,0.03)); }
.data-table-fees th {
    padding: 16px 20px;
    font-size: 12px;
    font-weight: 700;
    color: #182B48;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-bottom: 2px solid rgba(212,160,48,0.15);
}
.data-table-fees td {
    padding: 16px 20px;
    font-size: 14px;
    color: #2c3e50;
    border-bottom: 1px solid #f0f4f8;
    vertical-align: middle;
}
.data-table-fees tbody tr { transition: all 0.3s; }
.data-table-fees tbody tr:hover { background: linear-gradient(135deg, rgba(212,160,48,0.03), rgba(180,128,44,0.03)); }

.user-cell { display: flex; align-items: center; gap: 12px; }
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
.user-avatar.gold { background: linear-gradient(135deg, #d4a030, #B4802C); color: #fff; }
.user-name { font-weight: 600; color: #182B48; }

.student-cell { display: flex; align-items: center; gap: 8px; color: #182B48; }
.student-cell i { color: #d4a030; }

.category-badge {
    background: rgba(52,152,219,0.1);
    padding: 5px 12px;
    border-radius: 8px;
    font-size: 12px;
    color: #3498db;
    font-weight: 600;
}

.amount-cell { direction: ltr; text-align: right; }
.amount { font-weight: 700; color: #182B48; font-size: 16px; }
.currency { color: #7f8c8d; font-size: 12px; margin-right: 4px; }

.status-badge {
    display: inline-flex;
    align-items: center;
    padding: 7px 14px;
    border-radius: 10px;
    font-size: 13px;
    font-weight: 600;
}
.status-badge.paid { background: rgba(39,174,96,0.1); color: #27ae60; }
.status-badge.unpaid { background: rgba(192,57,43,0.1); color: #c0392b; }

.date-cell { color: #7f8c8d; font-size: 13px; }

.action-buttons { display: flex; gap: 8px; }
.action-btn-fees {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
}
.action-btn-fees.success { background: rgba(39,174,96,0.1); color: #27ae60; }
.action-btn-fees.success:hover { background: #27ae60; color: #fff; }
.action-btn-fees.delete { background: rgba(192,57,43,0.1); color: #c0392b; }
.action-btn-fees.delete:hover { background: #c0392b; color: #fff; }

.table-footer { padding: 20px 25px; border-top: 1px solid #f0f4f8; }
.table-footer .pagination { margin: 0; gap: 8px; }
.table-footer .page-link {
    padding: 10px 16px;
    border-radius: 10px;
    font-size: 14px;
    border: 2px solid #e9ecef;
    color: #182B48;
    transition: all 0.3s;
}
.table-footer .page-item.active .page-link {
    background: linear-gradient(135deg, #182B48, #2A4570);
    border-color: #182B48;
    color: #fff;
}

.empty-state-fees {
    text-align: center;
    padding: 70px 20px;
}
.empty-icon-wrapper { position: relative; display: inline-block; margin-bottom: 25px; }
.empty-icon {
    width: 100px;
    height: 100px;
    background: linear-gradient(135deg, rgba(24,43,72,0.1), rgba(42,69,112,0.1));
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 40px;
    color: #d4a030;
    position: relative;
    z-index: 1;
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
.empty-state-fees h5 { color: #182B48; font-size: 20px; font-weight: 700; margin-bottom: 10px; }
.empty-state-fees p { color: #7f8c8d; margin-bottom: 25px; font-size: 15px; }

.modal-content { border-radius: 16px; border: none; }
.modal-header {
    padding: 20px 25px;
    background: linear-gradient(135deg, #182B48, #2A4570);
    color: #fff;
    border-radius: 16px 16px 0 0;
}
.modal-title { font-weight: 700; font-size: 18px; }
.btn-close { filter: invert(1); }
.modal-body { padding: 25px; }
.form-group { margin-bottom: 20px; }
.form-label { display: block; font-weight: 600; color: #182B48; margin-bottom: 8px; font-size: 14px; }
.form-control, .form-select {
    width: 100%;
    padding: 12px 16px;
    border-radius: 12px;
    border: 2px solid #e9ecef;
    font-size: 14px;
    transition: all 0.3s ease;
}
.form-control:focus, .form-select:focus {
    border-color: #d4a030;
    box-shadow: 0 0 0 4px rgba(212,160,48,0.15);
    outline: none;
}
.text-danger { color: #c0392b; }
.form-check-input:checked { background-color: #182B48; border-color: #182B48; }
.alert-info {
    background: rgba(52,152,219,0.1);
    border: 1px solid rgba(52,152,219,0.2);
    color: #3498db;
    padding: 12px 16px;
    border-radius: 10px;
}
.modal-footer { padding: 15px 25px; border-top: 1px solid #f0f4f8; }
.btn { display: flex; align-items: center; gap: 8px; padding: 12px 25px; border-radius: 12px; font-weight: 600; font-size: 14px; transition: all 0.3s ease; border: none; cursor: pointer; }
.btn-primary { background: linear-gradient(135deg, #182B48, #2A4570); color: #fff; }
.btn-primary:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(24,43,72,0.3); }
.btn-warning { background: linear-gradient(135deg, #d4a030, #B4802C); color: #fff; }
.btn-warning:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(212,160,48,0.3); }
.btn-outline { background: transparent; color: #7f8c8d; border: 2px solid #e9ecef; }
.btn-outline:hover { border-color: #182B48; color: #182B48; }

/* Mobile responsive */
@media (max-width: 768px) {
    .islamic-fees-header { height: 56px; margin-bottom: 16px; }
    .header-bismillah { font-size: 16px; letter-spacing: 1px; }
    .header-decoration-left, .header-decoration-right { display: none; }

    .page-header-fees {
        flex-direction: column;
        align-items: stretch;
        padding: 18px 20px;
        gap: 14px;
    }
    .header-content { gap: 12px; }
    .header-icon { width: 52px; height: 52px; font-size: 22px; border-radius: 14px; }
    .header-text h4 { font-size: 18px; }
    .header-text p { font-size: 12px; }
    .header-actions { flex-wrap: wrap; gap: 8px; }
    .btn-action-header { padding: 9px 14px; font-size: 12px; }

    .stat-card-fees { padding: 18px; }
    .stat-icon { width: 48px; height: 48px; font-size: 20px; }
    .stat-number { font-size: 26px; }
    .stat-label { font-size: 12px; }
    .stat-decoration { font-size: 36px; }

    .filter-form { padding: 16px; }
    .form-select-fe { padding: 10px 12px; font-size: 13px; }
    .btn-search-fe, .btn-reset-fe { padding: 10px; }

    .actions-bar-fees {
        flex-direction: column;
        align-items: stretch;
        gap: 12px;
        padding: 14px 16px;
    }
    .actions-right { flex-wrap: wrap; gap: 8px; }
    .actions-right .btn-action-fees { flex: 1 1 calc(50% - 4px); justify-content: center; padding: 10px 12px; font-size: 12px; }
    .actions-left { text-align: center; }

    /* Keep the table layout like desktop; allow horizontal scroll inside the card */
    .table-responsive { overflow-x: auto; -webkit-overflow-scrolling: touch; }
    .data-table-fees { min-width: 760px; }
    .data-table-fees th, .data-table-fees td { padding: 10px 8px; font-size: 12px; }
    .user-avatar { width: 32px; height: 32px; font-size: 13px; }
    .user-name { font-size: 12px; }
    .category-badge { font-size: 11px; padding: 4px 8px; }
    .status-badge { font-size: 11px; padding: 5px 10px; }
    .action-btn-fees { width: 32px; height: 32px; }

    .table-header { flex-direction: column; align-items: flex-start; gap: 10px; padding: 16px 18px; }
    .table-footer { padding: 14px 16px; }
    .table-footer .page-link { padding: 8px 12px; font-size: 12px; }
}
</style>

@push('scripts')
<script>
let addPaymentModal, bulkCreateModal;

function resetStudentSelect(select, message) {
    select.innerHTML = '';
    const opt = document.createElement('option');
    opt.value = '';
    opt.textContent = message;
    select.appendChild(opt);
}

document.addEventListener('DOMContentLoaded', function() {
    addPaymentModal = new bootstrap.Modal(document.getElementById('addPaymentModal'));
    bulkCreateModal = new bootstrap.Modal(document.getElementById('bulkCreateModal'));

    document.getElementById('addPaymentForm').addEventListener('submit', function(e) {
        e.preventDefault();
        submitAddPayment();
    });

    document.getElementById('is_paid').addEventListener('change', function() {
        const paidAtContainer = document.getElementById('paid_at_container');
        const paidAtInput = document.getElementById('paid_at');
        const modalTitle = document.getElementById('addPaymentModalTitle');
        if (this.checked) {
            paidAtContainer.style.display = 'block';
            paidAtInput.required = true;
            modalTitle.innerHTML = '<i class="fas fa-plus-circle me-2"></i>تسجيل دفعة';
        } else {
            paidAtContainer.style.display = 'none';
            paidAtInput.required = false;
            paidAtInput.value = '';
            modalTitle.innerHTML = '<i class="fas fa-plus-circle me-2"></i>تسجيل دفعة غير مدفوعة';
        }
    });

    document.getElementById('payment_parent_id').addEventListener('change', function() {
        const parentId = this.value;
        const studentSelect = document.getElementById('payment_student_id');
        if (!parentId) { resetStudentSelect(studentSelect, 'اختر الطالب'); return; }
        resetStudentSelect(studentSelect, 'جاري التحميل...');
        const url = @json(route('admin.billing.studentsForParent', ['parentId' => '__PARENT_ID__'])).replace('__PARENT_ID__', parentId);
        fetch(url)
            .then(response => response.json())
            .then(students => {
                resetStudentSelect(studentSelect, 'اختر الطالب');
                students.forEach(student => {
                    const opt = document.createElement('option');
                    opt.value = student.id;
                    opt.textContent = student.name;
                    studentSelect.appendChild(opt);
                });
            })
            .catch(() => resetStudentSelect(studentSelect, 'حدث خطأ'));
    });

    document.getElementById('bulkCreateForm').addEventListener('submit', function(e) {
        e.preventDefault();
        submitBulkCreate();
    });

    document.getElementById('bulk_all_students').addEventListener('change', function() {
        const searchContainer = document.getElementById('bulk_student_search_container');
        if (this.checked) { searchContainer.style.display = 'none'; }
        else { searchContainer.style.display = 'block'; loadAllStudentsForBulk(); }
        updateBulkSummary();
    });

    document.getElementById('bulk_year').addEventListener('change', updateBulkSummary);
    document.getElementById('bulk_month').addEventListener('change', updateBulkSummary);
    document.getElementById('bulk_student_search').addEventListener('input', filterStudentList);

    document.getElementById('selectAll').addEventListener('change', function() {
        document.querySelectorAll('.fee-checkbox').forEach(cb => cb.checked = this.checked);
        updateSelectedCount();
    });

    document.querySelectorAll('.fee-checkbox').forEach(cb => {
        cb.addEventListener('change', updateSelectedCount);
    });
});

function updateSelectedCount() {
    const checked = document.querySelectorAll('.fee-checkbox:checked').length;
    document.getElementById('selectedCount').textContent = checked + ' محدد';
}

function openAddPaymentModal() {
    document.getElementById('addPaymentForm').reset();
    const studentSelect = document.getElementById('payment_student_id');
    resetStudentSelect(studentSelect, 'اختر الطالب');
    document.getElementById('is_paid').dispatchEvent(new Event('change'));
    addPaymentModal.show();
}

async function submitAddPayment() {
    const form = document.getElementById('addPaymentForm');
    const formData = new FormData(form);
    try {
        const response = await fetch('{{ route('admin.billing.recordPayment') }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
            body: formData
        });
        const data = await response.json();
        if (data.success) { addPaymentModal.hide(); location.reload(); }
        else { alert(data.message || 'حدث خطأ'); }
    } catch (error) { alert('حدث خطأ أثناء حفظ البيانات'); }
}

let allStudentsCache = null;

function openBulkCreateModal() {
    document.getElementById('bulkCreateForm').reset();
    document.getElementById('bulk_all_students').checked = true;
    document.getElementById('bulk_student_search_container').style.display = 'none';
    document.getElementById('bulk_student_list').innerHTML = '';
    document.getElementById('bulk_student_count').textContent = '';
    document.getElementById('bulk_summary').style.display = 'none';
    allStudentsCache = null;
    bulkCreateModal.show();
}

async function loadAllStudentsForBulk() {
    if (allStudentsCache) { renderStudentList(allStudentsCache); return; }
    allStudentsCache = window.__STUDENTS_FOR_BULK__ || [];
    renderStudentList(allStudentsCache);
}

function renderStudentList(students) {
    const container = document.getElementById('bulk_student_list');
    if (!students || students.length === 0) { container.innerHTML = '<div class="text-muted">لا يوجد طلاب</div>'; return; }
    const escapeHtml = (text) => text.replace(/[&<>"']/g, (m) => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":"&#39;"}[m]));
    container.innerHTML = students.map(student =>
        '<div class="form-check">' +
            '<input type="checkbox" class="form-check-input student-check" value="' + student.id + '" id="student_' + student.id + '">' +
            '<label class="form-check-label" for="student_' + student.id + '">' + escapeHtml(student.name) + '</label>' +
        '</div>'
    ).join('');
    container.querySelectorAll('.student-check').forEach(cb => cb.addEventListener('change', updateBulkSummary));
    updateBulkSummary();
}

function filterStudentList() {
    const searchTerm = document.getElementById('bulk_student_search').value.toLowerCase().trim();
    document.querySelectorAll('#bulk_student_list > div').forEach(item => {
        const text = item.querySelector('label').textContent.toLowerCase();
        item.style.display = text.includes(searchTerm) ? 'block' : 'none';
    });
}

function updateBulkSummary() {
    const year = document.getElementById('bulk_year').value;
    const month = document.getElementById('bulk_month').value;
    const monthName = document.querySelector('#bulk_month option[value="' + month + '"]').textContent;
    const isAllStudents = document.getElementById('bulk_all_students').checked;
    const checkedCount = document.querySelectorAll('#bulk_student_list .student-check:checked').length;
    const totalCount = allStudentsCache ? allStudentsCache.length : 0;
    const count = isAllStudents ? totalCount : checkedCount;
    document.getElementById('bulk_summary_text').textContent = 'سيتم إنشاء سجلات لـ ' + count + ' طالب لشهر ' + monthName + '/' + year;
    document.getElementById('bulk_summary').style.display = 'block';
}

async function submitBulkCreate() {
    const formData = new FormData();
    formData.append('_token', '{{ csrf_token() }}');
    formData.append('year', document.getElementById('bulk_year').value);
    formData.append('month', document.getElementById('bulk_month').value);
    if (!document.getElementById('bulk_all_students').checked) {
        const selectedIds = Array.from(document.querySelectorAll('#bulk_student_list .student-check:checked')).map(cb => cb.value);
        if (selectedIds.length === 0) { alert('الرجاء اختيار طالب واحد على الأقل'); return; }
        selectedIds.forEach(id => formData.append('student_ids[]', id));
    }
    if (!confirm('هل أنت متأكد من إنشاء سجلات غير مدفوعة بالجملة؟')) return;
    try {
        const response = await fetch('{{ route('admin.billing.createBulkUnpaid') }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
            body: formData
        });
        const data = await response.json();
        if (data.success) { bulkCreateModal.hide(); alert(data.message); location.reload(); }
        else { alert(data.message || 'حدث خطأ'); }
    } catch (error) { alert('حدث خطأ أثناء إنشاء السجلات'); }
}

async function markPaid(feeId) {
    if (!confirm('هل أنت متأكد من تعيين هذا السجل كمدفوع؟')) return;
    try {
        const response = await fetch('{{ route('admin.billing.markPaid') }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ fee_id: feeId })
        });
        const data = await response.json();
        if (data.success) { location.reload(); } else { alert(data.message || 'حدث خطأ'); }
    } catch (error) { alert('حدث خطأ أثناء تحديث حالة الدفع'); }
}

async function deleteFee(feeId) {
    if (!confirm('هل أنت متأكد من حذف هذا السجل؟')) return;
    try {
        const response = await fetch('{{ route('admin.billing.deleteFee') }}', {
            method: 'DELETE',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body: JSON.stringify({ fee_id: feeId })
        });
        const data = await response.json();
        if (data.success) { location.reload(); } else { alert(data.message || 'حدث خطأ'); }
    } catch (error) { alert('حدث خطأ أثناء حذف السجل'); }
}

function markSelectedPaid() {
    const checked = document.querySelectorAll('.fee-checkbox:checked');
    if (checked.length === 0) { alert('الرجاء تحديد سجلات أولاً'); return; }
    if (!confirm('هل أنت متأكد من تعيين ' + checked.length + ' سجل كمدفوع؟')) return;
    document.getElementById('bulkForm').submit();
}
</script>
@endpush
@endsection