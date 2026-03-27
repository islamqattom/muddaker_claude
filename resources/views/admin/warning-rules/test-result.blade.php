@extends('layouts.app')

@section('title', 'اختبار القاعدة')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">نتيجة اختبار القاعدة</h3>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <strong>نوع الشرط:</strong> {{ $conditionType }} |
                        <strong>الحد:</strong> {{ $threshold }} |
                        <strong>الفترة:</strong> {{ $periodDays }} يوم
                    </div>

                    @if($results->isEmpty())
                        <div class="alert alert-success">
                            لا يوجد طلاب يطابقون هذا الشرط حالياً.
                        </div>
                    @else
                        <div class="alert alert-warning">
                            <strong>{{ $results->count() }} طالب</strong> يطابقون هذا الشرط
                        </div>
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>اسم الطالب</th>
                                    <th>الصف</th>
                                    <th>المشرف</th>
                                    <th>العدد</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($results as $result)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $result['student_name'] }}</td>
                                    <td>{{ $result['level'] }}</td>
                                    <td>{{ $result['supervisor'] }}</td>
                                    <td><span class="badge badge-danger">{{ $result['count'] }}</span></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif

                    <a href="{{ route('admin.warning-rules.index') }}" class="btn btn-secondary mt-3">
                        <i class="fas fa-arrow-right"></i> العودة
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection