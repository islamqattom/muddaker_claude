@extends('layouts.app')
@section('title', 'فئات الرسوم')
@section('page-title', 'إدارة فئات الرسوم')
@section('role-label', 'مدير')

@section('sidebar-menu')
@include('admin.partials.sidebar')
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.billing.index') }}">الرسوم الشهرية</a></li>
    <li class="breadcrumb-item active">فئات الرسوم</li>
@endsection

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="fas fa-tags me-2"></i>فئات الرسوم الشهرية</h6>
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#createCategoryModal">
                    <i class="fas fa-plus me-1"></i>إضافة فئة جديدة
                </button>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                @if($categories->isEmpty())
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-tags fa-3x mb-3"></i>
                        <p>لا توجد فئات رسوم حتى الآن</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>اسم الفئة</th>
                                    <th>الرسوم الشهرية</th>
                                    <th>عدد الطلاب</th>
                                    <th>إجمالي الدخل الشهري</th>
                                    <th>الحالة</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($categories as $category)
                                    <tr>
                                        <td>{{ $category->name }}</td>
                                        <td>{{ number_format($category->amount, 2) }} دينار</td>
                                        <td>{{ $category->students_count }}</td>
                                        <td>{{ number_format($category->total_monthly_revenue, 2) }} دينار</td>
                                        <td>
                                            @if($category->is_active)
                                                <span class="badge bg-success">مفعّل</span>
                                            @else
                                                <span class="badge bg-secondary">معطّل</span>
                                            @endif
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-outline-primary me-1"
                                                onclick="editCategory({{ $category->id }}, {{ json_encode($category->name) }}, {{ $category->amount }})">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-{{ $category->is_active ? 'warning' : 'success' }} me-1"
                                                onclick="toggleActive({{ $category->id }})">
                                                <i class="fas fa-{{ $category->is_active ? 'ban' : 'check' }}"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger"
                                                onclick="deleteCategory({{ $category->id }})"
                                                @if($category->students_count > 0) disabled title="مرتبط بطلاب" @endif>
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

{{-- Create Modal --}}
<div class="modal fade" id="createCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">إضافة فئة رسوم جديدة</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="createCategoryForm" method="POST" action="{{ route('admin.billing.feeCategories.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">اسم الفئة <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required placeholder="مثال: فئة standard">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">الرسوم الشهرية (دينار) <span class="text-danger">*</span></label>
                        <input type="number" name="amount" class="form-control" required min="0" step="0.01" placeholder="مثال: 10">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary">حفظ</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit Modal --}}
<div class="modal fade" id="editCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">تعديل فئة الرسوم</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editCategoryForm" method="POST" action="{{ route('admin.billing.feeCategories.update', 'REPLACE_ID') }}">
                @csrf
                @method('PUT')
                <input type="hidden" name="id" id="edit_category_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">اسم الفئة <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="edit_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">الرسوم الشهرية (دينار) <span class="text-danger">*</span></label>
                        <input type="number" name="amount" id="edit_amount" class="form-control" required min="0" step="0.01">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary">تحديث</button>
                </div>
            </form>
        </div>
    </div>
</div>

@section('script')
<script>
const CSRF = document.querySelector('meta[name="csrf-token"]').content;

function showFormError(formId, message) {
    const form = document.getElementById(formId);
    const existing = form.querySelector('.alert-danger');
    if (existing) existing.remove();
    const alertDiv = document.createElement('div');
    alertDiv.className = 'alert alert-danger mt-2';
    alertDiv.textContent = message;
    form.querySelector('.modal-body').prepend(alertDiv);
}

function clearFormError(formId) {
    const form = document.getElementById(formId);
    const existing = form.querySelector('.alert-danger');
    if (existing) existing.remove();
}

function setButtonLoading(button, loading) {
    if (loading) {
        button.disabled = true;
        const spinner = document.createElement('span');
        spinner.className = 'spinner-border spinner-border-sm me-1';
        button.prepend(spinner);
        const text = document.createTextNode('جارٍ الحفظ...');
        button.append(text);
    } else {
        button.disabled = false;
        const spinner = button.querySelector('.spinner-border');
        if (spinner) spinner.remove();
        button.childNodes.forEach(n => { if (n.nodeType === 3) n.textContent = 'حفظ'; });
    }
}

// Create
document.getElementById('createCategoryForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const form = this;
    const submitBtn = form.querySelector('[type="submit"]');
    clearFormError('createCategoryForm');
    setButtonLoading(submitBtn, true);

    try {
        const response = await fetch(form.action, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF },
            body: new FormData(form)
        });
        const data = await response.json();
        if (data.success) {
            location.reload();
        } else {
            showFormError('createCategoryForm', data.message || 'حدث خطأ');
        }
    } catch (err) {
        showFormError('createCategoryForm', 'حدث خطأ في الاتصال');
    } finally {
        setButtonLoading(submitBtn, false);
    }
});

// Edit - populate modal
function editCategory(id, name, amount) {
    document.getElementById('edit_category_id').value = id;
    document.getElementById('edit_name').value = name;
    document.getElementById('edit_amount').value = amount;
    clearFormError('editCategoryForm');
    new bootstrap.Modal(document.getElementById('editCategoryModal')).show();
}

// Update
document.getElementById('editCategoryForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const form = this;
    const submitBtn = form.querySelector('[type="submit"]');
    const id = document.getElementById('edit_category_id').value;
    clearFormError('editCategoryForm');

    if (!id) {
        showFormError('editCategoryForm', 'معرّف الفئة غير صالح');
        return;
    }

    if (!form.action.includes('REPLACE_ID')) {
        showFormError('editCategoryForm', 'خطأ في تكوين النموذج');
        return;
    }

    const url = form.action.replace('REPLACE_ID', id);
    setButtonLoading(submitBtn, true);

    try {
        const response = await fetch(url, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF },
            body: new FormData(form)
        });
        const data = await response.json();
        if (data.success) {
            location.reload();
        } else {
            showFormError('editCategoryForm', data.message || 'حدث خطأ');
        }
    } catch (err) {
        showFormError('editCategoryForm', 'حدث خطأ في الاتصال');
    } finally {
        setButtonLoading(submitBtn, false);
    }
});

// Toggle active
async function toggleActive(id) {
    if (!confirm('هل أنت متأكد من تغيير حالة هذه الفئة؟')) return;
    try {
        const response = await fetch('/admin/billing/fee-categories/' + id + '/toggle-active', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF }
        });
        const data = await response.json();
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'حدث خطأ');
        }
    } catch (err) {
        alert('حدث خطأ في الاتصال');
    }
}

// Delete
async function deleteCategory(id) {
    if (!confirm('هل أنت متأكد من حذف هذه الفئة؟')) return;
    try {
        const response = await fetch('/admin/billing/fee-categories/' + id, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': CSRF }
        });
        const data = await response.json();
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'حدث خطأ');
        }
    } catch (err) {
        alert('حدث خطأ في الاتصال');
    }
}
</script>
@endsection
