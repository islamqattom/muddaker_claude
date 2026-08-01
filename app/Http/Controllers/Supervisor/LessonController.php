<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Admin\LessonController as AdminLessonController;
use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Models\Subject;
use Illuminate\Http\Request;

class LessonController extends Controller
{
    public function create(Subject $subject)
    {
        $controller = new AdminLessonController();
        $controller->authorizeSubjectPublic($subject);
        return view('admin.lessons.create', compact('subject'));
    }

    public function store(Request $request, Subject $subject)
    {
        $controller = new AdminLessonController();
        return $controller->store($request, $subject);
    }

    public function show(Subject $subject, Lesson $lesson)
    {
        $controller = new AdminLessonController();
        $controller->show($subject, $lesson);
        $lesson->load('subject', 'creator');
        return view('admin.lessons.show', compact('subject', 'lesson'));
    }

    public function edit(Subject $subject, Lesson $lesson)
    {
        $controller = new AdminLessonController();
        $controller->edit($subject, $lesson);
        return view('admin.lessons.edit', compact('subject', 'lesson'));
    }

    public function update(Request $request, Subject $subject, Lesson $lesson)
    {
        $controller = new AdminLessonController();
        return $controller->update($request, $subject, $lesson);
    }

    public function updateStatus(Request $request, Subject $subject, Lesson $lesson)
    {
        $controller = new AdminLessonController();
        return $controller->updateStatus($request, $subject, $lesson);
    }

    public function destroy(Request $request, Subject $subject, Lesson $lesson)
    {
        $controller = new AdminLessonController();
        return $controller->destroy($request, $subject, $lesson);
    }
}
