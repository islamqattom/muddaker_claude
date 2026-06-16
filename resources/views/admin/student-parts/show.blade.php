@extends('layouts.app')

@section('title', 'سجل أجزاء الطالب — ' . $student->user->name)
@section('page-title', 'أجزاء الطالب')
@section('role-label', 'مدير')

@section('sidebar-menu')
    @include('admin.partials.sidebar')
@endsection

@push('styles')
<style>
:root {
    --primary: #182B48;
    --primary-light: #2A4570;
    --primary-lighter: #3D5A80;
    --gold: #d4a030;
    --gold-dark: #B4802C;
    --gold-light: #E8C45A;
    --success: #27ae60;
    --danger: #c0392b;
    --info: #3498db;
    --purple: #8e44ad;
    --bg-main: #f8fafc;
    --card-shadow: 0 4px 25px rgba(24, 43, 72, 0.08);
}

body { background: var(--bg-main); font-family: 'Tajawal', sans-serif; }

.page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
    margin-bottom: 30px;
    flex-wrap: wrap;
}
.header-content { display: flex; align-items: center; gap: 20px; }
.header-icon {
    width: 70px; height: 70px;
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
    border-radius: 18px;
    display: flex; align-items: center; justify-content: center;
    font-size: 30px; color: var(--gold);
    box-shadow: 0 8px 25px rgba(24,43,72,0.25);
}
.header-text h4 { color: var(--primary); font-size: 24px; font-weight: 700; margin: 0 0 4px 0; }
.header-text p { color: #6c7a89; font-size: 14px; margin: 0; }
.header-text p::before { content: '✦'; color: var(--gold); margin-left: 8px; }

.btn-back {
    display: inline-flex; align-items: center; gap: 10px;
    padding: 12px 24px;
    background: #fff;
    color: var(--primary); border: 2px solid var(--primary);
    border-radius: 12px;
    font-weight: 600; font-size: 14px;
    transition: all 0.3s ease;
    text-decoration: none;
}
.btn-back:hover {
    background: var(--primary);
    color: #fff;
}
.btn-back i { color: var(--gold); }

.stats-card {
    background: #fff;
    border-radius: 16px;
    padding: 25px;
    box-shadow: var(--card-shadow);
    border: 1px solid rgba(24,43,72,0.05);
    display: flex;
    align-items: center;
    gap: 20px;
}
.stats-icon {
    width: 60px; height: 60px;
    border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    font-size: 24px;
}
.stats-icon.gold { background: linear-gradient(135deg, var(--gold), var(--gold-dark)); color: #fff; }
.stats-icon.success { background: linear-gradient(135deg, var(--success), #219a52); color: #fff; }
.stats-icon.info { background: linear-gradient(135deg, var(--info), #2980b9); color: #fff; }
.stats-text h3 { color: var(--primary); font-size: 28px; font-weight: 700; margin: 0 0 4px 0; }
.stats-text p { color: #7f8c8d; font-size: 14px; margin: 0; }

.table-card {
    background: #fff;
    border-radius: 20px;
    box-shadow: var(--card-shadow);
    border: 1px solid rgba(24,43,72,0.05);
    overflow: hidden;
}
.table-header {
    display: flex; align-items: center; gap: 12px;
    padding: 22px 28px;
    background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
    color: #fff;
    position: relative;
}
.table-header i { color: var(--gold); font-size: 20px; }
.table-header h5 { margin: 0; font-weight: 700; font-size: 18px; }
.table-header::after {
    content: '';
    position: absolute;
    bottom: 0; right: 0; left: 0;
    height: 4px;
    background: linear-gradient(90deg, var(--gold), var(--gold-dark), var(--gold));
}

.data-table { width: 100%; border-collapse: collapse; }
.data-table th {
    background: rgba(24,43,72,0.04);
    color: var(--primary);
    font-weight: 700;
    font-size: 13px;
    padding: 14px 16px;
    text-align: center;
}
.data-table td {
    padding: 16px;
    text-align: center;
    border-bottom: 1px solid rgba(24,43,72,0.06);
    vertical-align: middle;
}
.data-table tr:last-child td { border-bottom: none; }
.data-table tbody tr:hover { background: rgba(212,160,48,0.04); }

.status-badge {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}
.status-badge.mafsil { background: rgba(52,152,219,0.15); color: var(--info); }
.status-badge.mutqin { background: rgba(39,174,96,0.15); color: var(--success); }
.status-badge.not-completed { background: rgba(192,57,43,0.15); color: var(--danger); }

.score-high { color: var(--success); font-weight: 700; }
.score-mid { color: var(--gold); font-weight: 700; }
.score-low { color: var(--danger); font-weight: 700; }

.override-badge {
    background: rgba(212,160,48,0.2);
    font-size: 10px;
    padding: 2px 8px;
    border-radius: 4px;
    color: var(--gold-dark);
    margin-right: 6px;
}

.edit-btn {
    width: 36px; height: 36px;
    border-radius: 10px;
    border: none;
    background: linear-gradient(135deg, var(--primary), var(--primary-light));
    color: #fff;
    display: inline-flex; align-items: center; justify-content: center;
    transition: all 0.3s ease;
}
.edit-btn:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(24,43,72,0.3); color: var(--gold); }

/* Modal */
.modal-modern .modal-content {
    border: none;
    border-radius: 20px;
    overflow: hidden;
}
.modal-modern .modal-header {
    background: linear-gradient(135deg, var(--primary), var(--primary-light));
    color: #fff;
    padding: 20px 25px;
    border: none;
}
.modal-modern .modal-header i { color: var(--gold); }
.modal-modern .modal-title { font-weight: 700; font-size: 18px; }
.modal-modern .modal-header .btn-close { filter: brightness(0) invert(1); opacity: 0.5; }
.modal-modern .modal-header .btn-close:hover { opacity: 1; }
.modal-modern .modal-body { padding: 25px; }
.modal-modern .modal-footer { padding: 15px 25px; border-top: 1px solid #f0f4f8; }

.form-label-modal {
    font-weight: 600;
    color: var(--primary);
    margin-bottom: 8px;
    font-size: 13px;
}
.form-control-modal, .form-select-modal {
    border-radius: 10px;
    border: 2px solid #e9ecef;
    padding: 10px 14px;
    transition: all 0.3s ease;
    font-family: 'Tajawal', sans-serif;
    font-size: 14px;
}
.form-control-modal:focus, .form-select-modal:focus {
    border-color: var(--gold);
    box-shadow: 0 0 0 4px rgba(212,160,48,0.12);
}

.btn-gold {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 10px 20px;
    background: linear-gradient(135deg, var(--gold), var(--gold-dark));
    color: #fff; border: none; border-radius: 10px;
    font-weight: 600; font-size: 14px;
    transition: all 0.3s ease;
}
.btn-gold:hover { transform: translateY(-2px); box-shadow: 0 4px 15px rgba(212,160,48,0.3); }

.btn-outline-gray {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 10px 20px;
    background: #fff;
    color: var(--primary); border: 2px solid #e9ecef;
    border-radius: 10px;
    font-weight: 600; font-size: 14px;
    transition: all 0.3s ease;
}
.btn-outline-gray:hover { border-color: var(--primary); background: var(--primary); color: #fff; }

.alert-success {
    background: rgba(39,174,96,0.1);
    border: 1px solid rgba(39,174,96,0.3);
    border-radius: 12px;
    color: var(--success);
    padding: 16px 20px;
}
.alert-danger {
    background: rgba(192,57,43,0.1);
    border: 1px solid rgba(192,57,43,0.3);
    border-radius: 12px;
    color: var(--danger);
    padding: 16px 20px;
}
.alert-danger ul { margin: 0; padding-right: 20px; }

@media (max-width: 768px) {
    .page-header { flex-direction: column; align-items: flex-start; }
    .stats-row { flex-direction: column; }

    /* Switch the parts table into a stacked card list on mobile */
    .data-table thead { display: none; }
    .data-table, .data-table tbody, .data-table tr, .data-table td { display: block; width: 100%; }
    .data-table tr {
        background: #fff;
        border: 1px solid #eef1f6;
        border-radius: 12px;
        margin-bottom: 12px;
        padding: 10px 12px;
        box-shadow: 0 2px 6px rgba(24,43,72,0.05);
    }
    .data-table td {
        text-align: right;
        padding: 6px 0;
        border-bottom: 1px dashed #eef1f6;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
    }
    .data-table td:last-child { border-bottom: none; padding-top: 10px; justify-content: center; }
    .data-table td::before {
        content: attr(data-label);
        font-weight: 700;
        color: var(--primary);
        font-size: 12px;
        flex-shrink: 0;
    }
}
</style>
@endpush

@section('content')
<div class="page-header">
    <div class="header-content">
        <div class="header-icon">
            <i class="fas fa-book-quran"></i>
        </div>
        <div class="header-text">
            <h4>سجل أجزاء الطالب — {{ $student->user->name }}</h4>
            <p>عرض جميع أجزاء التسميع للطالب</p>
        </div>
    </div>
    <a href="{{ route('admin.student-parts.index') }}" class="btn-back">
        <i class="fas fa-arrow-right"></i>
        رجوع
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success mb-4">
        <i class="fas fa-check-circle me-2"></i>
        {{ session('success') }}
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger mb-4">
        <ul>
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="stats-card">
            <div class="stats-icon gold">
                <i class="fas fa-star"></i>
            </div>
            <div class="stats-text">
                <h3>{{ $mafsilCount }}</h3>
                <p>عدد المفصل</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stats-card">
            <div class="stats-icon success">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stats-text">
                <h3>{{ $totalMafsilAndMutqin }}</h3>
                <p>المفصل + المتقن</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stats-card">
            <div class="stats-icon info">
                <i class="fas fa-book-open"></i>
            </div>
            <div class="stats-text">
                <h3>{{ $parts->count() }}</h3>
                <p>إجمالي الأجزاء</p>
            </div>
        </div>
    </div>
</div>

<div class="table-card">
    <div class="table-header">
        <i class="fas fa-list"></i>
        <h5>جدول الأجزاء</h5>
    </div>
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>اسم الجزء</th>
                    <th>الحالة</th>
                    <th>الأخطاء</th>
                    <th>الترددات</th>
                    <th>الدرجة</th>
                    <th>آخر تسميع</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @foreach($parts as $part)
                    @php
                        $record = $records->get($part->part_number);
                        $isCompleted = $record && $record->is_completed;
                        $isOverride = $record && $record->is_manual_override;
                        $sessionType = $partSessionTypes[$part->part_number] ?? null;
                        $recordMasteryStatus = $record->mastery_status ?? null;
                        $statusLabel = match(true) {
                            $isOverride && $recordMasteryStatus === 'متقن' => 'متقن',
                            $isOverride && $recordMasteryStatus === 'غير متقن' => 'غير متقن',
                            $isOverride && $recordMasteryStatus === 'مفصل' => 'مفصل',
                            $sessionType === 'mafsil' => 'مفصل',
                            $sessionType === 'recitation' && $isCompleted => 'متقن',
                            $sessionType === 'recitation' && !$isCompleted => 'غير متقن',
                            default => 'غير متقن'
                        };
                        $statusClass = match($statusLabel) {
                            'مفصل' => 'mafsil',
                            'متقن' => 'mutqin',
                            default => 'not-completed'
                        };
                        $score = $record && $record->total_score !== null ? $record->total_score : null;
                        $scoreClass = match(true) {
                            $score !== null && $score >= 90 => 'score-high',
                            $score !== null && $score >= 70 => 'score-mid',
                            $score !== null => 'score-low',
                            default => ''
                        };
                    @endphp
                    @php
                        $modalData = $record ? [
                            'total_errors' => $record->total_errors,
                            'total_hesitations' => $record->total_hesitations,
                            'total_score' => $record->total_score,
                            'last_recitation_date' => $record->last_recitation_date ? $record->last_recitation_date->format('Y-m-d') : null,
                            'mastery_status' => $record->mastery_status,
                        ] : null;
                    @endphp
                    <tr>
                        <td data-label="الجزء"><strong>{{ $part->part_number }}</strong></td>
                        <td data-label="اسم الجزء">{{ $part->name }}</td>
                        <td data-label="الحالة">
                            <span class="status-badge {{ $statusClass }}">
                                @if($statusLabel === 'مفصل')<i class="fas fa-star"></i>
                                @elseif($statusLabel === 'متقن')<i class="fas fa-check"></i>
                                @else<i class="fas fa-times"></i>
                                @endif
                                {{ $statusLabel }}
                            </span>
                            @if($isOverride)
                                <span class="override-badge">يدوي</span>
                            @endif
                        </td>
                        <td data-label="الأخطاء">{{ $record ? $record->total_errors : '-' }}</td>
                        <td data-label="الترددات">{{ $record ? $record->total_hesitations : '-' }}</td>
                        <td data-label="الدرجة" class="{{ $scoreClass }}">{{ $score !== null ? number_format($score, 1) . '%' : '-' }}</td>
                        <td data-label="آخر تسميع">{{ $record && $record->last_recitation_date ? $record->last_recitation_date->format('Y-m-d') : '-' }}</td>
                        <td data-label="الإجراءات">
                            <button class="edit-btn"
                                data-part="{{ $part->part_number }}"
                                data-record='@json($modalData)'
                                data-bs-toggle="modal"
                                data-bs-target="#editModal">
                                <i class="fas fa-edit"></i>
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade edit-modal modal-modern" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <i class="fas fa-edit me-2"></i>
                    <span class="modal-title">تعديل الجزء <span id="modalPartNumber"></span></span>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="part_number" id="editPartNumber">

                    <div class="mb-3">
                        <label class="form-label-modal">إجمالي الأخطاء</label>
                        <input type="number" name="total_errors" id="editErrors" class="form-control-modal" min="0">
                    </div>

                    <div class="mb-3">
                        <label class="form-label-modal">إجمالي الترددات</label>
                        <input type="number" name="total_hesitations" id="editHesitations" class="form-control-modal" min="0">
                    </div>

                    <div class="mb-3">
                        <label class="form-label-modal">الدرجة (%)</label>
                        <input type="number" name="total_score" id="editScore" class="form-control-modal" min="0" max="100" step="0.01">
                    </div>

                    <div class="mb-3">
                        <label class="form-label-modal">تاريخ التسميع</label>
                        <input type="date" name="session_date" id="editDate" class="form-control-modal">
                    </div>

                    <div class="mb-3">
                        <label class="form-label-modal">حالة الإتقان</label>
                        <select name="mastery_status" id="editMasteryStatus" class="form-select-modal">
                            <option value="متقن">متقن</option>
                            <option value="غير متقن">غير متقن</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-outline-gray" id="resetBtn">
                        <i class="fas fa-undo"></i> إعادة للاحتساب التلقائي
                    </button>
                    <button type="submit" class="btn-gold">
                        <i class="fas fa-save"></i> حفظ
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function() {
    $('.edit-btn').on('click', function() {
        var part = $(this).data('part');
        var record = $(this).data('record') || {};

        $('#modalPartNumber').text(part);
        $('#editPartNumber').val(part);
        $('#editErrors').val(record.total_errors || 0);
        $('#editHesitations').val(record.total_hesitations || 0);
        $('#editScore').val(record.total_score || '');
        var dateVal = record.last_recitation_date ? record.last_recitation_date.substring(0, 10) : '';
        $('#editDate').val(dateVal);
        $('#editMasteryStatus').val(record.mastery_status || 'غير متقن');

        var studentId = {{ $student->id }};
        $('#editForm').attr('action', '/admin/student-parts/' + studentId + '/' + part);

        $('#resetBtn').off('click').on('click', function() {
            if (confirm('هل تريد إعادة الجزء للاحتساب التلقائي؟')) {
                var form = $('<form>').attr('method', 'POST')
                    .attr('action', '/admin/student-parts/' + studentId + '/' + part + '/reset');
                form.append($('<input>').attr({name: '_token', value: $('meta[name="csrf-token"]').attr('content')}));
                form.appendTo('body').submit();
            }
        });
    });
});
</script>
@endpush