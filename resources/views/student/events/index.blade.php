@extends('layouts.app')

@section('title', 'أحداث الحفظ الجماعي')
@section('page-title', 'أحداث الحفظ الجماعي')
@section('role-label', 'طالب')

@section('sidebar-menu')
    @include('student.partials.sidebar')
@endsection

@section('content')
<h5 class="mb-3"><i class="fas fa-users me-2"></i>الأحداث الجماعية</h5>

<!-- My Events -->
@if($myEvents->count() > 0)
<div class="card mb-4 border-primary">
    <div class="card-header bg-primary text-white">
        <h6 class="mb-0"><i class="fas fa-star me-2"></i>أحداثي النشطة</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>الحدث</th>
                        <th>الجزء</th>
                        <th>تاريخ البداية</th>
                        <th>تاريخ النهاية</th>
                        <th>المشاركون</th>
                        <th>التقدم</th>
                        <th>الإجراء</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($myEvents as $event)
                    @php
                        $participants = $event->participants->count();
                        $progress = $event->progress->last();
                    @endphp
                    <tr>
                        <td>
                            <strong>{{ $event->name }}</strong>
                            <br>
                            <small>{{ $event->supervisor?->name ?? '-' }}</small>
                        </td>
                        <td>الجزء {{ $event->juz_number }}</td>
                        <td>{{ $event->start_date->format('Y-m-d') }}</td>
                        <td>{{ $event->end_date->format('Y-m-d') }}</td>
                        <td>{{ $participants }} طالب</td>
                        <td>
                            @if($progress)
                                <span class="badge bg-{{ $progress->completion_percentage >= 100 ? 'success' : 'primary' }}">
                                    {{ $progress->completion_percentage }}%
                                </span>
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('student.events.show', $event) }}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

<!-- Available Events -->
@if($availableEvents->count() > 0)
<div class="card">
    <div class="card-header">
        <h6 class="mb-0"><i class="fas fa-plus-circle me-2"></i>أحداث متاحة للانضمام</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>الحدث</th>
                        <th>الجزء</th>
                        <th>المشرف</th>
                        <th>تاريخ البداية</th>
                        <th>تاريخ النهاية</th>
                        <th>المشاركون</th>
                        <th>الإجراء</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($availableEvents as $event)
                    <tr>
                        <td><strong>{{ $event->name }}</strong></td>
                        <td>الجزء {{ $event->juz_number }}</td>
                        <td>{{ $event->supervisor?->name ?? '-' }}</td>
                        <td>{{ $event->start_date->format('Y-m-d') }}</td>
                        <td>{{ $event->end_date->format('Y-m-d') }}</td>
                        <td>
                            <span class="badge bg-secondary">{{ $event->participants->count() }}</span>
                        </td>
                        <td>
                            <form action="{{ route('student.events.join', $event) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-success">
                                    <i class="fas fa-plus me-1"></i>انضم
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

@if($myEvents->isEmpty() && $availableEvents->isEmpty())
<div class="card">
    <div class="card-body text-center py-5">
        <i class="fas fa-users fa-4x text-muted opacity-25 mb-3"></i>
        <h5 class="text-muted">لا توجد أحداث جماعية</h5>
        <p class="text-muted">لا توجد حالياً أحداث حفظ جماعي متاحة</p>
        <p class="small text-muted">تحدث مع المشرف لإنشاء حدث جديد!</p>
    </div>
</div>
@endif
@endsection
