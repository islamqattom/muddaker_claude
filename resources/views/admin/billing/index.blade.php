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
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0"><i class="fas fa-money-bill me-2"></i>الرسوم الشهرية</h6>
        <a href="{{ route('admin.billing.settings') }}" class="btn btn-outline-primary btn-sm">
            <i class="fas fa-cog me-1"></i>إعدادات الفوترة
        </a>
    </div>
    <div class="card-body">
        {{-- Filters --}}
        <form method="GET" class="row g-3 mb-4">
            <div class="col-md-2">
                <label class="form-label">السنة</label>
                <select name="year" class="form-select">
                    @foreach($years as $year)
                        <option value="{{ $year }}" {{ request('year') == $year ? 'selected' : '' }}>{{ $year }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">الشهر</label>
                <select name="month" class="form-select">
                    <option value="">الكل</option>
                    @foreach($months as $num => $name)
                        <option value="{{ $num }}" {{ request('month') == $num ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">ولي الأمر</label>
                <select name="parent_id" class="form-select">
                    <option value="">الكل</option>
                    @foreach($parents as $parent)
                        <option value="{{ $parent->id }}" {{ request('parent_id') == $parent->id ? 'selected' : '' }}>
                            {{ $parent->user->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">الحالة</label>
                <select name="status" class="form-select">
                    <option value="">الكل</option>
                    <option value="unpaid" {{ request('status') == 'unpaid' ? 'selected' : '' }}>غير مدفوع</option>
                    <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>مدفوع</option>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2"><i class="fas fa-search"></i></button>
                <a href="{{ route('admin.billing.index') }}" class="btn btn-outline-secondary"><i class="fas fa-times"></i></a>
            </div>
        </form>

        {{-- Bulk Actions --}}
        <form id="bulkForm" method="POST" action="{{ route('admin.billing.markPaidBulk') }}">
            @csrf
            <div class="d-flex justify-content-between mb-3">
                <div>
                    <button type="button" class="btn btn-success btn-sm" onclick="markSelectedPaid()">
                        <i class="fas fa-check me-1"></i>تعيين المحدد كمدفوع
                    </button>
                </div>
                <div>
                    <a href="{{ route('admin.billing.history') }}" class="btn btn-outline-info btn-sm">
                        <i class="fas fa-history me-1"></i>سجل المدفوعات
                    </a>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="selectAll"></th>
                            <th>ولي الأمر</th>
                            <th>الطالب</th>
                            <th>الشهر</th>
                            <th>الحالة</th>
                            <th>تاريخ الدفع</th>
                            <th>مدفوع بواسطة</th>
                            <th>إجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($fees as $fee)
                        <tr>
                            <td><input type="checkbox" name="fee_ids[]" value="{{ $fee->id }}" class="fee-checkbox"></td>
                            <td>{{ $fee->parent->user->name ?? '-' }}</td>
                            <td>{{ $fee->student->user->name ?? '-' }}</td>
                            <td>{{ $months[$fee->month] ?? $fee->month }} / {{ $fee->year }}</td>
                            <td>
                                @if($fee->status === 'paid')
                                    <span class="badge bg-success">مدفوع</span>
                                @else
                                    <span class="badge bg-danger">غير مدفوع</span>
                                @endif
                            </td>
                            <td>{{ $fee->paid_at ? $fee->paid_at->format('d/m/Y') : '-' }}</td>
                            <td>{{ $fee->paidBy->name ?? '-' }}</td>
                            <td>
                                @if($fee->status === 'unpaid')
                                    <button type="button" class="btn btn-success btn-sm" onclick="markPaid({{ $fee->id }})">
                                        <i class="fas fa-check"></i>
                                    </button>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">لا توجد سجلات</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </form>

        {{ $fees->withQueryString()->links() }}
    </div>
</div>
@endsection

@section('scripts')
<script>
function markPaid(feeId) {
    if (!confirm('هل أنت متأكد من تعيين هذا السجل كمدفوع؟')) return;

    fetch('{{ route('admin.billing.markPaid') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ fee_id: feeId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    });
}

function markSelectedPaid() {
    const checked = document.querySelectorAll('.fee-checkbox:checked');
    if (checked.length === 0) {
        alert('الرجاء تحديد سجلات أولاً');
        return;
    }

    if (!confirm('هل أنت متأكد من تعيين ' + checked.length + ' سجل كمدفوع؟')) return;

    document.getElementById('bulkForm').submit();
}

document.getElementById('selectAll').addEventListener('change', function() {
    document.querySelectorAll('.fee-checkbox').forEach(cb => cb.checked = this.checked);
});
</script>
@endsection
