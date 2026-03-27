<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Services\MemorizationService;
use App\Services\AchievementService;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProgressController extends Controller
{
    private MemorizationService $memorizationService;
    private AchievementService $achievementService;

    public function __construct(
        MemorizationService $memorizationService,
        AchievementService $achievementService
    ) {
        $this->memorizationService = $memorizationService;
        $this->achievementService = $achievementService;
    }

    public function index()
    {
        $user = Auth::user();
        $student = $user->student()->with('level')->firstOrFail();

        $goal = $this->memorizationService->getOrCreateGoal($student);
        $todayProgress = $this->memorizationService->getTodayProgress($student);
        $weeklyProgress = $this->memorizationService->getWeeklyProgress($student);
        $monthlyProgress = $this->memorizationService->getMonthlyProgress($student);
        $earnedBadges = $this->achievementService->getEarnedBadges($student);
        $nextBadges = $this->achievementService->getNextEarnableBadges($student);

        return view('student.progress.index', compact(
            'student', 'goal', 'todayProgress', 'weeklyProgress',
            'monthlyProgress', 'earnedBadges', 'nextBadges'
        ));
    }

    public function goals()
    {
        $user = Auth::user();
        $student = $user->student()->firstOrFail();

        $goal = $this->memorizationService->getOrCreateGoal($student);

        return view('student.progress.goals', compact('student', 'goal'));
    }

    public function updateGoal(Request $request)
    {
        $user = Auth::user();
        $student = $user->student()->firstOrFail();

        $validated = $request->validate([
            'period' => 'required|in:daily,weekly',
            'target_pages' => 'required|integer|min:1|max:30',
            'target_days_per_week' => 'nullable|integer|min:1|max:7',
            'daily_reminder_time' => 'nullable|date_format:H:i',
            'is_active' => 'boolean',
        ]);

        $goal = $this->memorizationService->updateGoal($student, $validated);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'goal' => $goal,
            ]);
        }

        return redirect()->back()->with('success', 'تم تحديث الهدف بنجاح');
    }

    public function apiStats()
    {
        $user = Auth::user();
        $student = $user->student()->firstOrFail();

        $goal = $this->memorizationService->getOrCreateGoal($student);
        $todayProgress = $this->memorizationService->getTodayProgress($student);

        return response()->json([
            'success' => true,
            'data' => [
                'current_streak' => $student->current_streak,
                'longest_streak' => $student->longest_streak,
                'memorized_parts' => $student->memorized_parts,
                'accuracy_rate' => $student->accuracy_rate,
                'goal' => $goal,
                'today' => $todayProgress,
            ],
        ]);
    }

    public function apiWeekly()
    {
        $user = Auth::user();
        $student = $user->student()->firstOrFail();

        $weeklyProgress = $this->memorizationService->getWeeklyProgress($student);

        return response()->json([
            'success' => true,
            'data' => $weeklyProgress,
        ]);
    }

    public function apiRecordProgress(Request $request)
    {
        $user = Auth::user();
        $student = $user->student()->firstOrFail();

        $validated = $request->validate([
            'pages_memorized' => 'nullable|integer|min:0',
            'pages_reviewed' => 'nullable|integer|min:0',
            'minutes' => 'nullable|integer|min:0',
            'verses' => 'nullable|integer|min:0',
        ]);

        $progress = $this->memorizationService->recordProgress(
            $student,
            $validated['pages_memorized'] ?? 0,
            $validated['pages_reviewed'] ?? 0,
            $validated['minutes'] ?? 0,
            $validated['verses'] ?? 0
        );

        // Check for new achievements
        $newBadges = $this->achievementService->checkAllBadges($student);

        return response()->json([
            'success' => true,
            'progress' => $progress,
            'new_badges' => $newBadges,
        ]);
    }

    public function apiGoals()
    {
        $user = Auth::user();
        $student = $user->student()->firstOrFail();

        $goal = $this->memorizationService->getOrCreateGoal($student);

        return response()->json([
            'success' => true,
            'goal' => $goal,
        ]);
    }
}
