<span class="nav-section-title">الرئيسية</span>
<a href="{{ route('student.dashboard') }}" class="nav-link {{ request()->routeIs('student.dashboard') ? 'active' : '' }}">
    <i class="fas fa-home"></i> لوحتي الشخصية
</a>
<span class="nav-section-title mt-2">القرآن الكريم</span>
<a href="{{ route('student.quran') }}" class="nav-link {{ request()->routeIs('student.quran*') ? 'active' : '' }}">
    <i class="fas fa-book-quran"></i> قراءة القرآن
</a>
<a href="{{ route('student.live-recitation') }}" class="nav-link {{ request()->routeIs('student.live-recitation*') ? 'active' : '' }}">
    <i class="fas fa-microphone-alt"></i> التسميع المباشر
</a>
<span class="nav-section-title mt-2">المتابعة</span>
<a href="{{ route('student.sessions.index') }}" class="nav-link {{ request()->routeIs('student.sessions.*') ? 'active' : '' }}">
    <i class="fas fa-microphone"></i> جلسات التسميع
</a>
<a href="{{ route('student.attendance.index') }}" class="nav-link {{ request()->routeIs('student.attendance.*') ? 'active' : '' }}">
    <i class="fas fa-calendar-check"></i> سجل حضوري
</a>
<a href="{{ route('student.reports.index') }}" class="nav-link {{ request()->routeIs('student.reports.*') ? 'active' : '' }}">
    <i class="fas fa-chart-bar"></i> تقاريري
</a>
<a href="{{ route('student.warnings.index') }}" class="nav-link {{ request()->routeIs('student.warnings.*') ? 'active' : '' }}">
    <i class="fas fa-exclamation-triangle"></i> الإنذارات
</a>
<span class="nav-section-title mt-2">التقدم والإنجازات</span>
<a href="{{ route('student.progress') }}" class="nav-link {{ request()->routeIs('student.progress*') ? 'active' : '' }}">
    <i class="fas fa-chart-line"></i> تقدمي
</a>
<a href="{{ route('student.achievements') }}" class="nav-link {{ request()->routeIs('student.achievements*') ? 'active' : '' }}">
    <i class="fas fa-trophy"></i> إنجازاتي
</a>
<a href="{{ route('student.bookmarks') }}" class="nav-link {{ request()->routeIs('student.bookmarks*') ? 'active' : '' }}">
    <i class="fas fa-bookmark"></i> علاماتي
</a>
<span class="nav-section-title mt-2">المساعدة</span>
<a href="{{ route('student.memorization') }}" class="nav-link {{ request()->routeIs('student.memorization') ? 'active' : '' }}">
    <i class="fas fa-book-open"></i> طرق الحفظ
</a>
<span class="nav-section-title mt-2">الإعدادات</span>
<a href="{{ route('student.profile.index') }}" class="nav-link {{ request()->routeIs('student.profile.*') ? 'active' : '' }}">
    <i class="fas fa-user-cog"></i> ملفي الشخصي
</a>
