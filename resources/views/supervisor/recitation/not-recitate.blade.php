@extends('layouts.app')

@section('title', 'لم يسمع')
@section('page-title', 'تسجيل عدم التسميع')
@section('role-label', 'مشرف')

@section('sidebar-menu')
    @include('supervisor.partials.sidebar')
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-times-circle me-2"></i>لم يسمع</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info mb-4">
                    <i class="fas fa-user-graduate me-2"></i>
                    الطالب: <strong>{{ $student->user->name }}</strong>
                </div>

                <form method="POST" action="{{ route('supervisor.recitation.save-not-recitate') }}">
                    @csrf
                    <input type="hidden" name="student_id" value="{{ $student->id }}">

                    <div class="mb-3">
                        <label class="form-label">التاريخ <span class="text-danger">*</span></label>
                        <input type="date" name="session_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">السبب (اختياري)</label>
                        <textarea name="reason" class="form-control" rows="3" placeholder="أدخل سبب عدم التسميع"></textarea>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-grow-1">
                            <i class="fas fa-save me-2"></i>حفظ
                        </button>
                        <a href="{{ route('supervisor.recitation.index') }}" class="btn btn-secondary">
                            إلغاء
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
