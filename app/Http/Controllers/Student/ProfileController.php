<?php
namespace App\Http\Controllers\Student;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller {
    public function index() { $student = Auth::user()->student()->with('level','supervisor')->first(); return view('student.profile.index', compact('student')); }
    public function update(Request $request) { return back()->with('success','تم تحديث الملف الشخصي'); }
}
