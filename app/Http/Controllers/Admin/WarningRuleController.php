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
            'level_ids' => empty($request->level_ids) ? null : array_values($request->level_ids),
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
            'level_ids' => empty($request->level_ids) ? null : array_values($request->level_ids),
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
