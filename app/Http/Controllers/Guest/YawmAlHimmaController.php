<?php

namespace App\Http\Controllers\Guest;

use App\Http\Controllers\Controller;
use App\Models\QuranPart;
use App\Models\Student;
use App\Models\YawmAlHimmaSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class YawmAlHimmaController extends Controller
{
    public function showForm()
    {
        $students = Student::with('user')->orderBy('id')->get()->map(function ($s) {
            return [
                'id' => $s->id,
                'name' => $s->user->name ?? '',
            ];
        })->values();

        $parts = QuranPart::orderBy('part_number')->get(['id', 'part_number', 'name', 'start_page', 'end_page']);

        return view('guest.yawm-al-himma.form', [
            'students' => $students,
            'parts' => $parts,
        ]);
    }

    public function showParts(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'supervisor_name' => 'required|string|max:255',
            'student_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('guest.yawm-al-himma.index')
                ->withErrors($validator)
                ->withInput();
        }

        $student = null;
        $studentName = '';

        if ($request->student_id !== 'manual') {
            $student = Student::with('user')->find($request->student_id);
            if (! $student) {
                return redirect()
                    ->route('guest.yawm-al-himma.index')
                    ->withErrors(['student_id' => 'الطالب غير موجود'])
                    ->withInput();
            }
            $studentName = $student->user->name ?? '';
        } else {
            $studentName = trim((string) $request->student_name_manual);
            if ($studentName === '') {
                return redirect()
                    ->route('guest.yawm-al-himma.index')
                    ->withErrors(['student_name_manual' => 'الرجاء إدخال اسم الطالب'])
                    ->withInput();
            }
        }

        $parts = QuranPart::orderBy('part_number')->get(['id', 'part_number', 'name', 'start_page', 'end_page']);

        return view('guest.yawm-al-himma.parts', [
            'supervisorName' => $request->supervisor_name,
            'student' => $student,
            'studentName' => $studentName,
            'parts' => $parts,
        ]);
    }

    public function showEvaluation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'supervisor_name' => 'required|string|max:255',
            'student_name' => 'required|string|max:255',
            'student_id' => 'nullable|integer',
            'selected_parts' => 'required|array|min:1',
            'selected_parts.*' => 'integer|min:1|max:30',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $selectedParts = array_map('intval', $request->selected_parts);
        sort($selectedParts);

        $parts = QuranPart::whereIn('part_number', $selectedParts)
            ->orderBy('part_number')
            ->get(['id', 'part_number', 'name', 'start_page', 'end_page']);

        return view('guest.yawm-al-himma.evaluate', [
            'supervisorName' => $request->supervisor_name,
            'studentId' => $request->student_id ?: null,
            'studentName' => $request->student_name,
            'parts' => $parts,
        ]);
    }

    public function save(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'supervisor_name' => 'required|string|max:255',
            'student_id' => 'nullable|integer',
            'student_name' => 'required|string|max:255',
            'session_date' => 'required|date',
            'parts' => 'required|array|min:1',
            'parts.*' => 'integer|min:1|max:30',
            'pages' => 'required|array',
            'pages.*.part_number' => 'required|integer',
            'pages.*.page_number' => 'required|integer|min:1',
            'pages.*.errors' => 'required|integer|min:0',
            'pages.*.hesitations' => 'required|integer|min:0',
            'notes' => 'nullable|string|max:2000',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $pages = $request->pages;
        $totalErrors = 0;
        $totalHesitations = 0;
        foreach ($pages as $p) {
            $totalErrors += (int) $p['errors'];
            $totalHesitations += (int) $p['hesitations'];
        }

        $session = YawmAlHimmaSession::create([
            'supervisor_name' => $request->supervisor_name,
            'student_id' => $request->student_id ?: null,
            'student_name' => $request->student_name,
            'parts' => array_values(array_map('intval', $request->parts)),
            'pages_data' => array_values($pages),
            'total_errors' => $totalErrors,
            'total_hesitations' => $totalHesitations,
            'session_date' => $request->session_date,
            'notes' => $request->notes,
        ]);

        return redirect()
            ->route('guest.yawm-al-himma.result', ['session' => $session->id]);
    }

    public function result(YawmAlHimmaSession $session)
    {
        return view('guest.yawm-al-himma.result', [
            'session' => $session,
        ]);
    }
}
