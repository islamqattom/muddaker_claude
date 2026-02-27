<?php
namespace App\Http\Controllers\Student;
use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller {
    public function index() {
        $student = Auth::user()->student;
        $attendances = Attendance::where('student_id',$student->id)->latest('date')->paginate(30);
        $stats = ['present'=>Attendance::where('student_id',$student->id)->where('status','present')->count(),'absent'=>Attendance::where('student_id',$student->id)->where('status','absent')->count(),'late'=>Attendance::where('student_id',$student->id)->where('status','late')->count()];
        return view('student.attendance.index', compact('attendances','stats'));
    }
}
