<?php
namespace App\Http\Controllers\Supervisor;
use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Support\Facades\Auth;

class StudentController extends Controller {
    public function index() {
        $students = Student::where('supervisor_id', Auth::id())->with('user','level')->paginate(20);
        return view('supervisor.students.index', compact('students'));
    }
    public function show(Student $student) {
        $student->load(['user','level','attendances'=>fn($q)=>$q->latest()->take(30),'recitationSessions'=>fn($q)=>$q->with('part')->latest()->take(20),'warnings'=>fn($q)=>$q->latest()]);
        return view('supervisor.students.show', compact('student'));
    }
}
