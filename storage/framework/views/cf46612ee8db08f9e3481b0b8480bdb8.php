<?php $__env->startSection('title', 'لوحة تحكم الإدارة'); ?>
<?php $__env->startSection('page-title', 'لوحة التحكم الرئيسية'); ?>
<?php $__env->startSection('role-label', 'مدير النظام'); ?>
<?php $__env->startSection('sidebar-menu'); ?>
<span class="nav-section-title">الرئيسية</span>
<a href="<?php echo e(route('admin.dashboard')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.dashboard') ? 'active' : ''); ?>">
    <i class="fas fa-tachometer-alt"></i> لوحة التحكم
</a>
<span class="nav-section-title mt-2">إدارة المستخدمين</span>
<a href="<?php echo e(route('admin.students.index')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.students.*') ? 'active' : ''); ?>">
    <i class="fas fa-user-graduate"></i> الطلاب
    <span class="badge bg-primary"><?php echo e(\App\Models\Student::count()); ?></span>
</a>
<a href="<?php echo e(route('admin.supervisors.index')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.supervisors.*') ? 'active' : ''); ?>">
    <i class="fas fa-user-tie"></i> المشرفون
</a>
<a href="<?php echo e(route('admin.parents.index')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.parents.*') ? 'active' : ''); ?>">
    <i class="fas fa-users"></i> أولياء الأمور
</a>
<span class="nav-section-title mt-2">الإعدادات</span>
<a href="<?php echo e(route('admin.levels.index')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.levels.*') ? 'active' : ''); ?>">
    <i class="fas fa-layer-group"></i> المستويات
</a>
<a href="<?php echo e(route('admin.settings.index')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.settings.*') ? 'active' : ''); ?>">
    <i class="fas fa-cog"></i> إعدادات النظام
</a>
<span class="nav-section-title mt-2">التقارير والإنذارات</span>
<a href="<?php echo e(route('admin.reports.index')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.reports.*') ? 'active' : ''); ?>">
    <i class="fas fa-chart-line"></i> التقارير الشاملة
</a>
<a href="<?php echo e(route('admin.warnings.index')); ?>" class="nav-link <?php echo e(request()->routeIs('admin.warnings.*') ? 'active' : ''); ?>">
    <i class="fas fa-exclamation-triangle"></i> الإنذارات
</a>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
<div class="row g-4">
    <div class="col-6 col-md-3">
        <div class="stat-card" style="background:linear-gradient(135deg,#1a5276,#2980b9);">
            <i class="fas fa-user-graduate icon"></i>
            <div class="number"><?php echo e($stats['total_students']); ?></div>
            <div class="label">إجمالي الطلاب</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card" style="background:linear-gradient(135deg,#8e44ad,#9b59b6);">
            <i class="fas fa-user-tie icon"></i>
            <div class="number"><?php echo e($stats['total_supervisors']); ?></div>
            <div class="label">المشرفون</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card" style="background:linear-gradient(135deg,#27ae60,#2ecc71);">
            <i class="fas fa-user-check icon"></i>
            <div class="number"><?php echo e($stats['today_present']); ?></div>
            <div class="label">حاضر اليوم</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card" style="background:linear-gradient(135deg,#f39c12,#e67e22);">
            <i class="fas fa-microphone icon"></i>
            <div class="number"><?php echo e($stats['today_sessions']); ?></div>
            <div class="label">جلسات اليوم</div>
        </div>
    </div>
</div>

<div class="row g-4 mt-1">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h6><i class="fas fa-chart-bar me-2"></i>إحصائيات آخر 7 أيام</h6>
            </div>
            <div class="card-body">
                <canvas id="weeklyChart" height="120"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header"><h6><i class="fas fa-bolt me-2"></i>إجراءات سريعة</h6></div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="<?php echo e(route('admin.students.create')); ?>" class="btn btn-primary">
                        <i class="fas fa-user-plus me-2"></i>إضافة طالب جديد
                    </a>
                    <a href="<?php echo e(route('admin.supervisors.create')); ?>" class="btn btn-outline-primary">
                        <i class="fas fa-user-plus me-2"></i>إضافة مشرف جديد
                    </a>
                    <a href="<?php echo e(route('admin.reports.recitation')); ?>" class="btn btn-outline-info">
                        <i class="fas fa-chart-bar me-2"></i>تقرير التسميع
                    </a>
                    <a href="<?php echo e(route('admin.reports.attendance')); ?>" class="btn btn-outline-success">
                        <i class="fas fa-calendar me-2"></i>تقرير الحضور
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mt-1">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header"><h6><i class="fas fa-history me-2"></i>آخر جلسات التسميع</h6></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead class="table-light"><tr><th>الطالب</th><th>المشرف</th><th>النوع</th><th>النتيجة</th><th>التاريخ</th></tr></thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $recentSessions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $session): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><?php echo e($session->student->user->name); ?></td>
                                <td><small><?php echo e($session->supervisor->name); ?></small></td>
                                <td><span class="badge bg-info text-dark"><?php echo e($session->type_label); ?></span></td>
                                <td><span class="badge bg-<?php echo e($session->status_color); ?>"><?php echo e($session->status_label); ?></span></td>
                                <td><small><?php echo e($session->session_date->format('d/m')); ?></small></td>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr><td colspan="5" class="text-center py-3 text-muted">لا توجد جلسات</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header"><h6><i class="fas fa-exclamation-triangle me-2"></i>آخر الإنذارات</h6></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead class="table-light"><tr><th>الطالب</th><th>النوع</th><th>المشرف</th><th>التاريخ</th></tr></thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $recentWarnings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $warning): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><?php echo e($warning->student->user->name); ?></td>
                                <td><span class="badge bg-warning text-dark"><?php echo e($warning->type_label); ?></span></td>
                                <td><small><?php echo e($warning->issuedBy->name); ?></small></td>
                                <td><small><?php echo e($warning->issue_date->format('d/m')); ?></small></td>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr><td colspan="4" class="text-center py-3 text-muted">لا توجد إنذارات</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php $__env->startPush('scripts'); ?>
<script>
const weeklyData = <?php echo json_encode($weeklySessions, 15, 512) ?>;
const ctx = document.getElementById('weeklyChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: weeklyData.map(d => d.date),
        datasets: [
            { label: 'جلسات ناجحة', data: weeklyData.map(d => d.passed), backgroundColor: '#27ae60' },
            { label: 'جلسات راسبة', data: weeklyData.map(d => d.failed), backgroundColor: '#c0392b' },
        ]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'top' } },
        scales: { y: { beginAtZero: true } }
    }
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\quran-system\resources\views/admin/dashboard/index.blade.php ENDPATH**/ ?>