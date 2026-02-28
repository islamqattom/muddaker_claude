@extends('layouts.app')
@section('title', 'إضافة مستوى')
@section('page-title', 'إضافة مستوى')
@section('role-label', 'مدير')

@section('sidebar-menu')
@include('admin.partials.sidebar')
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.levels.index') }}">المستويات</a></li>
    <li class="breadcrumb-item active">إضافة مستوى</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-plus-circle me-2"></i>
                    إضافة مستوى جديد
                </h6>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
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

                <form method="POST" action="{{ route('admin.levels.store') }}">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">اسم المستوى <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="مثال: المستوى الأول" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">رقم المستوى <span class="text-danger">*</span></label>
                        <input type="number" name="level_number" class="form-control @error('level_number') is-invalid @enderror" value="{{ old('level_number') }}" min="1" required>
                        @error('level_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">صفحات الحفظ الجديدة <span class="text-danger">*</span></label>
                                <input type="number" name="new_memorization_pages" class="form-control @error('new_memorization_pages') is-invalid @enderror" value="{{ old('new_memorization_pages') }}" min="1" required>
                                @error('new_memorization_pages')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">صفحات المراجعة <span class="text-danger">*</span></label>
                                <input type="number" name="review_pages" class="form-control @error('review_pages') is-invalid @enderror" value="{{ old('review_pages') }}" min="1" required>
                                @error('review_pages')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">الوصف</label>
                        <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="3" placeholder="وصف المستوى اختياري">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>حفظ
                        </button>
                        <a href="{{ route('admin.levels.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-right me-2"></i>رجوع
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
