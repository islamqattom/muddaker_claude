<?php $__env->startSection('title','تسميع - سرد'); ?>
<?php $__env->startSection('page-title','جلسة سرد - الجزء <?php echo e($part->part_number); ?>'); ?>
<?php $__env->startSection('sidebar-menu'); ?> <?php echo $__env->make('supervisor.partials.sidebar', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?> <?php $__env->stopSection(); ?>
<?php $__env->startSection('content'); ?>
<div class="alert alert-info">
    <i class="fas fa-info-circle me-2"></i>
    <strong>السرد:</strong> تسميع الجزء <?php echo e($part->part_number); ?> كاملاً (<?php echo e(count($pages)); ?> صفحة)
    | في حال وصل الطالب لـ <strong>5 أخطاء</strong> يظهر تنبيه للإكمال أو التوقف
</div>
<div class="card mb-3">
    <div class="card-body d-flex align-items-center gap-3">
        <div style="width:50px;height:50px;border-radius:50%;background:#1a5276;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:bold;font-size:20px;">
            <?php echo e(mb_substr($student->user->name,0,1)); ?>

        </div>
        <div>
            <h5 class="mb-0"><?php echo e($student->user->name); ?></h5>
            <span class="badge bg-info text-dark">سرد | الجزء <?php echo e($part->part_number); ?></span>
            <span class="badge bg-secondary ms-1" id="error-counter">الأخطاء: 0 / 5</span>
        </div>
    </div>
</div>
<form id="sardForm">
    <?php echo csrf_field(); ?>
    <input type="hidden" name="student_id" value="<?php echo e($student->id); ?>">
    <input type="hidden" name="type" value="recitation">
    <input type="hidden" name="part_id" value="<?php echo e($part->id); ?>">
    <input type="hidden" name="session_date" value="<?php echo e(date('Y-m-d')); ?>">
    <input type="hidden" name="stopped" id="stoppedFlag" value="0">
    <div class="row g-2">
        <?php $__currentLoopData = $pages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $pageNum): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="col-md-4 col-lg-3">
            <div class="card page-card" id="page-<?php echo e($index); ?>">
                <div class="card-body p-2">
                    <div class="text-center mb-2 fw-bold" style="font-size:12px;">ص <?php echo e($pageNum); ?></div>
                    <input type="hidden" name="pages[<?php echo e($index); ?>][page_number]" value="<?php echo e($pageNum); ?>">
                    <div class="row g-1">
                        <div class="col-6">
                            <label style="font-size:10px;" class="d-block text-center text-danger">أخطاء</label>
                            <input type="number" name="pages[<?php echo e($index); ?>][errors]" id="e-<?php echo e($index); ?>"
                                   class="form-control form-control-sm text-center" value="0" min="0"
                                   oninput="updateSardColor(<?php echo e($index); ?>)">
                        </div>
                        <div class="col-6">
                            <label style="font-size:10px;" class="d-block text-center text-warning">ترددات</label>
                            <input type="number" name="pages[<?php echo e($index); ?>][hesitations]" id="h-<?php echo e($index); ?>"
                                   class="form-control form-control-sm text-center" value="0" min="0"
                                   oninput="updateSardColor(<?php echo e($index); ?>)">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
    <div class="d-flex gap-3 mt-4">
        <a href="<?php echo e(route('supervisor.recitation.index')); ?>" class="btn btn-outline-secondary btn-lg"><i class="fas fa-arrow-right me-2"></i>رجوع</a>
        <button type="button" class="btn btn-warning btn-lg" onclick="stopSard()"><i class="fas fa-stop me-2"></i>إيقاف مبكر</button>
        <button type="button" class="btn btn-primary btn-lg flex-fill" onclick="submitSard()"><i class="fas fa-save me-2"></i>حفظ نتيجة السرد</button>
    </div>
</form>
<!-- Max Errors Modal -->
<div class="modal fade" id="maxErrorsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white"><h5 class="modal-title">⚠️ تحذير - وصل للحد الأقصى من الأخطاء</h5></div>
            <div class="modal-body text-center"><p class="fs-5">وصل الطالب إلى <strong>5 أخطاء</strong> في السرد</p><p>هل تريد الإكمال أم الإيقاف؟</p></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" onclick="stopSardNow()"><i class="fas fa-stop me-2"></i>إيقاف</button>
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal"><i class="fas fa-play me-2"></i>إكمال</button>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php $__env->startPush('scripts'); ?>
<script>
let totalErrors = 0;
let maxErrorsShown = false;
const pageCount = <?php echo e(count($pages)); ?>;
const colors = { green:'#27ae60', orange:'#f39c12', red:'#e74c3c', black:'#2c3e50' };

function updateSardColor(index) {
    const errors = parseInt(document.getElementById('e-'+index).value)||0;
    const hesitations = parseInt(document.getElementById('h-'+index).value)||0;
    let color;
    if(errors===0&&hesitations<=2) color='green';
    else if(errors===1||(hesitations>=3&&hesitations<=5)) color='orange';
    else if(errors===2&&hesitations===0) color='red';
    else color='black';
    document.getElementById('page-'+index).style.borderColor = colors[color];
    document.getElementById('page-'+index).style.background = colors[color]+'22';
    updateTotalErrors();
}

function updateTotalErrors() {
    let total = 0;
    for(let i=0;i<pageCount;i++) total += parseInt(document.getElementById('e-'+i)?.value)||0;
    totalErrors = total;
    const counter = document.getElementById('error-counter');
    counter.textContent = `الأخطاء: ${total} / 5`;
    counter.className = total >= 5 ? 'badge bg-danger ms-1' : 'badge bg-secondary ms-1';
    if(total >= 5 && !maxErrorsShown) { maxErrorsShown=true; new bootstrap.Modal(document.getElementById('maxErrorsModal')).show(); }
}

function stopSard() { document.getElementById('stoppedFlag').value='1'; submitSard(); }
function stopSardNow() { document.getElementById('stoppedFlag').value='1'; bootstrap.Modal.getInstance(document.getElementById('maxErrorsModal')).hide(); submitSard(); }

function submitSard() {
    const data = new FormData(document.getElementById('sardForm'));
    $.ajax({ url:'<?php echo e(route('supervisor.recitation.save')); ?>', method:'POST', data:data, processData:false, contentType:false,
        success:()=>{ toastr.success('تم حفظ نتيجة السرد'); setTimeout(()=>window.location.href='<?php echo e(route('supervisor.recitation.index')); ?>',1500); },
        error:()=>{ window.location.href='<?php echo e(route('supervisor.recitation.index')); ?>'; }
    });
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\quran-system\resources\views/supervisor/recitation/recitation.blade.php ENDPATH**/ ?>