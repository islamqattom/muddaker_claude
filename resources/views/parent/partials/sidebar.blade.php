<span class="nav-section-title">الرئيسية</span>
<a href="{{ route('parent.dashboard') }}" class="nav-link {{ request()->routeIs('parent.dashboard') ? 'active' : '' }}">
    <i class="fas fa-home"></i> الصفحة الرئيسية
</a>
<span class="nav-section-title mt-2">المتابعة</span>
<a href="{{ route('parent.sessions.index') }}" class="nav-link {{ request()->routeIs('parent.sessions.*') ? 'active' : '' }}">
    <i class="fas fa-microphone"></i> جلسات التسميع
</a>
<a href="{{ route('parent.attendance.index') }}" class="nav-link {{ request()->routeIs('parent.attendance.*') ? 'active' : '' }}">
    <i class="fas fa-calendar-check"></i> سجل الحضور
</a>
<a href="{{ route('parent.warnings.index') }}" class="nav-link {{ request()->routeIs('parent.warnings.*') ? 'active' : '' }}">
    <i class="fas fa-exclamation-triangle"></i> الإنذارات
    @php $unread = \App\Models\Warning::whereHas('student', fn($q)=>$q->whereHas('parents',fn($p)=>$p->where('user_id',auth()->id())))->where('is_read_by_parent',false)->count(); @endphp
    @if($unread > 0)<span class="badge bg-danger">{{ $unread }}</span>@endif
</a>
<a href="{{ route('parent.reports.index') }}" class="nav-link {{ request()->routeIs('parent.reports.*') ? 'active' : '' }}">
    <i class="fas fa-chart-bar"></i> التقارير
</a>
<span class="nav-section-title mt-2">المالية</span>
<a href="{{ route('parent.billing') }}" class="nav-link {{ request()->routeIs('parent.billing') ? 'active' : '' }}">
    <i class="fas fa-money-bill"></i> الرسوم الشهرية
</a>
