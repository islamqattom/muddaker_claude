<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{User, Supervisor};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class SupervisorController extends Controller
{
    public function index()
    {
        $supervisors = User::where('role', 'supervisor')->with('supervisor')->paginate(20);
        return view('admin.supervisors.index', compact('supervisors'));
    }

    public function create()
    {
        return view('admin.supervisors.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'nullable|string|min:3|max:30|regex:/^[a-zA-Z0-9_]+$/|unique:users,username',
            'phone' => 'required|string|unique:users,phone',
            'email' => 'nullable|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        DB::transaction(function() use ($request) {
            $user = User::create([
                'name' => $request->name,
                'username' => $request->username,
                'phone' => $request->phone,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'supervisor',
            ]);
            Supervisor::create([
                'user_id' => $user->id,
                'employee_number' => 'EMP-' . str_pad(Supervisor::count() + 1, 3, '0', STR_PAD_LEFT),
                'specialization' => $request->specialization,
                'hire_date' => $request->hire_date ?? today(),
            ]);
        });

        return redirect()->route('admin.supervisors.index')->with('success', 'تم إضافة المشرف بنجاح');
    }

    public function show($id) { $supervisor = User::where('role','supervisor')->with('supervisor','supervisedStudents.user')->findOrFail($id); return view('admin.supervisors.show', compact('supervisor')); }
    public function edit($id) { $supervisor = User::where('role','supervisor')->with('supervisor')->findOrFail($id); return view('admin.supervisors.edit', compact('supervisor')); }
    public function update(Request $request, $id)
    {
        $supervisor = User::where('role', 'supervisor')->findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'nullable|string|min:3|max:30|regex:/^[a-zA-Z0-9_]+$/|unique:users,username,' . $supervisor->id,
            'phone' => 'required|string|unique:users,phone,' . $supervisor->id,
            'email' => 'nullable|email|unique:users,email,' . $supervisor->id,
        ]);

        $supervisor->update([
            'name' => $request->name,
            'username' => $request->username,
            'phone' => $request->phone,
            'email' => $request->email,
        ]);

        if ($request->password) {
            $supervisor->update(['password' => Hash::make($request->password)]);
        }

        return redirect()->route('admin.supervisors.index')->with('success', 'تم التحديث');
    }
    public function destroy($id) { User::findOrFail($id)->delete(); return redirect()->route('admin.supervisors.index')->with('success','تم الحذف'); }
}
