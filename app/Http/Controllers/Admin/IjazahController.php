<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\QuranPart;
use App\Models\Student;
use App\Models\User;
use App\Models\Ijazah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IjazahController extends Controller
{
    public function index(Request $request)
    {
        $parts = QuranPart::orderBy('part_number')->get();
        $supervisors = User::where('role', 'supervisor')->get();

        $studentsQuery = Student::with(['user', 'supervisor']);

        if ($request->supervisor_id) {
            $studentsQuery->where('supervisor_id', $request->supervisor_id);
        }

        if ($request->search) {
            $studentsQuery->whereHas('user', fn($q) => $q->where('name', 'like', "%{$request->search}%"));
        }

        $studentsWithIjazah = $studentsQuery->where('has_ijazah', true)->get();
        $allStudents = Student::with(['user', 'supervisor'])->get();

        $ijazahRecords = Ijazah::with(['student.user', 'quranPart', 'supervisor'])
            ->when($request->supervisor_id, fn($q) => $q->where('supervisor_id', $request->supervisor_id))
            ->when($request->search, function ($q) use ($request) {
                $q->whereHas('student.user', fn($sq) => $sq->where('name', 'like', "%{$request->search}%"));
            })
            ->orderByDesc('created_at')
            ->get()
            ->groupBy('student_id');

        return view('admin.ijazah.index', compact('parts', 'allStudents', 'studentsWithIjazah', 'supervisors', 'ijazahRecords'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'supervisor_id' => 'required|exists:users,id',
            'parts' => 'required|array|min:1',
            'parts.*' => 'exists:quran_parts,id',
        ]);

        $student = Student::findOrFail($validated['student_id']);

        DB::transaction(function () use ($validated, $student) {
            foreach ($validated['parts'] as $partId) {
                Ijazah::updateOrCreate(
                    [
                        'student_id' => $student->id,
                        'quran_part_id' => $partId,
                        'supervisor_id' => $validated['supervisor_id'],
                    ],
                    [
                        'is_completed' => true,
                        'completed_at' => now(),
                    ]
                );
            }
        });

        return redirect()->back()->with('success', 'تم تسجيل الإجازة بنجاح');
    }

    public function destroy(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'quran_part_id' => 'required|exists:quran_parts,id',
            'supervisor_id' => 'required|exists:users,id',
        ]);

        Ijazah::where('student_id', $validated['student_id'])
            ->where('quran_part_id', $validated['quran_part_id'])
            ->where('supervisor_id', $validated['supervisor_id'])
            ->delete();

        return redirect()->back()->with('success', 'تم حذف تسجيل الإجازة');
    }

    public function getStudentsForSupervisor(Request $request)
    {
        $students = Student::where('supervisor_id', $request->supervisor_id)
            ->where('has_ijazah', true)
            ->with('user')
            ->get()
            ->map(fn($s) => ['id' => $s->id, 'name' => $s->user->name]);

        return response()->json(['students' => $students]);
    }

    public function getIjazahForStudent(Request $request)
    {
        $ijazahRecords = Ijazah::where('student_id', $request->student_id)
            ->where('supervisor_id', $request->supervisor_id)
            ->pluck('quran_part_id');

        return response()->json(['ijazah_parts' => $ijazahRecords]);
    }

    public function assignStudentToSupervisor(Request $request)
    {
        try {
            $validated = $request->validate([
                'student_id' => 'required|exists:students,id',
                'supervisor_id' => 'required|exists:users,id',
            ]);

            $student = Student::findOrFail($validated['student_id']);
            $supervisor = User::findOrFail($validated['supervisor_id']);

            $student->update([
                'supervisor_id' => $validated['supervisor_id'],
                'has_ijazah' => true
            ]);

            return response()->json([
                'success' => true,
                'message' => 'تم تعيين الطالب ' . $student->user->name . ' للمشرف ' . $supervisor->name
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ: ' . $e->getMessage()
            ], 500);
        }
    }

    public function removeStudentFromIjazah(Request $request)
    {
        try {
            $validated = $request->validate([
                'student_id' => 'required|exists:students,id',
                'supervisor_id' => 'required|exists:users,id',
            ]);

            $student = Student::findOrFail($validated['student_id']);

            Ijazah::where('student_id', $validated['student_id'])
                ->where('supervisor_id', $validated['supervisor_id'])
                ->delete();

            $student->update([
                'supervisor_id' => null,
                'has_ijazah' => false
            ]);

            return response()->json([
                'success' => true,
                'message' => 'تم حذف الطالب ' . $student->user->name . ' من المشرف'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ: ' . $e->getMessage()
            ], 500);
        }
    }
}