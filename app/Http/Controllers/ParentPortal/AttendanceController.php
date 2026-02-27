<?php
namespace App\Http\Controllers\ParentPortal;
use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller {
    public function index() {
        $parent = Auth::user()->parentProfile()->with('students')->first();
        $studentIds = $parent->students->pluck('id');
        $attendances = Attendance::whereIn('student_id',$studentIds)->with('student.user')->latest('date')->paginate(30);
        return view('parent.attendance.index', compact('attendances'));
    }
}
