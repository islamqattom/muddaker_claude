<span class="nav-section-title">الرئيسية</span>
<a href="{{ route('student.dashboard') }}" class="nav-link {{ request()->routeIs('student.dashboard') ? 'active' : '' }}">
    <div class="icon-wrapper"><i class="fas fa-home"></i></div>
    <span>لوحتي الشخصية</span>
</a>
<a href="{{ route('chat.index') }}" class="nav-link {{ request()->routeIs('chat.*') ? 'active' : '' }}">
    <div class="icon-wrapper"><i class="fas fa-comments"></i></div>
    <span>المحادثات</span>
</a>
<span class="nav-section-title">القرآن الكريم</span>
<a href="{{ route('student.quran.page', 1) }}" class="nav-link {{ request()->routeIs('student.quran.page') ? 'active' : '' }}">
    <div class="icon-wrapper"><i class="fas fa-book-open"></i></div>
    <span>قراءة القرآن</span>
</a>
<a href="{{ route('student.live-recitation') }}" class="nav-link {{ request()->routeIs('student.live-recitation*') ? 'active' : '' }}">
    <div class="icon-wrapper"><i class="fas fa-microphone-alt"></i></div>
    <span>تسميع القرآن</span>
</a>
<a href="{{ route('student.voice-search') }}" class="nav-link {{ request()->routeIs('student.voice-search') ? 'active' : '' }}">
    <div class="icon-wrapper"><i class="fas fa-microphone"></i></div>
    <span>البحث الصوتي</span>
</a>
<a href="{{ route('student.tafseer') }}" class="nav-link {{ request()->routeIs('student.tafseer') ? 'active' : '' }}">
    <div class="icon-wrapper"><i class="fas fa-book"></i></div>
    <span>التفسير</span>
</a>
<a href="{{ route('student.tarteel-challenges') }}" class="nav-link {{ request()->routeIs('student.tarteel-challenges') ? 'active' : '' }}">
    <div class="icon-wrapper"><i class="fas fa-trophy"></i></div>
    <span>التحديات</span>
</a>
<span class="nav-section-title">المتابعة</span>
<a href="{{ route('student.sessions.index') }}" class="nav-link {{ request()->routeIs('student.sessions.*') ? 'active' : '' }}">
    <div class="icon-wrapper"><i class="fas fa-microphone"></i></div>
    <span>جلسات التسميع</span>
</a>
<a href="{{ route('student.attendance.index') }}" class="nav-link {{ request()->routeIs('student.attendance.*') ? 'active' : '' }}">
    <div class="icon-wrapper"><i class="fas fa-calendar-check"></i></div>
    <span>سجل حضوري</span>
</a>
<a href="{{ route('student.student-parts') }}" class="nav-link {{ request()->routeIs('student.student-parts') ? 'active' : '' }}">
    <div class="icon-wrapper"><i class="fas fa-book-quran"></i></div>
    <span>أجزائي</span>
</a>
<a href="{{ route('student.yawm-al-himma.index') }}" class="nav-link {{ request()->routeIs('student.yawm-al-himma.*') ? 'active' : '' }}">
    <div class="icon-wrapper"><i class="fas fa-mosque"></i></div>
    <span>يوم الهمة</span>
</a>
<a href="{{ route('student.reports.index') }}" class="nav-link {{ request()->routeIs('student.reports.*') ? 'active' : '' }}">
    <div class="icon-wrapper"><i class="fas fa-chart-bar"></i></div>
    <span>تقاريري</span>
</a>
<a href="{{ route('student.warnings.index') }}" class="nav-link {{ request()->routeIs('student.warnings.*') ? 'active' : '' }}">
    <div class="icon-wrapper"><i class="fas fa-exclamation-triangle"></i></div>
    <span>الإنذارات</span>
</a>
<a href="{{ route('student.tasks.calendar') }}" class="nav-link {{ request()->routeIs('student.tasks.*') ? 'active' : '' }}">
    <div class="icon-wrapper"><i class="fas fa-calendar-alt"></i></div>
    <span>تقويم مهامي</span>
</a>
<a href="{{ route('student.azkar') }}" class="nav-link {{ request()->routeIs('student.azkar*') ? 'active' : '' }}">
    <div class="icon-wrapper"><i class="fas fa-quran"></i></div>
    <span>أذكار المسلم</span>
</a>
<a href="{{ route('student.iman-program.index') }}" class="nav-link {{ request()->routeIs('student.iman-program.*') ? 'active' : '' }}">
    <div class="icon-wrapper"><i class="fas fa-star-and-crescent"></i></div>
    <span>البرنامج الإيماني</span>
</a>
<span class="nav-section-title">المساعدة</span>
<a href="{{ route('student.memorization') }}" class="nav-link {{ request()->routeIs('student.memorization') ? 'active' : '' }}">
    <div class="icon-wrapper"><i class="fas fa-book-open"></i></div>
    <span>طرق الحفظ</span>
</a>
<span class="nav-section-title">الإعدادات</span>
<a href="{{ route('student.profile.index') }}" class="nav-link {{ request()->routeIs('student.profile.*') ? 'active' : '' }}">
    <div class="icon-wrapper"><i class="fas fa-user-cog"></i></div>
    <span>ملفي الشخصي</span>
</a>
