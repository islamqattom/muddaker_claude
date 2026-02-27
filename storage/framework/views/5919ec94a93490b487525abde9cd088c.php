<?php $__env->startSection('title', 'بوابة ولي الأمر'); ?>
<?php $__env->startSection('page-title', 'متابعة أبنائي'); ?>
<?php $__env->startSection('role-label', 'ولي أمر'); ?>
<?php $__env->startSection('sidebar-menu'); ?>
<span class="nav-section-title">الرئيسية</span>
<a href="<?php echo e(route('parent.dashboard')); ?>" class="nav-link <?php echo e(request()->routeIs('parent.dashboard') ? 'active' : ''); ?>">
    <i class="fas fa-home"></i> الصفحة الرئيسية
</a>
<span class="nav-section-title mt-2">المتابعة</span>
<a href="<?php echo e(route('parent.sessions.index')); ?>" class="nav-link"><i class="fas fa-microphone"></i> جلسات التسميع</a>
<a href="<?php echo e(route('parent.attendance.index')); ?>" class="nav-link"><i class="fas fa-calendar-check"></i> سجل الحضور</a>
<a href="<?php echo e(route('parent.warnings.index')); ?>" class="nav-link">
    <i class="fas fa-exclamation-triangle"></i> الإنذارات
    <?php $unread = \App\Models\Warning::whereHas('student', fn($q)=>$q->whereHas('parents',fn($p)=>$p->where('user_id',auth()->id())))->where('is_read_by_parent',false)->count(); ?>
    <?php if($unread > 0): ?><span class="badge bg-danger"><?php echo e($unread); ?></span><?php endif; ?>
</a>
<a href="<?php echo e(route('parent.reports.index')); ?>" class="nav-link"><i class="fas fa-chart-bar"></i> التقارير</a>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
<div class="card mb-4" style="background:linear-gradient(135deg,#1a5276,#0d3349);color:#fff;">
    <div class="card-body py-4 px-4">
        <h4 class="mb-1">أهلاً <?php echo e(auth()->user()->name); ?></h4>
        <p class="mb-0 opacity-75">تابع تقدم أبنائك في حفظ القرآن الكريم</p>
    </div>
</div>

<?php $__currentLoopData = $studentsData; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
<?php $student = $data['student']; ?>
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-3">
            <div style="width:45px;height:45px;border-radius:50%;background:#1a5276;display:flex;align-items:center;justify-content:center;color:#fff;font-size:18px;font-weight:bold;">
                <?php echo e(mb_substr($student->user->name, 0, 1)); ?>

            </div>
            <div>
                <h5 class="mb-0"><?php echo e($student->user->name); ?></h5>
                <small class="text-muted"><?php echo e($student->level->name ?? ''); ?> | المشرف: <?php echo e($student->supervisor->name ?? 'غير محدد'); ?></small>
            </div>
        </div>
        <a href="<?php echo e(route('parent.student.detail', $student->id)); ?>" class="btn btn-outline-primary btn-sm">
            <i class="fas fa-eye me-1"></i>تفاصيل كاملة
        </a>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-6">
                <h6 class="text-muted mb-2"><i class="fas fa-calendar me-1"></i>الحضور هذا الشهر</h6>
                <div class="row g-2 text-center">
                    <div class="col-3"><div class="p-2 rounded bg-success text-white"><div class="fw-bold fs-5"><?php echo e($data['attendance']['present']); ?></div><small>حاضر</small></div></div>
                    <div class="col-3"><div class="p-2 rounded bg-danger text-white"><div class="fw-bold fs-5"><?php echo e($data['attendance']['absent']); ?></div><small>غائب</small></div></div>
                    <div class="col-3"><div class="p-2 rounded bg-warning text-dark"><div class="fw-bold fs-5"><?php echo e($data['attendance']['late']); ?></div><small>متأخر</small></div></div>
                    <div class="col-3"><div class="p-2 rounded bg-secondary text-white"><div class="fw-bold fs-5"><?php echo e($data['attendance']['total']); ?></div><small>إجمالي</small></div></div>
                </div>
            </div>
            <div class="col-md-6">
                <h6 class="text-muted mb-2"><i class="fas fa-microphone me-1"></i>التسميع هذا الشهر</h6>
                <div class="row g-2 text-center">
                    <div class="col-3"><div class="p-2 rounded bg-primary text-white"><div class="fw-bold fs-5"><?php echo e($data['sessions']['total']); ?></div><small>جلسات</small></div></div>
                    <div class="col-3"><div class="p-2 rounded bg-success text-white"><div class="fw-bold fs-5"><?php echo e($data['sessions']['passed']); ?></div><small>ناجح</small></div></div>
                    <div class="col-3"><div class="p-2 rounded bg-danger text-white"><div class="fw-bold fs-5"><?php echo e($data['sessions']['failed']); ?></div><small>راسب</small></div></div>
                    <div class="col-3"><div class="p-2 rounded bg-light text-dark"><div class="fw-bold fs-5"><?php echo e($data['sessions']['total'] > 0 ? round($data['sessions']['passed']/$data['sessions']['total']*100) : 100); ?>%</div><small>النجاح</small></div></div>
                </div>
            </div>
        </div>

        <?php if($data['recent_warnings']->count() > 0): ?>
        <div class="mt-3 p-3 bg-warning bg-opacity-10 rounded border border-warning">
            <h6 class="text-warning"><i class="fas fa-exclamation-triangle me-2"></i>إنذارات جديدة</h6>
            <?php $__currentLoopData = $data['recent_warnings']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $warning): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="d-flex justify-content-between">
                <span style="font-size:13px;"><?php echo e($warning->title); ?></span>
                <small class="text-muted"><?php echo e($warning->issue_date->format('d/m/Y')); ?></small>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

<?php if(count($studentsData) === 0): ?>
<div class="text-center py-5">
    <i class="fas fa-users fa-3x text-muted mb-3 d-block"></i>
    <h5 class="text-muted">لا يوجد طلاب مرتبطون بحسابك</h5>
    <p class="text-muted">يرجى التواصل مع إدارة الجمعية</p>
</div>
<?php endif; ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\quran-system\resources\views/parent/dashboard/index.blade.php ENDPATH**/ ?>