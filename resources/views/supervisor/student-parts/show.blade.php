@extends('layouts.app')

@section('title', 'سجل أجزاء الطالب — ' . $student->user->name)
@section('page-title', 'أجزاء الطالب')
@section('role-label', 'مشرف')

@section('sidebar-menu')
    @include('supervisor.partials.sidebar')
@endsection

@push('styles')
<style>
    .parts-table th { background: #f1f3f5; font-weight: 600; }
    .parts-table .completed { background-color: #d4edda !important; }
    .parts-table .not-completed { background-color: #f8d7da !important; }
    .override-badge {
        background: #fff3cd;
        font-size: 10px;
        padding: 2px 6px;
        border-radius: 4px;
    }
    .status-mafsil { color: #0d6efd; font-weight: bold; }
    .status-mutqin { color: #28a745; font-weight: bold; }
    .status-not-completed { color: #dc3545; font-weight: bold; }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- DEBUG -->
    <div class="alert alert-warning mb-4">
        <pre>{{ json_encode($debug, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
    </div>
    <!-- END DEBUG -->

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-book-quran"></i> سجل أجزاء الطالب — {{ $student->user->name }}</h2>
        <a href="{{ route('supervisor.student-parts.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-right"></i> رجوع
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="alert alert-info mb-4">
        <div>
            <strong>عدد المفصل:</strong> {{ $mafsilCount }}
        </div>
        <div class="mt-1">
            <strong>المفصل + المتقن:</strong> {{ $totalMafsilAndMutqin }}
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered parts-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>اسم الجزء</th>
                            <th>الحالة</th>
                            <th>الأخطاء</th>
                            <th>التلعثم</th>
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
                            @endphp
                            <tr class="{{ $isCompleted ? 'completed' : 'not-completed' }}">
                                <td>{{ $part->part_number }}</td>
                                <td>{{ $part->name }}</td>
                                @if($part->part_number == 2)
                                <td colspan="8" class="bg-warning text-dark">
                                    DEBUG: record={{ json_encode($record) }} isCompleted={{ var_export($isCompleted, true) }} sessionType={{ $partSessionTypes[$part->part_number] ?? 'null' }}
                                </td>
                            </tr>
                            <tr class="{{ $isCompleted ? 'completed' : 'not-completed' }}">
                                <td>{{ $part->part_number }}</td>
                                <td>{{ $part->name }}</td>
                                <td>
                                    @php
                                        $sessionType = $partSessionTypes[$part->part_number] ?? null;
                                        $statusLabel = match(true) {
                                            $sessionType === 'mafsil' => 'مفصل',
                                            $sessionType === 'recitation' && $isCompleted => 'متقن',
                                            $sessionType === 'recitation' && !$isCompleted => 'غير متقن',
                                            default => 'غير متقن'
                                        };
                                        $statusClass = match($sessionType) {
                                            'mafsil' => 'status-mafsil',
                                            'recitation' => $isCompleted ? 'status-mutqin' : 'status-not-completed',
                                            default => 'status-not-completed'
                                        };
                                    @endphp
                                    <span class="{{ $statusClass }}">
                                        {{ $statusLabel }}
                                    </span>
                                    @if($isOverride)
                                        <span class="override-badge">يدوي</span>
                                    @endif
                                </td>
                                <td>{{ $record ? $record->total_errors : '-' }}</td>
                                <td>{{ $record ? $record->total_hesitations : '-' }}</td>
                                <td>{{ $record && $record->total_score !== null ? number_format($record->total_score, 1) . '%' : '-' }}</td>
                                <td>{{ $record && $record->last_recitation_date ? $record->last_recitation_date->format('Y-m-d') : '-' }}</td>
                                <td>
                                    <button class="btn btn-sm btn-primary edit-btn"
                                        data-part="{{ $part->part_number }}"
                                        data-record='@json($record)'
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
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade edit-modal" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">تعديل الجزء <span id="modalPartNumber"></span></h5>
                    <button type="button" class="close" data-bs-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="part_number" id="editPartNumber">

                    <div class="form-group">
                        <label><strong>الحالة</strong></label>
                        <select name="is_completed" id="editIsCompleted" class="form-control">
                            <option value="1">متقن</option>
                            <option value="0">غير متقن</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label><strong>إجمالي الأخطاء</strong></label>
                        <input type="number" name="total_errors" id="editErrors" class="form-control" min="0">
                    </div>

                    <div class="form-group">
                        <label><strong>إجمالي التلعثم</strong></label>
                        <input type="number" name="total_hesitations" id="editHesitations" class="form-control" min="0">
                    </div>

                    <div class="form-group">
                        <label><strong>الدرجة (%)</strong></label>
                        <input type="number" name="total_score" id="editScore" class="form-control" min="0" max="100" step="0.01">
                    </div>

                    <div class="form-group">
                        <label><strong>تاريخ آخر تسميع</strong></label>
                        <input type="date" name="last_recitation_date" id="editDate" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> حفظ
                    </button>
                    <button type="button" class="btn btn-outline-secondary" id="resetBtn">
                        <i class="fas fa-undo"></i> إعادة للاحتساب التلقائي
                    </button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
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
        $('#editIsCompleted').val(record.is_completed ? '1' : '0');
        $('#editErrors').val(record.total_errors || 0);
        $('#editHesitations').val(record.total_hesitations || 0);
        $('#editScore').val(record.total_score || '');
        $('#editDate').val(record.last_recitation_date || '');

        var studentId = {{ $student->id }};
        $('#editForm').attr('action', '/supervisor/student-parts/' + studentId + '/' + part);

        $('#resetBtn').off('click').on('click', function() {
            if (confirm('هل تريد إعادة الجزء للاحتساب التلقائي؟')) {
                var form = $('<form>').attr('method', 'POST')
                    .attr('action', '/supervisor/student-parts/' + studentId + '/' + part + '/reset');
                form.append($('<input>').attr({name: '_token', value: $('meta[name="csrf-token"]').attr('content')}));
                form.appendTo('body').submit();
            }
        });
    });
});
</script>
@endpush