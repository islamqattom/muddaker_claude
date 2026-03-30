@extends('layouts.app')

@section('title', 'التحديات')
@section('page-title', 'تحديات الحفظ')
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
    <a href="{{ route('student.leaderboard.index') }}" class="nav-link {{ request()->routeIs('student.leaderboard*') ? 'active' : '' }}">
        <i class="fas fa-ranking-star"></i> المتصدرين
    </a>
@endsection

@section('content')
<!-- Stats -->
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="card text-center p-3 border-success">
            <div class="text-success fw-bold" style="font-size: 1.5rem;">{{ $stats['won'] }}</div>
            <small class="text-muted">تحديات فاز بها</small>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card text-center p-3 border-danger">
            <div class="text-danger fw-bold" style="font-size: 1.5rem;">{{ $stats['lost'] }}</div>
            <small class="text-muted">تحديات خسرها</small>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card text-center p-3">
            <div class="text-primary fw-bold" style="font-size: 1.5rem;">{{ $stats['total'] }}</div>
            <small class="text-muted">الإجمالي</small>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card text-center p-3 border-warning">
            <div class="text-warning fw-bold" style="font-size: 1.5rem;">{{ $stats['pending'] }}</div>
            <small class="text-muted">في الانتظار</small>
        </div>
    </div>
</div>

<!-- New Challenge Button -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">تحدياتي</h5>
    <a href="{{ route('student.challenges.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i>تحد جديد
    </a>
</div>

<!-- Pending Requests -->
@if($pendingRequests->count() > 0)
<div class="card mb-4 border-warning">
    <div class="card-header bg-warning text-dark">
        <h6 class="mb-0"><i class="fas fa-bell me-2"></i>تحديات تنتظر قبولك ({{ $pendingRequests->count() }})</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <tbody>
                    @foreach($pendingRequests as $challenge)
                    <tr>
                        <td>
                            <strong>{{ $challenge->challenger->name }}</strong>
                            <br>
                            <small class="text-muted">تحداك في سورة {{ $challenge->surah }}</small>
                        </td>
                        <td>
                            <small>تاريخ الهدف:</small>
                            <br>
                            {{ $challenge->target_date->format('Y-m-d') }}
                        </td>
                        <td>
                            <form action="{{ route('student.challenges.accept', $challenge) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-success">قبول</button>
                            </form>
                            <form action="{{ route('student.challenges.decline', $challenge) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-danger">رفض</button>
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

<!-- Active Challenges -->
@if($activeChallenges->count() > 0)
<div class="card mb-4">
    <div class="card-header">
        <h6 class="mb-0"><i class="fas fa-swords me-2"></i>تحديات نشطة</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>المتسابقين</th>
                        <th>السورة</th>
                        <th>تاريخ الهدف</th>
                        <th>الحالة</th>
                        <th>الإجراء</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($activeChallenges as $challenge)
                    @php
                        $isChallenger = $challenge->challenger_id === auth()->user()->student->id;
                    @endphp
                    <tr>
                        <td>
                            {{ $challenge->challenger->name }}
                            <i class="fas fa-vs text-muted mx-1"></i>
                            {{ $challenge->challenged->name }}
                        </td>
                        <td>سورة {{ $challenge->surah }} ({{ $challenge->verse_start }}-{{ $challenge->verse_end }})</td>
                        <td>{{ $challenge->target_date->format('Y-m-d') }}</td>
                        <td>
                            @if($challenge->status === 'accepted')
                                <span class="badge bg-success">مقبول</span>
                            @else
                                <span class="badge bg-warning">معلق</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('student.challenges.show', $challenge) }}" class="btn btn-sm btn-outline-primary">
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

<!-- History -->
@if($history->count() > 0)
<div class="card">
    <div class="card-header">
        <h6 class="mb-0"><i class="fas fa-history me-2"></i>التحديات السابقة</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>المتسابقين</th>
                        <th>السورة</th>
                        <th>النتيجة</th>
                        <th>الفائز</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($history as $challenge)
                    <tr>
                        <td>
                            {{ $challenge->challenger->name }}
                            <i class="fas fa-vs text-muted mx-1"></i>
                            {{ $challenge->challenged->name }}
                        </td>
                        <td>سورة {{ $challenge->surah }}</td>
                        <td>
                            @if($challenge->status === 'completed')
                                <span class="badge bg-success">مكتمل</span>
                            @elseif($challenge->status === 'failed')
                                <span class="badge bg-danger">فشل</span>
                            @else
                                <span class="badge bg-secondary">مرفوض</span>
                            @endif
                        </td>
                        <td>
                            @if($challenge->winner)
                                <span class="badge bg-primary">{{ $challenge->winner->name }}</span>
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif

@if($activeChallenges->isEmpty() && $pendingRequests->isEmpty() && $history->isEmpty())
<div class="card">
    <div class="card-body text-center py-5">
        <i class="fas fa-swords fa-4x text-muted opacity-25 mb-3"></i>
        <h5 class="text-muted">لا توجد تحديات</h5>
        <p class="text-muted">ابدأ تحدياً جديداً مع زملائك!</p>
        <a href="{{ route('student.challenges.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>تحد جديد
        </a>
    </div>
</div>
@endif
@endsection
