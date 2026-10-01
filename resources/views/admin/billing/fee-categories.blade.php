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

{{-- Islamic Pattern Header --}}
<div class="islamic-categories-header">
    <div class="header-pattern-overlay"></div>
    <div class="header-bismillah">بِسْمِ اللَّهِ الرَّحْمَنِ الرَّحِيمِ</div>
</div>

{{-- Page Header --}}
<x-page-hero icon="fas fa-tags">
    <h1 class="page-banner-title">إدارة فئات الرسوم</h1>
    <p class="page-banner-sub">إضافة وتعديل فئات الرسوم الشهرية للطلاب</p>
    <x-slot:actions>
        <button type="button" class="btn-gold" onclick="openCreateModal()">
            <i class="fas fa-plus"></i>
            <span>إضافة فئة جديدة</span>
        </button>
    </x-slot:actions>
</x-page-hero>

{{-- Stats Cards --}}
<div class="stats-grid-categories mb-4">
    <div class="stat-card-category primary">
        <div class="stat-card-bg-pattern"></div>
        <div class="stat-content-wrapper">
            <div class="stat-icon">
                <i class="fas fa-tags"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number">{{ $categories->count() }}</div>
                <div class="stat-label">إجمالي الفئات</div>
            </div>
        </div>
        <div class="stat-decoration">
            <i class="fas fa-layer-group"></i>
        </div>
        <div class="stat-corner-decoration"></div>
    </div>
    <div class="stat-card-category success">
        <div class="stat-card-bg-pattern"></div>
        <div class="stat-content-wrapper">
            <div class="stat-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number">{{ $categories->where('is_active', true)->count() }}</div>
                <div class="stat-label">فئات مفعّلة</div>
            </div>
        </div>
        <div class="stat-decoration">
            <i class="fas fa-toggle-on"></i>
        </div>
        <div class="stat-corner-decoration"></div>
    </div>
    <div class="stat-card-category gold">
        <div class="stat-card-bg-pattern"></div>
        <div class="stat-content-wrapper">
            <div class="stat-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number">{{ $categories->sum('students_count') }}</div>
                <div class="stat-label">الطلاب المسجلين</div>
            </div>
        </div>
        <div class="stat-decoration">
            <i class="fas fa-user-friends"></i>
        </div>
        <div class="stat-corner-decoration"></div>
    </div>
    <div class="stat-card-category emerald">
        <div class="stat-card-bg-pattern"></div>
        <div class="stat-content-wrapper">
            <div class="stat-icon">
                <i class="fas fa-coins"></i>
            </div>
            <div class="stat-content">
                <div class="stat-number">{{ number_format($categories->sum('total_monthly_revenue'), 0) }}</div>
                <div class="stat-label">إجمالي الدخل الشهري</div>
            </div>
        </div>
        <div class="stat-decoration">
            <i class="fas fa-money-bill"></i>
        </div>
        <div class="stat-corner-decoration"></div>
    </div>
</div>

{{-- Categories Grid --}}
@if($categories->isEmpty())
    <div class="empty-state-category">
        <div class="empty-icon-wrapper">
            <div class="empty-icon">
                <i class="fas fa-tags"></i>
            </div>
            <div class="empty-icon-glow"></div>
        </div>
        <h5>لا توجد فئات رسوم</h5>
        <p>ابدأ بإضافة فئات رسوم جديدة</p>
        <button type="button" class="btn-add-category-empty" onclick="openCreateModal()">
            <i class="fas fa-plus me-2"></i>إضافة فئة جديدة
        </button>
    </div>
@else
    <div class="categories-grid-islamic">
        @foreach($categories as $category)
        <div class="category-card-islamic {{ $category->is_active ? '' : 'inactive' }}">
            <div class="category-header-islamic">
                <div class="category-icon-wrapper">
                    <div class="category-icon">
                        <i class="fas fa-tag"></i>
                    </div>
                </div>
                <div class="category-info">
                    <h5>{{ $category->name }}</h5>
                    <div class="category-price">
                        <span class="price">{{ number_format($category->amount, 0) }}</span>
                        <span class="currency">د.ع</span>
                        <span class="per-month">/ شهرياً</span>
                    </div>
                </div>
                <div class="category-status">
                    @if($category->is_active)
                        <span class="status-badge-active">
                            <i class="fas fa-check-circle me-1"></i>مفعّل
                        </span>
                    @else
                        <span class="status-badge-inactive">
                            <i class="fas fa-ban me-1"></i>معطّل
                        </span>
                    @endif
                </div>
            </div>
            <div class="category-body-islamic">
                <div class="category-stats">
                    <div class="stat-item-category">
                        <div class="stat-item-icon">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                        <div class="stat-item-info">
                            <span class="stat-value">{{ $category->students_count }}</span>
                            <span class="stat-label">طالب</span>
                        </div>
                    </div>
                    <div class="stat-item-category">
                        <div class="stat-item-icon gold">
                            <i class="fas fa-wallet"></i>
                        </div>
                        <div class="stat-item-info">
                            <span class="stat-value">{{ number_format($category->total_monthly_revenue, 0) }}</span>
                            <span class="stat-label">دخل شهري</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="category-footer-islamic">
                <button type="button" class="action-btn-category edit" onclick="editCategory({{ $category->id }}, {{ json_encode($category->name) }}, {{ $category->amount }})" title="تعديل">
                    <i class="fas fa-edit"></i>
                    <span>تعديل</span>
                </button>
                <button type="button" class="action-btn-category {{ $category->is_active ? 'warning' : 'success' }}" onclick="toggleActive({{ $category->id }})" title="{{ $category->is_active ? 'تعطيل' : 'تفعيل' }}">
                    <i class="fas fa-{{ $category->is_active ? 'ban' : 'check' }}"></i>
                    <span>{{ $category->is_active ? 'تعطيل' : 'تفعيل' }}</span>
                </button>
                <button type="button" class="action-btn-category delete" onclick="deleteCategory({{ $category->id }})" title="حذف" @if($category->students_count > 0) disabled @endif>
                    <i class="fas fa-trash"></i>
                    <span>حذف</span>
                </button>
            </div>
        </div>
        @endforeach
    </div>
@endif

{{-- Create Modal --}}
<div class="modal fade" id="createCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-plus-circle me-2"></i>إضافة فئة رسوم جديدة
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="createCategoryForm" method="POST" action="{{ route('admin.billing.feeCategories.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="form-group-modal">
                        <label class="form-label-modal">
                            <i class="fas fa-tag"></i>
                            اسم الفئة <span class="required">*</span>
                        </label>
                        <input type="text" name="name" class="form-control-modal" required placeholder="مثال: فئة قياسية">
                    </div>
                    <div class="form-group-modal">
                        <label class="form-label-modal">
                            <i class="fas fa-coins"></i>
                            الرسوم الشهرية (دينار) <span class="required">*</span>
                        </label>
                        <input type="number" name="amount" class="form-control-modal" required min="0" step="0.01" placeholder="مثال: 10">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-cancel-modal" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i>
                        <span>إلغاء</span>
                    </button>
                    <button type="submit" class="btn-save-modal">
                        <i class="fas fa-save"></i>
                        <span>حفظ</span>
                    </button>
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
                <h5 class="modal-title">
                    <i class="fas fa-edit me-2"></i>تعديل فئة الرسوم
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editCategoryForm" method="POST" action="{{ route('admin.billing.feeCategories.update', 'REPLACE_ID') }}">
                @csrf
                @method('PUT')
                <input type="hidden" name="id" id="edit_category_id">
                <div class="modal-body">
                    <div class="form-group-modal">
                        <label class="form-label-modal">
                            <i class="fas fa-tag"></i>
                            اسم الفئة <span class="required">*</span>
                        </label>
                        <input type="text" name="name" id="edit_name" class="form-control-modal" required>
                    </div>
                    <div class="form-group-modal">
                        <label class="form-label-modal">
                            <i class="fas fa-coins"></i>
                            الرسوم الشهرية (دينار) <span class="required">*</span>
                        </label>
                        <input type="number" name="amount" id="edit_amount" class="form-control-modal" required min="0" step="0.01">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-cancel-modal" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i>
                        <span>إلغاء</span>
                    </button>
                    <button type="submit" class="btn-save-modal">
                        <i class="fas fa-save"></i>
                        <span>تحديث</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.islamic-categories-header {
    position: relative;
    height: 70px;
    margin-bottom: 16px;
    padding: 16px 22px;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    border-radius: 14px;
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
.header-icon-wrapper { position: relative; }
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
.add-btn-category {
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
    cursor: pointer;
}
.add-btn-category:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 30px rgba(212,160,48,0.4);
    color: #fff;
}
.stats-grid-categories {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
}
@media (max-width: 992px) {
.stats-grid-categories { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 576px) {
.stats-grid-categories { grid-template-columns: 1fr; }
}
.stat-card-category {
    position: relative;
    padding: 16px;
    border-radius: 13px;
    overflow: hidden;
    transition: all 0.4s ease;
}
.stat-card-category:hover {
    transform: translateY(-8px);
    box-shadow: 0 20px 50px rgba(0,0,0,0.15);
}
.stat-card-category.primary { background: linear-gradient(135deg, #182B48 0%, #2A4570 100%); }
.stat-card-category.success { background: linear-gradient(135deg, #27ae60 0%, #2ecc71 100%); }
.stat-card-category.gold { background: linear-gradient(135deg, #d4a030 0%, #B4802C 100%); }
.stat-card-category.emerald { background: linear-gradient(135deg, #8e44ad 0%, #9b59b6 100%); }
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
.categories-grid-islamic {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
    gap: 25px;
}
.category-card-islamic {
    background: #fff;
    border-radius: 13px;
    overflow: hidden;
    box-shadow: 0 4px 25px rgba(0,0,0,0.05);
    border: 1px solid rgba(212,160,48,0.1);
    transition: all 0.4s ease;
}
.category-card-islamic:hover {
    transform: translateY(-8px);
    box-shadow: 0 15px 40px rgba(0,0,0,0.1);
}
.category-card-islamic.inactive { opacity: 0.7; }
.category-header-islamic {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 16px;
    background: linear-gradient(135deg, #182B48 0%, #2A4570 100%);
    position: relative;
    overflow: hidden;
}
.category-header-islamic::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #d4a030, #B4802C, #d4a030);
}
.category-icon-wrapper { position: relative; }
.category-icon {
    width: 60px;
    height: 60px;
    background: linear-gradient(135deg, #d4a030, #B4802C);
    border-radius: 13px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 26px;
    color: #fff;
    box-shadow: 0 4px 20px rgba(212,160,48,0.4);
    position: relative;
    z-index: 1;
}
.category-info { flex: 1; position: relative; z-index: 1; }
.category-info h5 { color: #fff; font-size: 18px; font-weight: 700; margin: 0 0 8px 0; }
.category-price { display: flex; align-items: baseline; gap: 4px; }
.price { color: #d4a030; font-size: 24px; font-weight: 800; }
.currency { color: rgba(255,255,255,0.7); font-size: 12px; }
.per-month { color: rgba(255,255,255,0.5); font-size: 12px; margin-right: 4px; }
.category-status { position: relative; z-index: 1; }
.status-badge-active {
    display: inline-flex;
    align-items: center;
    padding: 8px 16px;
    border-radius: 13px;
    font-size: 13px;
    font-weight: 600;
    background: rgba(39,174,96,0.2);
    color: #2ecc71;
}
.status-badge-inactive {
    display: inline-flex;
    align-items: center;
    padding: 8px 16px;
    border-radius: 13px;
    font-size: 13px;
    font-weight: 600;
    background: rgba(127,140,141,0.2);
    color: #95a5a6;
}
.category-body-islamic { padding: 16px; }
.category-stats { display: flex; gap: 15px; }
.stat-item-category {
    display: flex;
    align-items: center;
    gap: 12px;
    flex: 1;
    padding: 16px;
    background: linear-gradient(135deg, #f8f9fa, #f0f4f8);
    border-radius: 13px;
    transition: all 0.3s;
}
.stat-item-category:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.08);
}
.stat-item-icon {
    width: 45px;
    height: 45px;
    background: linear-gradient(135deg, #182B48, #2A4570);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #d4a030;
    font-size: 18px;
    flex-shrink: 0;
}
.stat-item-icon.gold { background: linear-gradient(135deg, #d4a030, #B4802C); color: #fff; }
.stat-item-info { display: flex; flex-direction: column; }
.stat-value { font-size: 18px; font-weight: 700; color: #182B48; }
.category-footer-islamic {
    display: flex;
    gap: 10px;
    padding: 16px 16px;
    border-top: 1px solid rgba(212,160,48,0.1);
    background: linear-gradient(135deg, rgba(24,43,72,0.02), rgba(26,51,86,0.02));
}
.action-btn-category {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 12px 15px;
    border-radius: 12px;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
    font-weight: 600;
    font-size: 13px;
}
.action-btn-category.edit { background: rgba(212,160,48,0.1); color: #d4a030; }
.action-btn-category.edit:hover { background: #d4a030; color: #fff; }
.action-btn-category.success { background: rgba(39,174,96,0.1); color: #27ae60; }
.action-btn-category.success:hover { background: #27ae60; color: #fff; }
.action-btn-category.warning { background: rgba(230,126,34,0.1); color: #e67e22; }
.action-btn-category.warning:hover { background: #e67e22; color: #fff; }
.action-btn-category.delete { background: rgba(192,57,43,0.1); color: #c0392b; }
.action-btn-category.delete:hover { background: #c0392b; color: #fff; }
.action-btn-category:disabled { opacity: 0.4; cursor: not-allowed; }
.empty-state-category {
    text-align: center;
    padding: 40px 16px;
    background: #fff;
    border-radius: 13px;
    box-shadow: 0 4px 25px rgba(0,0,0,0.05);
    border: 1px solid rgba(212,160,48,0.1);
}
.empty-icon-wrapper { position: relative; display: inline-block; margin-bottom: 16px; }
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
.empty-state-category h5 { color: #182B48; font-size: 18px; font-weight: 700; margin-bottom: 10px; }
.empty-state-category p { color: #7f8c8d; margin-bottom: 16px; font-size: 15px; }
.btn-add-category-empty {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 40px 16px;
    background: linear-gradient(135deg, #d4a030, #B4802C);
    color: #fff;
    border-radius: 13px;
    font-weight: 700;
    font-size: 14px;
    transition: all 0.4s ease;
    box-shadow: 0 4px 20px rgba(212,160,48,0.3);
    border: none;
    cursor: pointer;
}
.btn-add-category-empty:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 30px rgba(212,160,48,0.4);
    color: #fff;
}
.modal-content { border-radius: 13px; border: none; }
.modal-header {
    padding: 16px 16px;
    background: linear-gradient(135deg, #182B48, #2A4570);
    color: #fff;
    border-radius: 13px 13px 0 0;
}
.modal-title { font-weight: 700; font-size: 18px; }
.btn-close { filter: invert(1); }
.modal-body { padding: 16px; }
.form-group-modal { margin-bottom: 16px; }
.form-label-modal {
    display: flex;
    align-items: center;
    gap: 10px;
    font-weight: 600;
    color: #182B48;
    margin-bottom: 10px;
    font-size: 15px;
}
.form-label-modal i { color: #d4a030; }
.required { color: #c0392b; }
.form-control-modal {
    width: 100%;
    padding: 14px 16px;
    border-radius: 13px;
    border: 2px solid #e9ecef;
    font-size: 14px;
    transition: all 0.3s ease;
}
.form-control-modal:focus {
    border-color: #d4a030;
    box-shadow: 0 0 0 4px rgba(212,160,48,0.15);
    outline: none;
}
.modal-footer { padding: 15px 16px; border-top: 1px solid #f0f4f8; display: flex; gap: 12px; justify-content: flex-end; }
.btn-cancel-modal {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 12px 16px;
    background: #f8f9fa;
    color: #7f8c8d;
    border: 2px solid #e9ecef;
    border-radius: 12px;
    font-weight: 600;
    font-size: 14px;
    transition: all 0.3s ease;
    cursor: pointer;
}
.btn-cancel-modal:hover { border-color: #182B48; color: #182B48; }
.btn-save-modal {
    display: flex;
    align-items: center;
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
}
.btn-save-modal:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(24,43,72,0.3); }
</style>

@push('scripts')
<script>
const CSRF = document.querySelector('meta[name="csrf-token"]').content;

function openCreateModal() {
    document.getElementById('createCategoryForm').reset();
    new bootstrap.Modal(document.getElementById('createCategoryModal')).show();
}

function editCategory(id, name, amount) {
    document.getElementById('edit_category_id').value = id;
    document.getElementById('edit_name').value = name;
    document.getElementById('edit_amount').value = amount;
    const form = document.getElementById('editCategoryForm');
    form.action = form.action.replace('REPLACE_ID', id);
    new bootstrap.Modal(document.getElementById('editCategoryModal')).show();
}

async function toggleActive(id) {
    if (!confirm('هل أنت متأكد من تغيير حالة هذه الفئة؟')) return;
    try {
        const response = await fetch('/admin/billing/fee-categories/' + id + '/toggle-active', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF }
        });
        const data = await response.json();
        if (data.success) { location.reload(); } else { alert(data.message || 'حدث خطأ'); }
    } catch (err) { alert('حدث خطأ في الاتصال'); }
}

async function deleteCategory(id) {
    if (!confirm('هل أنت متأكد من حذف هذه الفئة؟')) return;
    try {
        const response = await fetch('/admin/billing/fee-categories/' + id, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': CSRF }
        });
        const data = await response.json();
        if (data.success) { location.reload(); } else { alert(data.message || 'حدث خطأ'); }
    } catch (err) { alert('حدث خطأ في الاتصال'); }
}
</script>
@endpush
@endsection