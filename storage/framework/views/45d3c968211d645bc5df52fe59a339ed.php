<span class="nav-section-title">الرئيسية</span>
<a href="<?php echo e(route('student.dashboard')); ?>" class="nav-link <?php echo e(request()->routeIs('student.dashboard') ? 'active' : ''); ?>">
    <i class="fas fa-home"></i> لوحتي الشخصية
</a>
<span class="nav-section-title mt-2">المتابعة</span>
<a href="<?php echo e(route('student.sessions.index')); ?>" class="nav-link <?php echo e(request()->routeIs('student.sessions.*') ? 'active' : ''); ?>">
    <i class="fas fa-microphone"></i> جلسات التسميع
</a>
<a href="<?php echo e(route('student.attendance.index')); ?>" class="nav-link <?php echo e(request()->routeIs('student.attendance.*') ? 'active' : ''); ?>">
    <i class="fas fa-calendar-check"></i> سجل حضوري
</a>
<a href="<?php echo e(route('student.reports.index')); ?>" class="nav-link <?php echo e(request()->routeIs('student.reports.*') ? 'active' : ''); ?>">
    <i class="fas fa-chart-bar"></i> تقاريري
</a>
<a href="<?php echo e(route('student.warnings.index')); ?>" class="nav-link <?php echo e(request()->routeIs('student.warnings.*') ? 'active' : ''); ?>">
    <i class="fas fa-exclamation-triangle"></i> الإنذارات
</a>
<span class="nav-section-title mt-2">المساعدة</span>
<a href="<?php echo e(route('student.memorization')); ?>" class="nav-link <?php echo e(request()->routeIs('student.memorization') ? 'active' : ''); ?>">
    <i class="fas fa-book-open"></i> طرق الحفظ
</a>
<?php /**PATH C:\xampp\htdocs\quran-system\resources\views/student/partials/sidebar.blade.php ENDPATH**/ ?>