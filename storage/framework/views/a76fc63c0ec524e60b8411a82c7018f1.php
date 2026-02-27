<span class="nav-section-title">الرئيسية</span>
<a href="<?php echo e(route('supervisor.dashboard')); ?>" class="nav-link <?php echo e(request()->routeIs('supervisor.dashboard') ? 'active' : ''); ?>">
    <i class="fas fa-home"></i> لوحة التحكم
</a>

<span class="nav-section-title mt-2">إدارة الطلاب</span>
<a href="<?php echo e(route('supervisor.students.index')); ?>" class="nav-link <?php echo e(request()->routeIs('supervisor.students.*') ? 'active' : ''); ?>">
    <i class="fas fa-users"></i> الطلاب
</a>
<a href="<?php echo e(route('supervisor.attendance.index')); ?>" class="nav-link <?php echo e(request()->routeIs('supervisor.attendance.index') ? 'active' : ''); ?>">
    <i class="fas fa-calendar-check"></i> الحضور والغياب
    <?php $unrecorded = \App\Models\Student::where('supervisor_id', auth()->id())->count() - \App\Models\Attendance::where('supervisor_id', auth()->id())->where('date', today())->count(); ?>
    <?php if($unrecorded > 0): ?>
        <span class="badge bg-warning text-dark"><?php echo e($unrecorded); ?></span>
    <?php endif; ?>
</a>

<span class="nav-section-title mt-2">التسميع</span>
<a href="<?php echo e(route('supervisor.recitation.index')); ?>" class="nav-link <?php echo e(request()->routeIs('supervisor.recitation.index') ? 'active' : ''); ?>">
    <i class="fas fa-microphone"></i> جلسة تسميع جديدة
</a>
<a href="<?php echo e(route('supervisor.recitation.history')); ?>" class="nav-link <?php echo e(request()->routeIs('supervisor.recitation.history') ? 'active' : ''); ?>">
    <i class="fas fa-history"></i> سجل الجلسات
</a>

<span class="nav-section-title mt-2">التقارير والإنذارات</span>
<a href="<?php echo e(route('supervisor.reports.index')); ?>" class="nav-link <?php echo e(request()->routeIs('supervisor.reports.*') ? 'active' : ''); ?>">
    <i class="fas fa-chart-bar"></i> التقارير
</a>
<a href="<?php echo e(route('supervisor.warnings.index')); ?>" class="nav-link <?php echo e(request()->routeIs('supervisor.warnings.*') ? 'active' : ''); ?>">
    <i class="fas fa-exclamation-triangle"></i> الإنذارات
    <?php $warnCount = \App\Models\Warning::where('issued_by', auth()->id())->whereMonth('created_at', now()->month)->count(); ?>
    <?php if($warnCount > 0): ?>
        <span class="badge bg-danger"><?php echo e($warnCount); ?></span>
    <?php endif; ?>
</a>
<?php /**PATH C:\xampp\htdocs\quran-system\resources\views/supervisor/partials/sidebar.blade.php ENDPATH**/ ?>