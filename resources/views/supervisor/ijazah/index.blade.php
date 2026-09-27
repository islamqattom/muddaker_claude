@extends('layouts.app')

@section('title', 'الإجازة')
@section('page-title', 'تسجيل الإجازة')
@section('role-label', 'مشرف')

@section('sidebar-menu')
    @include('supervisor.partials.sidebar')
@endsection

@push('styles')
<style>
.ijazah-page {
    --gold: #d4a030;
    --gold-light: #e6b84d;
    --navy: #182B48;
    --navy-light: #2A4570;
}

.ijazah-page .form-card {
    background: white;
    border-radius: 13px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
    border: 1px solid rgba(24, 43, 72, 0.04);
    overflow: hidden;
    margin-bottom: 16px;
}

.ijazah-page .student-select-card {
    padding: 14px 16px;
    border: 2px solid #e9ecef;
    border-radius: 13px;
    transition: all 0.3s ease;
    cursor: pointer;
}

.ijazah-page .student-select-card:hover {
    border-color: var(--gold);
    box-shadow: 0 4px 15px rgba(212, 160, 48, 0.15);
}

.ijazah-page .student-select-card.selected {
    border-color: var(--navy);
    background: rgba(24, 43, 72, 0.03);
}

.ijazah-page .student-name {
    font-size: 16px;
    font-weight: 700;
    color: var(--navy);
}

.ijazah-page .student-level {
    font-size: 12px;
    color: #7f8c8d;
}

.ijazah-page .parts-grid {
    display: grid;
    grid-template-columns: repeat(6, 1fr);
    gap: 12px;
}

.ijazah-page .part-check-item {
    border: 2px solid #e9ecef;
    border-radius: 13px;
    padding: 14px 10px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
    background: white;
}

.ijazah-page .part-check-item:hover {
    border-color: var(--gold);
    transform: translateY(-2px);
}

.ijazah-page .part-check-item.checked {
    border-color: var(--success);
    background: rgba(39, 174, 96, 0.08);
}

.ijazah-page .part-check-item .part-num {
    font-size: 20px;
    font-weight: 800;
    color: var(--navy);
}

.ijazah-page .part-check-item .part-name {
    font-size: 11px;
    color: #7f8c8d;
    margin-top: 4px;
}

.ijazah-page .part-check-item input[type="checkbox"] {
    display: none;
}

.ijazah-page .part-check-item .check-icon {
    display: none;
    color: var(--success);
    margin-top: 4px;
}

.ijazah-page .part-check-item.checked .check-icon {
    display: block;
}

.ijazah-page .btn-gold {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 8px 16px;
    background: linear-gradient(135deg, var(--gold), #B4802C);
    color: #fff; border: none; border-radius: 9px;
    font-weight: 700; font-size: 13px;
    transition: all 0.3s ease;
}

.ijazah-page .btn-gold:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(212, 160, 48, 0.35);
}

.ijazah-page .ijazah-table {
    width: 100%;
    border-collapse: collapse;
}

.ijazah-page .ijazah-table th {
    background: rgba(24, 43, 72, 0.05);
    color: var(--navy);
    font-weight: 700;
    font-size: 11px;
    padding: 11px 14px;
    text-align: center;
}

.ijazah-page .ijazah-table td {
    padding: 11px 14px;
    font-size: 13px;
    text-align: center;
    border-bottom: 1px solid rgba(24, 43, 72, 0.04);
}

.ijazah-page .ijazah-table tbody tr:hover {
    background: rgba(212, 160, 48, 0.04);
}

.ijazah-page .status-badge {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 3px 10px;
    border-radius: 7px;
    font-size: 11.5px; font-weight: 600;
}

.ijazah-page .status-badge.completed {
    background: rgba(39, 174, 96, 0.15);
    color: #27ae60;
}

.ijazah-page .remove-btn {
    background: rgba(192, 57, 43, 0.1);
    color: #c0392b;
    border: none;
    border-radius: 8px;
    padding: 6px 12px;
    font-size: 12px;
    cursor: pointer;
    transition: all 0.2s;
}

.ijazah-page .remove-btn:hover {
    background: rgba(192, 57, 43, 0.2);
}

@media (max-width: 768px) {
    .ijazah-page .parts-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}
</style>
@endpush

@section('content')
<div class="ijazah-page">
    @if(session('success'))
        <div class="alert alert-success mb-4" style="background: rgba(39,174,96,0.1); border: 1px solid rgba(39,174,96,0.3); border-radius: 12px; color: #27ae60; padding: 16px 20px;">
            <i class="fas fa-check-circle me-2"></i>
            {{ session('success') }}
        </div>
    @endif

    <div class="page-header">
        <div class="header-content">
            <div class="header-icon">
                <i class="fas fa-award"></i>
            </div>
            <div class="header-text">
                <h4>تسجيل الإجازة</h4>
                <p>سجل إجازة الطالب في القرآن الكريم</p>
            </div>
        </div>
    </div>

    <div class="form-card">
        <div class="card-header-custom">
            <div class="card-title">
                <i class="fas fa-user-plus"></i>
                <span>اختر الطالب</span>
            </div>
        </div>
        <div class="card-body p-4">
            <form id="ijazahForm" method="POST" action="{{ route('supervisor.ijazah.store') }}">
                @csrf
                <div class="row g-3 mb-4">
                    <div class="col-md-8">
                        <select name="student_id" id="studentSelect" class="form-select form-select-lg" required onchange="loadStudentIjazah()">
                            <option value="">-- اختر طالب --</option>
                            @foreach($students as $student)
                                <option value="{{ $student->id }}">
                                    {{ $student->user->name ?? 'N/A' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold mb-3">
                        <i class="fas fa-check-square text-success me-1"></i>
                        الأجزاء المقروءة (اختر من 1 إلى 30)
                    </label>
                    <div class="parts-grid" id="partsGrid">
                        @foreach($parts as $part)
                            <label class="part-check-item" data-part-id="{{ $part->id }}" data-part-num="{{ $part->part_number }}">
                                <input type="checkbox" name="parts[]" value="{{ $part->id }}" onchange="togglePart(this)">
                                <div class="part-num">{{ $part->part_number }}</div>
                                <div class="part-name">{{ Str::limit($part->name, 10) }}</div>
                                <div class="check-icon"><i class="fas fa-check-circle"></i></div>
                            </label>
                        @endforeach
                    </div>
                </div>

                <button type="submit" class="btn-gold">
                    <i class="fas fa-save"></i>
                    تسجيل الإجازة
                </button>
            </form>
        </div>
    </div>

    @if($ijazahRecords->count() > 0)
    <div class="form-card">
        <div class="card-header-custom">
            <div class="card-title">
                <i class="fas fa-list"></i>
                <span>سجل الإجازات المسجلة</span>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="ijazah-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>الطالب</th>
                            <th>الجزء</th>
                            <th>تاريخ التسجيل</th>
                            <th>الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($ijazahRecords as $studentId => $records)
                            @foreach($records as $ijazah)
                                <tr>
                                    <td><span style="color: var(--gold); font-weight: 700;">{{ $loop->index + 1 }}</span></td>
                                    <td class="fw-bold" style="color: var(--navy);">{{ $ijazah->student->user->name }}</td>
                                    <td>
                                        <span class="status-badge completed">
                                            <i class="fas fa-check-circle"></i>
                                            جزء {{ $ijazah->quranPart->part_number }}
                                        </span>
                                    </td>
                                    <td style="color: #7f8c8d; font-size: 13px;">{{ $ijazah->completed_at ? $ijazah->completed_at->format('Y-m-d') : '-' }}</td>
                                    <td>
                                        <form method="POST" action="{{ route('supervisor.ijazah.destroy') }}" style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <input type="hidden" name="student_id" value="{{ $ijazah->student_id }}">
                                            <input type="hidden" name="quran_part_id" value="{{ $ijazah->quran_part_id }}">
                                            <button type="submit" class="remove-btn" onclick="return confirm('هل تريد حذف تسجيل الإجازة؟')">
                                                <i class="fas fa-trash"></i> حذف
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
function togglePart(checkbox) {
    const label = checkbox.closest('.part-check-item');
    if (checkbox.checked) {
        label.classList.add('checked');
    } else {
        label.classList.remove('checked');
    }
}

function loadStudentIjazah() {
    const studentId = document.getElementById('studentSelect').value;
    if (!studentId) return;

    document.querySelectorAll('.part-check-item').forEach(label => {
        const cb = label.querySelector('input[type="checkbox"]');
        cb.checked = false;
        cb.disabled = false;
        label.classList.remove('checked');
        label.style.opacity = '1';
    });

    const ijazahData = @json($ijazahRecords);
    if (ijazahData[studentId]) {
        ijazahData[studentId].forEach(record => {
            const label = document.querySelector(`.part-check-item[data-part-id="${record.quran_part_id}"]`);
            if (label) {
                const cb = label.querySelector('input[type="checkbox"]');
                cb.checked = true;
                cb.disabled = true;
                label.classList.add('checked');
                label.style.opacity = '0.5';
            }
        });
    }
}
</script>
@endpush