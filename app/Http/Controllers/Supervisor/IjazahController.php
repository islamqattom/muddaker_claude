<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\QuranPart;
use App\Models\Student;
use App\Models\Ijazah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class IjazahController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $supervisorId = $user ? $user->id : 0;

        $parts = QuranPart::orderBy('part_number')->get();

        $ijazahRecords = collect([]);
        try {
            $ijazahRecords = Ijazah::where('supervisor_id', $supervisorId)
                ->with(['student.user', 'quranPart'])
                ->get()
                ->groupBy('student_id');
        } catch (\Exception $e) {
            \Log::error('IjazahController index error: ' . $e->getMessage());
        }

        $students = collect([]);
        try {
            $students = Student::where('supervisor_id', $supervisorId)
                ->with('user')
                ->get();
        } catch (\Exception $e) {
            \Log::error('IjazahController students error: ' . $e->getMessage());
        }

        return view('supervisor.ijazah.index', compact('parts', 'students', 'ijazahRecords'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $supervisorId = $user ? $user->id : 0;

        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'parts' => 'required|array|min:1',
            'parts.*' => 'exists:quran_parts,id',
        ]);

        $student = Student::findOrFail($validated['student_id']);

        DB::transaction(function () use ($validated, $supervisorId, $student) {
            foreach ($validated['parts'] as $partId) {
                Ijazah::updateOrCreate(
                    [
                        'student_id' => $student->id,
                        'quran_part_id' => $partId,
                        'supervisor_id' => $supervisorId,
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
        $user = Auth::user();
        $supervisorId = $user ? $user->id : 0;

        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'quran_part_id' => 'required|exists:quran_parts,id',
        ]);

        Ijazah::where('student_id', $validated['student_id'])
            ->where('quran_part_id', $validated['quran_part_id'])
            ->where('supervisor_id', $supervisorId)
            ->delete();

        return redirect()->back()->with('success', 'تم حذف تسجيل الإجازة');
    }
}