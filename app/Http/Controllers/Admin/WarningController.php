<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Warning;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;

class WarningController extends Controller
{
    public function index(Request $request)
    {
        $warnings = Warning::with(['student.user', 'issuedBy'])
            ->when($request->type, function($query) use ($request) {
                return $query->where('type', $request->type);
            })
            ->latest()
            ->paginate(20);

        return view('admin.warnings.index', compact('warnings'));
    }

    public function show(Warning $warning)
    {
        $warning->load(['student.user', 'issuedBy']);
        return view('admin.warnings.show', compact('warning'));
    }

    public function destroy(Warning $warning)
    {
        $warning->delete();
        return redirect()->route('admin.warnings.index')->with('success', 'تم حذف الإنذار');
    }

    public function create()
    {
        $students = Student::with(['user', 'level'])->get();
        return view('admin.warnings.create', compact('students'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'type' => 'required|in:attendance,recitation,general',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'issue_date' => 'required|date',
        ]);

        $student = Student::findOrFail($request->student_id);
        $lastWarning = Warning::orderBy('id', 'desc')->first();
        $nextNumber = $lastWarning ? (int)substr($lastWarning->warning_number, -4) + 1 : 1;
        $warningNumber = 'WRN-' . date('Y') . '-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

        Warning::create([
            'student_id' => $request->student_id,
            'issued_by' => auth()->id(),
            'warning_number' => $warningNumber,
            'type' => $request->type,
            'title' => $request->title,
            'content' => $request->content,
            'issue_date' => $request->issue_date,
            'from_date' => $request->from_date,
            'to_date' => $request->to_date,
            'total_days' => $request->total_days,
            'absent_days' => $request->absent_days,
            'late_days' => $request->late_days,
            'total_sessions' => $request->total_sessions,
            'failed_sessions' => $request->failed_sessions,
            'additional_notes' => $request->additional_notes,
        ]);

        return redirect()->route('admin.warnings.index')->with('success', 'تم إضافة الإنذار بنجاح');
    }

    public function edit(Warning $warning)
    {
        $warning->load(['student.user', 'issuedBy']);
        $students = Student::with(['user', 'level'])->get();
        return view('admin.warnings.edit', compact('warning', 'students'));
    }

    public function update(Request $request, Warning $warning)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'type' => 'required|in:attendance,recitation,general',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'issue_date' => 'required|date',
        ]);

        $warning->update([
            'student_id' => $request->student_id,
            'type' => $request->type,
            'title' => $request->title,
            'content' => $request->content,
            'issue_date' => $request->issue_date,
            'from_date' => $request->from_date,
            'to_date' => $request->to_date,
            'total_days' => $request->total_days,
            'absent_days' => $request->absent_days,
            'late_days' => $request->late_days,
            'total_sessions' => $request->total_sessions,
            'failed_sessions' => $request->failed_sessions,
            'additional_notes' => $request->additional_notes,
        ]);

        return redirect()->route('admin.warnings.show', $warning)->with('success', 'تم تحديث الإنذار بنجاح');
    }
}
