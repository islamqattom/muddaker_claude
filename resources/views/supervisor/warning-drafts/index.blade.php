@extends('layouts.app')

@section('title', 'مسودات الإنذارات')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h3 class="card-title">مسودات الإنذارات</h3>
                </div>
                <div class="card-body">
                    @if($drafts->isEmpty())
                        <div class="alert alert-info text-center">
                            <i class="fas fa-check-circle"></i> لا توجد مسودات جديدة للمراجعة
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>الطالب</th>
                                        <th>قاعدة الإنذار</th>
                                        <th>القيمة المحفزة</th>
                                        <th>الفترة</th>
                                        <th>تاريخ الإنشاء</th>
                                        <th>إجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($drafts as $draft)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>
                                            <a href="{{ route('supervisor.students.show', $draft->student_id) }}">
                                                {{ $draft->student?->name ?? '-' }}
                                            </a>
                                        </td>
                                        <td>{{ $draft->warningRule?->name ?? '-' }}</td>
                                        <td>
                                            <span class="badge badge-danger">
                                                {{ $draft->triggered_value }} / {{ $draft->triggered_threshold }}
                                            </span>
                                        </td>
                                        <td>
                                            {{ $draft->from_date?->format('Y-m-d') ?? '-' }}
                                            →
                                            {{ $draft->to_date?->format('Y-m-d') ?? '-' }}
                                        </td>
                                        <td>{{ $draft->created_at->format('Y-m-d H:i') }}</td>
                                        <td>
                                            <a href="{{ route('supervisor.warning-drafts.show', $draft->id) }}"
                                               class="btn btn-sm btn-primary">
                                                <i class="fas fa-eye"></i> مراجعة
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3">
                            {{ $drafts->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection