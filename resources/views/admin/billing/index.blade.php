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
<script>
window.__STUDENTS_FOR_BULK__ = @json($studentsForBulk ?? []);
</script>
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0"><i class="fas fa-money-bill me-2"></i>الرسوم الشهرية</h6>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-primary btn-sm" onclick="openAddPaymentModal()">
                <i class="fas fa-plus me-1"></i>تسجيل دفعة سابقة
            </button>
            <button type="button" class="btn btn-warning btn-sm" onclick="openBulkCreateModal()">
                <i class="fas fa-layer-group me-1"></i>إنشاء رسوم غير مدفوعة
            </button>
            <a href="{{ route('admin.billing.settings') }}" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-cog me-1"></i>إعدادات الفوترة
            </a>
        </div>
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
            <div class="col-md-2">
                <label class="form-label">فئة الرسوم</label>
                <select name="fee_category_id" class="form-select">
                    <option value="">الكل</option>
                    @foreach($feeCategories as $cat)
                        <option value="{{ $cat->id }}" {{ request('fee_category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
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
                            <th>الرسوم</th>
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
                            <td>{{ $fee->fee_amount ? number_format($fee->fee_amount, 2) . ' د.ع' : '-' }}</td>
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
                                <button type="button" class="btn btn-danger btn-sm" onclick="deleteFee({{ $fee->id }})">
                                    <i class="fas fa-trash"></i>
                                </button>
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

{{-- Add Old Payment Modal --}}
<div class="modal fade" id="addPaymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addPaymentModalTitle">تسجيل دفعة سابقة</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="addPaymentForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">ولي الأمر</label>
                        <select name="parent_id" id="payment_parent_id" class="form-select" required>
                            <option value="">اختر ولي الأمر</option>
                            @foreach($parents as $parent)
                                <option value="{{ $parent->id }}">{{ $parent->user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">الطالب</label>
                        <select name="student_id" id="payment_student_id" class="form-select" required>
                            <option value="">اختر الطالب</option>
                        </select>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">السنة</label>
                            <select name="year" class="form-select" required>
                                @foreach($years as $year)
                                    <option value="{{ $year }}" {{ $year == now()->year ? 'selected' : '' }}>{{ $year }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">الشهر</label>
                            <select name="month" class="form-select" required>
                                @foreach($months as $num => $name)
                                    <option value="{{ $num }}">{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="form-check">
                            <input type="hidden" name="is_paid" value="0">
                            <input type="checkbox" class="form-check-input" id="is_paid" name="is_paid" value="1" checked>
                            <label class="form-check-label" for="is_paid">تسجيل كمدفوع</label>
                        </div>
                    </div>
                    <div class="mb-3" id="paid_at_container">
                        <label class="form-label">تاريخ الدفع</label>
                        <input type="date" name="paid_at" id="paid_at" class="form-control" max="{{ date('Y-m-d') }}">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary">حفظ</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Bulk Create Unpaid Modal --}}
<div class="modal fade" id="bulkCreateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">إنشاء رسوم غير مدفوعة بالجملة</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="bulkCreateForm">
                @csrf
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">السنة</label>
                            <select name="year" id="bulk_year" class="form-select" required>
                                @foreach($years as $year)
                                    <option value="{{ $year }}" {{ $year == now()->year ? 'selected' : '' }}>{{ $year }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">الشهر</label>
                            <select name="month" id="bulk_month" class="form-select" required>
                                @foreach($months as $num => $name)
                                    <option value="{{ $num }}">{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
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
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-warning">إنشاء السجلات</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let addPaymentModal;

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
            modalTitle.textContent = 'تسجيل دفعة سابقة';
        } else {
            paidAtContainer.style.display = 'none';
            paidAtInput.required = false;
            paidAtInput.value = '';
            modalTitle.textContent = 'تسجيل دفعة غير مدفوعة';
        }
    });

    document.getElementById('payment_parent_id').addEventListener('change', function() {
        const parentId = this.value;
        const studentSelect = document.getElementById('payment_student_id');

        if (!parentId) {
            resetStudentSelect(studentSelect, 'اختر الطالب');
            return;
        }

        resetStudentSelect(studentSelect, 'جاري التحميل...');

        const url = @json(route('admin.billing.studentsForParent', ['parentId' => '__PARENT_ID__'])).replace('__PARENT_ID__', parentId);
        fetch(url)
            .then(response => {
                if (!response.ok) {
                    throw new Error('HTTP error: ' + response.status);
                }
                return response.json();
            })
            .then(students => {
                resetStudentSelect(studentSelect, 'اختر الطالب');
                students.forEach(student => {
                    const opt = document.createElement('option');
                    opt.value = student.id;
                    opt.textContent = student.name;
                    studentSelect.appendChild(opt);
                });
            })
            .catch(() => {
                resetStudentSelect(studentSelect, 'حدث خطأ');
            });
    });

    document.getElementById('bulkCreateForm').addEventListener('submit', function(e) {
        e.preventDefault();
        submitBulkCreate();
    });

    document.getElementById('bulk_all_students').addEventListener('change', function() {
        const searchContainer = document.getElementById('bulk_student_search_container');
        if (this.checked) {
            searchContainer.style.display = 'none';
        } else {
            searchContainer.style.display = 'block';
            loadAllStudentsForBulk();
        }
        updateBulkSummary();
    });

    document.getElementById('bulk_year').addEventListener('change', updateBulkSummary);
    document.getElementById('bulk_month').addEventListener('change', updateBulkSummary);
    document.getElementById('bulk_student_search').addEventListener('input', filterStudentList);
});

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
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
            body: formData
        });

        const contentType = response.headers.get('content-type') || '';
        if (!contentType.includes('application/json')) {
            if (!response.ok) {
                throw new Error('Server error: ' + response.status);
            }
            throw new Error('Unexpected response format');
        }

        const data = await response.json();

        if (data.success) {
            addPaymentModal.hide();
            location.reload();
        } else {
            alert(data.message || 'حدث خطأ');
        }
    } catch (error) {
        alert(error.message === 'Server error: 302'
            ? 'انتهت الجلسة، يرجى تحديث الصفحة والمحاولة مرة أخرى'
            : 'حدث خطأ أثناء حفظ البيانات');
    }
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
    const escapeHtml = (text) => text.replace(/[&<>"']/g, (m) => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":"&#39;"}[m]));
    container.innerHTML = students.map(student =>
        '<div class="form-check">' +
            '<input type="checkbox" class="form-check-input student-check" value="' + student.id + '" checked>' +
            '<label class="form-check-label">' + escapeHtml(student.name) + '</label>' +
        '</div>'
    ).join('');
    updateBulkSummary();
}

function filterStudentList() {
    const searchTerm = document.getElementById('bulk_student_search').value.toLowerCase();
    const checkboxes = document.querySelectorAll('#bulk_student_list .student-check');
    checkboxes.forEach((cb) => {
        const label = cb.nextElementSibling.textContent.toLowerCase();
        cb.closest('div').style.display = label.includes(searchTerm) ? 'block' : 'none';
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
    document.getElementById('bulk_summary_text').textContent =
        'سيتم إنشاء سجلات لـ ' + count + ' طالب لشهر ' + monthName + '/' + year;
    document.getElementById('bulk_summary').style.display = 'block';
}

async function submitBulkCreate() {
    const formData = new FormData();
    formData.append('_token', '{{ csrf_token() }}');
    formData.append('year', document.getElementById('bulk_year').value);
    formData.append('month', document.getElementById('bulk_month').value);

    if (!document.getElementById('bulk_all_students').checked) {
        const selectedIds = Array.from(document.querySelectorAll('#bulk_student_list .student-check:checked')).map(cb => cb.value);
        if (selectedIds.length === 0) {
            alert('الرجاء اختيار طالب واحد على الأقل');
            return;
        }
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
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ fee_id: feeId })
        });

        if (!response.ok) {
            throw new Error('Server error: ' + response.status);
        }

        const data = await response.json();
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'حدث خطأ');
        }
    } catch (error) {
        alert('حدث خطأ أثناء تحديث حالة الدفع');
    }
}

async function deleteFee(feeId) {
    if (!confirm('هل أنت متأكد من حذف هذا السجل؟')) return;

    try {
        const response = await fetch('{{ route('admin.billing.deleteFee') }}', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ fee_id: feeId })
        });

        if (!response.ok) {
            throw new Error('Server error: ' + response.status);
        }

        const data = await response.json();
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'حدث خطأ');
        }
    } catch (error) {
        alert('حدث خطأ أثناء حذف السجل');
    }
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
