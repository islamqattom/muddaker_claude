@extends('layouts.app')

@section('title', 'نشاط النطق')
@section('page-title', 'نشاط النطق والتجويد')
@section('role-label', 'طالب')

@section('sidebar-menu')
    <span class="nav-section-title">الرئيسية</span>
    <a href="{{ route('student.dashboard') }}" class="nav-link {{ request()->routeIs('student.dashboard') ? 'active' : '' }}">
        <i class="fas fa-home"></i> لوحتي الشخصية
    </a>
    <span class="nav-section-title mt-2">القرآن الكريم</span>
    <a href="{{ route('student.quran') }}" class="nav-link {{ request()->routeIs('student.quran*') ? 'active' : '' }}">
        <i class="fas fa-book-quran"></i> قراءة القرآن
    </a>
    <span class="nav-section-title mt-2">المتابعة</span>
    <a href="{{ route('student.sessions.index') }}" class="nav-link {{ request()->routeIs('student.sessions.*') ? 'active' : '' }}">
        <i class="fas fa-microphone"></i> جلسات التسميع
    </a>
    <span class="nav-section-title mt-2">التقدم</span>
    <a href="{{ route('student.progress') }}" class="nav-link {{ request()->routeIs('student.progress*') ? 'active' : '' }}">
        <i class="fas fa-chart-line"></i> تقدمي
    </a>
    <a href="{{ route('student.pronunciation.index') }}" class="nav-link {{ request()->routeIs('student.pronunciation*') ? 'active' : '' }}">
        <i class="fas fa-microphone-alt"></i> النطق
    </a>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="mb-0">سجل محاولات النطق</h5>
    <a href="{{ route('student.pronunciation.practice') }}" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i>محاولة جديدة
    </a>
</div>

@if($attempts->count() > 0)
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>التاريخ</th>
                        <th>السورة</th>
                        <th>الآيات</th>
                        <th>النتيجة</th>
                        <th>الحالة</th>
                        <th>الإجراء</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($attempts as $attempt)
                    <tr>
                        <td>{{ $attempt->created_at->format('Y-m-d H:i') }}</td>
                        <td>سورة {{ $attempt->surah }}</td>
                        <td>{{ $attempt->verse_start }} - {{ $attempt->verse_end }}</td>
                        <td>
                            @if($attempt->status === 'completed')
                                @php
                                    $scoreColor = $attempt->tajweed_score >= 85 ? 'success' : ($attempt->tajweed_score >= 70 ? 'warning' : 'danger');
                                @endphp
                                <span class="badge bg-{{ $scoreColor }}">{{ $attempt->tajweed_score }}%</span>
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            @if($attempt->status === 'completed')
                                <span class="badge bg-success">مكتمل</span>
                            @elseif($attempt->status === 'processing')
                                <span class="badge bg-warning">قيد المعالجة</span>
                            @elseif($attempt->status === 'failed')
                                <span class="badge bg-danger">فشل</span>
                            @else
                                <span class="badge bg-secondary">في الانتظار</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('student.pronunciation.results', $attempt) }}" class="btn btn-sm btn-outline-primary">
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

<div class="mt-3">
    {{ $attempts->links() }}
</div>
@else
<div class="card">
    <div class="card-body text-center py-5">
        <i class="fas fa-microphone-alt fa-4x text-muted opacity-25 mb-3"></i>
        <h5 class="text-muted">لا توجد محاولات سابقة</h5>
        <p class="text-muted">ابدأ بتمرين النطق لتحسين تجويدك</p>
        <a href="{{ route('student.pronunciation.practice') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>محاولة جديدة
        </a>
    </div>
</div>
@endif
@endsection
