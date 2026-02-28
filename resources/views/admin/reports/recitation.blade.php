@extends('layouts.app')
@section('title', 'تقرير التسميع')
@section('page-title', 'تقرير التسميع')
@section('role-label', 'مدير')

@section('sidebar-menu')
@include('admin.partials.sidebar')
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">الرئيسية</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.reports.index') }}">التقارير</a></li>
    <li class="breadcrumb-item active">تقرير التسميع</li>
@endsection

@section('content')
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">من تاريخ</label>
                <input type="date" name="from_date" class="form-control" value="{{ request('from_date', $fromDate->format('Y-m-d')) }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">إلى تاريخ</label>
                <input type="date" name="to_date" class="form-control" value="{{ request('to_date', $toDate->format('Y-m-d')) }}">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search me-1"></i>بحث
                </button>
            </div>
            <div class="col-md-2">
                <a href="{{ route('admin.reports.recitation', ['from_date' => $fromDate->format('Y-m-d'), 'to_date' => $toDate->format('Y-m-d'), 'export' => 'pdf']) }}" class="btn btn-danger w-100">
                    <i class="fas fa-file-pdf me-1"></i>تصدير PDF
                </a>
            </div>
            <div class="col-md-2 text-end">
                <a href="{{ route('admin.reports.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-right me-1"></i>رجوع
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0">
            <i class="fas fa-microphone me-2"></i>
            نتائج التسميع
        </h6>
        <span class="badge bg-info">{{ $sessions->total() }} جلسة</span>
    </div>
    <div class="card-body p-0">
        @if($sessions->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="fas fa-microphone fa-3x mb-3 d-block"></i>
                <p>لا توجد جلسات تسميع في هذه الفترة</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="60">#</th>
                            <th>اسم الطالب</th>
                            <th>المشرف</th>
                            <th>الجزء</th>
                            <th>الصفحات</th>
                            <th>التاريخ</th>
                            <th>الحالة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sessions as $index => $session)
                        <tr>
                            <td>{{ $sessions->firstItem() + $index }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <img src="{{ $session->student->user->avatar_url }}" alt="Avatar" class="rounded-circle" style="width:36px;height:36px;object-fit:cover;">
                                    <span>{{ $session->student->user->name }}</span>
                                </div>
                            </td>
                            <td>{{ $session->supervisor->name ?? '-' }}</td>
                            <td>{{ $session->part->name ?? '-' }}</td>
                            <td>{{ $session->from_page }} - {{ $session->to_page }}</td>
                            <td>{{ $session->session_date?->format('d/m/Y') }}</td>
                            <td>
                                @if($session->status === 'passed')
                                    <span class="badge bg-success">ناجح</span>
                                @elseif($session->status === 'failed')
                                    <span class="badge bg-danger">راسب</span>
                                @else
                                    <span class="badge bg-secondary">{{ $session->status }}</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="p-3 border-top">
                {{ $sessions->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
