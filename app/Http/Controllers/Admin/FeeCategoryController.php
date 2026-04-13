<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FeeCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FeeCategoryController extends Controller
{
    public function index()
    {
        $categories = FeeCategory::withCount('students')
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($category) {
                $category->total_monthly_revenue = $category->amount * $category->students_count;
                return $category;
            });

        return view('admin.billing.fee-categories', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
        ]);

        try {
            $category = FeeCategory::create([
                'name' => $validated['name'],
                'amount' => $validated['amount'],
                'is_active' => true,
            ]);

            Log::info('Fee category created', [
                'admin_id' => auth()->id(),
                'category_id' => $category->id,
                'timestamp' => now()->toISOString(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'تم إضافة فئة الرسوم بنجاح',
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create fee category', [
                'admin_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء إنشاء فئة الرسوم',
            ], 500);
        }
    }

    public function update(Request $request, FeeCategory $feeCategory)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
        ]);

        try {
            $feeCategory->update([
                'name' => $validated['name'],
                'amount' => $validated['amount'],
            ]);

            Log::info('Fee category updated', [
                'admin_id' => auth()->id(),
                'category_id' => $feeCategory->id,
                'timestamp' => now()->toISOString(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'تم تحديث فئة الرسوم بنجاح',
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update fee category', [
                'admin_id' => auth()->id(),
                'category_id' => $feeCategory->id,
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تحديث فئة الرسوم',
            ], 500);
        }
    }

    public function toggleActive(FeeCategory $feeCategory)
    {
        try {
            $feeCategory->update(['is_active' => !$feeCategory->is_active]);

            Log::info('Fee category toggled', [
                'admin_id' => auth()->id(),
                'category_id' => $feeCategory->id,
                'is_active' => $feeCategory->is_active,
                'timestamp' => now()->toISOString(),
            ]);

            return response()->json([
                'success' => true,
                'message' => $feeCategory->is_active ? 'تم تفعيل فئة الرسوم' : 'تم تعطيل فئة الرسوم',
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'حدث خطأ'], 500);
        }
    }

    public function destroy(FeeCategory $feeCategory)
    {
        if ($feeCategory->students()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكن حذف فئة الرسوم لأنها مرتبطة بطلاب',
            ], 422);
        }

        try {
            $feeCategory->delete();

            Log::info('Fee category deleted', [
                'admin_id' => auth()->id(),
                'category_id' => $feeCategory->id,
                'timestamp' => now()->toISOString(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'تم حذف فئة الرسوم بنجاح',
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'حدث خطأ'], 500);
        }
    }
}
