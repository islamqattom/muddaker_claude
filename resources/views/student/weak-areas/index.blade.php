@extends('layouts.app')

@section('title', 'المناطق الضعيفة')
@section('page-title', 'المناطق الضعيفة')
@section('role-label', 'طالب')

@section('sidebar-menu')
    @include('student.partials.sidebar')
@endsection

@section('content')
<!-- Progress Overview -->
<div class="row g-3 mb-4">
    <div class="col-4">
        <div class="card text-center p-3">
            <div class="text-primary fw-bold" style="font-size: 1.5rem;">{{ $progress['total'] }}</div>
            <small class="text-muted">إجمالي المناطق</small>
        </div>
    </div>
    <div class="col-4">
        <div class="card text-center p-3">
            <div class="text-success fw-bold" style="font-size: 1.5rem;">{{ $progress['remediated'] }}</div>
            <small class="text-muted">تم تحسينها</small>
        </div>
    </div>
    <div class="col-4">
        <div class="card text-center p-3">
            <div class="text-warning fw-bold" style="font-size: 1.5rem;">{{ $progress['percentage'] }}%</div>
            <small class="text-muted">نسبة التحسن</small>
        </div>
    </div>
</div>

<!-- Refresh Button -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0"><i class="fas fa-exclamation-triangle text-warning me-2"></i>المناطق التي تحتاج تقوية</h5>
    <a href="{{ route('student.weak-areas.refresh') }}" class="btn btn-sm btn-outline-primary">
        <i class="fas fa-sync me-1"></i>تحديث التحليل
    </a>
</div>

<!-- Weak Areas List -->
@if($weakAreas->count() > 0)
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>السورة</th>
                        <th>الصفحة</th>
                        <th>معدل الخطأ</th>
                        <th>معدل التردد</th>
                        <th>درجة الأولوية</th>
                        <th>المحاولات</th>
                        <th>الإجراء</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($weakAreas as $index => $area)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>سورة {{ $area->surah }}</td>
                        <td>{{ $area->page_number ?? '-' }}</td>
                        <td>
                            @php
                                $errorColor = $area->error_rate >= 0.5 ? 'danger' : ($area->error_rate >= 0.3 ? 'warning' : 'success');
                            @endphp
                            <span class="badge bg-{{ $errorColor }}">{{ round($area->error_rate * 100) }}%</span>
                        </td>
                        <td>
                            @php
                                $hesitationColor = $area->hesitation_rate >= 0.5 ? 'danger' : ($area->hesitation_rate >= 0.3 ? 'warning' : 'success');
                            @endphp
                            <span class="badge bg-{{ $hesitationColor }}">{{ round($area->hesitation_rate * 100) }}%</span>
                        </td>
                        <td>
                            <div class="progress m-1" style="height: 8px; width: 60px;">
                                <div class="progress-bar bg-danger" style="width: {{ min(100, $area->priority_score * 100) }}%"></div>
                            </div>
                            <small class="text-muted">{{ round($area->priority_score, 2) }}</small>
                        </td>
                        <td>{{ $area->attempt_count }}</td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('student.quran.page', $area->page_number ?? 1) }}" class="btn btn-outline-primary" title="اذهب للقرآن">
                                    <i class="fas fa-book-quran"></i>
                                </a>
                                <form action="{{ route('student.weak-areas.mark', $area) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-success" title="تم التحسن" onclick="return confirm('هل تحسن هذا المستوى؟')">
                                        <i class="fas fa-check"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@else
<div class="card">
    <div class="card-body text-center py-5">
        <i class="fas fa-check-circle fa-4x text-success opacity-25 mb-3"></i>
        <h5 class="text-success">لا توجد مناطق ضعيفة!</h5>
        <p class="text-muted">أحسنت! لا توجد حالياً آيات تحتاج تقوية في التسميع</p>
        <a href="{{ route('student.sessions.index') }}" class="btn btn-primary">
            <i class="fas fa-microphone me-2"></i>تسميع جديد
        </a>
    </div>
</div>
@endif

<!-- Info Card -->
<div class="card mt-4 bg-light">
    <div class="card-body">
        <h6><i class="fas fa-info-circle me-2"></i>كيف يعمل تحليل المناطق الضعيفة؟</h6>
        <p class="small text-muted mb-0">
            يحلل النظام بيانات تسميعك السابقة لحساب نسبة الأخطاء والترددات في كل صفحة.
            إذا كانت نسبة الخطأ أكثر من 30% أو نسبة التردد أكثر من 40%،
            يتم تصنيف الصفحة كمنطقة ضعيفة تحتاج تقوية إضافية.
        </p>
    </div>
</div>
@endsection
