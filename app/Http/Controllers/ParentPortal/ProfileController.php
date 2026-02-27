<?php
namespace App\Http\Controllers\ParentPortal;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller {
    public function index() { $parent = Auth::user()->parentProfile()->with('students.user')->first(); return view('parent.profile.index', compact('parent')); }
}
