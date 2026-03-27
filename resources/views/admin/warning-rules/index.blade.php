@extends('layouts.app')

@section('title', 'قواعد الإنذار')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">قواعد الإنذار</h3>
                    <a href="{{ route('admin.warning-rules.create') }}" class="btn btn-primary float-left">
                        <i class="fas fa-plus"></i> إضافة قاعدة جديدة
                    </a>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>الاسم</th>
                                <th>النوع</th>
                                <th>الشرط</th>
                                <th>الحد</th>
                                <th>الفترة</th>
                                <th>الحالة</th>
                                <th>إجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($rules as $rule)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $rule->name }}</td>
                                <td>
                                    @php
                                        $typeLabels = [
                                            'attendance' => '<span class="badge badge-warning">حضور</span>',
                                            'recitation' => '<span class="badge badge-danger">تقييم</span>',
                                            'general' => '<span class="badge badge-info">عام</span>',
                                        ];
                                    @endphp
                                    {!! $typeLabels[$rule->type] ?? $rule->type !!}
                                </td>
                                <td>{{ $rule->condition_type }}</td>
                                <td>{{ $rule->threshold }}</td>
                                <td>{{ $rule->period_days }} يوم</td>
                                <td>
                                    @if($rule->is_active)
                                        <span class="badge badge-success">مفعّل</span>
                                    @else
                                        <span class="badge badge-secondary">معطّل</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.warning-rules.edit', $rule->id) }}"
                                       class="btn btn-sm btn-secondary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.warning-rules.toggle', $rule->id) }}" method="POST"
                                          class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-{{ $rule->is_active ? 'warning' : 'success' }}">
                                            <i class="fas fa-{{ $rule->is_active ? 'pause' : 'play' }}"></i>
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.warning-rules.destroy', $rule->id) }}" method="POST"
                                          class="d-inline" onsubmit="return confirm('هل أنت متأكد؟')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center">لا توجد قواعد</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">
                    {{ $rules->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection