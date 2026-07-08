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
