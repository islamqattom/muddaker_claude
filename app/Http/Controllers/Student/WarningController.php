<?php
namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Warning;
use Illuminate\Support\Facades\Auth;

class WarningController extends Controller
{
    public function index()
    {
        $student = Auth::user()->student;
        $warnings = Warning::where('student_id', $student->id)->latest()->paginate(15);
        return view('student.warnings.index', compact('warnings'));
    }

    public function show(Warning $warning)
    {
        $student = Auth::user()->student;
        if ($warning->student_id !== $student->id) {
            abort(403);
        }
        $warning->load(['issuedBy']);
        return view('student.warnings.show', compact('warning'));
    }
}
