<span class="nav-section-title">الرئيسية</span>
<a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
    <div class="icon-wrapper"><i class="fas fa-tachometer-alt"></i></div>
    <span>لوحة التحكم</span>
</a>

<span class="nav-section-title">البرامج</span>
<a href="{{ route('chat.index') }}" class="nav-link {{ request()->routeIs('chat.*') ? 'active' : '' }}">
    <div class="icon-wrapper"><i class="fas fa-comments"></i></div>
    <span>المحادثات</span>
</a>
<a href="{{ route('admin.yawm-al-himma.index') }}" class="nav-link {{ request()->routeIs('admin.yawm-al-himma.*') ? 'active' : '' }}">
    <div class="icon-wrapper"><i class="fas fa-mosque"></i></div>
    <span>يوم الهمة</span>
</a>
<a href="{{ route('admin.iman-program.index') }}" class="nav-link {{ request()->routeIs('admin.iman-program.*') ? 'active' : '' }}">
    <div class="icon-wrapper"><i class="fas fa-star-and-crescent"></i></div>
    <span>البرنامج الإيماني</span>
</a>
<a href="{{ route('admin.tasks.calendar') }}" class="nav-link {{ request()->routeIs('admin.tasks.calendar') ? 'active' : '' }}">
    <div class="icon-wrapper"><i class="fas fa-calendar-alt"></i></div>
    <span>تقويم المهام</span>
</a>

<span class="nav-section-title">إدارة المستخدمين</span>
<a href="{{ route('admin.supervisors.index') }}" class="nav-link {{ request()->routeIs('admin.supervisors.*') ? 'active' : '' }}">
    <div class="icon-wrapper"><i class="fas fa-user-tie"></i></div>
    <span>المشرفون</span>
</a>
<a href="{{ route('admin.levels.index') }}" class="nav-link {{ request()->routeIs('admin.levels.*') ? 'active' : '' }}">
    <div class="icon-wrapper"><i class="fas fa-layer-group"></i></div>
    <span>المستويات</span>
</a>
<a href="{{ route('admin.students.index') }}" class="nav-link {{ request()->routeIs('admin.students.*') ? 'active' : '' }}">
    <div class="icon-wrapper"><i class="fas fa-user-graduate"></i></div>
    <span>الطلاب</span>
</a>
<a href="{{ route('admin.parents.index') }}" class="nav-link {{ request()->routeIs('admin.parents.*') ? 'active' : '' }}">
    <div class="icon-wrapper"><i class="fas fa-users"></i></div>
    <span>أولياء الأمور</span>
</a>

<span class="nav-section-title">السجلات</span>
<a href="{{ route('admin.audit-logs.index') }}" class="nav-link {{ request()->routeIs('admin.audit-logs.*') ? 'active' : '' }}">
    <div class="icon-wrapper"><i class="fas fa-history"></i></div>
    <span>سجل التدقيق</span>
</a>
<a href="{{ route('admin.sessions.index') }}" class="nav-link {{ request()->routeIs('admin.sessions.*') ? 'active' : '' }}">
    <div class="icon-wrapper"><i class="fas fa-microphone"></i></div>
    <span>جلسات التسميع</span>
</a>
<a href="{{ route('admin.reports.index') }}" class="nav-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
    <div class="icon-wrapper"><i class="fas fa-chart-line"></i></div>
    <span>التقارير الشاملة</span>
</a>
<a href="{{ route('admin.student-parts.index') }}" class="nav-link {{ request()->routeIs('admin.student-parts.*') ? 'active' : '' }}">
    <div class="icon-wrapper"><i class="fas fa-book-quran"></i></div>
    <span>سجل أجزاء الطلاب</span>
</a>
<a href="{{ route('admin.ijazah.index') }}" class="nav-link {{ request()->routeIs('admin.ijazah.*') ? 'active' : '' }}">
    <div class="icon-wrapper"><i class="fas fa-award"></i></div>
    <span>الإجازة</span>
</a>

<span class="nav-section-title">إدارة المشروع</span>
<a href="{{ route('admin.settings.index') }}" class="nav-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
    <div class="icon-wrapper"><i class="fas fa-cog"></i></div>
    <span>إعدادات النظام</span>
</a>
<a href="{{ route('admin.billing.index') }}" class="nav-link {{ request()->routeIs('admin.billing.index') || request()->routeIs('admin.billing.history') || request()->routeIs('admin.billing.settings') || request()->routeIs('admin.billing.createBulkUnpaid') || request()->routeIs('admin.billing.markPaid') || request()->routeIs('admin.billing.markPaidBulk') || request()->routeIs('admin.billing.recordPayment') || request()->routeIs('admin.billing.deleteFee') || request()->routeIs('admin.billing.studentsForParent') ? 'active' : '' }}">
    <div class="icon-wrapper"><i class="fas fa-money-bill"></i></div>
    <span>إدارة الرسوم</span>
</a>
<a href="{{ route('admin.billing.feeCategories') }}" class="nav-link {{ request()->routeIs('admin.billing.feeCategories*') ? 'active' : '' }}">
    <div class="icon-wrapper"><i class="fas fa-tags"></i></div>
    <span>فئات الرسوم</span>
</a>
<a href="{{ route('admin.meeting-minutes.index') }}" class="nav-link {{ request()->routeIs('admin.meeting-minutes.*') ? 'active' : '' }}">
    <div class="icon-wrapper"><i class="fas fa-calendar-minus"></i></div>
    <span>محاضر الإجتماعات</span>
</a>
<a href="{{ route('admin.tasks.index') }}" class="nav-link {{ request()->routeIs('admin.tasks.index') ? 'active' : '' }}">
    <div class="icon-wrapper"><i class="fas fa-tasks"></i></div>
    <span>إدارة التكليفات</span>
</a>
<a href="{{ route('admin.warnings.index') }}" class="nav-link {{ request()->routeIs('admin.warnings.*') ? 'active' : '' }}">
    <div class="icon-wrapper"><i class="fas fa-exclamation-triangle"></i></div>
    <span>الإنذارات</span>
</a>
