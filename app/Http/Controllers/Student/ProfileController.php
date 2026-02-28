<?php
namespace App\Http\Controllers\Student;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller {
    public function index() {
        $student = Auth::user()->student()->with('level', 'supervisor')->first();
        return view('student.profile.index', compact('student'));
    }

    public function update(Request $request) {
        $user = Auth::user();
        $student = $user->student;

        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'nullable|string|max:255|unique:users,username,' . $user->id,
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
        ]);

        $user->update([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
        ]);

        $student->update([
            'phone' => $request->phone,
            'address' => $request->address,
        ]);

        return back()->with('success', 'تم تحديث الملف الشخصي بنجاح');
    }
}
