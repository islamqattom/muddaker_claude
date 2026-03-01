<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{User, Supervisor};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
            'username' => 'required|string|min:3|max:30|regex:/^[a-zA-Z0-9_]+$/|unique:users,username',
            'phone' => 'nullable|string|unique:users,phone',
            'email' => 'nullable|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'image' => 'nullable|file|mimes:jpeg,png,jpg,gif,webp|max:10240',
        ]);

        $avatarPath = null;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            try {
                $filename = uniqid('avatar_') . '.' . $file->extension();
                $destinationPath = storage_path('app/public/avatars');
                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0755, true);
                }
                $file->move($destinationPath, $filename);
                $avatarPath = 'avatars/' . $filename;
            } catch (\Exception $e) {
                Log::error('Image upload failed: ' . $e->getMessage());
                return back()->withInput()->with('error', 'فشل في رفع الصورة. يرجى المحاولة مرة أخرى.');
            }
        }

        DB::transaction(function() use ($request, $avatarPath) {
            $user = User::create([
                'name' => $request->name,
                'username' => $request->username,
                'phone' => $request->phone,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'supervisor',
                'avatar' => $avatarPath,
            ]);

            $lastSupervisor = Supervisor::orderBy('id', 'desc')->first();
            $nextNumber = $lastSupervisor ? (int)substr($lastSupervisor->employee_number, -3) + 1 : 1;

            Supervisor::create([
                'user_id' => $user->id,
                'employee_number' => 'EMP-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT),
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
            'username' => 'required|string|min:3|max:30|regex:/^[a-zA-Z0-9_]+$/|unique:users,username,' . $supervisor->id,
            'phone' => 'nullable|string|unique:users,phone,' . $supervisor->id,
            'email' => 'nullable|email|unique:users,email,' . $supervisor->id,
            'image' => 'nullable|file|mimes:jpeg,png,jpg,gif,webp|max:10240',
        ]);

        $newAvatarPath = null;

        if ($request->hasFile('image')) {
            // Delete old avatar if exists
            if ($supervisor->avatar) {
                try {
                    $oldPath = storage_path('app/public/' . $supervisor->avatar);
                    if (file_exists($oldPath)) {
                        unlink($oldPath);
                    }
                } catch (\Exception $e) {
                    Log::error('Failed to delete old avatar: ' . $e->getMessage());
                }
            }
            try {
                $file = $request->file('image');
                $filename = uniqid('avatar_') . '.' . $file->extension();
                $destinationPath = storage_path('app/public/avatars');
                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0755, true);
                }
                $file->move($destinationPath, $filename);
                $newAvatarPath = 'avatars/' . $filename;
            } catch (\Exception $e) {
                Log::error('Image upload failed: ' . $e->getMessage());
                return back()->withInput()->with('error', 'فشل في رفع الصورة. يرجى المحاولة مرة أخرى.');
            }
        }

        $userData = [
            'name' => $request->name,
            'username' => $request->username,
            'phone' => $request->phone,
            'email' => $request->email,
        ];

        if ($newAvatarPath) {
            $userData['avatar'] = $newAvatarPath;
        }

        $supervisor->update($userData);

        if ($request->password) {
            $supervisor->update(['password' => Hash::make($request->password)]);
        }

        // Update supervisor profile if exists
        if ($supervisor->supervisor) {
            $supervisor->supervisor->update([
                'specialization' => $request->specialization,
            ]);
        }

        return redirect()->route('admin.supervisors.index')->with('success', 'تم التحديث');
    }
    public function destroy($id) { User::findOrFail($id)->delete(); return redirect()->route('admin.supervisors.index')->with('success','تم الحذف'); }
}
