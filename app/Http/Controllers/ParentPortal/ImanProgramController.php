<?php

namespace App\Http\Controllers\ParentPortal;

use App\Http\Controllers\Controller;
use App\Models\ImanDailyEntry;
use App\Models\ImanProgram;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ImanProgramController extends Controller
{
    public function index(Request $request)
    {
        $parent = auth()->user()->parentProfile;
        if (! $parent) {
            abort(403, 'لا يوجد ملف ولي أمر مرتبط بهذا الحساب');
        }

        $studentIds = $parent->students()->pluck('students.id');
        $children = $parent->students()->with('user')->get();

        $studentFilter = $request->get('student_id');
        $student = null;
        if ($studentFilter && $studentIds->contains((int) $studentFilter)) {
            $student = Student::with('user')->find($studentFilter);
        }

        $weekStart = $this->resolveWeekStart($request);
        $weekEnd = $weekStart->copy()->addDays(6);

        $programs = collect();
        $entries = collect();

        if ($student) {
            $programs = ImanProgram::with(['criteria', 'creator'])
                ->where('owner_id', $student->id)
                ->where('owner_type', $student->getMorphClass())
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

        return view('parent.iman-program.index', [
            'children' => $children,
            'student' => $student,
            'studentFilter' => $studentFilter,
            'programs' => $programs,
            'entries' => $entries,
            'weekDates' => $weekDates,
            'weekStart' => $weekStart,
            'weekEnd' => $weekEnd,
            'stats' => $stats,
        ]);
    }

    public function show(Request $request, ImanProgram $program)
    {
        $parent = auth()->user()->parentProfile;
        if (! $parent) {
            abort(403);
        }
        $studentIds = $parent->students()->pluck('students.id');
        $owner = $program->owner;

        $isMyChild = $owner instanceof Student && $studentIds->contains($owner->id);
        if (! $isMyChild) {
            abort(403);
        }

        $program->load(['criteria', 'creator']);

        $weekStart = $this->resolveWeekStart($request);
        $weekEnd = $weekStart->copy()->addDays(6);

        $entries = ImanDailyEntry::where('iman_program_id', $program->id)
            ->whereBetween('entry_date', [$weekStart->toDateString(), $weekEnd->toDateString()])
            ->get()
            ->groupBy(fn ($e) => $e->iman_criterion_id . '|' . $e->entry_date->toDateString());

        $weekDates = [];
        for ($i = 0; $i < 7; $i++) {
            $weekDates[] = $weekStart->copy()->addDays($i);
        }

        return view('parent.iman-program.show', [
            'program' => $program,
            'entries' => $entries,
            'weekDates' => $weekDates,
            'weekStart' => $weekStart,
            'weekEnd' => $weekEnd,
            'student' => $owner,
        ]);
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

    protected function isEntryDone($criterion, $entry): bool
    {
        if (! $entry) {
            return false;
        }
        if ($criterion->type === 'check') {
            return (bool) $entry->is_checked;
        }
        return (int) $entry->value >= (int) ($criterion->target_value ?? 0) && (int) $criterion->target_value > 0;
    }
}
