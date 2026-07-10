<?php
namespace App\Http\Controllers\ParentPortal;
use App\Http\Controllers\Controller;
use App\Models\Warning;
use Illuminate\Support\Facades\Auth;

class WarningController extends Controller {
    public function index() {
        $parent = Auth::user()->parentProfile()->with('students')->first();
        $studentIds = $parent->students->pluck('id');
        $warnings = Warning::whereIn('student_id',$studentIds)->with('student.user')->latest()->paginate(15);
        return view('parent.warnings.index', compact('warnings'));
    }
    public function show(Warning $warning) { $warning->update(['is_read_by_parent'=>true]); $warning->load(['student.user','issuedBy']); return view('parent.warnings.show', compact('warning')); }
}
