<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\ImanCriterion;
use App\Models\ImanDailyEntry;
use App\Models\ImanProgram;
use App\Models\Student;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ImanProgramController extends Controller
{
    public function index(Request $request)
    {
        $supervisorId = auth()->id();
        $scope = $request->get('scope', 'self');
        $studentFilter = $request->get('student_id');

        $student = null;
        $owner = null;

        if ($scope === 'student') {
            if ($studentFilter) {
                $student = Student::with('user')
                    ->where('id', $studentFilter)
                    ->where('supervisor_id', $supervisorId)
                    ->first();
            }
            if (!$student) {
                $owner = null;
            } else {
                $owner = $student;
            }
        } else {
            $scope = 'self';
            $owner = User::find($supervisorId);
        }

        $weekStart = $this->resolveWeekStart($request);
        $weekEnd = $weekStart->copy()->addDays(6);

        $students = Student::with('user')
            ->where('supervisor_id', $supervisorId)
            ->orderBy('id')
            ->get();

        $programs = collect();
        $entries = collect();

        if ($owner) {
            $programs = ImanProgram::with(['criteria', 'creator'])
                ->where('owner_id', $owner->getKey())
                ->where('owner_type', $owner->getMorphClass())
                ->orderByDesc('is_active')
                ->orderByDesc('id')
                ->get();

            $entries = ImanDailyEntry::whereIn('iman_program_id', $programs->pluck('id'))
                ->whereBetween('entry_date', [$weekStart->toDateString(), $weekEnd->toDateString()])
                ->get()
                ->groupBy(fn ($e) => $e->iman_program_id . '|' . $e->iman_criterion_id . '|' . $e->entry_date->toDateString());
        }

        $weekDates = [];
        for ($i = 0; $i < 7; $i++) {
            $weekDates[] = $weekStart->copy()->addDays($i);
        }

        $stats = [
            'programs_count' => $programs->count(),
            'criteria_count' => $programs->sum(fn ($p) => $p->criteria->count()),
            'students_count' => $students->count(),
            'week_completed' => 0,
            'week_total' => 0,
        ];
        foreach ($programs as $program) {
            foreach ($program->criteria as $criterion) {
                foreach ($weekDates as $date) {
                    $key = $program->id . '|' . $criterion->id . '|' . $date->toDateString();
                    $entry = $entries->get($key)?->first();
                    $stats['week_total']++;
                    if ($this->isEntryDone($criterion, $entry)) {
                        $stats['week_completed']++;
                    }
                }
            }
        }

        return view('supervisor.iman-program.index', [
            'students' => $students,
            'student' => $student,
            'studentFilter' => $studentFilter,
            'scope' => $scope,
            'programs' => $programs,
            'entries' => $entries,
            'weekDates' => $weekDates,
            'weekStart' => $weekStart,
            'weekEnd' => $weekEnd,
            'stats' => $stats,
        ]);
    }

    public function report(Request $request, \App\Services\StudentImanReportService $reports)
    {
        $supervisorId = auth()->id();
        $routeName = optional($request->route())->getName();
        $type = match (true) {
            $routeName === 'supervisor.iman-program.reports.self' => 'self',
            $routeName === 'supervisor.iman-program.reports.students' => 'students',
            default => $request->get('type', 'students'),
        };
        if (! in_array($type, ['students', 'self'], true)) {
            $type = 'students';
        }

        $weekStart = $this->resolveWeekStart($request);
        $weekEnd = $weekStart->copy()->addDays(6);

        if ($type === 'self') {
            $owner = User::find($supervisorId);
            $programs = ImanProgram::with(['criteria', 'creator'])
                ->where('owner_id', $owner->getKey())
                ->where('owner_type', $owner->getMorphClass())
                ->orderByDesc('is_active')
                ->orderByDesc('id')
                ->get();

            $rows = $this->buildReportRows($programs, $weekStart, $weekEnd);

            return view('supervisor.iman-program.report', [
                'type' => 'self',
                'title' => 'تقرير برنامجي الإيماني',
                'subtitle' => 'ملخص التزامك بمعايير برنامجك الإيماني خلال الأسبوع المحدد',
                'rows' => $rows,
                'totals' => $this->summarizeRows($rows),
                'weekStart' => $weekStart,
                'weekEnd' => $weekEnd,
                'studentLabel' => null,
                'students' => collect(),
                'selectedStudent' => null,
                'reportData' => null,
            ]);
        }

        $students = Student::with('user')
            ->where('supervisor_id', $supervisorId)
            ->orderBy('id')
            ->get();

        $selectedStudentId = $request->get('student_id');
        $selectedStudent = null;
        $reportData = null;
        $formAction = route('supervisor.iman-program.reports.students');

        if ($selectedStudentId && $students->contains('id', (int) $selectedStudentId)) {
            $selectedStudent = $students->firstWhere('id', (int) $selectedStudentId);
            $reportData = $reports->build($selectedStudent, $request);
        }

        $studentMorph = (new Student)->getMorphClass();
        $programs = ImanProgram::with(['criteria', 'creator', 'owner'])
            ->whereIn('owner_id', $students->pluck('id'))
            ->where('owner_type', $studentMorph)
            ->orderByDesc('is_active')
            ->orderByDesc('id')
            ->get()
            ->groupBy('owner_id');

        $rows = [];
        foreach ($students as $student) {
            $studentPrograms = $programs->get($student->id, collect());
            $rows[] = [
                'student' => $student,
                'programs' => $studentPrograms,
                'report' => $this->buildReportRows($studentPrograms, $weekStart, $weekEnd),
            ];
        }

        $allRows = collect($rows)->pluck('report')->flatten(1);
        $totals = $this->summarizeRows($allRows);

        return view('supervisor.iman-program.report', [
            'type' => 'students',
            'title' => 'تقرير الطلاب',
            'subtitle' => 'ملخص التزام الطلاب بمعايير البرامج الإيمانية خلال الأسبوع المحدد',
            'rows' => $rows,
            'totals' => $totals,
            'weekStart' => $weekStart,
            'weekEnd' => $weekEnd,
            'studentLabel' => fn ($student) => $student->user->name ?? '—',
            'students' => $students,
            'selectedStudent' => $selectedStudent,
            'reportData' => $reportData,
            'formAction' => $formAction,
        ]);
    }

    protected function buildReportRows($programs, Carbon $weekStart, Carbon $weekEnd): array
    {
        $weekDates = [];
        for ($i = 0; $i < 7; $i++) {
            $weekDates[] = $weekStart->copy()->addDays($i);
        }

        if ($programs->isEmpty()) {
            return [];
        }

        $entries = ImanDailyEntry::whereIn('iman_program_id', $programs->pluck('id'))
            ->whereBetween('entry_date', [$weekStart->toDateString(), $weekEnd->toDateString()])
            ->get()
            ->groupBy(fn ($e) => $e->iman_program_id . '|' . $e->iman_criterion_id . '|' . $e->entry_date->toDateString());

        $rows = [];
        foreach ($programs as $program) {
            $programTotal = 0;
            $programDone = 0;
            $criteriaRows = [];

            foreach ($program->criteria as $criterion) {
                $criterionTotal = 7;
                $criterionDone = 0;
                $criterionValues = [];
                foreach ($weekDates as $date) {
                    $key = $program->id . '|' . $criterion->id . '|' . $date->toDateString();
                    $entry = $entries->get($key)?->first();
                    if ($this->isEntryDone($criterion, $entry)) {
                        $criterionDone++;
                    }
                    $criterionValues[] = [
                        'date' => $date,
                        'entry' => $entry,
                    ];
                }
                $programTotal += $criterionTotal;
                $programDone += $criterionDone;
                $criteriaRows[] = [
                    'criterion' => $criterion,
                    'total' => $criterionTotal,
                    'done' => $criterionDone,
                    'percent' => $criterionTotal > 0 ? round(($criterionDone / $criterionTotal) * 100) : 0,
                    'values' => $criterionValues,
                ];
            }

            $rows[] = [
                'program' => $program,
                'criteria' => $criteriaRows,
                'total' => $programTotal,
                'done' => $programDone,
                'percent' => $programTotal > 0 ? round(($programDone / $programTotal) * 100) : 0,
            ];
        }

        return $rows;
    }

    protected function summarizeRows(iterable $rows): array
    {
        $total = 0;
        $done = 0;
        foreach ($rows as $row) {
            $total += $row['total'] ?? 0;
            $done += $row['done'] ?? 0;
        }
        return [
            'total' => $total,
            'done' => $done,
            'percent' => $total > 0 ? round(($done / $total) * 100) : 0,
        ];
    }

    public function create(Request $request)
    {
        $owner = $this->resolveOwner($request);

        $student = $owner instanceof Student ? $owner : null;

        return view('supervisor.iman-program.form', [
            'program' => null,
            'criteria' => collect(),
            'owner' => $owner,
            'student' => $student,
            'students' => $this->supervisedStudents(),
            'scope' => $student ? 'student' : 'self',
        ]);
    }

    public function store(Request $request)
    {
        $owner = $this->resolveOwner($request);
        $data = $this->validateProgram($request);

        $program = DB::transaction(function () use ($owner, $data) {
            $program = ImanProgram::create([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'created_by' => auth()->id(),
                'owner_id' => $owner->getKey(),
                'owner_type' => $owner->getMorphClass(),
                'is_active' => true,
                'start_date' => $data['start_date'] ?? null,
            ]);

            $this->syncCriteria($program, $data['criteria'] ?? []);

            return $program;
        });

        return $this->redirectAfterSave($owner, null)
            ->with('success', 'تم إنشاء البرنامج الإيماني بنجاح');
    }

    public function edit(Request $request, ImanProgram $program)
    {
        $this->authorizeAccess($program);

        $program->load('criteria');
        $owner = $program->owner;
        $student = $owner instanceof Student ? $owner : null;

        return view('supervisor.iman-program.form', [
            'program' => $program,
            'criteria' => $program->criteria,
            'owner' => $owner,
            'student' => $student,
            'students' => $this->supervisedStudents(),
            'scope' => $student ? 'student' : 'self',
        ]);
    }

    public function manageStudentCriteria(Request $request, ImanProgram $program)
    {
        $this->authorizeAccess($program);
        $program->load('criteria');
        $owner = $program->owner;
        $student = $owner instanceof Student ? $owner : null;

        if (! $student) {
            abort(404);
        }

        $oldCriteria = old('criteria');
        $items = $oldCriteria !== null
            ? collect($oldCriteria)
            : ($program->criteria->isNotEmpty() ? $program->criteria : collect([['name' => '', 'type' => 'check', 'target_value' => null]]));

        return view('supervisor.iman-program.manage-student-criteria', [
            'program' => $program,
            'student' => $student,
            'items' => $items,
        ]);
    }

    public function updateStudentCriteria(Request $request, ImanProgram $program)
    {
        $this->authorizeAccess($program);
        $owner = $program->owner;
        if (! ($owner instanceof Student)) {
            abort(404);
        }

        $data = $request->validate([
            'criteria' => 'nullable|array',
            'criteria.*.id' => 'nullable|integer',
            'criteria.*.name' => 'required_with:criteria.*|string|max:255',
            'criteria.*.type' => 'required_with:criteria.*|in:check,number',
            'criteria.*.target_value' => 'nullable|integer|min:0',
        ]);

        $this->syncCriteria($program, $data['criteria'] ?? []);

        return redirect()->route('supervisor.iman-program.index', [
            'scope' => 'student',
            'student_id' => $studentId = $owner->id,
        ])->with('success', 'تم تحديث معايير برنامج الطالب بنجاح');
    }

    public function update(Request $request, ImanProgram $program)
    {
        $this->authorizeAccess($program);
        $data = $this->validateProgram($request);

        DB::transaction(function () use ($program, $request, $data) {
            $program->update([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'is_active' => $request->boolean('is_active', true),
                'start_date' => $data['start_date'] ?? null,
            ]);
            $this->syncCriteria($program, $data['criteria'] ?? []);
        });

        return $this->redirectAfterSave($program->owner, $program)
            ->with('success', 'تم تحديث البرنامج الإيماني بنجاح');
    }

    public function destroy(ImanProgram $program)
    {
        $this->authorizeAccess($program);
        $owner = $program->owner;
        $program->delete();

        return $this->redirectAfterSave($owner, null)
            ->with('success', 'تم حذف البرنامج الإيماني');
    }

    public function toggleEntry(Request $request, ImanProgram $program)
    {
        $this->authorizeAccess($program);

        $data = $request->validate([
            'criterion_id' => 'required|exists:iman_criteria,id',
            'date' => 'required|date',
            'is_checked' => 'sometimes|boolean',
            'value' => 'sometimes|nullable|integer|min:0',
        ]);

        $criterion = ImanCriterion::where('iman_program_id', $program->id)
            ->where('id', $data['criterion_id'])
            ->firstOrFail();

        $entry = ImanDailyEntry::firstOrNew([
            'iman_program_id' => $program->id,
            'iman_criterion_id' => $criterion->id,
            'entry_date' => $data['date'],
        ]);

        if ($criterion->isCheckType()) {
            $entry->is_checked = $request->boolean('is_checked');
            $entry->value = null;
        } else {
            $value = (int) ($data['value'] ?? 0);
            $maxTarget = (int) $criterion->target_value;
            if ($maxTarget > 0 && $value > $maxTarget) {
                return response()->json([
                    'success' => false,
                    'message' => 'القيمة تتجاوز الهدف المحدد (' . $maxTarget . ')',
                ], 422);
            }
            $entry->value = $value;
            $entry->is_checked = false;
        }
        $entry->save();

        return response()->json([
            'success' => true,
            'entry' => [
                'is_checked' => (bool) $entry->is_checked,
                'value' => $entry->value,
            ],
        ]);
    }

    public function bulkSave(Request $request, ImanProgram $program)
    {
        $this->authorizeAccess($program);

        $data = $request->validate([
            'date' => 'required|date',
            'entries' => 'required|array',
            'entries.*.criterion_id' => 'required|exists:iman_criteria,id',
            'entries.*.is_checked' => 'sometimes|boolean',
            'entries.*.value' => 'sometimes|nullable|integer|min:0',
        ]);

        DB::transaction(function () use ($program, $data) {
            foreach ($data['entries'] as $row) {
                $criterion = ImanCriterion::where('iman_program_id', $program->id)
                    ->where('id', $row['criterion_id'])
                    ->first();
                if (! $criterion) {
                    continue;
                }

                $entry = ImanDailyEntry::firstOrNew([
                    'iman_program_id' => $program->id,
                    'iman_criterion_id' => $criterion->id,
                    'entry_date' => $data['date'],
                ]);

                if ($criterion->isCheckType()) {
                    $entry->is_checked = ! empty($row['is_checked']);
                    $entry->value = null;
                } else {
                    $entry->value = $row['value'] ?? 0;
                    $entry->is_checked = false;
                }
                $entry->save();
            }
        });

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'تم حفظ الإدخالات بنجاح']);
        }

        return redirect()->back()->with('success', 'تم حفظ الإدخالات بنجاح');
    }

    protected function authorizeAccess(ImanProgram $program): void
    {
        $supervisorId = auth()->id();
        $owner = $program->owner;

        $isMyStudent = $owner instanceof Student && $owner->supervisor_id === $supervisorId;
        $isMine = $owner instanceof User && $owner->getKey() === $supervisorId;
        $isCreator = $program->created_by === $supervisorId;

        if (! $isMyStudent && ! $isMine && ! $isCreator) {
            abort(403);
        }
    }

    protected function resolveOwner(Request $request)
    {
        $scope = $request->get('scope', 'self');
        $studentId = $request->get('student_id');

        if ($scope === 'student' && $studentId) {
            $student = Student::where('id', $studentId)
                ->where('supervisor_id', auth()->id())
                ->first();
            if (! $student) {
                abort(404, 'الطالب غير موجود أو ليس من طلابك');
            }
            return $student;
        }

        return User::findOrFail(auth()->id());
    }

    protected function redirectAfterSave($owner, $program = null)
    {
        if ($owner instanceof Student) {
            return redirect()->route('supervisor.iman-program.index', [
                'scope' => 'student',
                'student_id' => $owner->id,
            ]);
        }

        return redirect()->route('supervisor.iman-program.index', ['scope' => 'self']);
    }

    protected function supervisedStudents()
    {
        return Student::with('user')
            ->where('supervisor_id', auth()->id())
            ->orderBy('id')
            ->get();
    }

    protected function validateProgram(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'start_date' => 'nullable|date',
            'criteria' => 'nullable|array',
            'criteria.*.id' => 'nullable|integer',
            'criteria.*.name' => 'required_with:criteria.*|string|max:255',
            'criteria.*.type' => 'required_with:criteria.*|in:check,number',
            'criteria.*.target_value' => 'nullable|integer|min:0',
        ]);
    }

    protected function syncCriteria(ImanProgram $program, array $criteria): void
    {
        $incomingIds = collect($criteria)->pluck('id')->map(fn($v) => (int) $v)->filter()->all();
        $program->criteria()->whereNotIn('id', $incomingIds)->whereDoesntHave('dailyEntries')->delete();

        foreach ($criteria as $index => $row) {
            $payload = [
                'name' => $row['name'],
                'type' => $row['type'] ?? 'check',
                'target_value' => ($row['type'] ?? 'check') === 'number' ? ($row['target_value'] ?? null) : null,
                'sort_order' => $index,
            ];

            $rowId = ! empty($row['id']) ? (int) $row['id'] : null;
            if ($rowId) {
                $criterion = $program->criteria()->where('id', $rowId)->first();
                if ($criterion) {
                    $criterion->update($payload);
                    continue;
                }
            }

            $program->criteria()->create($payload);
        }
    }

    protected function resolveWeekStart(Request $request): Carbon
    {
        $param = $request->get('week');
        if ($param) {
            try {
                $date = Carbon::parse($param);
            } catch (\Throwable $e) {
                $date = Carbon::now();
            }
        } else {
            $date = Carbon::now();
        }

        return $date->startOfWeek(Carbon::SATURDAY);
    }

    protected function isEntryDone(ImanCriterion $criterion, $entry): bool
    {
        if (! $entry) {
            return false;
        }
        if ($criterion->isCheckType()) {
            return (bool) $entry->is_checked;
        }
        return (int) $entry->value >= (int) ($criterion->target_value ?? 0) && (int) $criterion->target_value > 0;
    }

    public function studentReport(Request $request, Student $student, \App\Services\StudentImanReportService $reports)
    {
        $supervisorId = auth()->id();
        abort_unless($student->supervisor_id === $supervisorId, 403, 'هذا الطالب ليس من طلابك.');

        $data = $reports->build($student, $request);

        return view('supervisor.iman-program.student-report', [
            'data' => $data,
            'student' => $student,
            'formAction' => route('supervisor.iman-program.student-report', $student->id),
            'compareAction' => route('supervisor.iman-program.student-report', $student->id),
            'criteriaAction' => route('supervisor.iman-program.student-report', $student->id),
            'backUrl' => route('supervisor.iman-program.index', ['scope' => 'student', 'student_id' => $student->id]),
            'headerTitle' => 'تقرير الطالب: ' . ($student->user->name ?? ''),
        ]);
    }
}
