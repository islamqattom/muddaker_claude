<span class="nav-section-title">الرئيسية</span>
<a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
    <div class="icon-wrapper"><i class="fas fa-tachometer-alt"></i></div>
    <span>لوحة التحكم</span>
</a>
<span class="nav-section-title">إدارة المستخدمين</span>
<a href="{{ route('admin.students.index') }}" class="nav-link {{ request()->routeIs('admin.students.*') ? 'active' : '' }}">
    <div class="icon-wrapper"><i class="fas fa-user-graduate"></i></div>
    <span>الطلاب</span>
</a>
<a href="{{ route('admin.student-parts.index') }}" class="nav-link {{ request()->routeIs('admin.student-parts.*') ? 'active' : '' }}">
    <div class="icon-wrapper"><i class="fas fa-book-quran"></i></div>
    <span>سجل أجزاء الطلاب</span>
</a>
<a href="{{ route('admin.supervisors.index') }}" class="nav-link {{ request()->routeIs('admin.supervisors.*') ? 'active' : '' }}">
    <div class="icon-wrapper"><i class="fas fa-user-tie"></i></div>
    <span>المشرفون</span>
</a>
<a href="{{ route('admin.parents.index') }}" class="nav-link {{ request()->routeIs('admin.parents.*') ? 'active' : '' }}">
    <div class="icon-wrapper"><i class="fas fa-users"></i></div>
    <span>أولياء الأمور</span>
</a>
<span class="nav-section-title">الإعدادات</span>
<a href="{{ route('admin.levels.index') }}" class="nav-link {{ request()->routeIs('admin.levels.*') ? 'active' : '' }}">
    <div class="icon-wrapper"><i class="fas fa-layer-group"></i></div>
    <span>المستويات</span>
</a>
<a href="{{ route('admin.settings.index') }}" class="nav-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
    <div class="icon-wrapper"><i class="fas fa-cog"></i></div>
    <span>إعدادات النظام</span>
</a>
<span class="nav-section-title">سجل التدقيق</span>
<a href="{{ route('admin.audit-logs.index') }}" class="nav-link {{ request()->routeIs('admin.audit-logs.*') ? 'active' : '' }}">
    <div class="icon-wrapper"><i class="fas fa-history"></i></div>
    <span>سجل التدقيق</span>
</a>
<span class="nav-section-title">الفوترة</span>
<a href="{{ route('admin.billing.index') }}" class="nav-link {{ request()->routeIs('admin.billing.*') ? 'active' : '' }}">
    <div class="icon-wrapper"><i class="fas fa-money-bill"></i></div>
    <span>إدارة الرسوم</span>
</a>
<a href="{{ route('admin.billing.feeCategories') }}" class="nav-link {{ request()->routeIs('admin.billing.feeCategories') ? 'active' : '' }}">
    <div class="icon-wrapper"><i class="fas fa-tags"></i></div>
    <span>فئات الرسوم</span>
</a>
<span class="nav-section-title">محاضر الاجتماعات</span>
<a href="{{ route('admin.meeting-minutes.index') }}" class="nav-link {{ request()->routeIs('admin.meeting-minutes.*') ? 'active' : '' }}">
    <div class="icon-wrapper"><i class="fas fa-calendar-minus"></i></div>
    <span>محاضر الاجتماعات</span>
</a>
<a href="{{ route('admin.tasks.index') }}" class="nav-link {{ request()->routeIs('admin.tasks.*') ? 'active' : '' }}">
    <div class="icon-wrapper"><i class="fas fa-tasks"></i></div>
    <span>إدارة التكليفات</span>
</a>
<span class="nav-section-title">التقارير والإنذارات</span>
<a href="{{ route('admin.sessions.index') }}" class="nav-link {{ request()->routeIs('admin.sessions.*') ? 'active' : '' }}">
    <div class="icon-wrapper"><i class="fas fa-microphone"></i></div>
    <span>جلسات التسميع</span>
</a>
<a href="{{ route('admin.reports.index') }}" class="nav-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
    <div class="icon-wrapper"><i class="fas fa-chart-line"></i></div>
    <span>التقارير الشاملة</span>
</a>
<a href="{{ route('admin.warnings.index') }}" class="nav-link {{ request()->routeIs('admin.warnings.*') ? 'active' : '' }}">
    <div class="icon-wrapper"><i class="fas fa-exclamation-triangle"></i></div>
    <span>الإنذارات</span>
</a>
<a href="{{ route('admin.warning-rules.index') }}" class="nav-link {{ request()->routeIs('admin.warning-rules.*') ? 'active' : '' }}">
    <div class="icon-wrapper"><i class="fas fa-exclamation-triangle"></i></div>
    <span>قواعد الإنذار</span>
</a>
