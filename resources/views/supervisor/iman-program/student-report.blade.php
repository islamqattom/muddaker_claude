@extends('layouts.app')

@section('title', 'تقرير الطالب')
@section('page-title', $headerTitle ?? 'تقرير الطالب')
@section('role-label', 'مشرف')

@section('sidebar-menu')
    @include('supervisor.partials.sidebar')
@endsection

@section('content')
    @include('partials.iman-program.student-report-body', [
        'data' => $data,
        'formAction' => $formAction,
        'compareAction' => $compareAction,
        'criteriaAction' => $criteriaAction,
        'backUrl' => $backUrl,
        'headerTitle' => $headerTitle,
    ])
@endsection
