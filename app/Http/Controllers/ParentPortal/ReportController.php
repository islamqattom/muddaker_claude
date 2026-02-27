<?php
namespace App\Http\Controllers\ParentPortal;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller {
    public function index() { $parent = Auth::user()->parentProfile()->with('students.user')->first(); return view('parent.reports.index', compact('parent')); }
}
