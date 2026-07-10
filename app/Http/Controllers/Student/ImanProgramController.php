<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\ImanCriterion;
use App\Models\ImanDailyEntry;
use App\Models\ImanProgram;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ImanProgramController extends Controller
{
    public function index(Request $request)
    {
        $student = auth()->user()->student;
        if (! $student) {
            abort(403, 'لا يوجد ملف طالب مرتبط بهذا الحساب');
        }

        $weekStart = $this->resolveWeekStart($request);
        $weekEnd = $weekStart->copy()->addDays(6);

        $programs = ImanProgram::with(['criteria', 'creator'])
            ->where(function ($q) use ($student) {
                $q->where('owner_id', $student->id)
                    ->where('owner_type', $student->getMorphClass())
                    ->orWhere('created_by', auth()->id());
            })
            ->orderByDesc('is_active')
            ->orderByDesc('id')
            ->get();

        $entries = ImanDailyEntry::whereIn('iman_program_id', $programs->pluck('id'))
            ->whereBetween('entry_date', [$weekStart->toDateString(), $weekEnd->toDateString()])
            ->get()
            ->groupBy(fn ($e) => $e->iman_program_id . '|' . $e->iman_criterion_id . '|' . $e->entry_date->toDateString());

        $weekDates = [];
        for ($i = 0; $i < 7; $i++) {
            $weekDates[] = $weekStart->copy()->addDays($i);
        }

        $stats = [
            'programs_count' => $programs->count(),
            'criteria_count' => $programs->sum(fn ($p) => $p->criteria->count()),
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

        return view('student.iman-program.index', [
            'student' => $student,
            'programs' => $programs,
            'entries' => $entries,
            'weekDates' => $weekDates,
            'weekStart' => $weekStart,
            'weekEnd' => $weekEnd,
            'stats' => $stats,
        ]);
    }

    public function create()
    {
        return view('student.iman-program.form', [
            'program' => null,
            'criteria' => collect(),
        ]);
    }

    public function store(Request $request)
    {
        $student = auth()->user()->student;
        if (! $student) {
            abort(403);
        }

        $data = $this->validateProgram($request);

        $program = DB::transaction(function () use ($request, $student, $data) {
            $program = ImanProgram::create([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'created_by' => auth()->id(),
                'owner_id' => $student->id,
                'owner_type' => $student->getMorphClass(),
                'is_active' => true,
                'start_date' => $data['start_date'] ?? null,
            ]);

            $this->syncCriteria($program, $data['criteria'] ?? []);

            return $program;
        });

        return redirect()->route('student.iman-program.index')
            ->with('success', 'تم إنشاء البرنامج الإيماني بنجاح');
    }

    public function edit(ImanProgram $program)
    {
        $this->authorizeAccess($program);

        $program->load('criteria');
        return view('student.iman-program.form', [
            'program' => $program,
            'criteria' => $program->criteria,
        ]);
    }

    public function update(Request $request, ImanProgram $program)
    {
        $this->authorizeAccess($program);

        $data = $this->validateProgram($request);

        DB::transaction(function () use ($program, $data) {
            $program->update([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'is_active' => $request->boolean('is_active', true),
                'start_date' => $data['start_date'] ?? null,
            ]);

            $this->syncCriteria($program, $data['criteria'] ?? []);
        });

        return redirect()->route('student.iman-program.index')
            ->with('success', 'تم تحديث البرنامج الإيماني بنجاح');
    }

    public function destroy(ImanProgram $program)
    {
        $this->authorizeAccess($program);
        $program->delete();

        return redirect()->route('student.iman-program.index')
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
        $student = auth()->user()->student;
        if (! $student) {
            abort(403);
        }

        $isOwner = $program->owner_id === $student->id
            && $program->owner_type === $student->getMorphClass();
        $isCreator = $program->created_by === auth()->id();

        if (! $isOwner && ! $isCreator) {
            abort(403);
        }
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

    public function myReport(Request $request, \App\Services\StudentImanReportService $reports)
    {
        $student = auth()->user()->student;
        if (! $student) {
            abort(403, 'لا يوجد ملف طالب مرتبط بهذا الحساب');
        }

        $data = $reports->build($student, $request);

        return view('student.iman-program.my-report', [
            'data' => $data,
            'student' => $student,
            'formAction' => route('student.iman-program.my-report'),
            'compareAction' => route('student.iman-program.my-report'),
            'criteriaAction' => route('student.iman-program.my-report'),
            'backUrl' => route('student.iman-program.index'),
            'headerTitle' => 'تقاريري الإيمانية',
        ]);
    }
}
