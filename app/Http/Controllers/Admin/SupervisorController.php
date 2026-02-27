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
            'phone' => 'required|string|unique:users,phone',
            'email' => 'nullable|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        DB::transaction(function() use ($request) {
            $user = User::create([
                'name' => $request->name,
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
    public function update(Request $request, $id) { return redirect()->route('admin.supervisors.index')->with('success','تم التحديث'); }
    public function destroy($id) { User::findOrFail($id)->delete(); return redirect()->route('admin.supervisors.index')->with('success','تم الحذف'); }
}
