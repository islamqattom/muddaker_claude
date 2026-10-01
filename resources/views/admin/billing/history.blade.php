@extends('layouts.app')
@section('title', 'سجل المدفوعات')
@section('page-title', 'سجل المدفوعات')
@section('role-label', 'مدير')

@section('sidebar-menu')
@include('admin.partials.sidebar')
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.billing.index') }}">الرسوم</a></li>
    <li class="breadcrumb-item active">سجل المدفوعات</li>
@endsection

@section('content')

<x-page-hero icon="fas fa-coins">
    <h1 class="page-banner-title">سجل المدفوعات</h1>
</x-page-hero>
<div class="card">
    <div class="card-header">
        <h6 class="mb-0"><i class="fas fa-history me-2"></i>سجل المدفوعات</h6>
    </div>
    <div class="card-body">
        <form method="GET" class="row g-3 mb-4">
            <div class="col-md-3">
                <label class="form-label">السنة</label>
                <select name="year" class="form-select">
                    @foreach($years as $year)
                        <option value="{{ $year }}" {{ request('year') == $year ? 'selected' : '' }}>{{ $year }}</option>
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
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2"><i class="fas fa-search"></i></button>
                <a href="{{ route('admin.billing.history') }}" class="btn btn-outline-secondary"><i class="fas fa-times"></i></a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>ولي الأمر</th>
                        <th>الطالب</th>
                        <th>الشهر</th>
                        <th>تاريخ الدفع</th>
                        <th>مدفوع بواسطة</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($fees as $fee)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $fee->parent->user->name ?? '-' }}</td>
                        <td>{{ $fee->student->user->name ?? '-' }}</td>
                        <td>{{ $fee->month }} / {{ $fee->year }}</td>
                        <td>{{ $fee->paid_at ? $fee->paid_at->format('d/m/Y H:i') : '-' }}</td>
                        <td>{{ $fee->paidBy->name ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">لا توجد سجلات</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $fees->withQueryString()->links() }}
    </div>
</div>
@endsection
