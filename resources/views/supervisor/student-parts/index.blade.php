@extends('layouts.app')

@section('title', 'Student Parts Record')

@push('styles')
<style>
    .student-selector-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 12px;
        padding: 24px;
        margin-bottom: 24px;
    }
    .student-selector-card h4 {
        font-weight: 600;
        margin-bottom: 16px;
    }
    .student-info-card {
        background: #f8f9fa;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 24px;
        display: none;
    }
    .student-info-card.active {
        display: block;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="student-selector-card">
        <h4><i class="fas fa-search"></i> Find Student</h4>
        <select id="studentSelect" class="form-control" style="max-width: 400px;">
            <option value="">-- Select Student --</option>
        </select>
    </div>

    <div id="studentInfoCard" class="student-info-card">
        <div class="row">
            <div class="col-md-3">
                <strong>Name:</strong> <span id="studentName">-</span>
            </div>
            <div class="col-md-3">
                <strong>Level:</strong> <span id="studentLevel">-</span>
            </div>
            <div class="col-md-3">
                <strong>Supervisor:</strong> <span id="studentSupervisor">-</span>
            </div>
            <div class="col-md-3">
                <strong>Student Number:</strong> <span id="studentNumber">-</span>
            </div>
        </div>
        <div class="mt-3">
            <a id="viewRecordLink" href="#" class="btn btn-primary">
                <i class="fas fa-list"></i> View Parts Record
            </a>
        </div>
    </div>

    <div id="partsContainer" style="display: none;">
        <p class="text-muted">Select a student to view their parts record.</p>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function() {
    $('#studentSelect').select2({
        ajax: {
            url: '/supervisor/students/search',
            processResults: function(data) {
                return {
                    results: data.map(function(s) {
                        return { id: s.id, text: s.name + ' (' + s.student_number + ')' };
                    })
                };
            }
        },
        minimumInputLength: 2,
        placeholder: 'Search by name or student number...'
    });

    $('#studentSelect').on('change', function() {
        var studentId = $(this).val();
        if (!studentId) {
            $('#studentInfoCard').removeClass('active');
            $('#partsContainer').hide();
            return;
        }

        $.get('/supervisor/students/' + studentId, function(student) {
            $('#studentName').text(student.name);
            $('#studentLevel').text(student.level_name);
            $('#studentSupervisor').text(student.supervisor_name);
            $('#studentNumber').text(student.student_number);
            $('#viewRecordLink').attr('href', '/supervisor/student-parts/' + studentId);
            $('#studentInfoCard').addClass('active');
        });
    });
});
</script>
@endpush
@endsection
