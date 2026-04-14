<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MonthlyFee;
use App\Models\ParentProfile;
use App\Models\Student;
use App\Models\FeeCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class BillingController extends Controller
{
    public function index(Request $request)
    {
        $query = MonthlyFee::with(['parent.user', 'student', 'paidBy'])
            ->select('monthly_fees.*');

        // Filters
        if ($request->filled('year')) {
            $query->where('year', $request->year);
        } else {
            $query->where('year', now()->year);
        }

        if ($request->filled('month')) {
            $query->where('month', $request->month);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('parent_id')) {
            $query->where('parent_id', $request->parent_id);
        }

        if ($request->filled('fee_category_id')) {
            $query->whereHas('student', function ($q) use ($request) {
                $q->where('fee_category_id', $request->fee_category_id);
            });
        }

        $fees = $query->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->paginate(50);

        $parents = ParentProfile::with('user')->get();
        $years = range(now()->year, now()->year - 2);
        $months = MonthlyFee::getMonthNames();
        $feeCategories = FeeCategory::active()->orderBy('name')->get();

        $studentsForBulk = Student::with('user')->get()->map(fn($s) => ['id' => $s->id, 'name' => $s->user->name ?? 'غير معروف']);
        \Log::info('Students for bulk query result', ['count' => $studentsForBulk->count(), 'students' => $studentsForBulk->toArray()]);

        return view('admin.billing.index', compact('fees', 'parents', 'years', 'months', 'feeCategories', 'studentsForBulk'));
    }

    public function settings()
    {
        $settings = [
            'billing_monthly_fee_per_student' => (float) config('app.' . MonthlyFee::CONFIG_FEE_PER_STUDENT, 0),
            'billing_reminder_frequency_days' => (int) config('app.' . MonthlyFee::CONFIG_REMINDER_FREQUENCY, 7),
            'billing_reminder_enabled' => config('app.' . MonthlyFee::CONFIG_REMINDER_ENABLED, true) == '1',
        ];

        return view('admin.billing.settings', compact('settings'));
    }

    public function updateSettings(Request $request)
    {
        $request->validate([
            'billing_monthly_fee_per_student' => 'nullable|numeric|min:0',
            'billing_reminder_frequency_days' => 'nullable|integer|min:1|max:365',
        ]);

        $settingsToSave = [
            MonthlyFee::CONFIG_FEE_PER_STUDENT => $request->billing_monthly_fee_per_student ?? 0,
            MonthlyFee::CONFIG_REMINDER_FREQUENCY => $request->billing_reminder_frequency_days ?? 7,
            MonthlyFee::CONFIG_REMINDER_ENABLED => $request->boolean('billing_reminder_enabled') ? '1' : '0',
        ];

        $now = now();
        foreach ($settingsToSave as $key => $value) {
            $existing = DB::table('settings')->where('key', $key)->first();

            DB::table('settings')->updateOrInsert(
                ['key' => $key],
                [
                    'value' => $value,
                    'group' => 'billing',
                    'created_at' => $existing?->created_at ?? $now,
                    'updated_at' => $now,
                ]
            );

            config(['app.' . $key => $value]);
        }

        return redirect()->route('admin.billing.settings')->with('success', 'تم حفظ الإعدادات بنجاح');
    }

    public function markPaid(Request $request)
    {
        $request->validate([
            'fee_id' => 'required|exists:monthly_fees,id',
        ]);

        $fee = MonthlyFee::findOrFail($request->fee_id);

        try {
            $fee->markAsPaid(Auth::id());

            Log::info('Billing: fee marked as paid', [
                'admin_id' => Auth::id(),
                'fee_id' => $fee->id,
                'timestamp' => now()->toIso8601String(),
            ]);

            return response()->json(['success' => true, 'message' => 'تم تحديث حالة الدفع بنجاح']);
        } catch (\Exception $e) {
            Log::error('Failed to mark fee as paid', [
                'fee_id' => $request->fee_id,
                'admin_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);
            return response()->json(['success' => false, 'message' => 'حدث خطأ أثناء تحديث حالة الدفع'], 500);
        }
    }

    public function markPaidBulk(Request $request)
    {
        $request->validate([
            'fee_ids' => 'required|array|min:1',
            'fee_ids.*' => 'exists:monthly_fees,id',
        ]);

        $adminId = Auth::id();

        try {
            $affected = DB::transaction(function () use ($request, $adminId) {
                return MonthlyFee::whereIn('id', $request->fee_ids)
                    ->where('status', MonthlyFee::STATUS_UNPAID)
                    ->update([
                        'status' => MonthlyFee::STATUS_PAID,
                        'paid_at' => now(),
                        'paid_by' => $adminId,
                    ]);
            });

            Log::info('Billing: fees marked as paid (bulk)', [
                'admin_id' => $adminId,
                'fee_ids' => $request->fee_ids,
                'affected_count' => $affected,
                'requested_count' => count($request->fee_ids),
                'timestamp' => now()->toIso8601String(),
            ]);

            $notUpdated = count($request->fee_ids) - $affected;
            $message = "تم تحديث {$affected} سجل بنجاح";
            if ($notUpdated > 0) {
                $message .= " ({$notUpdated} لم يتم تحديثها - ربما كانت مدفوعة مسبقاً)";
            }

            return response()->json(['success' => true, 'message' => $message]);
        } catch (\Exception $e) {
            Log::error('Failed to bulk mark fees as paid', [
                'fee_ids' => $request->fee_ids,
                'admin_id' => $adminId,
                'error' => $e->getMessage(),
            ]);
            return response()->json(['success' => false, 'message' => 'حدث خطأ أثناء تحديث حالة الدفع'], 500);
        }
    }

    public function recordPayment(Request $request)
    {
        $rules = [
            'parent_id' => 'required|exists:parents,id',
            'student_id' => 'required|exists:students,id',
            'year' => 'required|integer|min:2020|max:' . (now()->year + 1),
            'month' => 'required|integer|min:1|max:12',
            'is_paid' => 'boolean',
            'paid_at' => $request->boolean('is_paid')
                ? 'required|date|before_or_equal:today'
                : 'nullable|date|before_or_equal:today',
        ];

        $request->validate($rules);

        $isPaid = $request->boolean('is_paid', false);

        $fee = MonthlyFee::forParentStudent($request->parent_id, $request->student_id)
            ->forPeriod($request->year, $request->month)
            ->first();

        if ($fee && $fee->status === MonthlyFee::STATUS_PAID && $isPaid) {
            return response()->json([
                'success' => false,
                'message' => 'هذا السجل مسجل كمدفوع مسبقاً'
            ], 422);
        }

        try {
            $logContext = [
                'admin_id' => Auth::id(),
                'parent_id' => $request->parent_id,
                'student_id' => $request->student_id,
                'year' => $request->year,
                'month' => $request->month,
                'is_paid' => $isPaid,
            ];

            if ($fee) {
                if ($isPaid) {
                    $fee->markAsPaid(Auth::id(), $request->paid_at);
                    $logContext['paid_at'] = $request->paid_at;
                } else {
                    $fee->markAsUnpaid();
                }
            } else {
                $student = Student::find($request->student_id);
                $feeAmount = $student->feeCategory?->amount ?? 0;
                MonthlyFee::create([
                    'parent_id' => $request->parent_id,
                    'student_id' => $request->student_id,
                    'year' => $request->year,
                    'month' => $request->month,
                    'status' => $isPaid ? MonthlyFee::STATUS_PAID : MonthlyFee::STATUS_UNPAID,
                    'fee_amount' => $feeAmount,
                    'paid_at' => $isPaid ? $request->paid_at : null,
                    'paid_by' => $isPaid ? Auth::id() : null,
                ]);
                if ($isPaid) {
                    $logContext['paid_at'] = $request->paid_at;
                }
            }

            Log::info('Billing: fee record saved', $logContext);

            return response()->json([
                'success' => true,
                'message' => $isPaid ? 'تم تسجيل الدفعة بنجاح' : 'تم تسجيل السجل كغير مدفوع'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to record payment', [
                'admin_id' => Auth::id(),
                'parent_id' => $request->parent_id,
                'student_id' => $request->student_id,
                'year' => $request->year,
                'month' => $request->month,
                'is_paid' => $isPaid,
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تسجيل الدفعة'
            ], 500);
        }
    }

    public function createBulkUnpaid(Request $request)
    {
        $request->validate([
            'year' => 'required|integer|min:2020|max:' . (now()->year + 1),
            'month' => 'required|integer|min:1|max:12',
            'student_ids' => 'nullable|array',
            'student_ids.*' => 'exists:students,id',
        ]);

        $year = $request->year;
        $month = $request->month;
        $studentIds = $request->input('student_ids', []);

        $studentsQuery = Student::with(['parents', 'feeCategory']);
        if (!empty($studentIds)) {
            $studentsQuery->whereIn('id', $studentIds);
        }
        $students = $studentsQuery->get();

        if ($students->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'لم يتم العثور على طلاب'], 422);
        }

        try {
            $result = ['created' => 0, 'skipped' => 0, 'noCategory' => 0, 'failed' => 0];

            DB::transaction(function () use ($students, $year, $month, &$result) {
                $existingKeys = MonthlyFee::forPeriod($year, $month)
                    ->select('parent_id', 'student_id')
                    ->get()
                    ->mapWithKeys(fn($f) => [$f->parent_id . '-' . $f->student_id => true]);

                foreach ($students as $student) {
                    $feeAmount = $student->feeCategory?->amount ?? 0;
                    if ($feeAmount <= 0) { $result['noCategory']++; continue; }

                    foreach ($student->parents as $parent) {
                        $key = $parent->id . '-' . $student->id;
                        if (isset($existingKeys[$key])) { $result['skipped']++; continue; }

                        try {
                            MonthlyFee::create([
                                'parent_id' => $parent->id,
                                'student_id' => $student->id,
                                'year' => $year,
                                'month' => $month,
                                'status' => MonthlyFee::STATUS_UNPAID,
                                'fee_amount' => $feeAmount,
                            ]);
                            $result['created']++;
                        } catch (\Exception $e) {
                            $result['failed']++;
                            Log::error('Failed to create bulk unpaid fee', [
                                'parent_id' => $parent->id,
                                'student_id' => $student->id,
                                'year' => $year,
                                'month' => $month,
                                'error' => $e->getMessage(),
                            ]);
                        }
                    }
                }
            });

            $message = "تم إنشاء {$result['created']} سجل بنجاح";
            if ($result['skipped'] > 0) $message .= " ({$result['skipped']} تم تخطيها - موجودة مسبقاً)";
            if ($result['noCategory'] > 0) $message .= " ({$result['noCategory']} تم تخطيها - بدون فئة رسوم)";
            if ($result['failed'] > 0) $message .= " ({$result['failed']} فشل)";

            return response()->json(['success' => true, 'message' => $message, 'counts' => $result]);
        } catch (\Exception $e) {
            Log::error('Bulk unpaid fee creation failed', [
                'admin_id' => Auth::id(),
                'year' => $year,
                'month' => $month,
                'student_count' => $students->count(),
                'error' => $e->getMessage(),
            ]);
            return response()->json(['success' => false, 'message' => 'حدث خطأ أثناء إنشاء السجلات'], 500);
        }
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'fee_id' => 'required|exists:monthly_fees,id',
        ]);

        $fee = MonthlyFee::find($request->fee_id);

        if (!$fee) {
            return response()->json(['success' => false, 'message' => 'السجل غير موجود'], 404);
        }

        try {
            $fee->delete();

            Log::info('Billing: fee deleted', [
                'admin_id' => Auth::id(),
                'fee_id' => $fee->id,
                'timestamp' => now()->toIso8601String(),
            ]);

            return response()->json(['success' => true, 'message' => 'تم حذف السجل بنجاح']);
        } catch (\Exception $e) {
            Log::error('Failed to delete fee', [
                'fee_id' => $request->fee_id,
                'admin_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);
            return response()->json(['success' => false, 'message' => 'حدث خطأ أثناء حذف السجل'], 500);
        }
    }

    public function getStudentsForParent($parentId)
    {
        $parent = ParentProfile::with('students.user:id,name')->find($parentId);

        if (!$parent) {
            return response()->json(['message' => 'Parent not found'], 404);
        }

        $students = $parent->students;
        return response()->json($students->map(fn($s) => [
            'id' => $s->id,
            'name' => $s->user->name
        ]));
    }

    public function history(Request $request)
    {
        $query = MonthlyFee::with(['parent.user', 'student', 'paidBy'])
            ->paid();

        if ($request->filled('year')) {
            $query->where('year', $request->year);
        }

        if ($request->filled('parent_id')) {
            $query->where('parent_id', $request->parent_id);
        }

        if ($request->filled('student_id')) {
            $query->where('student_id', $request->student_id);
        }

        $fees = $query->orderBy('paid_at', 'desc')->paginate(50);

        $parents = ParentProfile::with('user')->get();
        $years = range(now()->year, now()->year - 2);

        return view('admin.billing.history', compact('fees', 'parents', 'years'));
    }
}
