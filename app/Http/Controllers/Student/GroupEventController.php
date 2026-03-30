<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\GroupEvent;
use App\Models\Student;
use App\Services\GroupEventService;
use Illuminate\Http\Request;

class GroupEventController extends Controller
{
    public function __construct(private GroupEventService $groupEventService) {}

    public function index()
    {
        $student = $this->getStudent();

        $availableEvents = $this->groupEventService->getAvailableEvents($student);
        $myEvents = $this->groupEventService->getMyEvents($student);

        return view('student.events.index', [
            'availableEvents' => $availableEvents,
            'myEvents' => $myEvents,
        ]);
    }

    public function show(GroupEvent $event)
    {
        $student = $this->getStudent();

        if (!$event->isParticipating($student)) {
            abort(403);
        }

        $progress = $this->groupEventService->getEventProgress($event);
        $progressHistory = $this->groupEventService->getProgressHistory($event);

        return view('student.events.show', [
            'event' => $event,
            'progress' => $progress,
            'progressHistory' => $progressHistory,
        ]);
    }

    public function join(GroupEvent $event)
    {
        $student = $this->getStudent();

        if ($event->status !== GroupEventService::STATUS_ACTIVE) {
            return back()->with('error', 'هذا الحدث غير نشط');
        }

        if ($event->isParticipating($student)) {
            return redirect()->route('student.events.show', $event);
        }

        $this->groupEventService->joinEvent($event, $student);

        return redirect()->route('student.events.show', $event)
            ->with('success', 'تم الانضمام للحدث بنجاح!');
    }

    public function leave(GroupEvent $event)
    {
        $student = $this->getStudent();

        $this->groupEventService->leaveEvent($event, $student);

        return redirect()->route('student.events.index')
            ->with('success', 'تم مغادرة الحدث');
    }

    private function getStudent(): Student
    {
        return auth()->user()->student;
    }
}
