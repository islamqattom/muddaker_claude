@extends('layouts.app')

@section('title', 'مراجعة مسودة الإنذار')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            {{-- Draft Summary Card --}}
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">ملخص المسودة</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <strong>الطالب:</strong>
                            <a href="{{ route('supervisor.students.show', $draft->student_id) }}">
                                {{ $draft->student?->name ?? '-' }}
                            </a>
                        </div>
                        <div class="col-md-4">
                            <strong>قاعدة الإنذار:</strong> {{ $draft->warningRule?->name ?? '-' }}
                        </div>
                        <div class="col-md-4">
                            <strong>القيمة المحفزة:</strong>
                            <span class="text-danger">{{ $draft->triggered_value }}</span>
                            (الحد: {{ $draft->triggered_threshold }})
                        </div>
                        <div class="col-md-4 mt-2">
                            <strong>الفترة:</strong>
                            {{ $draft->from_date?->format('Y-m-d') ?? '-' }} → {{ $draft->to_date?->format('Y-m-d') ?? '-' }}
                        </div>
                        <div class="col-md-4 mt-2">
                            <strong>تاريخ المسودة:</strong> {{ $draft->created_at->format('Y-m-d H:i') }}
                        </div>
                    </div>

                    <hr>

                    <h5>محتوى المسودة</h5>
                    <div class="alert alert-secondary">
                        <strong>العنوان:</strong> {{ $draft->title }}<br><br>
                        <strong>المحتوى:</strong><br>
                        {{ $draft->content }}
                    </div>
                </div>
            </div>

            {{-- Attendance Data --}}
            @if(!empty($draft->attendance_data) && count($draft->attendance_data) > 0)
            <div class="card card-warning card-outline">
                <div class="card-header">
                    <h3 class="card-title">بيانات الحضور</h3>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-sm table-striped">
                        <thead>
                            <tr>
                                <th>اليوم</th>
                                <th>التاريخ</th>
                                <th>الحالة</th>
                                <th>وقت الحضور</th>
                                <th>ملاحظات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($draft->attendance_data as $record)
                            <tr>
                                <td>{{ $record['day'] ?? '-' }}</td>
                                <td>{{ $record['date'] ?? '-' }}</td>
                                <td>
                                    @php
                                        $statusClass = match($record['status'] ?? '') {
                                            'absent' => 'badge-danger',
                                            'late' => 'badge-warning',
                                            'excused' => 'badge-info',
                                            default => 'badge-secondary'
                                        };
                                    @endphp
                                    <span class="badge {{ $statusClass }}">
                                        {{ $record['status'] ?? '-' }}
                                    </span>
                                </td>
                                <td>{{ $record['arrival_time'] ?? '-' }}</td>
                                <td>{{ $record['notes'] ?? '-' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            {{-- Recitation Data --}}
            @if(!empty($draft->recitation_data) && count($draft->recitation_data) > 0)
            <div class="card card-danger card-outline">
                <div class="card-header">
                    <h3 class="card-title">بيانات التقييم</h3>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-sm table-striped">
                        <thead>
                            <tr>
                                <th>اليوم</th>
                                <th>التاريخ</th>
                                <th>نوع الحصة</th>
                                <th>الحالة</th>
                                <th>الصفحات</th>
                                <th>ملاحظات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($draft->recitation_data as $record)
                            <tr>
                                <td>{{ $record['day'] ?? '-' }}</td>
                                <td>{{ $record['session_date'] ?? '-' }}</td>
                                <td>{{ $record['type'] ?? '-' }}</td>
                                <td>
                                    @php
                                        $statusClass = match($record['status'] ?? '') {
                                            'failed' => 'badge-danger',
                                            'not_recitate' => 'badge-dark',
                                            default => 'badge-secondary'
                                        };
                                    @endphp
                                    <span class="badge {{ $statusClass }}">
                                        {{ $record['status'] ?? '-' }}
                                    </span>
                                </td>
                                <td>
                                    @if(isset($record['total_pages']))
                                        {{ $record['passed_pages'] ?? 0 }} / {{ $record['total_pages'] }}
                                        (<span class="text-danger">{{ $record['failed_pages'] ?? 0 }} فشل</span>)
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>{{ $record['notes'] ?? '-' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            {{-- Actions --}}
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <form action="{{ route('supervisor.warning-drafts.approve', $draft->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-success btn-lg btn-block"
                                        onclick="return confirm('هل أنت متأكد من إصدار هذا الإنذار؟')">
                                    <i class="fas fa-check"></i> اعتماد وإصدار الإنذار
                                </button>
                            </form>
                        </div>
                        <div class="col-md-6">
                            <form action="{{ route('supervisor.warning-drafts.dismiss', $draft->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-secondary btn-lg btn-block"
                                        onclick="return confirm('هل أنت متأكد من تجاهل هذه المسودة؟')">
                                    <i class="fas fa-times"></i> تجاهل المسودة
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection