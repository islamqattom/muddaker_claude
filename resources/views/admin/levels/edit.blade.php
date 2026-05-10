@extends('layouts.app')
@section('title', 'تعديل المستوى')
@section('page-title', 'تعديل المستوى')
@section('role-label', 'مدير')

@section('sidebar-menu')
@include('admin.partials.sidebar')
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.levels.index') }}">المستويات</a></li>
    <li class="breadcrumb-item active">تعديل المستوى</li>
@endsection

@section('content')

{{-- Page Header --}}
<div class="page-header">
    <div class="header-content">
        <div class="header-icon warning">
            <i class="fas fa-edit"></i>
        </div>
        <div class="header-text">
            <h4>تعديل المستوى</h4>
            <p>تعديل بيانات المستوى الحالي</p>
        </div>
    </div>
</div>

<div class="form-card">
    @if(session('success'))
        <div class="alert alert-success">
            <i class="fas fa-check-circle me-2"></i>
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.levels.update', $level->id) }}">
        @csrf
        @method('PUT')

        <div class="form-grid">
            <div class="form-group">
                <label class="form-label">اسم المستوى <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $level->name) }}" required>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label">رقم المستوى <span class="text-danger">*</span></label>
                <input type="number" name="level_number" class="form-control @error('level_number') is-invalid @enderror" value="{{ old('level_number', $level->level_number) }}" min="1" required>
                @error('level_number')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="form-grid">
            <div class="form-group">
                <label class="form-label">صفحات الحفظ الجديدة <span class="text-danger">*</span></label>
                <div class="input-icon">
                    <i class="fas fa-book"></i>
                    <input type="number" name="new_memorization_pages" class="form-control @error('new_memorization_pages') is-invalid @enderror" value="{{ old('new_memorization_pages', $level->new_memorization_pages) }}" min="1" required>
                </div>
                @error('new_memorization_pages')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label">صفحات المراجعة <span class="text-danger">*</span></label>
                <div class="input-icon">
                    <i class="fas fa-redo"></i>
                    <input type="number" name="review_pages" class="form-control @error('review_pages') is-invalid @enderror" value="{{ old('review_pages', $level->review_pages) }}" min="1" required>
                </div>
                @error('review_pages')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">الوصف</label>
            <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="4">{{ old('description', $level->description) }}</textarea>
            @error('description')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-2"></i>تحديث
            </button>
            <a href="{{ route('admin.levels.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-right me-2"></i>رجوع
            </a>
        </div>
    </form>
</div>

<style>
.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
    margin-bottom: 25px;
}

.header-content { display: flex; align-items: center; gap: 20px; }

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

.header-icon.warning { background: linear-gradient(135deg, #f39c12, #e67e22); color: #fff; }

.header-text h4 { color: #182B48; font-size: 22px; font-weight: 700; margin: 0 0 4px 0; }
.header-text p { color: #7f8c8d; font-size: 14px; margin: 0; }

.form-card {
    background: #fff;
    border-radius: 16px;
    padding: 30px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.05);
    border: 1px solid #f0f4f8;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
    margin-bottom: 20px;
}

@media (max-width: 768px) { .form-grid { grid-template-columns: 1fr; } }

.form-group { margin-bottom: 20px; }

.form-label {
    display: block;
    color: #182B48;
    font-size: 14px;
    font-weight: 600;
    margin-bottom: 8px;
}

.form-control {
    width: 100%;
    padding: 14px 16px;
    border: 1.5px solid #dee2e6;
    border-radius: 12px;
    font-size: 14px;
    transition: all 0.3s ease;
}

.form-control:focus {
    border-color: #182B48;
    box-shadow: 0 0 0 0.2rem rgba(24,43,72,0.15);
    outline: none;
}

.form-control.is-invalid { border-color: #c0392b; }

.invalid-feedback { color: #c0392b; font-size: 12px; margin-top: 5px; }

.input-icon {
    position: relative;
}

.input-icon i {
    position: absolute;
    right: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: #d4a030;
    font-size: 16px;
}

.input-icon .form-control { padding-right: 45px; }

.form-actions {
    display: flex;
    gap: 15px;
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid #f0f4f8;
}

.btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 25px;
    border-radius: 12px;
    font-weight: 600;
    font-size: 14px;
    transition: all 0.3s ease;
    text-decoration: none;
    border: none;
    cursor: pointer;
}

.btn-primary {
    background: linear-gradient(135deg, #182B48, #2A4570);
    color: #fff;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(24,43,72,0.3);
}

.btn-outline-secondary {
    background: transparent;
    color: #7f8c8d;
    border: 1.5px solid #dee2e6;
}

.btn-outline-secondary:hover {
    background: #f8f9fa;
    color: #182B48;
}
</style>
@endsection