<span class="nav-section-title">الرئيسية</span>
<a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
    <i class="fas fa-tachometer-alt"></i> لوحة التحكم
</a>
<span class="nav-section-title mt-2">إدارة المستخدمين</span>
<a href="{{ route('admin.students.index') }}" class="nav-link {{ request()->routeIs('admin.students.*') ? 'active' : '' }}">
    <i class="fas fa-user-graduate"></i> الطلاب
</a>
<a href="{{ route('admin.supervisors.index') }}" class="nav-link {{ request()->routeIs('admin.supervisors.*') ? 'active' : '' }}">
    <i class="fas fa-user-tie"></i> المشرفون
</a>
<a href="{{ route('admin.parents.index') }}" class="nav-link {{ request()->routeIs('admin.parents.*') ? 'active' : '' }}">
    <i class="fas fa-users"></i> أولياء الأمور
</a>
<span class="nav-section-title mt-2">الإعدادات</span>
<a href="{{ route('admin.levels.index') }}" class="nav-link {{ request()->routeIs('admin.levels.*') ? 'active' : '' }}">
    <i class="fas fa-layer-group"></i> المستويات
</a>
<a href="{{ route('admin.settings.index') }}" class="nav-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
    <i class="fas fa-cog"></i> إعدادات النظام
</a>
<span class="nav-section-title mt-2">التقارير والإنذارات</span>
<a href="{{ route('admin.sessions.index') }}" class="nav-link {{ request()->routeIs('admin.sessions.*') ? 'active' : '' }}">
    <i class="fas fa-microphone"></i> جلسات التسميع
</a>
<a href="{{ route('admin.reports.index') }}" class="nav-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
    <i class="fas fa-chart-line"></i> التقارير الشاملة
</a>
<a href="{{ route('admin.warnings.index') }}" class="nav-link {{ request()->routeIs('admin.warnings.*') ? 'active' : '' }}">
    <i class="fas fa-exclamation-triangle"></i> الإنذارات
</a>
<a href="{{ route('admin.warning-rules.index') }}" class="nav-link {{ request()->routeIs('admin.warning-rules.*') ? 'active' : '' }}">
    <i class="fas fa-exclamation-triangle"></i> قواعد الإنذار
</a>
