@extends('layouts.app')

@section('title', 'تقاريري الإيمانية')
@section('page-title', 'تقاريري الإيمانية')
@section('role-label', 'طالب')

@section('sidebar-menu')
    @include('student.partials.sidebar')
@endsection

@section('content')
    @include('partials.iman-program.student-report-body', [
        'data' => $data,
        'formAction' => $formAction,
        'compareAction' => $compareAction,
        'criteriaAction' => $criteriaAction,
        'backUrl' => $backUrl,
        'headerTitle' => $headerTitle,
        'showHeaderMeta' => false,
    ])
@endsection
