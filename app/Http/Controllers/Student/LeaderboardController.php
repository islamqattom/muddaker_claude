<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Services\LeaderboardService;
use App\Models\Student;
use Illuminate\Http\Request;

class LeaderboardController extends Controller
{
    public function __construct(private LeaderboardService $leaderboardService) {}

    public function index(Request $request)
    {
        $student = $this->getStudent();

        $period = $request->get('period', LeaderboardService::PERIOD_WEEKLY);
        $type = $request->get('type', LeaderboardService::TYPE_PAGES);

        $leaderboard = $this->leaderboardService->getLeaderboard($period, $type, 50);

        // Get student's own rank
        $myRank = $this->leaderboardService->getStudentRank($student, $period, $type);

        // Get surrounding students
        $surrounding = $this->leaderboardService->getSurroundingStudents($student, $period, $type);

        // Stats
        $stats = [
            'pages' => $this->leaderboardService->getStudentRank($student, $period, LeaderboardService::TYPE_PAGES),
            'accuracy' => $this->leaderboardService->getStudentRank($student, $period, LeaderboardService::TYPE_ACCURACY),
            'streak' => $this->leaderboardService->getStudentRank($student, $period, LeaderboardService::TYPE_STREAK),
            'reviews' => $this->leaderboardService->getStudentRank($student, $period, LeaderboardService::TYPE_REVIEWS),
        ];

        return view('student.leaderboard.index', [
            'leaderboard' => $leaderboard,
            'myRank' => $myRank,
            'surrounding' => $surrounding,
            'stats' => $stats,
            'currentPeriod' => $period,
            'currentType' => $type,
            'types' => [
                LeaderboardService::TYPE_PAGES,
                LeaderboardService::TYPE_ACCURACY,
                LeaderboardService::TYPE_STREAK,
                LeaderboardService::TYPE_REVIEWS,
            ],
            'periods' => [
                LeaderboardService::PERIOD_WEEKLY,
                LeaderboardService::PERIOD_MONTHLY,
            ],
        ]);
    }

    private function getStudent(): Student
    {
        return auth()->user()->student;
    }
}
