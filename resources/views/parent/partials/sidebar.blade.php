<span class="nav-section-title">الرئيسية</span>
<a href="{{ route('parent.dashboard') }}" class="nav-link {{ request()->routeIs('parent.dashboard') ? 'active' : '' }}">
    <div class="icon-wrapper"><i class="fas fa-home"></i></div>
    <span>الصفحة الرئيسية</span>
</a>
<span class="nav-section-title">المتابعة</span>
<a href="{{ route('parent.sessions.index') }}" class="nav-link {{ request()->routeIs('parent.sessions.*') ? 'active' : '' }}">
    <div class="icon-wrapper"><i class="fas fa-microphone"></i></div>
    <span>جلسات التسميع</span>
</a>
<a href="{{ route('parent.attendance.index') }}" class="nav-link {{ request()->routeIs('parent.attendance.*') ? 'active' : '' }}">
    <div class="icon-wrapper"><i class="fas fa-calendar-check"></i></div>
    <span>سجل الحضور</span>
</a>
<a href="{{ route('parent.student-parts.index') }}" class="nav-link {{ request()->routeIs('parent.student-parts.*') ? 'active' : '' }}">
    <div class="icon-wrapper"><i class="fas fa-book-quran"></i></div>
    <span>أجزاء الأبناء</span>
</a>
<a href="{{ route('parent.yawm-al-himma.index') }}" class="nav-link {{ request()->routeIs('parent.yawm-al-himma.*') ? 'active' : '' }}">
    <div class="icon-wrapper"><i class="fas fa-mosque"></i></div>
    <span>يوم الهمة</span>
</a>
<a href="{{ route('parent.iman-program.index') }}" class="nav-link {{ request()->routeIs('parent.iman-program.*') ? 'active' : '' }}">
    <div class="icon-wrapper"><i class="fas fa-star-and-crescent"></i></div>
    <span>البرنامج الإيماني</span>
</a>
<a href="{{ route('parent.warnings.index') }}" class="nav-link {{ request()->routeIs('parent.warnings.*') ? 'active' : '' }}">
    <div class="icon-wrapper"><i class="fas fa-exclamation-triangle"></i></div>
    <span>الإنذارات</span>
    @php $unread = \App\Models\Warning::whereHas('student', fn($q)=>$q->whereHas('parents',fn($p)=>$p->where('user_id',auth()->id())))->where('is_read_by_parent',false)->count(); @endphp
    @if($unread > 0)<span class="badge">{{ $unread }}</span>@endif
</a>
<a href="{{ route('parent.reports.index') }}" class="nav-link {{ request()->routeIs('parent.reports.*') ? 'active' : '' }}">
    <div class="icon-wrapper"><i class="fas fa-chart-bar"></i></div>
    <span>التقارير</span>
</a>
<span class="nav-section-title">المالية</span>
<a href="{{ route('parent.billing') }}" class="nav-link {{ request()->routeIs('parent.billing') ? 'active' : '' }}">
    <div class="icon-wrapper"><i class="fas fa-money-bill"></i></div>
    <span>الرسوم الشهرية</span>
</a>
