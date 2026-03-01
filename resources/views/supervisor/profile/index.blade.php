@extends('layouts.app')
@section('title', 'الملف الشخصي')
@section('page-title', 'الملف الشخصي')
@section('content')
<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">الملف الشخصي</h5>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                <form method="POST" action="{{ route('supervisor.profile.update') }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="col-12 mb-3">
                        <label class="form-label">صورة الملف الشخصي</label>
                        <div class="d-flex align-items-center gap-3">
                            <img src="{{ $user->avatar_url }}" alt="Avatar" class="rounded-circle" style="width:80px;height:80px;object-fit:cover;">
                            <div>
                                <input type="file" name="image" class="form-control @error('image') is-invalid @enderror" accept="image/*">
                                <small class="text-muted">الصيغ المدعومة: jpeg, png, jpg, gif, webp</small>
                            </div>
                        </div>
                        @error('image')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">الاسم</label>
                        <input type="text" name="name" class="form-control" value="{{ $user->name }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">اسم المستخدم</label>
                        <input type="text" name="username" class="form-control @error('username') is-invalid @enderror" value="{{ $user->username }}" placeholder="أرقام إنجليزية وحروف">
                        @error('username')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">البريد الإلكتروني</label>
                        <input type="email" name="email" class="form-control" value="{{ $user->email }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">الدور</label>
                        <input type="text" class="form-control" value="{{ $user->role }}" disabled>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>حفظ التغييرات
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
