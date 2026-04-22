# Student Parts Record Page — Implementation Plan

> **For Claude:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Build a standalone supervisor page at `/supervisor/student-parts` that displays a student's Quran memorization record across all 30 parts, auto-filled from recitation sessions and editable by supervisors.

**Architecture:** New `student_part_records` table stores per-student-per-part records. A `StudentPartRecordController` serves a single view that first shows a student selector, then renders all 30 parts with auto-calculated or manually-overridden data. Inline editing per row; "Reset to Auto" re-aggregates from recitation sessions.

**Tech Stack:** Laravel (PHP), Blade views, MySQL, existing Supervisor authentication/authorization.

---

## Task 1: Create Migration for `student_part_records`

**Files:**
- Create: `database/migrations/2026_04_22_120000_create_student_part_records_table.php`

**Step 1: Write the migration**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_part_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->tinyInteger('part_number')->unsigned();
            $table->boolean('is_completed')->default(false);
            $table->integer('total_errors')->default(0);
            $table->integer('total_hesitations')->default(0);
            $table->decimal('total_score', 5, 2)->nullable();
            $table->date('last_recitation_date')->nullable();
            $table->boolean('is_manual_override')->default(false);
            $table->timestamps();

            $table->unique(['student_id', 'part_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_part_records');
    }
};
```

**Step 2: Run migration**

```bash
cd C:/xampp/htdocs/quran-system && php artisan migrate
```

Expected: table `student_part_records` created

**Step 3: Commit**

```bash
git add database/migrations/2026_04_22_120000_create_student_part_records_table.php
git commit -m "feat: add student_part_records migration"
```

---

## Task 2: Create `StudentPartRecord` Model

**Files:**
- Create: `app/Models/StudentPartRecord.php`

**Step 1: Write the model**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentPartRecord extends Model
{
    protected $fillable = [
        'student_id',
        'part_number',
        'is_completed',
        'total_errors',
        'total_hesitations',
        'total_score',
        'last_recitation_date',
        'is_manual_override',
    ];

    protected $casts = [
        'is_completed' => 'boolean',
        'total_errors' => 'integer',
        'total_hesitations' => 'integer',
        'total_score' => 'decimal:2',
        'last_recitation_date' => 'date',
        'is_manual_override' => 'boolean',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
```

**Step 2: Verify model compiles**

```bash
cd C:/xampp/htdocs/quran-system && php artisan tinker --execute="echo App\Models\StudentPartRecord::class;"
```

Expected: class name output (no errors)

**Step 3: Commit**

```bash
git add app/Models/StudentPartRecord.php
git commit -m "feat: add StudentPartRecord model"
```

---

## Task 3: Create `StudentPartRecordController`

**Files:**
- Create: `app/Http/Controllers/Supervisor/StudentPartRecordController.php`

**Step 1: Write the controller**

```php
<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\QuranPart;
use App\Models\RecitationSession;
use App\Models\Student;
use App\Models\StudentPartRecord;
use Illuminate\Http\Request;

class StudentPartRecordController extends Controller
{
    public function index()
    {
        return view('supervisor.student-parts.index');
    }

    public function show($studentId)
    {
        $student = Student::with(['user', 'level', 'supervisor'])->findOrFail($studentId);

        $this->autoFillRecords($student);

        $parts = QuranPart::orderBy('part_number')->get();

        $records = StudentPartRecord::where('student_id', $studentId)
            ->get()
            ->keyBy('part_number');

        return view('supervisor.student-parts.show', [
            'student' => $student,
            'parts' => $parts,
            'records' => $records,
        ]);
    }

    public function update(Request $request, $studentId, $partNumber)
    {
        $validated = $request->validate([
            'is_completed' => 'boolean',
            'total_errors' => 'integer|min:0',
            'total_hesitations' => 'integer|min:0',
            'total_score' => 'nullable|numeric|min:0|max:100',
            'last_recitation_date' => 'nullable|date',
        ]);

        $validated['is_manual_override'] = true;

        StudentPartRecord::updateOrCreate(
            ['student_id' => $studentId, 'part_number' => $partNumber],
            $validated
        );

        return redirect()->back()->with('success', 'Part record updated.');
    }

    public function resetToAuto($studentId, $partNumber)
    {
        $student = Student::findOrFail($studentId);
        $calculated = $this->calculatePartRecord($student, $partNumber);

        StudentPartRecord::where('student_id', $studentId)
            ->where('part_number', $partNumber)
            ->delete();

        return redirect()->back()->with('success', 'Part record reset to auto-calculated.');
    }

    private function autoFillRecords(Student $student): void
    {
        foreach (range(1, 30) as $partNumber) {
            $existing = StudentPartRecord::where('student_id', $student->id)
                ->where('part_number', $partNumber)
                ->first();

            if ($existing && $existing->is_manual_override) {
                continue;
            }

            $calculated = $this->calculatePartRecord($student, $partNumber);

            StudentPartRecord::updateOrCreate(
                ['student_id' => $student->id, 'part_number' => $partNumber],
                $calculated
            );
        }
    }

    private function calculatePartRecord(Student $student, int $partNumber): array
    {
        $sessions = RecitationSession::where('student_id', $student->id)
            ->whereIn('type', ['new_memorization', 'review'])
            ->where('status', 'passed')
            ->get();

        $partSessions = $sessions->filter(function ($session) use ($partNumber) {
            $part = $session->part;
            if (!$part) {
                return false;
            }
            return $part->part_number == $partNumber;
        });

        if ($partSessions->isEmpty()) {
            return [
                'is_completed' => false,
                'total_errors' => 0,
                'total_hesitations' => 0,
                'total_score' => null,
                'last_recitation_date' => null,
                'is_manual_override' => false,
            ];
        }

        $totalErrors = $partSessions->sum('total_errors');
        $totalHesitations = $partSessions->sum('total_hesitations');
        $avgScore = $partSessions->avg('final_score');
        $lastDate = $partSessions->max('session_date');

        return [
            'is_completed' => true,
            'total_errors' => $totalErrors,
            'total_hesitations' => $totalHesitations,
            'total_score' => $avgScore,
            'last_recitation_date' => $lastDate,
            'is_manual_override' => false,
        ];
    }
}
```

**Step 2: Verify controller compiles**

```bash
cd C:/xampp/htdocs/quran-system && php artisan tinker --execute="echo App\Http\Controllers\Supervisor\StudentPartRecordController::class;"
```

Expected: class name output (no errors)

**Step 3: Commit**

```bash
git add app/Http/Controllers/Supervisor/StudentPartRecordController.php
git commit -m "feat: add StudentPartRecordController"
```

---

## Task 4: Add Routes

**Files:**
- Modify: `routes/web.php`

**Step 1: Add route**

Find the supervisor routes block and add:

```php
Route::get('/supervisor/student-parts', [StudentPartRecordController::class, 'index'])
    ->name('supervisor.student-parts.index');
Route::get('/supervisor/student-parts/{studentId}', [StudentPartRecordController::class, 'show'])
    ->name('supervisor.student-parts.show');
Route::post('/supervisor/student-parts/{studentId}/{partNumber}', [StudentPartRecordController::class, 'update'])
    ->name('supervisor.student-parts.update');
Route::post('/supervisor/student-parts/{studentId}/{partNumber}/reset', [StudentPartRecordController::class, 'resetToAuto'])
    ->name('supervisor.student-parts.reset');
```

**Step 2: Verify routes**

```bash
cd C:/xampp/htdocs/quran-system && php artisan route:list --name=supervisor.student-parts
```

Expected: four routes listed

**Step 3: Commit**

```bash
git add routes/web.php
git commit -m "feat: add student-parts routes"
```

---

## Task 5: Create Blade View — `index.blade.php`

**Files:**
- Create: `resources/views/supervisor/student-parts/index.blade.php`

**Step 1: Write the view**

```blade
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
    .parts-table th {
        background: #f1f3f5;
        font-weight: 600;
    }
    .parts-table .completed {
        background-color: #d4edda !important;
    }
    .parts-table .not-completed {
        background-color: #f8d7da !important;
    }
    .override-badge {
        background: #fff3cd;
        font-size: 10px;
        padding: 2px 6px;
        border-radius: 4px;
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
        <!-- Table rendered in show.blade.php -->
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function() {
    let debounceTimer;

    $('#studentSelect').select2({
        ajax: {
            url: '/supervisor/students/search',
            processResults: function(data) {
                return {
                    results: data.map(s => ({
                        id: s.id,
                        text: s.name + ' (' + s.student_number + ')'
                    }))
                };
            }
        },
        minimumInputLength: 2,
        placeholder: 'Search by name or student number...'
    });

    $('#studentSelect').on('change', function() {
        let studentId = $(this).val();
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
```

**Step 2: Verify view file exists**

```bash
test -f C:/xampp/htdocs/quran-system/resources/views/supervisor/student-parts/index.blade.php && echo "exists"
```

Expected: `exists`

**Step 3: Commit**

```bash
git add resources/views/supervisor/student-parts/index.blade.php
git commit -m "feat: add student-parts index view"
```

---

## Task 6: Create Blade View — `show.blade.php`

**Files:**
- Create: `resources/views/supervisor/student-parts/show.blade.php`

**Step 1: Write the view**

```blade
@extends('layouts.app')

@section('title', 'Parts Record — ' . $student->user->name)

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
    .edit-modal .modal-header { background: #667eea; color: white; }
    .status-completed { color: #28a745; font-weight: bold; }
    .status-not-completed { color: #dc3545; font-weight: bold; }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-book-quran"></i> Parts Record — {{ $student->user->name }}</h2>
        <a href="{{ route('supervisor.student-parts.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered parts-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Part Name</th>
                            <th>Status</th>
                            <th>Errors</th>
                            <th>Hesitations</th>
                            <th>Score</th>
                            <th>Last Recitation</th>
                            <th>Actions</th>
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
                                <td>
                                    <span class="{{ $isCompleted ? 'status-completed' : 'status-not-completed' }}">
                                        {{ $isCompleted ? 'مُكمَل' : 'غير مُكمَل' }}
                                    </span>
                                    @if($isOverride)
                                        <span class="override-badge">Manual</span>
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
                                        data-toggle="modal"
                                        data-target="#editModal">
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
                <div class="modal-header">
                    <h5 class="modal-title">Edit Part <span id="modalPartNumber"></span></h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="part_number" id="editPartNumber">

                    <div class="form-group">
                        <label><strong>Status</strong></label>
                        <select name="is_completed" id="editIsCompleted" class="form-control">
                            <option value="1">مُكمَل (Completed)</option>
                            <option value="0">غير مُكمَل (Not Completed)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label><strong>Total Errors</strong></label>
                        <input type="number" name="total_errors" id="editErrors" class="form-control" min="0">
                    </div>

                    <div class="form-group">
                        <label><strong>Total Hesitations</strong></label>
                        <input type="number" name="total_hesitations" id="editHesitations" class="form-control" min="0">
                    </div>

                    <div class="form-group">
                        <label><strong>Score (%)</strong></label>
                        <input type="number" name="total_score" id="editScore" class="form-control" min="0" max="100" step="0.01">
                    </div>

                    <div class="form-group">
                        <label><strong>Last Recitation Date</strong></label>
                        <input type="date" name="last_recitation_date" id="editDate" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Save
                    </button>
                    <button type="button" class="btn btn-outline-secondary" id="resetBtn">
                        <i class="fas fa-undo"></i> Reset to Auto
                    </button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
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
        let part = $(this).data('part');
        let record = $(this).data('record') || {};

        $('#modalPartNumber').text(part);
        $('#editPartNumber').val(part);
        $('#editIsCompleted').val(record.is_completed ? '1' : '0');
        $('#editErrors').val(record.total_errors || 0);
        $('#editHesitations').val(record.total_hesitations || 0);
        $('#editScore').val(record.total_score || '');
        $('#editDate').val(record.last_recitation_date || '');

        let studentId = {{ $student->id }};
        $('#editForm').attr('action', '/supervisor/student-parts/' + studentId + '/' + part);
        $('#resetBtn').off('click').on('click', function() {
            if (confirm('Reset this part to auto-calculated values?')) {
                $('<form>').attr('method', 'POST')
                    .attr('action', '/supervisor/student-parts/' + studentId + '/' + part + '/reset')
                    .append($('<input>').attr({name: '_token', value: $('meta[name="csrf-token"]').attr('content')}))
                    .appendTo('body');
            }
        });
    });
});
</script>
@endpush
@endsection
```

**Step 2: Verify view file exists**

```bash
test -f C:/xampp/htdocs/quran-system/resources/views/supervisor/student-parts/show.blade.php && echo "exists"
```

Expected: `exists`

**Step 3: Commit**

```bash
git add resources/views/supervisor/student-parts/show.blade.php
git commit -m "feat: add student-parts show view"
```

---

## Task 7: Add Student Search API Route

The index view uses `/supervisor/students/search` for Select2. Need to verify this route exists.

**Files:**
- Modify (if needed): `routes/web.php` or `Supervisor/StudentController.php`

**Step 1: Check if search route exists**

```bash
cd C:/xampp/htdocs/quran-system && php artisan route:list --name=supervisor.students.search
```

**Step 2: If not found, add to StudentController:**

In `app/Http/Controllers/Supervisor/StudentController.php`, add:

```php
public function search(Request $request)
{
    $query = $request->get('q', '');
    $students = Student::with('user')
        ->whereHas('user', function($q) use ($query) {
            $q->where('name', 'like', '%' . $query . '%')
              ->orWhere('student_number', 'like', '%' . $query . '%');
        })
        ->limit(20)
        ->get()
        ->map(function($s) {
            return [
                'id' => $s->id,
                'name' => $s->user->name,
                'student_number' => $s->student_number,
            ];
        });

    return response()->json($students);
}
```

And add route: `Route::get('/supervisor/students/search', [StudentController::class, 'search'])->name('supervisor.students.search');`

**Step 3: Commit**

```bash
git add app/Http/Controllers/Supervisor/StudentController.php routes/web.php
git commit -m "feat: add student search endpoint for student-parts selector"
```

---

## Task 8: Sidebar Navigation Link

**Files:**
- Modify: `resources/views/supervisor/partials/sidebar.blade.php`

**Step 1: Add nav item**

Find the navigation section and add:

```blade
<li class="nav-item">
    <a class="nav-link" href="{{ route('supervisor.student-parts.index') }}">
        <i class="fas fa-book-quran"></i> Student Parts Record
    </a>
</li>
```

**Step 2: Commit**

```bash
git add resources/views/supervisor/partials/sidebar.blade.php
git commit -m "feat: add student-parts link to supervisor sidebar"
```

---

## Verification

1. Run `php artisan migrate` — confirm `student_part_records` table exists
2. Browse to `/supervisor/student-parts` — confirm page loads with student selector
3. Select a student with recitation history — confirm table shows 30 parts
4. Verify auto-filled data matches recitation sessions
5. Click edit on a row, change values, save — verify data persists and `is_manual_override = true`
6. Click "Reset to Auto" — verify record reverts to auto-calculated
7. Verify sidebar link appears under supervisor navigation
