<span class="nav-section-title">الرئيسية</span>
<a href="{{ route('supervisor.dashboard') }}" class="nav-link {{ request()->routeIs('supervisor.dashboard') ? 'active' : '' }}">
    <div class="icon-wrapper"><i class="fas fa-home"></i></div>
    <span>لوحة التحكم</span>
</a>

<span class="nav-section-title">إدارة الطلاب</span>
<a href="{{ route('supervisor.students.index') }}" class="nav-link {{ request()->routeIs('supervisor.students.*') ? 'active' : '' }}">
    <div class="icon-wrapper"><i class="fas fa-users"></i></div>
    <span>الطلاب</span>
</a>
<a href="{{ route('supervisor.attendance.index') }}" class="nav-link {{ request()->routeIs('supervisor.attendance.index') ? 'active' : '' }}">
    <div class="icon-wrapper"><i class="fas fa-calendar-check"></i></div>
    <span>الحضور والغياب</span>
    @php $unrecorded = \App\Models\Student::where('supervisor_id', auth()->id())->count() - \App\Models\Attendance::where('supervisor_id', auth()->id())->where('date', today())->count(); @endphp
    @if($unrecorded > 0)
        <span class="badge">{{ $unrecorded }}</span>
    @endif
</a>
<a href="{{ route('supervisor.attendance.history') }}" class="nav-link {{ request()->routeIs('supervisor.attendance.history') ? 'active' : '' }}">
    <div class="icon-wrapper"><i class="fas fa-history"></i></div>
    <span>سجل الحضور</span>
</a>
<a href="{{ route('supervisor.student-parts.index') }}" class="nav-link {{ request()->routeIs('supervisor.student-parts.*') ? 'active' : '' }}">
    <div class="icon-wrapper"><i class="fas fa-book-quran"></i></div>
    <span>أجزاء الطالب</span>
</a>

<span class="nav-section-title">التسميع</span>
<a href="{{ route('supervisor.recitation.index') }}" class="nav-link {{ request()->routeIs('supervisor.recitation.index') ? 'active' : '' }}">
    <div class="icon-wrapper"><i class="fas fa-microphone"></i></div>
    <span>جلسة تسميع جديدة</span>
</a>
<a href="{{ route('supervisor.recitation.history') }}" class="nav-link {{ request()->routeIs('supervisor.recitation.history') ? 'active' : '' }}">
    <div class="icon-wrapper"><i class="fas fa-history"></i></div>
    <span>سجل الجلسات</span>
</a>
<a href="{{ route('supervisor.exam-assistant.index') }}" class="nav-link {{ request()->routeIs('supervisor.exam-assistant.*') ? 'active' : '' }}">
    <div class="icon-wrapper"><i class="fas fa-graduation-cap"></i></div>
    <span>اختبار المواضع</span>
</a>
<a href="{{ route('supervisor.ijazah.index') }}" class="nav-link {{ request()->routeIs('supervisor.ijazah.*') ? 'active' : '' }}">
    <div class="icon-wrapper"><i class="fas fa-award"></i></div>
    <span>الإجازة</span>
</a>

<span class="nav-section-title">التقارير والإنذارات</span>
<a href="{{ route('supervisor.reports.index') }}" class="nav-link {{ request()->routeIs('supervisor.reports.*') ? 'active' : '' }}">
    <div class="icon-wrapper"><i class="fas fa-chart-bar"></i></div>
    <span>التقارير</span>
</a>
<a href="{{ route('supervisor.tasks.index') }}" class="nav-link {{ request()->routeIs('supervisor.tasks.index') ? 'active' : '' }}">
    <div class="icon-wrapper"><i class="fas fa-tasks"></i></div>
    <span>تكليفاتي</span>
</a>
<a href="{{ route('supervisor.warnings.index') }}" class="nav-link {{ request()->routeIs('supervisor.warnings.*') ? 'active' : '' }}">
    <div class="icon-wrapper"><i class="fas fa-exclamation-triangle"></i></div>
    <span>الإنذارات</span>
    @php $warnCount = \App\Models\Warning::where('issued_by', auth()->id())->whereMonth('created_at', now()->month)->count(); @endphp
    @if($warnCount > 0)
        <span class="badge">{{ $warnCount }}</span>
    @endif
</a>
<a href="{{ route('supervisor.warning-drafts.index') }}" class="nav-link {{ request()->routeIs('supervisor.warning-drafts.*') ? 'active' : '' }}">
    <div class="icon-wrapper"><i class="fas fa-edit"></i></div>
    <span>مسودات الإنذارات</span>
</a>
