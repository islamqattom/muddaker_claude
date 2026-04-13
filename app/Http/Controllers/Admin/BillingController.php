<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MonthlyFee;
use App\Models\ParentProfile;
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

        $fees = $query->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->paginate(50);

        $parents = ParentProfile::with('user')->get();
        $years = range(now()->year, now()->year - 2);
        $months = MonthlyFee::getMonthNames();

        return view('admin.billing.index', compact('fees', 'parents', 'years', 'months'));
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

        $fee = MonthlyFee::find($request->fee_id);
        if (!$fee) {
            return response()->json(['success' => false, 'message' => 'السجل غير موجود'], 404);
        }

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
