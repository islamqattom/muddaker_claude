# Warning Automation Implementation Plan

> **For Claude:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Build a threshold-based warning automation system that detects at-risk students and generates warning drafts for supervisor approval.

**Architecture:** Two new database tables (`warning_rules`, `warning_drafts`), an Artisan command for scheduled rule evaluation, a Supervisor draft review UI, and an Admin rule management UI. Reuses existing Warning model and notification system.

**Tech Stack:** Laravel 10, MySQL, Laravel Sanctum, existing Blade/Bootstrap RTL templates.

---

## Task 1: Database Migrations

### Task 1a: Create `warning_rules` migration

**File:** Create: `database/migrations/2026_03_27_000001_create_warning_rules_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('warning_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['attendance', 'recitation', 'general']);
            $table->enum('condition_type', ['absent_days', 'late_days', 'failed_sessions', 'not_recitate_count']);
            $table->integer('threshold')->unsigned();
            $table->integer('period_days')->unsigned()->default(30);
            $table->json('level_ids')->nullable(); // null = all levels
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('warning_rules');
    }
};
```

**Step 2: Run migration**

Run: `php artisan migrate`
Expected: `Migrating: 2026_03_27_000001_create_warning_rules_table` → `Migrated: 2026_03_27_000001_create_warning_rules_table`

---

### Task 1b: Create `warning_drafts` migration

**File:** Create: `database/migrations/2026_03_27_000002_create_warning_drafts_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('warning_drafts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warning_rule_id')->constrained('warning_rules')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('supervisor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['pending', 'approved', 'dismissed'])->default('pending');
            $table->string('title')->nullable();
            $table->text('content')->nullable();
            $table->date('issue_date')->nullable();
            $table->date('from_date')->nullable();
            $table->date('to_date')->nullable();
            $table->json('attendance_data')->nullable();
            $table->json('recitation_data')->nullable();
            $table->json('test_data')->nullable();
            $table->integer('triggered_value')->unsigned()->default(0);
            $table->integer('triggered_threshold')->unsigned()->default(0);
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index(['student_id', 'warning_rule_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('warning_drafts');
    }
};
```

**Step 2: Run migration**

Run: `php artisan migrate`
Expected: `Migrating: 2026_03_27_000002_create_warning_drafts_table` → `Migrated: 2026_03_27_000002_create_warning_drafts_table`

**Step 3: Commit**

```bash
git add database/migrations/2026_03_27_000001_create_warning_rules_table.php database/migrations/2026_03_27_000002_create_warning_drafts_table.php
git commit -m "feat(warnings): add warning_rules and warning_drafts tables

Co-Authored-By: Claude Opus 4.6 <noreply@anthropic.com>"
```

---

## Task 2: WarningRule Model

**File:** Create: `app/Models/WarningRule.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WarningRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'condition_type',
        'threshold',
        'period_days',
        'level_ids',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'level_ids' => 'array',
        'is_active' => 'boolean',
        'threshold' => 'integer',
        'period_days' => 'integer',
    ];

    public const TYPES = ['attendance', 'recitation', 'general'];
    public const CONDITION_TYPES = ['absent_days', 'late_days', 'failed_sessions', 'not_recitate_count'];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function drafts(): HasMany
    {
        return $this->hasMany(WarningDraft::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function isAttendance(): bool
    {
        return $this->type === 'attendance';
    }

    public function isRecitation(): bool
    {
        return $this->type === 'recitation';
    }
}
```

**Step 2: Verify model loads**

Run: `php artisan tinker --execute="echo App\Models\WarningRule::class;"`
Expected: `App\Models\WarningRule`

**Step 3: Commit**

```bash
git add app/Models/WarningRule.php
git commit -m "feat(warnings): add WarningRule model

Co-Authored-By: Claude Opus 4.6 <noreply@anthropic.com>"
```

---

## Task 3: WarningDraft Model

**File:** Create: `app/Models/WarningDraft.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WarningDraft extends Model
{
    use HasFactory;

    protected $fillable = [
        'warning_rule_id',
        'student_id',
        'supervisor_id',
        'status',
        'title',
        'content',
        'issue_date',
        'from_date',
        'to_date',
        'attendance_data',
        'recitation_data',
        'test_data',
        'triggered_value',
        'triggered_threshold',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'attendance_data' => 'array',
        'recitation_data' => 'array',
        'test_data' => 'array',
        'triggered_value' => 'integer',
        'triggered_threshold' => 'integer',
        'issue_date' => 'date',
        'from_date' => 'date',
        'to_date' => 'date',
        'reviewed_at' => 'datetime',
    ];

    public const STATUSES = ['pending', 'approved', 'dismissed'];

    public function rule(): BelongsTo
    {
        return $this->belongsTo(WarningRule::class, 'warning_rule_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isDismissed(): bool
    {
        return $this->status === 'dismissed';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }
}
```

**Step 2: Verify model loads**

Run: `php artisan tinker --execute="echo App\Models\WarningDraft::class;"`
Expected: `App\Models\WarningDraft`

**Step 3: Commit**

```bash
git add app/Models/WarningDraft.php
git commit -m "feat(warnings): add WarningDraft model

Co-Authored-By: Claude Opus 4.6 <noreply@anthropic.com>"
```

---

## Task 4: EvaluateWarningRules Artisan Command

**File:** Create: `app/Console/Commands/EvaluateWarningRules.php`

```php
<?php

namespace App\Console\Commands;

use App\Models\Attendance;
use App\Models\RecitationSession;
use App\Models\Student;
use App\Models\User;
use App\Models\WarningDraft;
use App\Models\WarningRule;
use App\Notifications\WarningDraftCreated;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EvaluateWarningRules extends Command
{
    protected $signature = 'warning:evaluate-rules {--rule= : Specific rule ID to evaluate}';

    protected $description = 'Evaluate all active warning rules and create drafts for triggering students';

    public function handle(): int
    {
        $query = WarningRule::active();
        if ($this->option('rule')) {
            $query->where('id', $this->option('rule'));
        }
        $rules = $query->get();

        $this->info("Evaluating {$rules->count()} rule(s)...");

        $createdCount = 0;

        foreach ($rules as $rule) {
            $results = $this->evaluateRule($rule);
            $createdCount += $results;
        }

        $this->info("Done. Created {$createdCount} draft(s).");
        return Command::SUCCESS;
    }

    protected function evaluateRule(WarningRule $rule): int
    {
        $toDate = Carbon::today();
        $fromDate = $toDate->copy()->subDays($rule->period_days);
        $createdCount = 0;

        if ($rule->condition_type === 'absent_days') {
            $triggeringStudents = $this->getStudentsWithAbsentDays($fromDate, $toDate, $rule->threshold, $rule->level_ids);
        } elseif ($rule->condition_type === 'late_days') {
            $triggeringStudents = $this->getStudentsWithLateDays($fromDate, $toDate, $rule->threshold, $rule->level_ids);
        } elseif ($rule->condition_type === 'failed_sessions') {
            $triggeringStudents = $this->getStudentsWithFailedSessions($fromDate, $toDate, $rule->threshold, $rule->level_ids);
        } elseif ($rule->condition_type === 'not_recitate_count') {
            $triggeringStudents = $this->getStudentsWithNotRecitate($fromDate, $toDate, $rule->threshold, $rule->level_ids);
        } else {
            return 0;
        }

        foreach ($triggeringStudents as $studentData) {
            $student = Student::find($studentData['student_id']);
            if (!$student || !$student->supervisor_id) {
                continue;
            }

            // Skip if pending/approved draft already exists for this rule+student
            $existingDraft = WarningDraft::where('warning_rule_id', $rule->id)
                ->where('student_id', $student->id)
                ->whereIn('status', ['pending', 'approved'])
                ->first();

            if ($existingDraft) {
                continue;
            }

            // Skip if dismissed draft from same rule within the same period
            $recentlyDismissed = WarningDraft::where('warning_rule_id', $rule->id)
                ->where('student_id', $student->id)
                ->where('status', 'dismissed')
                ->where('created_at', '>=', $fromDate)
                ->exists();

            if ($recentlyDismissed) {
                continue;
            }

            $attendanceData = [];
            $recitationData = [];

            if ($rule->isAttendance()) {
                $attendanceData = $this->fetchAttendanceData($student, $fromDate, $toDate, $rule->condition_type);
            }

            if ($rule->isRecitation()) {
                $recitationData = $this->fetchRecitationData($student, $fromDate, $toDate, $rule->condition_type);
            }

            $title = $this->generateTitle($rule, $student, $studentData['count']);
            $content = $this->generateContent($rule, $student, $studentData['count'], $fromDate, $toDate);

            $draft = WarningDraft::create([
                'warning_rule_id' => $rule->id,
                'student_id' => $student->id,
                'supervisor_id' => $student->supervisor_id,
                'status' => 'pending',
                'title' => $title,
                'content' => $content,
                'issue_date' => $toDate,
                'from_date' => $fromDate,
                'to_date' => $toDate,
                'attendance_data' => $attendanceData,
                'recitation_data' => $recitationData,
                'triggered_value' => $studentData['count'],
                'triggered_threshold' => $rule->threshold,
            ]);

            // Notify supervisor
            $supervisor = User::find($student->supervisor_id);
            if ($supervisor) {
                try {
                    $supervisor->notify(new WarningDraftCreated($draft));
                } catch (\Exception $e) {
                    Log::error("Failed to notify supervisor about warning draft: " . $e->getMessage());
                }
            }

            $createdCount++;
            $this->line("  Created draft for student {$student->name} (triggered: {$studentData['count']})");
        }

        return $createdCount;
    }

    protected function getStudentsWithAbsentDays(Carbon $fromDate, Carbon $toDate, int $threshold, ?array $levelIds): array
    {
        $query = Attendance::select('student_id', DB::raw('COUNT(*) as count'))
            ->whereBetween('date', [$fromDate->toDateString(), $toDate->toDateString()])
            ->where('status', 'absent')
            ->groupBy('student_id')
            ->having('count', '>=', $threshold);

        if ($levelIds) {
            $query->whereHas('student', fn($q) => $q->whereIn('level_id', $levelIds));
        }

        return $query->get()->map(fn($r) => ['student_id' => $r->student_id, 'count' => (int) $r->count])->toArray();
    }

    protected function getStudentsWithLateDays(Carbon $fromDate, Carbon $toDate, int $threshold, ?array $levelIds): array
    {
        $query = Attendance::select('student_id', DB::raw('COUNT(*) as count'))
            ->whereBetween('date', [$fromDate->toDateString(), $toDate->toDateString()])
            ->where('status', 'late')
            ->groupBy('student_id')
            ->having('count', '>=', $threshold);

        if ($levelIds) {
            $query->whereHas('student', fn($q) => $q->whereIn('level_id', $levelIds));
        }

        return $query->get()->map(fn($r) => ['student_id' => $r->student_id, 'count' => (int) $r->count])->toArray();
    }

    protected function getStudentsWithFailedSessions(Carbon $fromDate, Carbon $toDate, int $threshold, ?array $levelIds): array
    {
        $query = RecitationSession::select('student_id', DB::raw('COUNT(*) as count'))
            ->whereBetween('session_date', [$fromDate->toDateString(), $toDate->toDateString()])
            ->where('status', 'failed')
            ->groupBy('student_id')
            ->having('count', '>=', $threshold);

        if ($levelIds) {
            $query->whereHas('student', fn($q) => $q->whereIn('level_id', $levelIds));
        }

        return $query->get()->map(fn($r) => ['student_id' => $r->student_id, 'count' => (int) $r->count])->toArray();
    }

    protected function getStudentsWithNotRecitate(Carbon $fromDate, Carbon $toDate, int $threshold, ?array $levelIds): array
    {
        $query = RecitationSession::select('student_id', DB::raw('COUNT(*) as count'))
            ->whereBetween('session_date', [$fromDate->toDateString(), $toDate->toDateString()])
            ->where('status', 'not_recitate')
            ->groupBy('student_id')
            ->having('count', '>=', $threshold);

        if ($levelIds) {
            $query->whereHas('student', fn($q) => $q->whereIn('level_id', $levelIds));
        }

        return $query->get()->map(fn($r) => ['student_id' => $r->student_id, 'count' => (int) $r->count])->toArray();
    }

    protected function fetchAttendanceData(Student $student, Carbon $fromDate, Carbon $toDate, string $conditionType): array
    {
        $statusMap = [
            'absent_days' => ['absent', 'late', 'excused'],
            'late_days' => ['late'],
        ];

        $statuses = $statusMap[$conditionType] ?? ['absent'];

        return Attendance::where('student_id', $student->id)
            ->whereBetween('date', [$fromDate->toDateString(), $toDate->toDateString()])
            ->whereIn('status', $statuses)
            ->orderBy('date', 'desc')
            ->get()
            ->map(fn($a) => [
                'id' => $a->id,
                'day' => $a->day,
                'date' => $a->date,
                'status' => $a->status,
                'arrival_time' => $a->arrival_time,
                'notes' => $a->notes,
            ])
            ->toArray();
    }

    protected function fetchRecitationData(Student $student, Carbon $fromDate, Carbon $toDate, string $conditionType): array
    {
        $statusMap = [
            'failed_sessions' => ['failed', 'not_recitate'],
            'not_recitate_count' => ['not_recitate'],
        ];

        $statuses = $statusMap[$conditionType] ?? ['failed'];

        return RecitationSession::where('student_id', $student->id)
            ->whereBetween('session_date', [$fromDate->toDateString(), $toDate->toDateString()])
            ->whereIn('status', $statuses)
            ->with(['pages'])
            ->orderBy('session_date', 'desc')
            ->get()
            ->map(fn($s) => [
                'id' => $s->id,
                'day' => $s->day,
                'session_date' => $s->session_date,
                'type' => $s->type,
                'status' => $s->status,
                'total_pages' => $s->total_pages,
                'passed_pages' => $s->passed_pages ?? 0,
                'failed_pages' => $s->failed_pages ?? 0,
                'notes' => $s->notes,
            ])
            ->toArray();
    }

    protected function generateTitle(WarningRule $rule, Student $student, int $count): string
    {
        $typeLabels = [
            'attendance' => 'إنذار غياب/تأخير',
            'recitation' => 'إنذار تقييم حفظ',
            'general' => 'إنذار عام',
        ];
        return $typeLabels[$rule->type] ?? 'إنذار';
    }

    protected function generateContent(WarningRule $rule, Student $student, int $count, Carbon $fromDate, Carbon $toDate): string
    {
        $fromFormatted = $fromDate->format('Y-m-d');
        $toFormatted = $toDate->format('Y-m-d');

        if ($rule->condition_type === 'absent_days') {
            return "خلال الفترة من {$fromFormatted} إلى {$toFormatted}، تغيب الطالب {$student->name} عن {$count} أيام من أصل الحضور المطلوب.";
        }
        if ($rule->condition_type === 'late_days') {
            return "خلال الفترة من {$fromFormatted} إلى {$toFormatted}، تأخر الطالب {$student->name} في الحضور {$count} مرات.";
        }
        if ($rule->condition_type === 'failed_sessions') {
            return "خلال الفترة من {$fromFormatted} إلى {$toFormatted}، لم يجتز الطالب {$student->name} {$count} حصص تقييم.";
        }
        if ($rule->condition_type === 'not_recitate_count') {
            return "خلال الفترة من {$fromFormatted} إلى {$toFormatted}، لم يسمع الطالب {$student->name} {$count} حصص.";
        }
        return "تم رصد حالة تستدعي إصدار إنذار للطالب {$student->name}.";
    }
}
```

**Step 2: Verify command registers**

Run: `php artisan list warning`
Expected: `warning:evaluate-rules` in the list

**Step 3: Commit**

```bash
git add app/Console/Commands/EvaluateWarningRules.php
git commit -m "feat(warnings): add EvaluateWarningRules artisan command

Co-Authored-By: Claude Opus 4.6 <noreply@anthropic.com>"
```

---

## Task 5: Schedule the Command

**File:** Modify: `app/Console/Kernel.php`

Find the `schedule()` method and add:

```php
$schedule->command('warning:evaluate-rules')->daily();
```

(Keep existing schedules intact, add the new one.)

**Step 2: Verify schedule**

Run: `php artisan schedule:list`
Expected: `warning:evaluate-rules` listed with daily frequency

**Step 3: Commit**

```bash
git add app/Console/Kernel.php
git commit -m "feat(warnings): schedule warning rule evaluation daily

Co-Authored-By: Claude Opus 4.6 <noreply@anthropic.com>"
```

---

## Task 6: WarningDraftCreated Notification

**File:** Create: `app/Notifications/WarningDraftCreated.php`

```php
<?php

namespace App\Notifications;

use App\Models\WarningDraft;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WarningDraftCreated extends Notification
{
    use Queueable;

    public function __construct(public WarningDraft $draft)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $studentName = $this->draft->student?->name ?? 'الطالب';
        $ruleName = $this->draft->rule?->name ?? 'قاعدة الإنذار';

        return [
            'type' => 'warning_draft_ready',
            'warning_draft_id' => $this->draft->id,
            'student_id' => $this->draft->student_id,
            'message' => "تنبيه جديد: تم إنشاء مسودة إنذار لـ {$studentName} بناءً على قاعدة \"{$ruleName}\"",
        ];
    }
}
```

**Step 2: Verify notification class**

Run: `php artisan tinker --execute="echo class_exists(App\Notifications\WarningDraftCreated::class) ? 'OK' : 'FAIL';"`
Expected: `OK`

**Step 3: Commit**

```bash
git add app/Notifications/WarningDraftCreated.php
git commit -m "feat(warnings): add WarningDraftCreated notification

Co-Authored-By: Claude Opus 4.6 <noreply@anthropic.com>"
```

---

## Task 7: Supervisor WarningDraftController

**File:** Create: `app/Http/Controllers/Supervisor/WarningDraftController.php`

```php
<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Warning;
use App\Models\WarningDraft;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WarningDraftController extends Controller
{
    public function index()
    {
        $drafts = WarningDraft::where('supervisor_id', Auth::id())
            ->where('status', 'pending')
            ->with(['student', 'rule'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('supervisor.warning-drafts.index', compact('drafts'));
    }

    public function show(int $id)
    {
        $draft = WarningDraft::where('supervisor_id', Auth::id())
            ->where('id', $id)
            ->where('status', 'pending')
            ->with(['student', 'rule'])
            ->firstOrFail();

        return view('supervisor.warning-drafts.show', compact('draft'));
    }

    public function approve(int $id)
    {
        $draft = WarningDraft::where('supervisor_id', Auth::id())
            ->where('id', $id)
            ->where('status', 'pending')
            ->firstOrFail();

        // Determine warning type from rule
        $type = $draft->rule->type ?? 'general';

        // Calculate absent_days, late_days, etc. from the stored data
        $attendanceRecords = $draft->attendance_data ?? [];
        $recitationRecords = $draft->recitation_data ?? [];

        $absentDays = collect($attendanceRecords)->where('status', 'absent')->count();
        $lateDays = collect($attendanceRecords)->where('status', 'late')->count();
        $totalAttendance = $absentDays + $lateDays + collect($attendanceRecords)->whereIn('status', ['present', 'excused'])->count();

        $failedSessions = collect($recitationRecords)->whereIn('status', ['failed', 'not_recitate'])->count();
        $totalSessions = count($recitationRecords);

        // Get next warning number
        $prefix = 'WRN-' . date('Y') . '-';
        $lastWarning = Warning::where('warning_number', 'LIKE', $prefix)
            ->orderBy('warning_number', 'desc')
            ->first();
        $lastNum = $lastWarning ? (int) substr($lastWarning->warning_number, -4) : 0;
        $warningNumber = $prefix . str_pad($lastNum + 1, 4, '0', STR_PAD_LEFT);

        // Create the actual warning
        $warning = Warning::create([
            'student_id' => $draft->student_id,
            'issued_by' => Auth::id(),
            'warning_number' => $warningNumber,
            'type' => $type,
            'title' => $draft->title,
            'content' => $draft->content,
            'issue_date' => $draft->issue_date,
            'issue_day' => $draft->issue_date ? \App\Models\Student::getArabicDay($draft->issue_date) : null,
            'from_date' => $draft->from_date,
            'from_day' => $draft->from_date ? \App\Models\Student::getArabicDay($draft->from_date) : null,
            'to_date' => $draft->to_date,
            'to_day' => $draft->to_date ? \App\Models\Student::getArabicDay($draft->to_date) : null,
            'absent_days' => $absentDays,
            'late_days' => $lateDays,
            'total_days' => $totalAttendance ?: null,
            'failed_sessions' => $failedSessions,
            'total_sessions' => $totalSessions ?: null,
            'attendance_data' => $attendanceRecords,
            'recitation_data' => $recitationRecords,
            'test_data' => $draft->test_data ?? [],
        ]);

        // Send parent notification (reuse existing logic from WarningController)
        $this->notifyParent($draft->student, $warning);

        // Mark draft as approved
        $draft->update([
            'status' => 'approved',
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        return redirect()
            ->route('supervisor.warnings.show', $warning->id)
            ->with('success', 'تم إصدار الإنذار بنجاح');
    }

    public function dismiss(int $id)
    {
        $draft = WarningDraft::where('supervisor_id', Auth::id())
            ->where('id', $id)
            ->where('status', 'pending')
            ->firstOrFail();

        $draft->update([
            'status' => 'dismissed',
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        return redirect()
            ->route('supervisor.warning-drafts.index')
            ->with('success', 'تم تجاهل المسودة');
    }

    protected function notifyParent(Student $student, Warning $warning): void
    {
        $parent = $student->parent;
        if (!$parent || !$parent->user) {
            return;
        }

        try {
            $parent->user->notify(new \App\Notifications\WarningIssued($warning));
        } catch (\Exception $e) {
            // Log but don't fail the operation
            \Log::error("Failed to notify parent: " . $e->getMessage());
        }
    }
}
```

Note: If `WarningIssued` notification does not exist, replace the notification call with the existing parent notification logic from `Supervisor/WarningController.php`.

**Step 2: Verify controller compiles**

Run: `php artisan tinker --execute="echo class_exists(App\Http\Controllers\Supervisor\WarningDraftController::class) ? 'OK' : 'FAIL';"`
Expected: `OK`

**Step 3: Commit**

```bash
git add app/Http/Controllers/Supervisor/WarningDraftController.php
git commit -m "feat(warnings): add Supervisor WarningDraftController

Co-Authored-By: Claude Opus 4.6 <noreply@anthropic.com>"
```

---

## Task 8: Supervisor Warning Drafts Views

**File:** Create: `resources/views/supervisor/warning-drafts/index.blade.php`

```blade
@extends('layouts.app')

@section('title', 'مسودات الإنذارات')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h3 class="card-title">مسودات الإنذارات</h3>
                </div>
                <div class="card-body">
                    @if($drafts->isEmpty())
                        <div class="alert alert-info text-center">
                            <i class="fas fa-check-circle"></i> لا توجد مسودات.pending review
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>الطالب</th>
                                        <th>قاعدة الإنذار</th>
                                        <th>القيمة المحفزة</th>
                                        <th>الفترة</th>
                                        <th>تاريخ الإنشاء</th>
                                        <th>إجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($drafts as $draft)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>
                                            <a href="{{ route('supervisor.students.show', $draft->student_id) }}">
                                                {{ $draft->student?->name ?? '-' }}
                                            </a>
                                        </td>
                                        <td>{{ $draft->rule?->name ?? '-' }}</td>
                                        <td>
                                            <span class="badge badge-danger">
                                                {{ $draft->triggered_value }} / {{ $draft->triggered_threshold }}
                                            </span>
                                        </td>
                                        <td>
                                            {{ $draft->from_date?->format('Y-m-d') ?? '-' }}
                                            →
                                            {{ $draft->to_date?->format('Y-m-d') ?? '-' }}
                                        </td>
                                        <td>{{ $draft->created_at->format('Y-m-d H:i') }}</td>
                                        <td>
                                            <a href="{{ route('supervisor.warning-drafts.show', $draft->id) }}"
                                               class="btn btn-sm btn-primary">
                                                <i class="fas fa-eye"></i> مراجعة
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3">
                            {{ $drafts->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
```

**File:** Create: `resources/views/supervisor/warning-drafts/show.blade.php`

```blade
@extends('layouts.app')

@section('title', 'مراجعة مسودة الإنذار')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            {{-- Draft Summary Card --}}
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">ملخص المسودة</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <strong>الطالب:</strong>
                            <a href="{{ route('supervisor.students.show', $draft->student_id) }}">
                                {{ $draft->student?->name ?? '-' }}
                            </a>
                        </div>
                        <div class="col-md-4">
                            <strong>قاعدة الإنذار:</strong> {{ $draft->rule?->name ?? '-' }}
                        </div>
                        <div class="col-md-4">
                            <strong>القيمة المحفزة:</strong>
                            <span class="text-danger">{{ $draft->triggered_value }}</span>
                            (الحد: {{ $draft->triggered_threshold }})
                        </div>
                        <div class="col-md-4 mt-2">
                            <strong>الفترة:</strong>
                            {{ $draft->from_date?->format('Y-m-d') ?? '-' }} → {{ $draft->to_date?->format('Y-m-d') ?? '-' }}
                        </div>
                        <div class="col-md-4 mt-2">
                            <strong>تاريخ المسودة:</strong> {{ $draft->created_at->format('Y-m-d H:i') }}
                        </div>
                    </div>

                    <hr>

                    <h5>محتوى المسودة</h5>
                    <div class="alert alert-secondary">
                        <strong>العنوان:</strong> {{ $draft->title }}<br><br>
                        <strong>المحتوى:</strong><br>
                        {{ $draft->content }}
                    </div>
                </div>
            </div>

            {{-- Attendance Data --}}
            @if(!empty($draft->attendance_data) && count($draft->attendance_data) > 0)
            <div class="card card-warning card-outline">
                <div class="card-header">
                    <h3 class="card-title">بيانات الحضور</h3>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-sm table-striped">
                        <thead>
                            <tr>
                                <th>اليوم</th>
                                <th>التاريخ</th>
                                <th>الحالة</th>
                                <th>وقت الحضور</th>
                                <th>ملاحظات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($draft->attendance_data as $record)
                            <tr>
                                <td>{{ $record['day'] ?? '-' }}</td>
                                <td>{{ $record['date'] ?? '-' }}</td>
                                <td>
                                    @php
                                        $statusClass = match($record['status'] ?? '') {
                                            'absent' => 'badge-danger',
                                            'late' => 'badge-warning',
                                            'excused' => 'badge-info',
                                            default => 'badge-secondary'
                                        };
                                    @endphp
                                    <span class="badge {{ $statusClass }}">
                                        {{ $record['status'] ?? '-' }}
                                    </span>
                                </td>
                                <td>{{ $record['arrival_time'] ?? '-' }}</td>
                                <td>{{ $record['notes'] ?? '-' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            {{-- Recitation Data --}}
            @if(!empty($draft->recitation_data) && count($draft->recitation_data) > 0)
            <div class="card card-danger card-outline">
                <div class="card-header">
                    <h3 class="card-title">بيانات التقييم</h3>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-sm table-striped">
                        <thead>
                            <tr>
                                <th>اليوم</th>
                                <th>التاريخ</th>
                                <th>نوع الحصة</th>
                                <th>الحالة</th>
                                <th>الصفحات</th>
                                <th>ملاحظات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($draft->recitation_data as $record)
                            <tr>
                                <td>{{ $record['day'] ?? '-' }}</td>
                                <td>{{ $record['session_date'] ?? '-' }}</td>
                                <td>{{ $record['type'] ?? '-' }}</td>
                                <td>
                                    @php
                                        $statusClass = match($record['status'] ?? '') {
                                            'failed' => 'badge-danger',
                                            'not_recitate' => 'badge-dark',
                                            default => 'badge-secondary'
                                        };
                                    @endphp
                                    <span class="badge {{ $statusClass }}">
                                        {{ $record['status'] ?? '-' }}
                                    </span>
                                </td>
                                <td>
                                    @if(isset($record['total_pages']))
                                        {{ $record['passed_pages'] ?? 0 }} / {{ $record['total_pages'] }}
                                        (<span class="text-danger">{{ $record['failed_pages'] ?? 0 }} فشل</span>)
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>{{ $record['notes'] ?? '-' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            {{-- Actions --}}
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <form action="{{ route('supervisor.warning-drafts.approve', $draft->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-success btn-lg btn-block"
                                        onclick="return confirm('هل أنت متأكد من إصدار هذا الإنذار؟')">
                                    <i class="fas fa-check"></i> اعتماد وإصدار الإنذار
                                </button>
                            </form>
                        </div>
                        <div class="col-md-6">
                            <form action="{{ route('supervisor.warning-drafts.dismiss', $draft->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-secondary btn-lg btn-block"
                                        onclick="return confirm('هل أنت متأكد من تجاهل هذه المسودة؟')">
                                    <i class="fas fa-times"></i> تجاهل المسودة
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
```

**Step 3: Commit**

```bash
git add resources/views/supervisor/warning-drafts/
git commit -m "feat(warnings): add supervisor warning drafts views

Co-Authored-By: Claude Opus 4.6 <noreply@anthropic.com>"
```

---

## Task 9: Admin WarningRuleController

**File:** Create: `app/Http/Controllers/Admin/WarningRuleController.php`

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\RecitationSession;
use App\Models\Student;
use App\Models\WarningRule;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class WarningRuleController extends Controller
{
    public function index()
    {
        $rules = WarningRule::with('creator')->orderBy('created_at', 'desc')->paginate(20);
        return view('admin.warning-rules.index', compact('rules'));
    }

    public function create()
    {
        $levels = \App\Models\Level::orderBy('order')->get();
        return view('admin.warning-rules.create', compact('levels'));
    }

    public function store(Request $request)
    {
        $this->validateStore($request);

        WarningRule::create([
            'name' => $request->name,
            'type' => $request->type,
            'condition_type' => $request->condition_type,
            'threshold' => $request->threshold,
            'period_days' => $request->period_days ?? 30,
            'level_ids' => $request->level_ids ?: null,
            'is_active' => $request->boolean('is_active', true),
            'created_by' => auth()->id(),
        ]);

        return redirect()
            ->route('admin.warning-rules.index')
            ->with('success', 'تم إنشاء القاعدة بنجاح');
    }

    public function edit(int $id)
    {
        $rule = WarningRule::findOrFail($id);
        $levels = \App\Models\Level::orderBy('order')->get();
        return view('admin.warning-rules.edit', compact('rule', 'levels'));
    }

    public function update(Request $request, int $id)
    {
        $rule = WarningRule::findOrFail($id);
        $this->validateStore($request, $rule->id);

        $rule->update([
            'name' => $request->name,
            'type' => $request->type,
            'condition_type' => $request->condition_type,
            'threshold' => $request->threshold,
            'period_days' => $request->period_days ?? 30,
            'level_ids' => $request->level_ids ?: null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()
            ->route('admin.warning-rules.index')
            ->with('success', 'تم تحديث القاعدة بنجاح');
    }

    public function destroy(int $id)
    {
        $rule = WarningRule::findOrFail($id);
        $rule->delete();

        return redirect()
            ->route('admin.warning-rules.index')
            ->with('success', 'تم حذف القاعدة بنجاح');
    }

    public function toggle(int $id)
    {
        $rule = WarningRule::findOrFail($id);
        $rule->update(['is_active' => !$rule->is_active]);

        $status = $rule->is_active ? 'تفعيل' : 'إلغاء تفعيل';
        return redirect()
            ->route('admin.warning-rules.index')
            ->with('success', "تم {$status} القاعدة بنجاح");
    }

    public function test(Request $request)
    {
        $request->validate([
            'condition_type' => 'required|in:' . implode(',', WarningRule::CONDITION_TYPES),
            'threshold' => 'required|integer|min:1',
            'period_days' => 'required|integer|min:1',
            'type' => 'required|in:' . implode(',', WarningRule::TYPES),
        ]);

        $fromDate = Carbon::today()->subDays($request->period_days);
        $toDate = Carbon::today();
        $threshold = $request->threshold;
        $conditionType = $request->condition_type;

        $triggeringStudents = $this->getTriggeringStudents($conditionType, $fromDate, $toDate, $threshold);

        $studentIds = array_column($triggeringStudents, 'student_id');
        $students = Student::with(['level', 'supervisor'])
            ->whereIn('id', $studentIds)
            ->get()
            ->keyBy('id');

        $results = collect($triggeringStudents)->map(function ($item) use ($students) {
            $student = $students->get($item['student_id']);
            return [
                'student_id' => $item['student_id'],
                'student_name' => $student?->name ?? '-',
                'level' => $student?->level?->name ?? '-',
                'supervisor' => $student?->supervisor?->name ?? '-',
                'count' => $item['count'],
            ];
        });

        return view('admin.warning-rules.test-result', [
            'results' => $results,
            'conditionType' => $conditionType,
            'threshold' => $threshold,
            'periodDays' => $request->period_days,
            'type' => $request->type,
        ]);
    }

    protected function getTriggeringStudents(string $conditionType, Carbon $fromDate, Carbon $toDate, int $threshold): array
    {
        if ($conditionType === 'absent_days') {
            return Attendance::select('student_id', DB::raw('COUNT(*) as count'))
                ->whereBetween('date', [$fromDate->toDateString(), $toDate->toDateString()])
                ->where('status', 'absent')
                ->groupBy('student_id')
                ->having('count', '>=', $threshold)
                ->get()->map(fn($r) => ['student_id' => $r->student_id, 'count' => (int) $r->count])->toArray();
        }
        if ($conditionType === 'late_days') {
            return Attendance::select('student_id', DB::raw('COUNT(*) as count'))
                ->whereBetween('date', [$fromDate->toDateString(), $toDate->toDateString()])
                ->where('status', 'late')
                ->groupBy('student_id')
                ->having('count', '>=', $threshold)
                ->get()->map(fn($r) => ['student_id' => $r->student_id, 'count' => (int) $r->count])->toArray();
        }
        if ($conditionType === 'failed_sessions') {
            return RecitationSession::select('student_id', DB::raw('COUNT(*) as count'))
                ->whereBetween('session_date', [$fromDate->toDateString(), $toDate->toDateString()])
                ->where('status', 'failed')
                ->groupBy('student_id')
                ->having('count', '>=', $threshold)
                ->get()->map(fn($r) => ['student_id' => $r->student_id, 'count' => (int) $r->count])->toArray();
        }
        if ($conditionType === 'not_recitate_count') {
            return RecitationSession::select('student_id', DB::raw('COUNT(*) as count'))
                ->whereBetween('session_date', [$fromDate->toDateString(), $toDate->toDateString()])
                ->where('status', 'not_recitate')
                ->groupBy('student_id')
                ->having('count', '>=', $threshold)
                ->get()->map(fn($r) => ['student_id' => $r->student_id, 'count' => (int) $r->count])->toArray();
        }
        return [];
    }

    protected function validateStore(Request $request, ?int $excludeId = null): void
    {
        $rules = [
            'name' => 'required|string|max:255',
            'type' => 'required|in:' . implode(',', WarningRule::TYPES),
            'condition_type' => 'required|in:' . implode(',', WarningRule::CONDITION_TYPES),
            'threshold' => 'required|integer|min:1',
            'period_days' => 'nullable|integer|min:1',
            'level_ids' => 'nullable|array',
            'level_ids.*' => 'exists:levels,id',
        ];

        Validator::make($request->all(), $rules)->validate();
    }
}
```

**Step 2: Verify controller compiles**

Run: `php artisan tinker --execute="echo class_exists(App\Http\Controllers\Admin\WarningRuleController::class) ? 'OK' : 'FAIL';"`
Expected: `OK`

**Step 3: Commit**

```bash
git add app/Http/Controllers/Admin/WarningRuleController.php
git commit -m "feat(warnings): add Admin WarningRuleController

Co-Authored-By: Claude Opus 4.6 <noreply@anthropic.com>"
```

---

## Task 10: Admin Warning Rules Views

**File:** Create: `resources/views/admin/warning-rules/index.blade.php`

```blade
@extends('layouts.app')

@section('title', 'قواعد الإنذار')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">قواعد الإنذار</h3>
                    <a href="{{ route('admin.warning-rules.create') }}" class="btn btn-primary float-left">
                        <i class="fas fa-plus"></i> إضافة قاعدة جديدة
                    </a>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>الاسم</th>
                                <th>النوع</th>
                                <th>الشرط</th>
                                <th>الحد</th>
                                <th>الفترة</th>
                                <th>الحالة</th>
                                <th>إجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($rules as $rule)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $rule->name }}</td>
                                <td>
                                    @php
                                        $typeLabels = [
                                            'attendance' => '<span class="badge badge-warning">حضور</span>',
                                            'recitation' => '<span class="badge badge-danger">تقييم</span>',
                                            'general' => '<span class="badge badge-info">عام</span>',
                                        ];
                                    @endphp
                                    {!! $typeLabels[$rule->type] ?? $rule->type !!}
                                </td>
                                <td>{{ $rule->condition_type }}</td>
                                <td>{{ $rule->threshold }}</td>
                                <td>{{ $rule->period_days }} يوم</td>
                                <td>
                                    @if($rule->is_active)
                                        <span class="badge badge-success">مفعّل</span>
                                    @else
                                        <span class="badge badge-secondary">معطّل</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.warning-rules.edit', $rule->id) }}"
                                       class="btn btn-sm btn-secondary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.warning-rules.toggle', $rule->id) }}" method="POST"
                                          class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-{{ $rule->is_active ? 'warning' : 'success' }}">
                                            <i class="fas fa-{{ $rule->is_active ? 'pause' : 'play' }}"></i>
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.warning-rules.destroy', $rule->id) }}" method="POST"
                                          class="d-inline" onsubmit="return confirm('هل أنت متأكد؟')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center">لا توجد قواعد</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">
                    {{ $rules->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
```

**File:** Create: `resources/views/admin/warning-rules/form.blade.php`

```blade
@extends('layouts.app')

@section('title', $rule ? 'تعديل قاعدة' : 'إضافة قاعدة جديدة')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">{{ $rule ? 'تعديل قاعدة' : 'إضافة قاعدة جديدة' }}</h3>
                </div>
                <form method="POST"
                      action="{{ $rule ? route('admin.warning-rules.update', $rule->id) : route('admin.warning-rules.store') }}">
                    @csrf
                    @if($rule)
                        @method('PUT')
                    @endif

                    <div class="card-body">
                        <div class="form-group">
                            <label>اسم القاعدة</label>
                            <input type="text" name="name" class="form-control"
                                   value="{{ old('name', $rule->name ?? '') }}" required>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>نوع الإنذار</label>
                                    <select name="type" class="form-control" required>
                                        <option value="attendance" @selected(old('type', $rule->type ?? '') === 'attendance')>
                                            حضور
                                        </option>
                                        <option value="recitation" @selected(old('type', $rule->type ?? '') === 'recitation')>
                                            تقييم حفظ
                                        </option>
                                        <option value="general" @selected(old('type', $rule->type ?? '') === 'general')>
                                            عام
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>نوع الشرط</label>
                                    <select name="condition_type" class="form-control" required>
                                        <option value="absent_days" @selected(old('condition_type', $rule->condition_type ?? '') === 'absent_days')>
                                            أيام الغياب
                                        </option>
                                        <option value="late_days" @selected(old('condition_type', $rule->condition_type ?? '') === 'late_days')>
                                            أيام التأخير
                                        </option>
                                        <option value="failed_sessions" @selected(old('condition_type', $rule->condition_type ?? '') === 'failed_sessions')>
                                            حصص الرسوب
                                        </option>
                                        <option value="not_recitate_count" @selected(old('condition_type', $rule->condition_type ?? '') === 'not_recitate_count')>
                                            حصص لم يسمع
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>الحد (الحد الأدنى للمحفّز)</label>
                                    <input type="number" name="threshold" class="form-control" min="1"
                                           value="{{ old('threshold', $rule->threshold ?? 3) }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>الفترة (بالأيام)</label>
                                    <input type="number" name="period_days" class="form-control" min="1"
                                           value="{{ old('period_days', $rule->period_days ?? 30) }}">
                                    <small class="form-text text-muted">عدد الأيام للرجوع إليها</small>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>الصفوف (اختياري)</label>
                            <select name="level_ids[]" class="form-control" multiple>
                                @foreach($levels ?? [] as $level)
                                    <option value="{{ $level->id }}"
                                        @selected(in_array($level->id, old('level_ids', $rule->level_ids ?? [])))>
                                        {{ $level->name }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">اتركها فارغة لتطبيق القاعدة على جميع الصفوف</small>
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" name="is_active" class="custom-control-input"
                                       id="is_active" value="1"
                                       @checked(old('is_active', $rule->is_active ?? true))>
                                <label class="custom-control-label" for="is_active">مفعّل</label>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> حفظ
                        </button>
                        <a href="{{ route('admin.warning-rules.index') }}" class="btn btn-secondary">إلغاء</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
```

**File:** Create: `resources/views/admin/warning-rules/test-result.blade.php`

```blade
@extends('layouts.app')

@section('title', 'اختبار القاعدة')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">نتيجة اختبار القاعدة</h3>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <strong>نوع الشرط:</strong> {{ $conditionType }} |
                        <strong>الحد:</strong> {{ $threshold }} |
                        <strong>الفترة:</strong> {{ $periodDays }} يوم
                    </div>

                    @if($results->isEmpty())
                        <div class="alert alert-success">
                            لا يوجد طلاب يطابقون هذا الشرط حالياً.
                        </div>
                    @else
                        <div class="alert alert-warning">
                            <strong>{{ $results->count() }} طالب</strong> يطابقون هذا الشرط
                        </div>
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>اسم الطالب</th>
                                    <th>الصف</th>
                                    <th>المشرف</th>
                                    <th>العدد</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($results as $result)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $result['student_name'] }}</td>
                                    <td>{{ $result['level'] }}</td>
                                    <td>{{ $result['supervisor'] }}</td>
                                    <td><span class="badge badge-danger">{{ $result['count'] }}</span></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif

                    <a href="{{ route('admin.warning-rules.index') }}" class="btn btn-secondary mt-3">
                        <i class="fas fa-arrow-right"></i> العودة
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
```

Also create `create.blade.php` and `edit.blade.php` as wrappers that extend `form.blade.php`:
- `create.blade.php`: `@extends('layouts.app') @section('content') @include('admin.warning-rules.form') @endsection`
- `edit.blade.php`: `@extends('layouts.app') @section('content') @include('admin.warning-rules.form') @endsection`

**Step 2: Verify views exist**

Run: `php artisan view:exists admin.warning-rules.index 2>&1 || echo "CHECK INDIVIDUAL"; ls resources/views/admin/warning-rules/`
Expected: Files listed

**Step 3: Commit**

```bash
git add resources/views/admin/warning-rules/
git commit -m "feat(warnings): add admin warning rules views

Co-Authored-By: Claude Opus 4.6 <noreply@anthropic.com>"
```

---

## Task 11: Routes

**File:** Modify: `routes/web.php`

Add to existing route files. Find the `supervisor` middleware group and `admin` middleware group, add:

```php
// Supervisor Warning Drafts (add to existing supervisor group)
// Make sure this is added WITHIN the existing Route::middleware(['auth', 'role:supervisor'])->prefix('supervisor')->group(function () { ... });

Route::get('warning-drafts', [App\Http\Controllers\Supervisor\WarningDraftController::class, 'index'])->name('supervisor.warning-drafts.index');
Route::get('warning-drafts/{id}', [App\Http\Controllers\Supervisor\WarningDraftController::class, 'show'])->name('supervisor.warning-drafts.show');
Route::post('warning-drafts/{id}/approve', [App\Http\Controllers\Supervisor\WarningDraftController::class, 'approve'])->name('supervisor.warning-drafts.approve');
Route::post('warning-drafts/{id}/dismiss', [App\Http\Controllers\Supervisor\WarningDraftController::class, 'dismiss'])->name('supervisor.warning-drafts.dismiss');

// Admin Warning Rules (add to existing admin group)
// Make sure this is added WITHIN the existing Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () { ... });

Route::resource('warning-rules', App\Http\Controllers\Admin\WarningRuleController::class);
Route::post('warning-rules/{id}/toggle', [App\Http\Controllers\Admin\WarningRuleController::class, 'toggle'])->name('warning-rules.toggle');
Route::post('warning-rules/test', [App\Http\Controllers\Admin\WarningRuleController::class, 'test'])->name('warning-rules.test');
```

**Step 2: Verify routes**

Run: `php artisan route:list --path=warning`
Expected: All warning-drafts and warning-rules routes listed

**Step 3: Commit**

```bash
git add routes/web.php
git commit -m "feat(warnings): add warning-drafts and warning-rules routes

Co-Authored-By: Claude Opus 4.6 <noreply@anthropic.com>"
```

---

## Task 12: Sidebar Navigation

**File:** Modify: `resources/views/layouts/sidebar.blade.php` (or whichever sidebar partial is used)

Add menu items in the appropriate sections:

**For Admin sidebar** (in the settings/system section):
```blade
<li class="nav-item">
    <a href="{{ route('admin.warning-rules.index') }}"
       class="nav-link {{ request()->routeIs('admin.warning-rules.*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-exclamation-triangle"></i>
        <p>قواعد الإنذار</p>
    </a>
</li>
```

**For Supervisor sidebar** (near existing warnings link):
```blade
<li class="nav-item">
    <a href="{{ route('supervisor.warning-drafts.index') }}"
       class="nav-link {{ request()->routeIs('supervisor.warning-drafts.*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-edit"></i>
        <p>مسودات الإنذارات</p>
        @php
            $draftCount = \App\Models\WarningDraft::where('supervisor_id', auth()->id())->where('status', 'pending')->count();
        @endphp
        @if($draftCount > 0)
            <span class="badge badge-warning">{{ $draftCount }}</span>
        @endif
    </a>
</li>
```

**Step 3: Commit**

```bash
git add resources/views/layouts/sidebar.blade.php
git commit -m "feat(warnings): add warning rules and drafts to sidebar navigation

Co-Authored-By: Claude Opus 4.6 <noreply@anthropic.com>"
```

---

## Task 13: Verify End-to-End

**Step 1: Run all migrations fresh**

Run: `php artisan migrate:fresh`
Expected: All migrations run successfully including new tables

**Step 2: Seed test data**

Create a student with 4 absent attendance records in the last 20 days via tinker:
```php
php artisan tinker
```
```php
$student = App\Models\Student::first();
$fromDate = now()->subDays(20);
for ($i = 1; $i <= 4; $i++) {
    App\Models\Attendance::create([
        'student_id' => $student->id,
        'date' => $fromDate->copy()->addDays($i * 3)->toDateString(),
        'day' => 'السبت',
        'status' => 'absent',
        'created_by' => $student->supervisor_id,
    ]);
}
echo "Done";
```

**Step 3: Create a rule via UI**

Navigate: Admin → قواعد الإنذار → إضافة قاعدة جديدة
- Name: "3+ absent days"
- Type: attendance
- Condition: absent_days
- Threshold: 3
- Period: 20 days
- Active: yes

**Step 4: Run evaluation**

Run: `php artisan warning:evaluate-rules`
Expected: "Created 1 draft(s)"

**Step 5: Check draft appears**

Navigate: Supervisor → مسودات الإنذارات
Expected: See the student from step 2 in the draft list

**Step 6: Approve and verify warning created**

Click "مراجعة" → "اعتماد وإصدار الإنذار"
Expected: Redirected to warning detail page, warning contains attendance data

**Step 7: Verify no duplicate draft**

Run: `php artisan warning:evaluate-rules`
Expected: "Created 0 draft(s)"
