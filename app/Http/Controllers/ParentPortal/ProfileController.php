<?php
namespace App\Http\Controllers\ParentPortal;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller {
    public function index() {
        $parent = Auth::user()->parentProfile()->with('students.user', 'students.level')->first();
        return view('parent.profile.index', compact('parent'));
    }

    public function update(Request $request) {
        $user = Auth::user();
        $parent = $user->parentProfile;

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        $parent->update([
            'phone' => $request->phone,
        ]);

        return redirect()->route('parent.profile.index')->with('success', 'تم تحديث البيانات الشخصية بنجاح');
    }
}
