<?php $__env->startSection('title', 'التسميع'); ?>
<?php $__env->startSection('page-title', 'جلسة تسميع جديدة'); ?>
<?php $__env->startSection('role-label', 'مشرف'); ?>

<?php $__env->startSection('sidebar-menu'); ?>
    <?php echo $__env->make('supervisor.partials.sidebar', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
<div class="row g-4">
    <div class="col-md-5">
        <div class="card">
            <div class="card-header">
                <h6><i class="fas fa-user-graduate me-2"></i>اختر الطالب ونوع التسميع</h6>
            </div>
            <div class="card-body">
                <form id="sessionStartForm">
                    <div class="mb-3">
                        <label class="form-label">الطالب <span class="text-danger">*</span></label>
                        <select name="student_id" id="studentSelect" class="form-select" required onchange="loadStudentInfo()">
                            <option value="">-- اختر الطالب --</option>
                            <?php $__currentLoopData = $students; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $student): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($student->id); ?>"
                                    data-level="<?php echo e($student->level->name ?? ''); ?>"
                                    data-memorization="<?php echo e($student->level->new_memorization_pages ?? 3); ?>"
                                    data-review="<?php echo e($student->level->review_pages ?? 6); ?>"
                                    data-part="<?php echo e($student->current_part); ?>"
                                    data-page="<?php echo e($student->current_page); ?>">
                                    <?php echo e($student->user->name); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>

                    <!-- Student Info Preview -->
                    <div id="studentInfo" class="d-none mb-3 p-3 bg-light rounded">
                        <div class="row g-2 text-center">
                            <div class="col-4">
                                <div class="fw-bold text-primary" id="si-level">-</div>
                                <small class="text-muted">المستوى</small>
                            </div>
                            <div class="col-4">
                                <div class="fw-bold text-success" id="si-memorization">-</div>
                                <small class="text-muted">صفحات الحفظ</small>
                            </div>
                            <div class="col-4">
                                <div class="fw-bold text-info" id="si-review">-</div>
                                <small class="text-muted">صفحات المراجعة</small>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">نوع التسميع <span class="text-danger">*</span></label>
                        <div class="row g-2" id="sessionTypes">
                            <?php
                            $types = [
                                ['value' => 'new_memorization', 'label' => 'حفظ جديد', 'icon' => 'fa-book-open', 'color' => 'primary'],
                                ['value' => 'review', 'label' => 'مراجعة', 'icon' => 'fa-redo', 'color' => 'success'],
                                ['value' => 'unmastered', 'label' => 'حفظ غير متقن', 'icon' => 'fa-exclamation', 'color' => 'warning'],
                                ['value' => 'recitation', 'label' => 'سرد', 'icon' => 'fa-scroll', 'color' => 'info'],
                                ['value' => 'mafsil', 'label' => 'مفصل', 'icon' => 'fa-star', 'color' => 'danger'],
                            ];
                            ?>
                            <?php $__currentLoopData = $types; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="col-6">
                                <input type="radio" class="btn-check" name="type" id="type-<?php echo e($type['value']); ?>"
                                       value="<?php echo e($type['value']); ?>" onchange="showTypeOptions('<?php echo e($type['value']); ?>')">
                                <label class="btn btn-outline-<?php echo e($type['color']); ?> w-100 py-3" for="type-<?php echo e($type['value']); ?>">
                                    <i class="fas <?php echo e($type['icon']); ?> d-block mb-1 fs-5"></i>
                                    <?php echo e($type['label']); ?>

                                </label>
                            </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>

                    <!-- Dynamic Options -->
                    <div id="options-new_memorization" class="d-none session-options">
                        <div class="row g-2">
                            <div class="col-6">
                                <label class="form-label">الجزء</label>
                                <select name="part_id_new" class="form-select">
                                    <?php $__currentLoopData = $parts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $part): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($part->id); ?>">الجزء <?php echo e($part->part_number); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label">رقم الصفحة الأولى</label>
                                <input type="number" name="start_page_new" class="form-control" min="1" max="604" value="1">
                            </div>
                        </div>
                    </div>

                    <div id="options-review" class="d-none session-options">
                        <div class="row g-2">
                            <div class="col-6">
                                <label class="form-label">الجزء</label>
                                <select name="part_id_review" class="form-select">
                                    <?php $__currentLoopData = $parts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $part): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($part->id); ?>">الجزء <?php echo e($part->part_number); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label">رقم الصفحة الأولى</label>
                                <input type="number" name="start_page_review" class="form-control" min="1" max="604" value="1">
                            </div>
                        </div>
                    </div>

                    <div id="options-unmastered" class="d-none session-options">
                        <div class="row g-2">
                            <div class="col-6">
                                <label class="form-label">الجزء</label>
                                <select name="part_id_unmastered" class="form-select">
                                    <?php $__currentLoopData = $parts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $part): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($part->id); ?>">الجزء <?php echo e($part->part_number); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label">رقم الصفحة الأولى</label>
                                <input type="number" name="start_page_unmastered" class="form-control" min="1" max="604" value="1">
                            </div>
                            <div class="col-12">
                                <label class="form-label">عدد الصفحات</label>
                                <div class="btn-group w-100">
                                    <?php $__currentLoopData = [5, 10, 15, 20]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $count): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <input type="radio" class="btn-check" name="unmastered_count" id="uc-<?php echo e($count); ?>" value="<?php echo e($count); ?>" <?php echo e($count === 5 ? 'checked' : ''); ?>>
                                    <label class="btn btn-outline-secondary" for="uc-<?php echo e($count); ?>"><?php echo e($count); ?></label>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="options-recitation" class="d-none session-options">
                        <div class="col-12">
                            <label class="form-label">اختر الجزء للسرد</label>
                            <select name="part_id_recitation" class="form-select">
                                <?php $__currentLoopData = $parts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $part): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($part->id); ?>">الجزء <?php echo e($part->part_number); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                    </div>

                    <div id="options-mafsil" class="d-none session-options">
                        <label class="form-label">نوع المفصل</label>
                        <div class="row g-2">
                            <?php $mafsilTypes = [
                                [3, '90%'], [6, '90%'], [10, '80%'],
                                [15, '80%'], [20, '75%'], [25, '75%'], [30, '75%']
                            ]; ?>
                            <?php $__currentLoopData = $mafsilTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as [$num, $score]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="col-4">
                                <input type="radio" class="btn-check" name="mafsil_type" id="mt-<?php echo e($num); ?>" value="<?php echo e($num); ?>">
                                <label class="btn btn-outline-danger w-100 py-2" for="mt-<?php echo e($num); ?>">
                                    <div class="fw-bold"><?php echo e($num); ?></div>
                                    <small><?php echo e($score); ?></small>
                                </label>
                            </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>

                    <div class="mt-3">
                        <button type="button" class="btn btn-primary w-100 py-3 fs-5" onclick="startSession()">
                            <i class="fas fa-play me-2"></i>
                            بدء الجلسة
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Color Guide -->
    <div class="col-md-7">
        <div class="card mb-4">
            <div class="card-header">
                <h6><i class="fas fa-info-circle me-2"></i>نظام الألوان والتقييم</h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-sm-6">
                        <div class="p-3 rounded text-white" style="background:#27ae60;">
                            <div class="fw-bold fs-5 mb-1">🟢 أخضر - ممتاز</div>
                            <div style="font-size:13px;">لم يخطئ الطالب أو تردد بحد أقصى ترددين</div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="p-3 rounded text-dark" style="background:#f39c12;">
                            <div class="fw-bold fs-5 mb-1">🟠 برتقالي - جيد</div>
                            <div style="font-size:13px;">خطأ واحد أو من 3 إلى 5 ترددات</div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="p-3 rounded text-white" style="background:#c0392b;">
                            <div class="fw-bold fs-5 mb-1">🔴 أحمر - ضعيف</div>
                            <div style="font-size:13px;">خطآن أو ما يعادلهما من الترددات</div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="p-3 rounded text-white" style="background:#2c3e50;">
                            <div class="fw-bold fs-5 mb-1">⚫ أسود - راسب</div>
                            <div style="font-size:13px;">خطآن + تردد واحد أو أكثر = رسوب</div>
                        </div>
                    </div>
                </div>

                <div class="mt-3 p-3 bg-light rounded">
                    <h6 class="text-primary">المفاصل وعلاماتها:</h6>
                    <table class="table table-sm mb-0">
                        <tr><td><strong>مفصل 3 و6</strong></td><td>علامة البداية: <span class="badge bg-success">90%</span></td></tr>
                        <tr><td><strong>مفصل 10 و15</strong></td><td>علامة البداية: <span class="badge bg-warning text-dark">80%</span></td></tr>
                        <tr><td><strong>مفصل 20 و25 و30</strong></td><td>علامة البداية: <span class="badge bg-danger">75%</span></td></tr>
                    </table>
                    <small class="text-muted mt-2 d-block">خطأ = -3 علامات | كل 3 ترددات = -1 علامة</small>
                </div>
            </div>
        </div>

        <!-- Today's Sessions -->
        <div class="card">
            <div class="card-header">
                <h6><i class="fas fa-list me-2"></i>جلسات اليوم</h6>
            </div>
            <div class="card-body p-0">
                <?php
                    $todaySessions = \App\Models\RecitationSession::whereHas('student', fn($q) => $q->where('supervisor_id', auth()->id()))
                        ->whereDate('session_date', today())
                        ->with(['student.user', 'part'])
                        ->latest()
                        ->take(5)
                        ->get();
                ?>
                <?php $__empty_1 = true; $__currentLoopData = $todaySessions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $session): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fw-semibold"><?php echo e($session->student->user->name); ?></div>
                        <small class="text-muted">
                            <?php echo e($session->type_label); ?>

                            <?php if($session->part): ?> - الجزء <?php echo e($session->part->part_number); ?> <?php endif; ?>
                        </small>
                    </div>
                    <div class="text-end">
                        <span class="badge bg-<?php echo e($session->status_color); ?>"><?php echo e($session->status_label); ?></span>
                        <div class="text-muted" style="font-size:11px;">
                            <?php echo e($session->total_errors); ?> خطأ | <?php echo e($session->total_hesitations); ?> تردد
                        </div>
                    </div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="p-4 text-center text-muted">لا توجد جلسات اليوم</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
function loadStudentInfo() {
    const select = document.getElementById('studentSelect');
    const option = select.options[select.selectedIndex];
    const info = document.getElementById('studentInfo');

    if (select.value) {
        document.getElementById('si-level').textContent = option.dataset.level || '-';
        document.getElementById('si-memorization').textContent = option.dataset.memorization + ' صفحات';
        document.getElementById('si-review').textContent = option.dataset.review + ' صفحات';
        info.classList.remove('d-none');
    } else {
        info.classList.add('d-none');
    }
}

function showTypeOptions(type) {
    document.querySelectorAll('.session-options').forEach(el => el.classList.add('d-none'));
    const opt = document.getElementById('options-' + type);
    if (opt) opt.classList.remove('d-none');
}

function startSession() {
    const studentId = document.getElementById('studentSelect').value;
    const typeEl = document.querySelector('input[name="type"]:checked');

    if (!studentId) {
        toastr.warning('يرجى اختيار الطالب');
        return;
    }
    if (!typeEl) {
        toastr.warning('يرجى اختيار نوع التسميع');
        return;
    }

    const type = typeEl.value;
    let url = '';

    if (type === 'new_memorization') {
        const partId = document.querySelector('select[name="part_id_new"]').value;
        const startPage = document.querySelector('input[name="start_page_new"]').value;
        url = `<?php echo e(route('supervisor.recitation.new-memorization')); ?>?student_id=${studentId}&part_id=${partId}&start_page=${startPage}`;
    } else if (type === 'review') {
        const partId = document.querySelector('select[name="part_id_review"]').value;
        const startPage = document.querySelector('input[name="start_page_review"]').value;
        url = `<?php echo e(route('supervisor.recitation.review')); ?>?student_id=${studentId}&part_id=${partId}&start_page=${startPage}`;
    } else if (type === 'unmastered') {
        const partId = document.querySelector('select[name="part_id_unmastered"]').value;
        const startPage = document.querySelector('input[name="start_page_unmastered"]').value;
        const count = document.querySelector('input[name="unmastered_count"]:checked')?.value || 5;
        url = `<?php echo e(route('supervisor.recitation.unmastered')); ?>?student_id=${studentId}&part_id=${partId}&start_page=${startPage}&pages_count=${count}`;
    } else if (type === 'recitation') {
        const partId = document.querySelector('select[name="part_id_recitation"]').value;
        url = `<?php echo e(route('supervisor.recitation.recitation')); ?>?student_id=${studentId}&part_id=${partId}`;
    } else if (type === 'mafsil') {
        const mafsilType = document.querySelector('input[name="mafsil_type"]:checked')?.value;
        if (!mafsilType) { toastr.warning('يرجى اختيار نوع المفصل'); return; }
        url = `<?php echo e(route('supervisor.recitation.mafsil')); ?>?student_id=${studentId}&mafsil_type=${mafsilType}`;
    }

    if (url) window.location.href = url;
}
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\quran-system\resources\views/supervisor/recitation/index.blade.php ENDPATH**/ ?>