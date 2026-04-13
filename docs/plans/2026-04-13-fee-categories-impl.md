# Fee Categories Implementation Plan

> **For Claude:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Add fee categories so admins can assign different students to different monthly fee amounts (e.g., $10 vs $15), managed entirely from the admin side.

**Architecture:** New `fee_categories` table linked to students. `MonthlyFee` records store the fee amount at creation time (denormalized for historical accuracy). Students get a `fee_category_id` foreign key. The global `billing_monthly_fee_per_student` setting is deprecated.

**Tech Stack:** Laravel 11, MySQL, Blade views, Bootstrap 5, vanilla JS fetch for AJAX.

---

## Task 1: Create fee_categories migration and model

**Files:**
- Create: `database/migrations/2026_04_13_000002_create_fee_categories_table.php`
- Create: `app/Models/FeeCategory.php`

**Step 1: Write migration**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fee_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('amount', 10, 2);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_categories');
    }
};
```

**Step 2: Write FeeCategory model**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FeeCategory extends Model
{
    protected $fillable = ['name', 'amount', 'is_active'];

    protected $casts = [
        'amount' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
```

**Step 3: Run migration**
```
php artisan migrate
```

**Step 4: Commit**
```
git add database/migrations/2026_04_13_000002_create_fee_categories_table.php app/Models/FeeCategory.php
git commit -m "feat(billing): add FeeCategory model and migration"
```

---

## Task 2: Add fee_category_id to students table

**Files:**
- Create: `database/migrations/2026_04_13_000003_add_fee_category_to_students_table.php`

**Step 1: Write migration**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->foreignId('fee_category_id')
                ->nullable()
                ->after('supervisor_id')
                ->constrained('fee_categories')
                ->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropForeign(['fee_category_id']);
            $table->dropColumn('fee_category_id');
        });
    }
};
```

**Step 2: Run migration**
```
php artisan migrate
```

**Step 3: Commit**
```
git add database/migrations/2026_04_13_000003_add_fee_category_to_students_table.php
git commit -m "feat(billing): add fee_category_id to students table"
```

---

## Task 3: Add fee_amount to monthly_fees table

**Files:**
- Create: `database/migrations/2026_04_13_000004_add_fee_amount_to_monthly_fees_table.php`

**Step 1: Write migration**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('monthly_fees', function (Blueprint $table) {
            $table->decimal('fee_amount', 10, 2)->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('monthly_fees', function (Blueprint $table) {
            $table->dropColumn('fee_amount');
        });
    }
};
```

**Step 2: Run migration**
```
php artisan migrate
```

**Step 3: Commit**
```
git add database/migrations/2026_04_13_000004_add_fee_amount_to_monthly_fees_table.php
git commit -m "feat(billing): add fee_amount column to monthly_fees"
```

---

## Task 4: Add feeCategory() relationship to Student model

**Files:**
- Modify: `app/Models/Student.php`

**Step 1: Read current Student.php to find where to add the relationship**

Add this method to `Student.php`:
```php
public function feeCategory(): BelongsTo
{
    return $this->belongsTo(FeeCategory::class);
}
```

Also add the import at the top:
```php
use Illuminate\Database\Eloquent\Relations\BelongsTo;
```

**Step 2: Commit**
```
git add app/Models/Student.php
git commit -m "feat(billing): add feeCategory() relationship to Student"
```

---

## Task 5: Update MonthlyFee model — fillable, casts, relationship

**Files:**
- Modify: `app/Models/MonthlyFee.php`

**Step 1: Add to $fillable array**
Add `'fee_amount'` to the existing `$fillable` array (line ~16 of MonthlyFee.php).

**Step 2: Add casts**
Add to `$casts` array:
```php
'fee_amount' => 'decimal:2',
```

**Step 3: Add relationship method**
```php
public function feeCategory(): BelongsTo
{
    return $this->belongsTo(FeeCategory::class);
}
```

**Step 4: Add import**
```php
use Illuminate\Database\Eloquent\Relations\BelongsTo;
```

**Step 5: Commit**
```
git add app/Models/MonthlyFee.php
git commit -m "feat(billing): add fee_amount to MonthlyFee fillable, casts, and relationship"
```

---

## Task 6: Create FeeCategoryController

**Files:**
- Create: `app/Http/Controllers/Admin/FeeCategoryController.php`

**Step 1: Write the controller**

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FeeCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
```

**Step 2: Commit**
```
git add app/Http/Controllers/Admin/FeeCategoryController.php
git commit -m "feat(billing): add FeeCategoryController with CRUD operations"
```

---

## Task 7: Add fee categories routes

**Files:**
- Modify: `routes/web.php`

**Step 1: Find the admin billing routes block (around line 89) and add inside the group:**

```php
Route::get('/fee-categories', [Admin\FeeCategoryController::class, 'index'])->name('billing.feeCategories');
Route::post('/fee-categories', [Admin\FeeCategoryController::class, 'store'])->name('billing.feeCategories.store');
Route::put('/fee-categories/{feeCategory}', [Admin\FeeCategoryController::class, 'update'])->name('billing.feeCategories.update');
Route::post('/fee-categories/{feeCategory}/toggle-active', [Admin\FeeCategoryController::class, 'toggleActive'])->name('billing.feeCategories.toggleActive');
Route::delete('/fee-categories/{feeCategory}', [Admin\FeeCategoryController::class, 'destroy'])->name('billing.feeCategories.destroy');
```

**Step 2: Commit**
```
git add routes/web.php
git commit -m "feat(billing): add fee categories routes"
```

---

## Task 8: Create fee categories admin view

**Files:**
- Create: `resources/views/admin/billing/fee-categories.blade.php`

**Step 1: Write the view** — following the same layout/structure as `admin/billing/index.blade.php` and `settings.blade.php`:

```blade
@extends('layouts.app')
@section('title')فئات الرسوم @endsection
@section('page-title')إدارة فئات الرسوم@endsection
@section('role-label')مدير@endsection

@section('content')
@include('admin.partials.sidebar')

<div class="container-fluid py-4">

    {{-- Page Header --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="fas fa-tags me-2"></i>فئات الرسوم الشهرية</h6>
                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#createCategoryModal">
                        <i class="fas fa-plus me-1"></i>إضافة فئة جديدة
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Categories Table --}}
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    @if($categories->isEmpty())
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-tags fa-3x mb-3"></i>
                            <p>لا توجد فئات رسوم حتى الآن</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>اسم الفئة</th>
                                        <th>الرسوم الشهرية</th>
                                        <th>عدد الطلاب</th>
                                        <th>إجمالي الدخل الشهري</th>
                                        <th>الحالة</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($categories as $category)
                                        <tr>
                                            <td>{{ $category->name }}</td>
                                            <td>{{ number_format($category->amount, 2) }} دينار</td>
                                            <td>{{ $category->students_count }}</td>
                                            <td>{{ number_format($category->total_monthly_revenue, 2) }} دينار</td>
                                            <td>
                                                @if($category->is_active)
                                                    <span class="badge bg-success">مفعّل</span>
                                                @else
                                                    <span class="badge bg-secondary">معطّل</span>
                                                @endif
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary me-1"
                                                    onclick="editCategory({{ $category->id }}, '{{ $category->name }}', {{ $category->amount }})">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-{{ $category->is_active ? 'warning' : 'success' }} me-1"
                                                    onclick="toggleActive({{ $category->id }})">
                                                    <i class="fas fa-{{ $category->is_active ? 'ban' : 'check' }}"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger"
                                                    onclick="deleteCategory({{ $category->id }})"
                                                    @if($category->students_count > 0) disabled title="مرتبط بطلاب" @endif>
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

</div>

{{-- Create Modal --}}
<div class="modal fade" id="createCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">إضافة فئة رسوم جديدة</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="createCategoryForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">اسم الفئة <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required placeholder="مثال: فئة standard">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">الرسوم الشهرية (دينار) <span class="text-danger">*</span></label>
                        <input type="number" name="amount" class="form-control" required min="0" step="0.01" placeholder="مثال: 10">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary">حفظ</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Edit Modal --}}
<div class="modal fade" id="editCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">تعديل فئة الرسوم</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editCategoryForm">
                @csrf
                @method('PUT')
                <input type="hidden" name="id" id="edit_category_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">اسم الفئة <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="edit_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">الرسوم الشهرية (دينار) <span class="text-danger">*</span></label>
                        <input type="number" name="amount" id="edit_amount" class="form-control" required min="0" step="0.01">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary">تحديث</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('script')
<script>
const CSRF = document.querySelector('meta[name="csrf-token"]').content;

// Create
document.getElementById('createCategoryForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const form = new FormData(this);
    const response = await fetch('{{ route("admin.billing.feeCategories.store") }}', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': CSRF },
        body: form
    });
    const data = await response.json();
    if (data.success) {
        location.reload();
    } else {
        alert(data.message);
    }
});

// Edit - populate modal
function editCategory(id, name, amount) {
    document.getElementById('edit_category_id').value = id;
    document.getElementById('edit_name').value = name;
    document.getElementById('edit_amount').value = amount;
    new bootstrap.Modal(document.getElementById('editCategoryModal')).show();
}

// Update
document.getElementById('editCategoryForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const id = document.getElementById('edit_category_id').value;
    const form = new FormData(this);
    const response = await fetch('/admin/billing/fee-categories/' + id, {
        method: 'POST', // Laravel doesn't support PUT from FormData, use route helper
        headers: { 'X-CSRF-TOKEN': CSRF, 'X-HTTP-METHOD-OVERRIDE': 'PUT' },
        body: form
    });
    const data = await response.json();
    if (data.success) {
        location.reload();
    } else {
        alert(data.message);
    }
});

// Toggle active
async function toggleActive(id) {
    if (!confirm('هل أنت متأكد من تغيير حالة هذه الفئة؟')) return;
    const response = await fetch('/admin/billing/fee-categories/' + id + '/toggle-active', {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': CSRF }
    });
    const data = await response.json();
    if (data.success) {
        location.reload();
    } else {
        alert(data.message);
    }
}

// Delete
async function deleteCategory(id) {
    if (!confirm('هل أنت متأكد من حذف هذه الفئة؟')) return;
    const response = await fetch('/admin/billing/fee-categories/' + id, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': CSRF }
    });
    const data = await response.json();
    if (data.success) {
        location.reload();
    } else {
        alert(data.message);
    }
}
</script>
@endsection
```

**Step 2: Commit**
```
git add resources/views/admin/billing/fee-categories.blade.php
git commit -m "feat(billing): add fee categories admin view"
```

---

## Task 9: Add sidebar link to fee categories page

**Files:**
- Modify: `resources/views/admin/partials/sidebar.blade.php`

**Step 1: Find the billing section in the sidebar and add a link:**

In the sidebar, find where billing links exist (e.g., `<li><a href="{{ route('admin.billing.index') }}">...</a></li>`) and add:

```blade
<li><a href="{{ route('admin.billing.feeCategories') }}"><i class="fas fa-tags me-2"></i>فئات الرسوم</a></li>
```

**Step 2: Commit**
```
git add resources/views/admin/partials/sidebar.blade.php
git commit -m "feat(billing): link fee categories page in admin sidebar"
```

---

## Task 10: Add fee category dropdown to student create/edit

**Files:**
- Modify: `app/Http/Controllers/Admin/StudentController.php` — `create()` and `edit()` methods
- Modify: `resources/views/admin/students/create.blade.php`
- Modify: `resources/views/admin/students/edit.blade.php`
- Modify: `app/Http/Controllers/Admin/StudentController.php` — `store()` and `update()` validation & logic

**Step 1: Update StudentController — create() method**

Add to the `create()` method, after fetching levels and supervisors:
```php
$feeCategories = FeeCategory::where('is_active', true)->orderBy('name')->get();
return view('admin.students.create', compact('levels', 'supervisors', 'feeCategories'));
```

**Step 2: Update StudentController — edit() method**

Add:
```php
$feeCategories = FeeCategory::where('is_active', true)->orderBy('name')->get();
return view('admin.students.edit', compact('student', 'levels', 'supervisors', 'feeCategories'));
```

**Step 3: Update StudentController — store() validation**

Add to the validation array:
```php
'fee_category_id' => 'nullable|exists:fee_categories,id',
```

And in the `Student::create()` call inside the transaction, add:
```php
'fee_category_id' => $request->fee_category_id,
```

**Step 4: Update StudentController — update() validation**

Add to validation:
```php
'fee_category_id' => 'nullable|exists:fee_categories,id',
```

And in `$student->update([...])` inside the transaction, add:
```php
'fee_category_id' => $request->fee_category_id,
```

**Step 5: Update student create.blade.php — add dropdown**

In the "Student Info Section" (around line 117), add a new `col-md-6` div alongside the level dropdown:
```blade
<div class="col-md-6">
    <div class="mb-3">
        <label class="form-label">فئة الرسوم</label>
        <select name="fee_category_id" class="form-select">
            <option value="">— بدون فئة —</option>
            @foreach($feeCategories as $category)
                <option value="{{ $category->id }}" {{ old('fee_category_id') == $category->id ? 'selected' : '' }}>
                    {{ $category->name }} ({{ number_format($category->amount, 2) }} دينار)
                </option>
            @endforeach
        </select>
    </div>
</div>
```

**Step 6: Update student edit.blade.php — add dropdown**

Same as above, but with `{{ $student->fee_category_id == $category->id ? 'selected' : '' }}` for the selected state, and pre-select the student's current category.

**Step 7: Commit**
```
git add app/Http/Controllers/Admin/StudentController.php resources/views/admin/students/create.blade.php resources/views/admin/students/edit.blade.php
git commit -m "feat(billing): add fee category dropdown to student create/edit"
```

---

## Task 11: Update BillingGenerate command to use fee category amount

**Files:**
- Modify: `app/Console/Commands/BillingGenerate.php`

**Step 1: Update the handle() method**

Change the `MonthlyFee::create()` block from:
```php
MonthlyFee::create([
    'parent_id' => $parent->id,
    'student_id' => $student->id,
    'year' => $year,
    'month' => $month,
    'status' => MonthlyFee::STATUS_UNPAID,
]);
```

To:
```php
$feeAmount = $student->feeCategory?->amount ?? 0;

MonthlyFee::create([
    'parent_id' => $parent->id,
    'student_id' => $student->id,
    'year' => $year,
    'month' => $month,
    'status' => MonthlyFee::STATUS_UNPAID,
    'fee_amount' => $feeAmount,
]);
```

Also add a counter for students without a category:
```php
$unassigned = 0;
// Inside the loop after checking feeAmount:
if ($feeAmount <= 0) {
    $unassigned++;
    Log::warning('Student has no fee category', [
        'student_id' => $student->id,
        'year' => $year,
        'month' => $month,
    ]);
}
```

And update the end message:
```php
$this->info("Done! Created: {$created}, Skipped (already exist): {$skipped}, Failed: {$failed}, Unassigned (no category): {$unassigned}");
```

**Step 2: Commit**
```
git add app/Console/Commands/BillingGenerate.php
git commit -m "feat(billing): use fee category amount in BillingGenerate command"
```

---

## Task 12: Update BillingController settings — deprecate global fee setting

**Files:**
- Modify: `app/Http/Controllers/Admin/BillingController.php` — `settings()` method
- Modify: `resources/views/admin/billing/settings.blade.php`

**Step 1: In settings() method**, remove `'billing_monthly_fee_per_student'` from the compact list (it will still be in config for backward compat but not shown in UI).

**Step 2: In settings.blade.php**, find the fee per student input section and replace it with a notice box:**

Replace the fee input with:
```blade
<div class="alert alert-info">
    <i class="fas fa-info-circle me-2"></i>
    <strong>ملاحظة:</strong> يتم الآن تحديد الرسوم الشهرية عبر <a href="{{ route('admin.billing.feeCategories') }}">فئات الرسوم</a>. قم بإضافة فئات رسوم وإassign الطلاب إليها.
</div>
```

**Step 3: Commit**
```
git add app/Http/Controllers/Admin/BillingController.php resources/views/admin/billing/settings.blade.php
git commit -m "feat(billing): deprecate global fee setting in favor of fee categories"
```

---

## Task 13: Update admin billing index — show fee amounts

**Files:**
- Modify: `resources/views/admin/billing/index.blade.php`

**Step 1: In the billing table, add a "Fee Amount" column**

Find the `<thead>` row and add:
```blade
<th>الرسوم</th>
```

Find each `<tr>` in the tbody and add a `<td>`:
```blade
<td>{{ $fee->fee_amount ? number_format($fee->fee_amount, 2) . ' د.ع' : '—' }}</td>
```

**Step 2: Commit**
```
git add resources/views/admin/billing/index.blade.php
git commit -m "feat(billing): display fee amount in admin billing index"
```

---

## Task 14: Update parent billing portal — show fee amount

**Files:**
- Modify: `app/Http/Controllers/ParentPortal/BillingController.php`
- Modify: `resources/views/parent/billing/index.blade.php`

**Step 1: In ParentPortal BillingController**, pass `feeCategories` to the view:
```php
$feeCategories = FeeCategory::pluck('name', 'id');
return view('parent.billing.index', compact(...) + ['feeCategories' => $feeCategories]);
```

Add the import:
```php
use App\Models\FeeCategory;
```

**Step 2: In parent/billing/index.blade.php**, inside each fee badge (near the month name), add:
```blade
<div class="fw-bold">{{ $months[$fee->month] }}</div>
@if($fee->fee_amount)
    <small>{{ number_format($fee->fee_amount, 2) }} د.ع</small>
@endif
```

**Step 3: Commit**
```
git add app/Http/Controllers/ParentPortal/BillingController.php resources/views/parent/billing/index.blade.php
git commit -m "feat(billing): show fee amounts in parent billing portal"
```

---

## Verification

1. **Create fee categories**: Go to `/admin/billing/fee-categories`, create two categories ($10 and $15)
2. **Assign to students**: Go to student edit, assign a category to each student
3. **Generate billing**: Run `php artisan billing:generate --year=2026 --month=4`
4. **Check amounts**: Go to `/admin/billing` — confirm fee amounts match categories
5. **Check history**: Confirm `fee_amount` column is populated on `monthly_fees` table
6. **Test edge cases**:
   - Student with no category → should create fee with `fee_amount = 0` and log warning
   - Edit category amount → existing billing records should keep their original `fee_amount`
   - Delete category → should fail if any student is assigned (foreign key restrict)
   - Toggle active → inactive categories should not appear in student dropdown

---

## File Summary

| Action | File |
|--------|------|
| Create | `database/migrations/2026_04_13_000002_create_fee_categories_table.php` |
| Create | `app/Models/FeeCategory.php` |
| Create | `database/migrations/2026_04_13_000003_add_fee_category_to_students_table.php` |
| Create | `database/migrations/2026_04_13_000004_add_fee_amount_to_monthly_fees_table.php` |
| Create | `app/Http/Controllers/Admin/FeeCategoryController.php` |
| Create | `resources/views/admin/billing/fee-categories.blade.php` |
| Modify | `app/Models/Student.php` — add `feeCategory()` relation |
| Modify | `app/Models/MonthlyFee.php` — add `fee_amount` fillable/casts/relation |
| Modify | `app/Console/Commands/BillingGenerate.php` — use category amount |
| Modify | `app/Http/Controllers/Admin/StudentController.php` — store/update fee_category_id |
| Modify | `app/Http/Controllers/Admin/BillingController.php` — remove fee per student from settings |
| Modify | `app/Http/Controllers/ParentPortal/BillingController.php` — pass fee categories to view |
| Modify | `routes/web.php` — add fee categories routes |
| Modify | `resources/views/admin/billing/settings.blade.php` — deprecate global fee |
| Modify | `resources/views/admin/billing/index.blade.php` — show fee amount column |
| Modify | `resources/views/admin/partials/sidebar.blade.php` — link to fee categories |
| Modify | `resources/views/admin/students/create.blade.php` — add fee category dropdown |
| Modify | `resources/views/admin/students/edit.blade.php` — add fee category dropdown |
| Modify | `resources/views/parent/billing/index.blade.php` — show fee amount per record |
