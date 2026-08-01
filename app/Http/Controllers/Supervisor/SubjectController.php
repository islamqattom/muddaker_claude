<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Admin\SubjectController as AdminSubjectController;
use App\Http\Controllers\Controller;
use App\Models\Subject;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    public function index(Request $request)
    {
        $controller = new AdminSubjectController();
        return $controller->index($request);
    }

    public function create()
    {
        return view('admin.subjects.create');
    }

    public function store(Request $request)
    {
        $controller = new AdminSubjectController();
        return $controller->store($request);
    }

    public function show(Request $request, Subject $subject)
    {
        $controller = new AdminSubjectController();
        return $controller->show($request, $subject);
    }

    public function edit(Subject $subject)
    {
        $controller = new AdminSubjectController();
        $controller->authorizeAccessPublic($subject);
        return view('admin.subjects.edit', compact('subject'));
    }

    public function update(Request $request, Subject $subject)
    {
        $controller = new AdminSubjectController();
        return $controller->update($request, $subject);
    }

    public function destroy(Request $request, Subject $subject)
    {
        $controller = new AdminSubjectController();
        return $controller->destroy($request, $subject);
    }

    public function calendar(Request $request, Subject $subject)
    {
        $controller = new AdminSubjectController();
        return $controller->calendar($request, $subject);
    }

    public function feed(Request $request, Subject $subject)
    {
        $controller = new AdminSubjectController();
        return $controller->feed($request, $subject);
    }
}
