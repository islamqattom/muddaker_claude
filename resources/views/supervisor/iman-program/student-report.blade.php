@extends('layouts.app')

@section('title', 'تقرير الطالب')
@section('page-title', $headerTitle ?? 'تقرير الطالب')
@section('role-label', 'مشرف')

@section('sidebar-menu')
    @include('supervisor.partials.sidebar')
@endsection

@section('content')

<x-page-hero icon="fas fa-star-and-crescent">
    <h1 class="page-banner-title">تقرير البرنامج الإيماني</h1>
</x-page-hero>

    @include('partials.iman-program.student-report-body', [
        'data' => $data,
        'formAction' => $formAction,
        'compareAction' => $compareAction,
        'criteriaAction' => $criteriaAction,
        'backUrl' => $backUrl,
        'headerTitle' => $headerTitle,
    ])
@endsection
