<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Student, User, Level, Supervisor, FeeCategory};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $query = Student::with(['user', 'level', 'supervisor']);

        if ($request->search) {
            $query->whereHas('user', fn($q) => $q->where('name', 'like', "%{$request->search}%")
                ->orWhere('phone', 'like', "%{$request->search}%"));
        }

        if ($request->level_id) {
            $query->where('level_id', $request->level_id);
        }

        if ($request->supervisor_id) {
            $query->where('supervisor_id', $request->supervisor_id);
        }

        $students = $query->latest()->paginate(20);
        $levels = Level::all();
        $supervisors = User::where('role', 'supervisor')->get();

        return view('admin.students.index', compact('students', 'levels', 'supervisors'));
    }

    public function create()
    {
        $levels = Level::all();
        $supervisors = User::where('role', 'supervisor')->get();
        $feeCategories = FeeCategory::active()->orderBy('name')->get();
        return view('admin.students.create', compact('levels', 'supervisors', 'feeCategories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|min:3|max:30|regex:/^[a-zA-Z0-9_]+$/|unique:users,username',
            'phone' => 'nullable|string|unique:users,phone',
            'email' => 'nullable|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'level_id' => 'required|exists:levels,id',
            'supervisor_id' => 'required|exists:users,id',
            'national_id' => 'nullable|string',
            'birth_date' => 'nullable|date',
            'address' => 'nullable|string',
            'enrollment_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'image' => 'nullable|file|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'fee_category_id' => 'nullable|exists:fee_categories,id',
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
                'role' => 'student',
                'avatar' => $avatarPath,
            ]);

            $currentYear = date('Y');
            $lastStudentThisYear = Student::where('student_number', 'like', "STU-{$currentYear}-%")
                ->orderBy('student_number', 'desc')
                ->first();
            $nextNumber = $lastStudentThisYear ? (int)substr($lastStudentThisYear->student_number, -4) + 1 : 1;
            $studentNumber = 'STU-' . $currentYear . '-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

            Student::create([
                'user_id' => $user->id,
                'supervisor_id' => $request->supervisor_id,
                'level_id' => $request->level_id,
                'student_number' => $studentNumber,
                'national_id' => $request->national_id,
                'birth_date' => $request->birth_date,
                'phone' => $request->phone,
                'address' => $request->address,
                'enrollment_date' => $request->enrollment_date ?? today(),
                'notes' => $request->notes,
                'fee_category_id' => $request->fee_category_id,
            ]);
        });

        return redirect()->route('admin.students.index')
            ->with('success', 'تم إضافة الطالب بنجاح');
    }

    public function show(Student $student)
    {
        $student->load(['user', 'level', 'supervisor', 'parents.user',
            'attendances' => fn($q) => $q->latest()->take(30),
            'recitationSessions' => fn($q) => $q->with('part')->latest()->take(20),
            'warnings' => fn($q) => $q->latest()->take(10),
        ]);

        $attendanceStats = [
            'present' => $student->attendances()->where('status', 'present')->count(),
            'absent' => $student->attendances()->where('status', 'absent')->count(),
            'late' => $student->attendances()->where('status', 'late')->count(),
            'excused' => $student->attendances()->where('status', 'excused')->count(),
        ];

        return view('admin.students.show', compact('student', 'attendanceStats'));
    }

    public function edit(Student $student)
    {
        $student->load('user');
        $levels = Level::all();
        $supervisors = User::where('role', 'supervisor')->get();
        $feeCategories = FeeCategory::active()->orderBy('name')->get();
        return view('admin.students.edit', compact('student', 'levels', 'supervisors', 'feeCategories'));
    }

    public function update(Request $request, Student $student)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|min:3|max:30|regex:/^[a-zA-Z0-9_]+$/|unique:users,username,' . $student->user_id,
            'phone' => 'required|string|unique:users,phone,' . $student->user_id,
            'email' => 'nullable|email|unique:users,email,' . $student->user_id,
            'level_id' => 'required|exists:levels,id',
            'supervisor_id' => 'required|exists:users,id',
            'image' => 'nullable|file|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'fee_category_id' => 'nullable|exists:fee_categories,id',
        ]);

        $newAvatarPath = null;

        if ($request->hasFile('image')) {
            // Delete old avatar if exists
            if ($student->user->avatar) {
                try {
                    $oldPath = storage_path('app/public/' . $student->user->avatar);
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

        $oldSupervisorId = $student->supervisor_id;
        $newSupervisorId = $request->supervisor_id;

        DB::transaction(function() use ($request, $student, $newAvatarPath, $oldSupervisorId, $newSupervisorId) {
            $userData = [
                'name' => $request->name,
                'username' => $request->username,
                'phone' => $request->phone,
                'email' => $request->email,
            ];

            if ($newAvatarPath) {
                $userData['avatar'] = $newAvatarPath;
            }

            $student->user->update($userData);

            if ($request->password) {
                $student->user->update(['password' => Hash::make($request->password)]);
            }

            $student->update([
                'supervisor_id' => $newSupervisorId,
                'level_id' => $request->level_id,
                'national_id' => $request->national_id,
                'birth_date' => $request->birth_date,
                'address' => $request->address,
                'enrollment_date' => $request->enrollment_date,
                'notes' => $request->notes,
                'is_active' => $request->boolean('is_active'),
                'fee_category_id' => $request->fee_category_id,
            ]);

            // Cascade supervisor change to all related records
            if ($oldSupervisorId != $newSupervisorId) {
                $student->recitationSessions()->update(['supervisor_id' => $newSupervisorId]);
                $student->attendances()->update(['supervisor_id' => $newSupervisorId]);
            }
        });

        return redirect()->route('admin.students.show', $student)
            ->with('success', 'تم تحديث بيانات الطالب بنجاح');
    }

    public function destroy(Student $student)
    {
        try {
            DB::transaction(function() use ($student) {
                $student->user->delete();
                $student->delete();
            });

            Log::info('Student deleted', [
                'admin_id' => auth()->id(),
                'student_id' => $student->id,
                'user_id' => $student->user_id,
                'timestamp' => now()->toISOString(),
            ]);

            return redirect()->route('admin.students.index')
                ->with('success', 'تم حذف الطالب بنجاح');
        } catch (\Exception $e) {
            Log::error('Failed to delete student', [
                'admin_id' => auth()->id(),
                'student_id' => $student->id,
                'user_id' => $student->user_id,
                'error' => $e->getMessage(),
            ]);
            return redirect()->route('admin.students.show', $student)
                ->with('error', 'فشل في حذف الطالب. يرجى المحاولة مرة أخرى.');
        }
    }
}
