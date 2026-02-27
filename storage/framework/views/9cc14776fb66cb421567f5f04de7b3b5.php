<?php $__env->startSection('title', 'تسجيل الحضور والغياب'); ?>
<?php $__env->startSection('page-title', 'الحضور والغياب'); ?>
<?php $__env->startSection('role-label', 'مشرف'); ?>

<?php $__env->startSection('sidebar-menu'); ?>
    <?php echo $__env->make('supervisor.partials.sidebar', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('breadcrumb'); ?>
    <li class="breadcrumb-item"><a href="<?php echo e(route('supervisor.dashboard')); ?>">الرئيسية</a></li>
    <li class="breadcrumb-item active">الحضور والغياب</li>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="card mb-4">
    <div class="card-body">
        <div class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label">التاريخ</label>
                <input type="date" id="dateFilter" class="form-control"
                       value="<?php echo e($date->toDateString()); ?>"
                       onchange="window.location='<?php echo e(route('supervisor.attendance.index')); ?>?date='+this.value">
            </div>
            <div class="col-md-4">
                <div class="d-flex gap-2">
                    <div class="text-center p-2 rounded bg-success text-white flex-fill">
                        <div class="fw-bold fs-4"><?php echo e($stats['present']); ?></div>
                        <small>حاضر</small>
                    </div>
                    <div class="text-center p-2 rounded bg-danger text-white flex-fill">
                        <div class="fw-bold fs-4"><?php echo e($stats['absent']); ?></div>
                        <small>غائب</small>
                    </div>
                    <div class="text-center p-2 rounded bg-warning text-dark flex-fill">
                        <div class="fw-bold fs-4"><?php echo e($stats['late']); ?></div>
                        <small>متأخر</small>
                    </div>
                    <div class="text-center p-2 rounded bg-secondary text-white flex-fill">
                        <div class="fw-bold fs-4"><?php echo e($stats['total'] - $stats['present'] - $stats['absent'] - $stats['late'] - $stats['excused']); ?></div>
                        <small>غير مسجل</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0">
            <i class="fas fa-calendar-check me-2"></i>
            سجل حضور يوم <?php echo e($date->translatedFormat('l d/m/Y')); ?>

        </h6>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-sm btn-outline-success" onclick="markAll('present')">
                <i class="fas fa-check me-1"></i>تحديد الكل حاضر
            </button>
            <button type="button" class="btn btn-sm btn-outline-danger" onclick="markAll('absent')">
                <i class="fas fa-times me-1"></i>تحديد الكل غائب
            </button>
        </div>
    </div>
    <div class="card-body p-0">
        <?php if($students->isEmpty()): ?>
            <div class="text-center py-5 text-muted">
                <i class="fas fa-users fa-3x mb-3 d-block"></i>
                لا يوجد طلاب مسجلون تحت إشرافك
            </div>
        <?php else: ?>
        <form action="<?php echo e(route('supervisor.attendance.store')); ?>" method="POST" id="attendanceForm">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="date" value="<?php echo e($date->toDateString()); ?>">

            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="40">#</th>
                            <th>اسم الطالب</th>
                            <th>المستوى</th>
                            <th width="300">الحالة</th>
                            <th width="150">وقت الحضور</th>
                            <th width="200">ملاحظة</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $students; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $student): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php $currentStatus = $existingAttendances[$student->id] ?? null; ?>
                        <tr id="row-<?php echo e($student->id); ?>">
                            <td><?php echo e($index + 1); ?></td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="student-avatar" style="width:36px;height:36px;border-radius:50%;background:#1a5276;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:bold;">
                                        <?php echo e(mb_substr($student->user->name, 0, 1)); ?>

                                    </div>
                                    <div>
                                        <div class="fw-semibold"><?php echo e($student->user->name); ?></div>
                                        <small class="text-muted"><?php echo e($student->student_number); ?></small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark"><?php echo e($student->level->name ?? '-'); ?></span>
                            </td>
                            <td>
                                <div class="btn-group attendance-btns" role="group">
                                    <input type="radio" class="btn-check" name="attendance[<?php echo e($student->id); ?>][status]"
                                           id="present-<?php echo e($student->id); ?>" value="present"
                                           <?php echo e($currentStatus === 'present' ? 'checked' : ''); ?>

                                           onchange="handleStatusChange(<?php echo e($student->id); ?>, 'present')">
                                    <label class="btn btn-sm btn-outline-success" for="present-<?php echo e($student->id); ?>">
                                        <i class="fas fa-check me-1"></i>حاضر
                                    </label>

                                    <input type="radio" class="btn-check" name="attendance[<?php echo e($student->id); ?>][status]"
                                           id="late-<?php echo e($student->id); ?>" value="late"
                                           <?php echo e($currentStatus === 'late' ? 'checked' : ''); ?>

                                           onchange="handleStatusChange(<?php echo e($student->id); ?>, 'late')">
                                    <label class="btn btn-sm btn-outline-warning" for="late-<?php echo e($student->id); ?>">
                                        <i class="fas fa-clock me-1"></i>متأخر
                                    </label>

                                    <input type="radio" class="btn-check" name="attendance[<?php echo e($student->id); ?>][status]"
                                           id="absent-<?php echo e($student->id); ?>" value="absent"
                                           <?php echo e($currentStatus === 'absent' ? 'checked' : ''); ?>

                                           onchange="handleStatusChange(<?php echo e($student->id); ?>, 'absent')">
                                    <label class="btn btn-sm btn-outline-danger" for="absent-<?php echo e($student->id); ?>">
                                        <i class="fas fa-times me-1"></i>غائب
                                    </label>

                                    <input type="radio" class="btn-check" name="attendance[<?php echo e($student->id); ?>][status]"
                                           id="excused-<?php echo e($student->id); ?>" value="excused"
                                           <?php echo e($currentStatus === 'excused' ? 'checked' : ''); ?>

                                           onchange="handleStatusChange(<?php echo e($student->id); ?>, 'excused')">
                                    <label class="btn btn-sm btn-outline-info" for="excused-<?php echo e($student->id); ?>">
                                        <i class="fas fa-file-alt me-1"></i>بعذر
                                    </label>
                                </div>
                            </td>
                            <td>
                                <input type="time"
                                       name="attendance[<?php echo e($student->id); ?>][arrival_time]"
                                       class="form-control form-control-sm arrival-time"
                                       id="arrival-<?php echo e($student->id); ?>"
                                       style="<?php echo e($currentStatus === 'late' ? '' : 'display:none;'); ?>"
                                       value="<?php echo e(optional($student->attendances->first())->arrival_time); ?>">
                            </td>
                            <td>
                                <input type="text"
                                       name="attendance[<?php echo e($student->id); ?>][notes]"
                                       class="form-control form-control-sm"
                                       placeholder="ملاحظة...">
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>

            <div class="p-3 border-top d-flex justify-content-between align-items-center">
                <span class="text-muted"><?php echo e($students->count()); ?> طالب</span>
                <button type="submit" class="btn btn-primary btn-lg px-5">
                    <i class="fas fa-save me-2"></i>
                    حفظ سجل الحضور
                </button>
            </div>
        </form>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function handleStatusChange(studentId, status) {
    const arrivalInput = document.getElementById('arrival-' + studentId);
    if (status === 'late') {
        arrivalInput.style.display = '';
        arrivalInput.required = true;
    } else {
        arrivalInput.style.display = 'none';
        arrivalInput.required = false;
    }
    // Color the row
    const row = document.getElementById('row-' + studentId);
    row.className = 'table-' + (status === 'present' ? 'success' : status === 'absent' ? 'danger' : status === 'late' ? 'warning' : 'info');
}

function markAll(status) {
    document.querySelectorAll(`input[type="radio"][value="${status}"]`).forEach(radio => {
        radio.checked = true;
        const studentId = radio.id.split('-')[1];
        handleStatusChange(studentId, status);
    });
}

// Initialize colors on load
document.addEventListener('DOMContentLoaded', function() {
    <?php $__currentLoopData = $students; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $student): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <?php $status = $existingAttendances[$student->id] ?? null; ?>
    <?php if($status): ?>
        handleStatusChange(<?php echo e($student->id); ?>, '<?php echo e($status); ?>');
    <?php endif; ?>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
});
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\quran-system\resources\views/supervisor/attendance/index.blade.php ENDPATH**/ ?>