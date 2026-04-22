<span class="nav-section-title">الرئيسية</span>
<a href="{{ route('supervisor.dashboard') }}" class="nav-link {{ request()->routeIs('supervisor.dashboard') ? 'active' : '' }}">
    <i class="fas fa-home"></i> لوحة التحكم
</a>

<span class="nav-section-title mt-2">إدارة الطلاب</span>
<a href="{{ route('supervisor.students.index') }}" class="nav-link {{ request()->routeIs('supervisor.students.*') ? 'active' : '' }}">
    <i class="fas fa-users"></i> الطلاب
</a>
<a href="{{ route('supervisor.attendance.index') }}" class="nav-link {{ request()->routeIs('supervisor.attendance.index') ? 'active' : '' }}">
    <i class="fas fa-calendar-check"></i> الحضور والغياب
    @php $unrecorded = \App\Models\Student::where('supervisor_id', auth()->id())->count() - \App\Models\Attendance::where('supervisor_id', auth()->id())->where('date', today())->count(); @endphp
    @if($unrecorded > 0)
        <span class="badge bg-warning text-dark">{{ $unrecorded }}</span>
    @endif
</a>
<a href="{{ route('supervisor.attendance.history') }}" class="nav-link {{ request()->routeIs('supervisor.attendance.history') ? 'active' : '' }}">
    <i class="fas fa-history"></i> سجل الحضور
</a>
<a href="{{ route('supervisor.student-parts.index') }}" class="nav-link {{ request()->routeIs('supervisor.student-parts.*') ? 'active' : '' }}">
    <i class="fas fa-book-quran"></i> أجزاء الطالب
</a>

<span class="nav-section-title mt-2">التسميع</span>
<a href="{{ route('supervisor.recitation.index') }}" class="nav-link {{ request()->routeIs('supervisor.recitation.index') ? 'active' : '' }}">
    <i class="fas fa-microphone"></i> جلسة تسميع جديدة
</a>
<a href="{{ route('supervisor.recitation.history') }}" class="nav-link {{ request()->routeIs('supervisor.recitation.history') ? 'active' : '' }}">
    <i class="fas fa-history"></i> سجل الجلسات
</a>
<a href="{{ route('supervisor.exam-assistant.index') }}" class="nav-link {{ request()->routeIs('supervisor.exam-assistant.*') ? 'active' : '' }}">
    <i class="fas fa-graduation-cap"></i> مساعد الاختبار
</a>

<span class="nav-section-title mt-2">التقارير والإنذارات</span>
<a href="{{ route('supervisor.reports.index') }}" class="nav-link {{ request()->routeIs('supervisor.reports.*') ? 'active' : '' }}">
    <i class="fas fa-chart-bar"></i> التقارير
</a>
<a href="{{ route('supervisor.warnings.index') }}" class="nav-link {{ request()->routeIs('supervisor.warnings.*') ? 'active' : '' }}">
    <i class="fas fa-exclamation-triangle"></i> الإنذارات
    @php $warnCount = \App\Models\Warning::where('issued_by', auth()->id())->whereMonth('created_at', now()->month)->count(); @endphp
    @if($warnCount > 0)
        <span class="badge bg-danger">{{ $warnCount }}</span>
    @endif
</a>
<a href="{{ route('supervisor.warning-drafts.index') }}" class="nav-link {{ request()->routeIs('supervisor.warning-drafts.*') ? 'active' : '' }}">
    <i class="fas fa-edit"></i> مسودات الإنذارات
</a>
