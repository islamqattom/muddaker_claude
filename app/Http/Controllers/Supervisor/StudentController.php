<?php
namespace App\Http\Controllers\Supervisor;
use App\Http\Controllers\Controller;
use App\Models\{Student, User, Level};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StudentController extends Controller {
    public function index() {
        $query = Student::where('supervisor_id', Auth::id())->with('user', 'level');

        if (request('search')) {
            $search = request('search');
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            })->orWhere('national_id', 'like', "%{$search}%")
              ->orWhere('student_number', 'like', "%{$search}%");
        }

        if (request('level_id')) {
            $query->where('level_id', request('level_id'));
        }

        $students = $query->paginate(20);
        return view('supervisor.students.index', compact('students'));
    }

    public function create() {
        $levels = Level::all();
        return view('supervisor.students.create', compact('levels'));
    }

    public function store(Request $request) {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|unique:users,phone',
            'email' => 'nullable|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'level_id' => 'required|exists:levels,id',
            'national_id' => 'nullable|string',
            'birth_date' => 'nullable|date',
            'address' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        DB::transaction(function() use ($request) {
            $user = User::create([
                'name' => $request->name,
                'phone' => $request->phone,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'student',
            ]);

            $studentNumber = 'STU-' . date('Y') . '-' . str_pad(Student::count() + 1, 4, '0', STR_PAD_LEFT);

            Student::create([
                'user_id' => $user->id,
                'supervisor_id' => Auth::id(),
                'level_id' => $request->level_id,
                'student_number' => $studentNumber,
                'national_id' => $request->national_id,
                'birth_date' => $request->birth_date,
                'phone' => $request->phone,
                'address' => $request->address,
                'enrollment_date' => today(),
                'notes' => $request->notes,
            ]);
        });

        return redirect()->route('supervisor.students.index')
            ->with('success', 'تم إضافة الطالب بنجاح');
    }

    public function edit(Student $student) {
        // Ensure the student belongs to this supervisor
        if ($student->supervisor_id !== Auth::id()) {
            abort(403);
        }
        $student->load('user');
        $levels = Level::all();
        return view('supervisor.students.edit', compact('student', 'levels'));
    }

    public function update(Request $request, Student $student) {
        // Ensure the student belongs to this supervisor
        if ($student->supervisor_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|unique:users,phone,' . $student->user_id,
            'email' => 'nullable|email|unique:users,email,' . $student->user_id,
            'level_id' => 'required|exists:levels,id',
        ]);

        DB::transaction(function() use ($request, $student) {
            $student->user->update([
                'name' => $request->name,
                'phone' => $request->phone,
                'email' => $request->email,
            ]);

            if ($request->password) {
                $student->user->update(['password' => Hash::make($request->password)]);
            }

            $student->update([
                'level_id' => $request->level_id,
                'national_id' => $request->national_id,
                'birth_date' => $request->birth_date,
                'phone' => $request->phone,
                'address' => $request->address,
                'notes' => $request->notes,
            ]);
        });

        return redirect()->route('supervisor.students.show', $student)
            ->with('success', 'تم تحديث بيانات الطالب بنجاح');
    }

    public function show(Student $student) {
        // Ensure the student belongs to this supervisor
        if ($student->supervisor_id !== Auth::id()) {
            abort(403);
        }
        $student->load(['user','level','attendances'=>fn($q)=>$q->latest()->take(30),'recitationSessions'=>fn($q)=>$q->with('part')->latest()->take(20),'warnings'=>fn($q)=>$q->latest()]);
        return view('supervisor.students.show', compact('student'));
    }
}
