<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Services\AchievementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AchievementController extends Controller
{
    private AchievementService $achievementService;

    public function __construct(AchievementService $achievementService)
    {
        $this->achievementService = $achievementService;
    }

    public function index()
    {
        $user = Auth::user();
        $student = $user->student()->firstOrFail();

        $earnedBadges = $this->achievementService->getEarnedBadges($student);
        $nextBadges = $this->achievementService->getNextEarnableBadges($student);

        return view('student.achievements.index', compact(
            'student', 'earnedBadges', 'nextBadges'
        ));
    }

    public function apiEarned()
    {
        $user = Auth::user();
        $student = $user->student()->firstOrFail();

        $earnedBadges = $this->achievementService->getEarnedBadges($student);

        return response()->json([
            'success' => true,
            'badges' => $earnedBadges,
        ]);
    }

    public function apiNext()
    {
        $user = Auth::user();
        $student = $user->student()->firstOrFail();

        $nextBadges = $this->achievementService->getNextEarnableBadges($student);

        return response()->json([
            'success' => true,
            'badges' => $nextBadges,
        ]);
    }

    public function apiProgress(string $code)
    {
        $user = Auth::user();
        $student = $user->student()->firstOrFail();

        $progress = $this->achievementService->getProgressToBadge($student, $code);

        return response()->json([
            'success' => true,
            'progress' => $progress,
        ]);
    }
}
