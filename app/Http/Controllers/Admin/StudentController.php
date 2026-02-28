<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Student, User, Level, Supervisor};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

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
        return view('admin.students.create', compact('levels', 'supervisors'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'nullable|string|min:3|max:30|regex:/^[a-zA-Z0-9_]+$/|unique:users,username',
            'phone' => 'required|string|unique:users,phone',
            'email' => 'nullable|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'level_id' => 'required|exists:levels,id',
            'supervisor_id' => 'nullable|exists:users,id',
            'national_id' => 'nullable|string',
            'birth_date' => 'nullable|date',
            'address' => 'nullable|string',
            'enrollment_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        DB::transaction(function() use ($request) {
            $user = User::create([
                'name' => $request->name,
                'username' => $request->username,
                'phone' => $request->phone,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'student',
            ]);

            $lastStudent = Student::orderBy('id', 'desc')->first();
            $nextNumber = $lastStudent ? (int)substr($lastStudent->student_number, -4) + 1 : 1;
            $studentNumber = 'STU-' . date('Y') . '-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

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
        return view('admin.students.edit', compact('student', 'levels', 'supervisors'));
    }

    public function update(Request $request, Student $student)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'nullable|string|min:3|max:30|regex:/^[a-zA-Z0-9_]+$/|unique:users,username,' . $student->user_id,
            'phone' => 'required|string|unique:users,phone,' . $student->user_id,
            'email' => 'nullable|email|unique:users,email,' . $student->user_id,
            'level_id' => 'required|exists:levels,id',
            'supervisor_id' => 'nullable|exists:users,id',
        ]);

        DB::transaction(function() use ($request, $student) {
            $student->user->update([
                'name' => $request->name,
                'username' => $request->username,
                'phone' => $request->phone,
                'email' => $request->email,
            ]);

            if ($request->password) {
                $student->user->update(['password' => Hash::make($request->password)]);
            }

            $student->update([
                'supervisor_id' => $request->supervisor_id,
                'level_id' => $request->level_id,
                'national_id' => $request->national_id,
                'birth_date' => $request->birth_date,
                'address' => $request->address,
                'notes' => $request->notes,
                'is_active' => $request->boolean('is_active'),
            ]);
        });

        return redirect()->route('admin.students.show', $student)
            ->with('success', 'تم تحديث بيانات الطالب بنجاح');
    }

    public function destroy(Student $student)
    {
        DB::transaction(function() use ($student) {
            $student->user->delete();
            $student->delete();
        });

        return redirect()->route('admin.students.index')
            ->with('success', 'تم حذف الطالب بنجاح');
    }
}
