<?php
namespace App\Http\Controllers\Student;
use App\Http\Controllers\Controller;
use App\Models\Warning;
use Illuminate\Support\Facades\Auth;

class WarningController extends Controller {
    public function index() { $student = Auth::user()->student; $warnings = Warning::where('student_id',$student->id)->latest()->get(); return view('student.warnings.index', compact('warnings')); }
}
