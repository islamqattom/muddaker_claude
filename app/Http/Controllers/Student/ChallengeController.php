<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Challenge;
use App\Models\Student;
use App\Services\ChallengeService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ChallengeController extends Controller
{
    public function __construct(private ChallengeService $challengeService) {}

    public function index()
    {
        $student = $this->getStudent();

        $activeChallenges = $this->challengeService->getActiveChallenges($student);
        $pendingRequests = $this->challengeService->getPendingRequests($student);
        $history = $this->challengeService->getHistory($student, 10);
        $stats = $this->challengeService->getStats($student);

        return view('student.challenges.index', [
            'activeChallenges' => $activeChallenges,
            'pendingRequests' => $pendingRequests,
            'history' => $history,
            'stats' => $stats,
        ]);
    }

    public function create()
    {
        $student = $this->getStudent();
        $availableOpponents = $this->challengeService->getAvailableOpponents($student, 20);

        return view('student.challenges.create', [
            'opponents' => $availableOpponents,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'opponent_id' => 'required|exists:students,id',
            'surah' => 'required|integer|min:1|max:114',
            'verse_start' => 'required|integer|min:1',
            'verse_end' => 'required|integer|min:1|gte:verse_start',
            'target_date' => 'required|date|after:today',
        ]);

        $student = $this->getStudent();

        // Can't challenge yourself
        if ($request->opponent_id === $student->id) {
            return back()->with('error', 'لا يمكنك تحدي نفسك');
        }

        $challenge = $this->challengeService->createChallenge(
            $student,
            Student::findOrFail($request->opponent_id),
            $request->surah,
            $request->verse_start,
            $request->verse_end,
            Carbon::parse($request->target_date)
        );

        return redirect()->route('student.challenges.show', $challenge)
            ->with('success', 'تم إرسال التحدي بنجاح!');
    }

    public function show(Challenge $challenge)
    {
        $student = $this->getStudent();

        // Verify participant
        if (!$challenge->isParticipant($student)) {
            abort(403);
        }

        $challenge->load(['challenger.user', 'challenged.user', 'verifications.recitationSession']);

        return view('student.challenges.show', [
            'challenge' => $challenge,
        ]);
    }

    public function accept(Challenge $challenge)
    {
        $student = $this->getStudent();

        if ($challenge->challenged_id !== $student->id) {
            abort(403);
        }

        if ($challenge->status !== ChallengeService::STATUS_PENDING) {
            return back()->with('error', 'لا يمكن قبول هذا التحدي');
        }

        $this->challengeService->accept($challenge);

        return redirect()->route('student.challenges.show', $challenge)
            ->with('success', 'تم قبول التحدي!');
    }

    public function decline(Challenge $challenge)
    {
        $student = $this->getStudent();

        if ($challenge->challenged_id !== $student->id) {
            abort(403);
        }

        if ($challenge->status !== ChallengeService::STATUS_PENDING) {
            return back()->with('error', 'لا يمكن رفض هذا التحدي');
        }

        $this->challengeService->decline($challenge);

        return redirect()->route('student.challenges.index')
            ->with('success', 'تم رفض التحدي');
    }

    private function getStudent(): Student
    {
        return auth()->user()->student;
    }
}
