# Exam Assistant — Implementation Plan

> **For Claude:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Add supervisor exam workflow: select student, evaluate verses with errors+notes, review/edit before finishing, save complete exam record.

**Architecture:** New Exam + ExamQuestion models (MySQL). Existing QuranExamAssistantController updated to handle exam flow. Session-based "current exam" tracking for supervisors. Full page HTML display with highlighted verse.

**Tech Stack:** Laravel 11, MySQL, Blade views, vanilla JS

---

## Task 1: Create database migrations

**Files:**
- Create: `database/migrations/2026_04_22_000001_create_exams_table.php`
- Create: `database/migrations/2026_04_22_000002_create_exam_questions_table.php`

**Step 1: Create exams migration**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignId('supervisor_id')->constrained('users')->onDelete('cascade');
            $table->date('exam_date');
            $table->enum('status', ['in_progress', 'completed'])->default('in_progress');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exams');
    }
};
```

**Step 2: Create exam_questions migration**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained('exams')->onDelete('cascade');
            $table->foreignId('surah_id')->constrained('surahs')->onDelete('cascade');
            $table->foreignId('ayah_id')->constrained('ayahs')->onDelete('cascade');
            $table->integer('ayah_number');
            $table->integer('page_number');
            $table->integer('juz_number');
            $table->text('verse_text');
            $table->integer('errors_count')->default(0);
            $table->text('notes')->nullable();
            $table->integer('order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_questions');
    }
};
```

**Step 3: Run migrations**

Run: `php artisan migrate`
Expected: `Migrated: 2026_04_22_000001_create_exams_table.php`, `Migrated: 2026_04_22_000002_create_exam_questions_table.php`

---

## Task 2: Create Exam and ExamQuestion models

**Files:**
- Create: `app/Models/Exam.php`
- Create: `app/Models/ExamQuestion.php`

**Step 1: Write Exam model**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Exam extends Model
{
    protected $fillable = [
        'student_id',
        'supervisor_id',
        'exam_date',
        'status',
    ];

    protected $casts = [
        'exam_date' => 'date',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    public function questions(): HasMany
    {
        return $this->questions()->orderBy('order');
    }
}
```

**Step 2: Write ExamQuestion model**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamQuestion extends Model
{
    protected $fillable = [
        'exam_id',
        'surah_id',
        'ayah_id',
        'ayah_number',
        'page_number',
        'juz_number',
        'verse_text',
        'errors_count',
        'notes',
        'order',
    ];

    protected $casts = [
        'errors_count' => 'integer',
        'order' => 'integer',
    ];

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function surah(): BelongsTo
    {
        return $this->belongsTo(Surah::class);
    }

    public function ayah(): BelongsTo
    {
        return $this->belongsTo(Ayah::class);
    }
}
```

**Step 3: Add relationships to existing models**

Modify `app/Models/Student.php` — add:
```php
public function exams(): HasMany
{
    return $this->hasMany(Exam::class);
}
```

Modify `app/Models/User.php` — add:
```php
public function supervisedExams(): HasMany
{
    return $this->hasMany(Exam::class, 'supervisor_id');
}
```

---

## Task 3: Add exam API endpoints

**Files:**
- Create: `app/Http/Controllers/Api/ExamController.php`
- Modify: `routes/api.php`

**Step 1: Write ExamController**

```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamQuestion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExamController extends Controller
{
    public function start(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
        ]);

        $exam = Exam::create([
            'student_id' => $validated['student_id'],
            'supervisor_id' => Auth::id(),
            'exam_date' => now()->toDateString(),
            'status' => 'in_progress',
        ]);

        return response()->json(['exam' => $exam->load('student', 'questions')], 201);
    }

    public function addQuestion(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'exam_id' => 'required|exists:exams,id',
            'surah_id' => 'required|exists:surahs,id',
            'ayah_id' => 'required|exists:ayahs,id',
            'ayah_number' => 'required|integer',
            'page_number' => 'required|integer',
            'juz_number' => 'required|integer',
            'verse_text' => 'required|string',
            'errors_count' => 'required|integer|min:0',
            'notes' => 'nullable|string',
        ]);

        $exam = Exam::find($validated['exam_id']);
        if ($exam->status === 'completed') {
            return response()->json(['error' => 'Cannot add questions to a completed exam'], 422);
        }

        $order = ExamQuestion::where('exam_id', $validated['exam_id'])->max('order') + 1;

        $question = ExamQuestion::create([
            'exam_id' => $validated['exam_id'],
            'surah_id' => $validated['surah_id'],
            'ayah_id' => $validated['ayah_id'],
            'ayah_number' => $validated['ayah_number'],
            'page_number' => $validated['page_number'],
            'juz_number' => $validated['juz_number'],
            'verse_text' => $validated['verse_text'],
            'errors_count' => $validated['errors_count'],
            'notes' => $validated['notes'] ?? null,
            'order' => $order,
        ]);

        return response()->json(['question' => $question->load('surah', 'ayah')], 201);
    }

    public function updateQuestion(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'errors_count' => 'required|integer|min:0',
            'notes' => 'nullable|string',
        ]);

        $question = ExamQuestion::findOrFail($id);

        if ($question->exam->status === 'completed') {
            return response()->json(['error' => 'Cannot update questions in a completed exam'], 422);
        }

        $question->update($validated);

        return response()->json(['question' => $question->fresh()->load('surah', 'ayah')]);
    }

    public function finish(int $id): JsonResponse
    {
        $exam = Exam::findOrFail($id);

        if ($exam->supervisor_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $exam->update(['status' => 'completed']);

        return response()->json(['exam' => $exam->load('student', 'questions.surah', 'questions.ayah')]);
    }

    public function getQuestions(int $examId): JsonResponse
    {
        $exam = Exam::findOrFail($examId);

        return response()->json([
            'exam' => $exam->load('student'),
            'questions' => $exam->questions()->with('surah', 'ayah')->orderBy('order')->get(),
        ]);
    }

    public function getInProgress(): JsonResponse
    {
        $exam = Exam::where('supervisor_id', Auth::id())
            ->where('status', 'in_progress')
            ->latest()
            ->first();

        if (!$exam) {
            return response()->json(['exam' => null]);
        }

        return response()->json([
            'exam' => $exam->load('student'),
            'questions' => $exam->questions()->with('surah', 'ayah')->orderBy('order')->get(),
        ]);
    }
}
```

**Step 2: Register API routes**

In `routes/api.php` add:
```php
use App\Http\Controllers\Api\ExamController;

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/exam/start', [ExamController::class, 'start']);
    Route::post('/exam/add-question', [ExamController::class, 'addQuestion']);
    Route::put('/exam/questions/{id}', [ExamController::class, 'updateQuestion']);
    Route::post('/exam/{id}/finish', [ExamController::class, 'finish']);
    Route::get('/exam/{examId}/questions', [ExamController::class, 'getQuestions']);
    Route::get('/exam/in-progress', [ExamController::class, 'getInProgress']);
});
```

---

## Task 4: Add page HTML generation for Quran pages

**Files:**
- Modify: `app/Http/Controllers/Supervisor/QuranExamAssistantController.php`
- Modify: `app/Models/Ayah.php`

**Step 1: Add helper method to Ayah model for page HTML**

In `app/Models/Ayah.php`, add:

```php
public function getPageHtmlAttribute(): string
{
    $pageNumber = $this->page_number;
    $currentAyahId = $this->id;

    $pageAyahs = Ayah::where('page_number', $pageNumber)
        ->with('surah')
        ->orderBy('id')
        ->get();

    if ($pageAyahs->isEmpty()) {
        return '<p>Page not found</p>';
    }

    $html = '<div class="quran-page" dir="rtl" style="font-family: Amiri, serif; font-size: 1.4rem; line-height: 2.2; text-align: justify; padding: 2rem;">';

    $currentSurah = null;
    foreach ($pageAyahs as $ayah) {
        if ($currentSurah !== $ayah->surah_id) {
            if ($currentSurah !== null) {
                $html .= '</div>';
            }
            $html .= '<div class="surah-block" style="margin-bottom: 1.5rem;">';
            $html .= '<div class="surah-name" style="font-size: 1.6rem; font-weight: bold; margin-bottom: 0.5rem; color: #2c5282;">' . e($ayah->surah->name_ar) . '</div>';
            $currentSurah = $ayah->surah_id;
        }

        $highlightClass = $ayah->id === $currentAyahId ? 'highlighted-verse' : '';
        $html .= '<span class="ayah-text ' . $highlightClass . '" data-ayah-id="' . $ayah->id . '" data-surah="' . e($ayah->surah->name_ar) . '" data-ayah="' . $ayah->ayah_number . '" data-page="' . $ayah->page_number . '">';
        $html .= e($ayah->text) . ' ﴾' . $this->arabicNumeral($ayah->ayah_number) . '﴿ ';
        $html .= '</span>';
    }

    $html .= '</div></div>';

    return $html;
}

private function arabicNumeral(int $num): string
{
    $arabic = ['٠','١','٢','٣','٤','٥','٦','٧','٨','٩'];
    return implode('', array_map(fn($d) => $arabic[(int)$d], str_split($num)));
}
```

**Step 2: Update generateQuestion to return page_html**

Modify `generateQuestion` in `QuranExamAssistantController.php` to add `page_html` to response:

```php
return response()->json([
    'ayah' => $ayah->text,
    'surah' => $ayah->surah->name_ar,
    'ayah_number' => $ayah->ayah_number,
    'surah_number' => $ayah->surah->surah_number,
    'page_number' => $ayah->page_number,
    'juz_number' => $ayah->juz_number,
    'reference' => $reference,
    'page_html' => $ayah->page_html,
]);
```

---

## Task 5: Update the exam-assistant view

**Files:**
- Modify: `resources/views/supervisor/exam-assistant/index.blade.php`

**Step 1: Student selection and questions queue layout**

Replace the entire file content with a layout that has:
- **Top bar**: Student searchable dropdown + "Start Exam" button (shown when no active exam)
- **Exam header**: Student name, exam date, "Finish Exam" button (shown during exam)
- **Main area (two columns)**:
  - Left: Full page HTML display with highlighted verse
  - Right: Question form (errors count, notes, "Add Result") + questions queue list
- **Questions queue**: Scrollable list showing verse reference, errors count, notes — click to review

**Key UI elements needed:**
1. Student select dropdown (searchable — use a simple datalist or select2)
2. Exam session info bar
3. Full-page verse display area (dark theme card, Arabic text)
4. Result form: number input for errors, textarea for notes, "Add Result" button
5. Questions list: each item clickable to review/edit
6. "Next Question" button
7. "Finish Exam" button
8. "Cancel Exam" button (discard in-progress exam)

**Step 2: JavaScript logic**

JS should handle:
- On "Start Exam": POST `/api/exam/start` → store `examId` in JS state
- On "Generate Question": existing behavior (load verse + page_html)
- On "Add Result": POST `/api/exam/add-question` with current verse data + errors + notes
- On "Finish Exam": POST `/api/exam/{id}/finish`
- On question click in queue: load that question into review mode (pre-fill form)
- On form save in review mode: PUT `/api/exam/questions/{id}`
- Poll or check on page load: GET `/api/exam/in-progress` to resume any existing exam

---

## Task 6: Integrate exam flow into QuranExamAssistantController

**Files:**
- Modify: `app/Http/Controllers/Supervisor/QuranExamAssistantController.php`

**Step 1: Add exam_id parameter to generateQuestion**

The `generateQuestion` method should accept an optional `exam_id` to associate the generated verse with. Update route to accept `exam_id` as query param.

**Step 2: Keep generateQuestion the same for non-exam usage**

If no `exam_id` provided, works as before (verse generation only). If `exam_id` provided, the response includes data needed for `addQuestion` call.

---

## Task 7: Verify end-to-end

**Step 1: Start exam**

Navigate to `/supervisor/exam-assistant`, select a student, click Start Exam.
Expected: Exam record created with `status: in_progress`

**Step 2: Generate and add questions**

Generate verses, add error counts and notes, click Add Result for each.
Expected: Each question appears in queue list with correct data

**Step 3: Review/edit**

Click a question in the queue.
Expected: Form pre-fills with existing values, edit saves via API

**Step 4: Finish exam**

Click Finish Exam.
Expected: Exam status → `completed`, all questions permanently saved

**Step 5: Page refresh**

Refresh page during in-progress exam.
Expected: Exam resumes automatically via `/api/exam/in-progress`

---

## Execution Options

**1. Subagent-Driven (this session)** — I dispatch fresh subagent per task, review between tasks, fast iteration

**2. Parallel Session (separate)** — Open new session with executing-plans, batch execution with checkpoints

Which approach?
