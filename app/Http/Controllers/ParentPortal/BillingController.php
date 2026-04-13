<?php

namespace App\Http\Controllers\ParentPortal;

use App\Http\Controllers\Controller;
use App\Models\MonthlyFee;
use Illuminate\Support\Facades\Auth;

class BillingController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $parentProfile = $user->parentProfile;

        if (!$parentProfile) {
            return redirect()->route('parent.dashboard')->with('error', 'لا يوجد ملف ولي الأمر مرتبط بحسابك');
        }

        $students = $parentProfile->students()->with(['user', 'level'])->get();

        if ($students->isEmpty()) {
            return view('parent.billing.index', [
                'studentFees' => [],
                'months' => MonthlyFee::getMonthNames(),
                'currentYear' => now()->year,
                'totalPaidThisYear' => 0,
                'totalUnpaid' => 0,
            ]);
        }

        $studentIds = $students->pluck('id')->toArray();

        // Get fees for all children in one query, filtered by student IDs
        $fees = MonthlyFee::where('parent_id', $parentProfile->id)
            ->whereIn('student_id', $studentIds)
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();

        // Group by student
        $studentFees = [];
        foreach ($students as $student) {
            $studentFees[$student->id] = [
                'student' => $student,
                'fees' => $fees->where('student_id', $student->id)->values(),
            ];
        }

        // Calculate summary
        $currentYear = now()->year;
        $currentMonth = now()->month;
        $feeAmount = (float) config('app.' . MonthlyFee::CONFIG_FEE_PER_STUDENT, 0);

        $totalPaidThisYear = $fees
            ->where('year', $currentYear)
            ->where('status', MonthlyFee::STATUS_PAID)
            ->count() * $feeAmount;

        $totalUnpaid = $fees
            ->filter(function ($fee) use ($currentYear, $currentMonth) {
                if ($fee->status !== MonthlyFee::STATUS_UNPAID) {
                    return false;
                }
                return $fee->year < $currentYear
                    || ($fee->year === $currentYear && $fee->month <= $currentMonth);
            })
            ->count() * $feeAmount;

        return view('parent.billing.index', compact(
            'studentFees',
            'months',
            'currentYear',
            'totalPaidThisYear',
            'totalUnpaid'
        ));
    }
}
