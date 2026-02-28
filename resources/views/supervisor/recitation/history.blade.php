@extends('layouts.app')
@section('title', 'سجل الجلسات')
@section('page-title', 'سجل الجلسات')
@section('sidebar-menu') @include('supervisor.partials.sidebar') @endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-history me-2"></i>سجل جلسات التسميع</h5>
    </div>
    <div class="card-body">
        <!-- Filters -->
        <form method="GET" action="{{ route('supervisor.recitation.history') }}" class="mb-4">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">الطالب</label>
                    <select name="student_id" class="form-select">
                        <option value="">-- جميع الطلاب --</option>
                        @foreach($students as $student)
                            <option value="{{ $student->id }}" {{ $studentId == $student->id ? 'selected' : '' }}>
                                {{ $student->user->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">من تاريخ</label>
                    <input type="date" name="from_date" class="form-control" value="{{ request('from_date', $fromDate->toDateString()) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">إلى تاريخ</label>
                    <input type="date" name="to_date" class="form-control" value="{{ request('to_date', $toDate->toDateString()) }}">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search me-1"></i>بحث
                    </button>
                </div>
            </div>
        </form>

        <!-- Results Summary -->
        <div class="mb-3">
            <span class="badge bg-primary fs-6">
                <i class="fas fa-list me-1"></i>عدد النتائج: {{ $sessions->total() }}
            </span>
            @if($studentId)
                @php $selectedStudent = $students->firstWhere('id', $studentId) @endphp
                @if($selectedStudent)
                    <span class="badge bg-info text-dark ms-1">
                        <i class="fas fa-user-graduate me-1"></i>{{ $selectedStudent->user->name }}
                    </span>
                @endif
            @endif
        </div>

        <!-- Sessions Table -->
        <div class="table-responsive">
            <table class="table table-hover table-bordered">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" style="width:60px">#</th>
                        <th>الطالب</th>
                        <th>نوع الجلسة</th>
                        <th>الجزء</th>
                        <th class="text-center">الصفحات</th>
                        <th class="text-center">الأخطاء</th>
                        <th class="text-center">الترددات</th>
                        <th class="text-center">العلامة</th>
                        <th class="text-center">الحالة</th>
                        <th>التاريخ</th>
                        <th width="80">الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sessions as $index => $session)
                        <tr>
                            <td class="text-center text-muted">{{ $sessions->firstItem() + $index }}</td>
                            <td>
                                <div class="fw-semibold">{{ $session->student->user->name }}</div>
                            </td>
                            <td>
                                <span class="badge bg-secondary">{{ $session->type_label }}</span>
                            </td>
                            <td>
                                @if($session->part)
                                    <span class="text-primary fw-semibold">الجزء {{ $session->part->part_number }}</span>
                                @elseif($session->mafsil_type)
                                    <span class="text-danger fw-semibold">مفصل ({{ $session->mafsil_type }})</span>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="text-center">
                                @if($session->total_pages)
                                    <span class="badge bg-dark">{{ $session->total_pages }}</span>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="text-center">
                                @if($session->total_errors > 0)
                                    <span class="text-danger fw-bold">{{ $session->total_errors }}</span>
                                @else
                                    <span class="text-muted">0</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($session->total_hesitations > 0)
                                    <span class="text-warning fw-bold">{{ $session->total_hesitations }}</span>
                                @else
                                    <span class="text-muted">0</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($session->final_score !== null)
                                    @php
                                        $scoreClass = $session->final_score >= 90 ? 'bg-success' : ($session->final_score >= 80 ? 'bg-warning text-dark' : 'bg-danger');
                                    @endphp
                                    <span class="badge {{ $scoreClass }} fs-6">{{ number_format($session->final_score, 1) }}%</span>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge bg-{{ $session->status_color }}">{{ $session->status_label }}</span>
                            </td>
                            <td>
                                <small class="text-muted">{{ $session->session_date->format('Y/m/d') }}</small>
                            </td>
                            <td>
                                <button type="button" class="btn btn-sm btn-outline-danger" title="حذف" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $session->id }}">
                                    <i class="fas fa-trash"></i>
                                </button>

                                <!-- Delete Modal -->
                                <div class="modal fade" id="deleteModal{{ $session->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">حذف جلسة التسميع</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p>هل أنت متأكد من حذف جلسة التسميع للطالب <strong>{{ $session->student->user->name }}</strong>؟</p>
                                                <p class="text-muted small">بتاريخ: {{ $session->session_date->format('Y/m/d') }}</p>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                                                <form action="{{ route('supervisor.recitation.destroy', $session->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger">حذف</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="text-center py-4 text-muted">
                                <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                لا توجد جلسات في الفترة المحددة
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($sessions->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $sessions->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
