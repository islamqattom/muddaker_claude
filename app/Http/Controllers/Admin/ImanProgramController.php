<?php

namespace App\Http\Controllers\Admin;

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
        $scope = $request->get('scope', 'self');
        $studentFilter = $request->get('student_id');

        $student = null;
        $owner = null;

        if ($scope === 'student' && $studentFilter) {
            $student = Student::with('user')->find($studentFilter);
            $owner = $student;
        } elseif ($scope === 'student') {
            $owner = null;
        } elseif ($scope === 'reports') {
            $owner = null;
        } else {
            $scope = 'self';
            $owner = User::find(auth()->id());
        }

        $weekStart = $this->resolveWeekStart($request);
        $weekEnd = $weekStart->copy()->addDays(6);

        $students = Student::with('user')
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
            'total_programs' => ImanProgram::count(),
            'total_criteria' => ImanCriterion::count(),
            'total_entries' => ImanDailyEntry::count(),
            'students_with_programs' => ImanProgram::where('owner_type', (new Student)->getMorphClass())->distinct('owner_id')->count('owner_id'),
        ];

        $studentReports = [];
        $aggregateDailyPoints = [];
        if ($scope === 'reports') {
            $studentMorph = (new Student)->getMorphClass();
            $weekStart = Carbon::today()->subDays(6);
            $weekEnd = Carbon::today();
            $studentReports = Student::with(['user', 'supervisor'])
                ->whereHas('imanPrograms')
                ->get()
                ->map(function ($s) use ($studentMorph, $weekStart, $weekEnd) {
                    $programs = ImanProgram::with('criteria')
                        ->where('owner_id', $s->id)
                        ->where('owner_type', $studentMorph)
                        ->get();

                    $programIds = $programs->pluck('id');
                    $criteriaCount = $programs->sum(fn ($p) => $p->criteria->count());
                    $entriesCount = ImanDailyEntry::whereIn('iman_program_id', $programIds)->count();
                    $weeklyEntriesCount = ImanDailyEntry::whereIn('iman_program_id', $programIds)
                        ->whereBetween('entry_date', [$weekStart->toDateString(), $weekEnd->toDateString()])
                        ->count();
                    $lastEntry = ImanDailyEntry::whereIn('iman_program_id', $programIds)
                        ->orderByDesc('entry_date')
                        ->first();

                    $completion = 0;
                    if ($criteriaCount > 0) {
                        $weeklyCompleted = ImanDailyEntry::whereIn('iman_program_id', $programIds)
                            ->whereBetween('entry_date', [$weekStart->toDateString(), $weekEnd->toDateString()])
                            ->where(function ($q) {
                                $q->where('is_checked', true)->orWhere('value', '>', 0);
                            })
                            ->count();
                        $totalPossible = $criteriaCount * 7;
                        $completion = $totalPossible > 0 ? min(100, round(($weeklyCompleted / $totalPossible) * 100)) : 0;
                    }

                    return (object) [
                        'student' => $s,
                        'programs_count' => $programs->count(),
                        'criteria_count' => $criteriaCount,
                        'entries_count' => $entriesCount,
                        'weekly_entries_count' => $weeklyEntriesCount,
                        'last_entry_date' => $lastEntry?->entry_date,
                        'completion' => $completion,
                    ];
                })
                ->sortByDesc('weekly_entries_count')
                ->values();

            $allProgramIds = ImanProgram::where('owner_type', $studentMorph)->pluck('id');
            if ($allProgramIds->isNotEmpty()) {
                $start = Carbon::today()->subDays(29);
                $end = Carbon::today();
                $rows = ImanDailyEntry::whereIn('iman_program_id', $allProgramIds)
                    ->whereBetween('entry_date', [$start->toDateString(), $end->toDateString()])
                    ->get(['entry_date', 'is_checked', 'value']);

                $bucket = [];
                foreach ($rows as $r) {
                    $d = $r->entry_date->toDateString();
                    if (! isset($bucket[$d])) {
                        $bucket[$d] = 0;
                    }
                    if ($r->is_checked || (int) ($r->value ?? 0) > 0) {
                        $bucket[$d]++;
                    }
                }

                $cursor = $start->copy();
                while ($cursor->lte($end)) {
                    $key = $cursor->toDateString();
                    $aggregateDailyPoints[] = [
                        'date' => $key,
                        'label' => $cursor->format('m/d'),
                        'points' => $bucket[$key] ?? 0,
                    ];
                    $cursor->addDay();
                }
            }
        }

        return view('admin.iman-program.index', [
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
            'studentReports' => $studentReports,
            'aggregateDailyPoints' => $aggregateDailyPoints,
        ]);
    }

    public function create(Request $request)
    {
        $owner = $this->resolveOwner($request);
        $student = $owner instanceof Student ? $owner : null;

        return view('admin.iman-program.form', [
            'program' => null,
            'criteria' => collect(),
            'owner' => $owner,
            'student' => $student,
            'students' => Student::with('user')->orderBy('id')->get(),
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

        return $this->redirectAfterSave($owner)
            ->with('success', 'تم إنشاء البرنامج الإيماني بنجاح');
    }

    public function edit(ImanProgram $program)
    {
        $program->load('criteria');
        $owner = $program->owner;
        $student = $owner instanceof Student ? $owner : null;

        return view('admin.iman-program.form', [
            'program' => $program,
            'criteria' => $program->criteria,
            'owner' => $owner,
            'student' => $student,
            'students' => Student::with('user')->orderBy('id')->get(),
            'scope' => $student ? 'student' : 'self',
        ]);
    }

    public function update(Request $request, ImanProgram $program)
    {
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

        return $this->redirectAfterSave($program->owner)
            ->with('success', 'تم تحديث البرنامج الإيماني بنجاح');
    }

    public function destroy(ImanProgram $program)
    {
        $owner = $program->owner;
        $program->delete();

        return $this->redirectAfterSave($owner)
            ->with('success', 'تم حذف البرنامج الإيماني');
    }

    public function toggleEntry(Request $request, ImanProgram $program)
    {
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
            $entry->value = $data['value'] ?? 0;
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

    public function studentReport(Request $request, Student $student, \App\Services\StudentImanReportService $reports)
    {
        $data = $reports->build($student, $request);
        $data['routePrefix'] = 'admin.iman-program.student-report';
        return view('admin.iman-program.student-report', $data);
    }

    public function bulkAssign(Request $request)
    {
        $oldCriteria = old('criteria');
        $items = $oldCriteria !== null
            ? collect($oldCriteria)
            : collect([['name' => '', 'type' => 'check', 'target_value' => null]]);

        $students = Student::with('user')->orderBy('id')->get();
        $supervisors = User::where('role', 'supervisor')->orderBy('id')->get();

        $existingPrograms = ImanProgram::query()
            ->select('id', 'name', 'description', 'start_date')
            ->orderBy('name')
            ->orderByDesc('id')
            ->get()
            ->groupBy('name')
            ->map(function ($group, $name) {
                $first = $group->first();
                return (object) [
                    'id' => $first->id,
                    'name' => $name,
                    'description' => $first->description,
                    'start_date' => $first->start_date ? $first->start_date->format('Y-m-d') : null,
                    'owners_count' => $group->count(),
                    'criteria_count' => \App\Models\ImanCriterion::whereIn('iman_program_id', $group->pluck('id'))->count(),
                ];
            })
            ->values();

        return view('admin.iman-program.bulk-assign', [
            'items' => $items,
            'students' => $students,
            'supervisors' => $supervisors,
            'existingPrograms' => $existingPrograms,
            'old' => $request->old(),
        ]);
    }

    public function loadProgram(ImanProgram $program)
    {
        $program->load('criteria');
        $programs = ImanProgram::where('name', $program->name)->get();
        $studentMorph = (new Student)->getMorphClass();
        $userMorph = (new User)->getMorphClass();

        $studentIds = $programs->where('owner_type', $studentMorph)->pluck('owner_id');
        $userIds = $programs->where('owner_type', $userMorph)->pluck('owner_id');

        $students = Student::with('user')->whereIn('id', $studentIds)->get()
            ->map(fn ($s) => ['id' => $s->id, 'name' => $s->user->name ?? '—', 'type' => 'student']);
        $supervisors = User::whereIn('id', $userIds)->get()
            ->map(fn ($u) => ['id' => $u->id, 'name' => $u->name, 'type' => 'user']);

        $owners = $students->merge($supervisors)->values();

        return response()->json([
            'id' => $program->id,
            'name' => $program->name,
            'description' => $program->description,
            'start_date' => $program->start_date ? $program->start_date->format('Y-m-d') : null,
            'criteria' => $program->criteria->map(fn ($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'type' => $c->type,
                'target_value' => $c->target_value,
            ])->values(),
            'owners_count' => $programs->count(),
            'owners' => $owners,
        ]);
    }

    public function bulkStore(Request $request)
    {
        $mode = $request->input('mode', 'create');

        $rules = [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'start_date' => 'nullable|date',
        ];

        if ($mode === 'delete') {
            $rules['criteria'] = 'nullable';
        } else {
            $rules += [
                'criteria' => 'nullable|array|min:1',
                'criteria.*.name' => 'required_with:criteria.*|string|max:255',
                'criteria.*.type' => 'required_with:criteria.*|in:check,number',
                'criteria.*.target_value' => 'nullable|integer|min:0',
            ];
        }

        if ($mode === 'create') {
            $rules += [
                'all_students' => 'sometimes|boolean',
                'student_ids' => 'sometimes|array',
                'student_ids.*' => 'integer|exists:students,id',
                'all_supervisors' => 'sometimes|boolean',
                'supervisor_ids' => 'sometimes|array',
                'supervisor_ids.*' => 'integer|exists:users,id',
            ];
        } else {
            $rules += [
                'source_program_id' => 'required|integer|exists:iman_programs,id',
                'confirm_delete' => 'sometimes|boolean',
            ];
        }

        $data = $request->validate($rules);

        if ($mode === 'edit') {
            return $this->bulkUpdate($request, $data);
        }

        if ($mode === 'delete') {
            return $this->bulkDelete($request, $data);
        }

        $studentMorph = (new Student)->getMorphClass();
        $userMorph = (new User)->getMorphClass();

        $ownerIds = [];

        if ($request->boolean('all_students')) {
            $ownerIds[] = [Student::pluck('id')->all(), $studentMorph];
        } elseif ($request->filled('student_ids')) {
            $ownerIds[] = [array_map('intval', (array) $request->input('student_ids')), $studentMorph];
        }

        if ($request->boolean('all_supervisors')) {
            $ownerIds[] = [User::where('role', 'supervisor')->pluck('id')->all(), $userMorph];
        } elseif ($request->filled('supervisor_ids')) {
            $ownerIds[] = [array_map('intval', (array) $request->input('supervisor_ids')), $userMorph];
        }

        $targets = [];
        foreach ($ownerIds as [$ids, $type]) {
            foreach ($ids as $id) {
                $targets[] = ['id' => (int) $id, 'type' => $type];
            }
        }

        $targets = collect($targets)->unique(fn ($t) => $t['type'] . '|' . $t['id'])->values();

        if ($targets->isEmpty()) {
            return back()->withErrors(['targets' => 'يجب اختيار طالب واحد على الأقل أو مشرف لتعيين البرنامج له.'])->withInput();
        }

        $created = 0;
        $skipped = 0;

        DB::transaction(function () use ($data, $targets, &$created, &$skipped) {
            foreach ($targets as $target) {
                $exists = ImanProgram::where('owner_id', $target['id'])
                    ->where('owner_type', $target['type'])
                    ->where('name', $data['name'])
                    ->exists();
                if ($exists) {
                    $skipped++;
                    continue;
                }

                $program = ImanProgram::create([
                    'name' => $data['name'],
                    'description' => $data['description'] ?? null,
                    'created_by' => auth()->id(),
                    'owner_id' => $target['id'],
                    'owner_type' => $target['type'],
                    'is_active' => true,
                    'start_date' => $data['start_date'] ?? null,
                ]);

                $this->syncCriteria($program, $data['criteria'] ?? []);
                $created++;
            }
        });

        $msg = "تم إنشاء البرنامج بنجاح لـ {$created} مستخدم";
        if ($skipped > 0) {
            $msg .= " (تم تخطي {$skipped} لوجود برنامج بنفس الاسم)";
        }

        return redirect()->route('admin.iman-program.bulk-assign')->with('success', $msg);
    }

    protected function bulkUpdate(Request $request, array $data)
    {
        $source = ImanProgram::with('criteria')->findOrFail($data['source_program_id']);

        $programs = ImanProgram::where('name', $source->name)->get();

        if ($programs->isEmpty()) {
            return back()->withErrors(['source_program_id' => 'لم يتم العثور على برامج بهذا الاسم.'])->withInput();
        }

        $updated = 0;
        $skipped = 0;

        DB::transaction(function () use ($data, $programs, &$updated, &$skipped) {
            foreach ($programs as $program) {
                $program->update([
                    'name' => $data['name'],
                    'description' => $data['description'] ?? null,
                    'start_date' => $data['start_date'] ?? null,
                ]);
                $this->syncCriteria($program, $data['criteria'] ?? []);
                $updated++;
            }
        });

        $msg = "تم تحديث معايير برنامج «{$source->name}» لـ {$updated} مستخدم";
        return redirect()->route('admin.iman-program.bulk-assign', ['mode' => 'edit', 'program' => $source->id])->with('success', $msg);
    }

    protected function bulkDelete(Request $request, array $data)
    {
        if (! $request->boolean('confirm_delete')) {
            return back()->withErrors(['confirm_delete' => 'يجب تأكيد الحذف قبل المتابعة.'])->withInput();
        }

        $source = ImanProgram::findOrFail($data['source_program_id']);
        $programName = $source->name;

        $programs = ImanProgram::where('name', $programName)->get();
        $programIds = $programs->pluck('id');

        $deleted = DB::transaction(function () use ($programIds) {
            $count = $programIds->count();
            ImanDailyEntry::whereIn('iman_program_id', $programIds)->delete();
            ImanCriterion::whereIn('iman_program_id', $programIds)->delete();
            ImanProgram::whereIn('id', $programIds)->delete();
            return $count;
        });

        $msg = "تم حذف برنامج «{$programName}» من {$deleted} مستخدم (مع جميع المعايير والإدخالات)";
        return redirect()->route('admin.iman-program.bulk-assign', ['mode' => 'delete'])->with('success', $msg);
    }

    protected function resolveOwner(Request $request)
    {
        $scope = $request->get('scope', 'self');
        $studentId = $request->get('student_id');

        if ($scope === 'student' && $studentId) {
            $student = Student::find($studentId);
            if (! $student) {
                abort(404, 'الطالب غير موجود');
            }
            return $student;
        }

        return User::findOrFail(auth()->id());
    }

    protected function redirectAfterSave($owner)
    {
        if ($owner instanceof Student) {
            return redirect()->route('admin.iman-program.index', [
                'scope' => 'student',
                'student_id' => $owner->id,
            ]);
        }

        return redirect()->route('admin.iman-program.index', ['scope' => 'self']);
    }

    protected function validateProgram(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'start_date' => 'nullable|date',
            'criteria' => 'nullable|array',
            'criteria.*.name' => 'required_with:criteria.*|string|max:255',
            'criteria.*.type' => 'required_with:criteria.*|in:check,number',
            'criteria.*.target_value' => 'nullable|integer|min:0',
        ]);
    }

    protected function syncCriteria(ImanProgram $program, array $criteria): void
    {
        $incomingIds = collect($criteria)->pluck('id')->filter()->all();
        $program->criteria()->whereNotIn('id', $incomingIds)->delete();

        foreach ($criteria as $index => $row) {
            $payload = [
                'name' => $row['name'],
                'type' => $row['type'] ?? 'check',
                'target_value' => ($row['type'] ?? 'check') === 'number' ? ($row['target_value'] ?? null) : null,
                'sort_order' => $index,
            ];

            if (! empty($row['id'])) {
                $criterion = $program->criteria()->where('id', $row['id'])->first();
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
}
