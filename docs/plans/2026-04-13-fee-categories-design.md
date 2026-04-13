# Fee Categories Design

## Context

The admin billing system currently uses a single global `billing_monthly_fee_per_student` setting — one fixed fee amount for all students. The requirement is to allow different students to have different fee amounts (e.g., some pay $10/month, some $15/month), organized into fee categories managed by the super admin, with reporting support per category.

## Database Schema

### New Table: `fee_categories`
| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | |
| name | string | e.g., "Standard $10", "Premium $15" |
| amount | decimal(10,2) | fee amount per student per month |
| is_active | boolean | default true — inactive categories cannot be assigned |
| created_at | timestamp | |
| updated_at | timestamp | |

### Changes to `students` table
- Add `fee_category_id` → `fee_categories(id)` — nullable (existing students may not yet have a category)

### Changes to `monthly_fees` table
- Add `fee_amount` column (decimal 10,2) — stores the fee amount at billing creation time for historical accuracy

### Deprecation
- The global `billing_monthly_fee_per_student` setting can remain for backward compat but will no longer be used for billing generation once categories are in use.

## Architecture

### Models
- **FeeCategory**: `id`, `name`, `amount`, `is_active`; hasMany `Student`
- **Student**: add `feeCategory()` belongsTo relationship; add `fee_category_id` column
- **MonthlyFee**: add `fee_amount` column; add `feeCategory()` belongsTo through student

### Relationships
```
FeeCategory (1) → (many) Student
Student (1) → (many) MonthlyFee
MonthlyFee → Student → FeeCategory (for reporting via student)
```

## Admin UI

### 1. Fee Categories Management (new page: `/admin/billing/fee-categories`)
- **List view**: table of categories with name, amount, student count, total monthly revenue
- **Create/Edit modal or page**: name input, amount input, is_active toggle
- **Delete**: only allowed if no students are assigned to the category
- **Actions**: create, edit, toggle active, delete

### 2. Student Create/Edit
- Add "Fee Category" dropdown (only shows active categories)
- Existing students without a category flagged in billing view

### 3. Billing Settings (`/admin/billing/settings`)
- Remove or deprecate `billing_monthly_fee_per_student` input
- Add notice: "Fee amounts are now managed via Fee Categories"

### 4. Billing Index (`/admin/billing`)
- No major changes to existing flow — fee amounts come from `fee_amount` column on `monthly_fees`
- Optional future enhancement: filter/group by fee category

### 5. Billing Reports (within Fee Categories list)
- Per category: student count, total monthly revenue = `amount × student_count`

## Billing Generation Flow

**When generating monthly fees:**
1. Fetch all active students
2. For each student, get `fee_category_id`
3. If no category assigned → skip and log a warning (or mark as "unassigned" in UI)
4. Create `MonthlyFee` record with `fee_amount = category.amount`
5. This preserves historical accuracy — even if category amount changes later, past billing records retain the correct amount

## Implementation Steps

1. Create `fee_categories` migration and model
2. Add `fee_category_id` to `students` table
3. Add `fee_amount` to `monthly_fees` table
4. Create `FeeCategoryController` (admin) with CRUD
5. Add routes for fee categories management
6. Create fee categories view (list, create, edit)
7. Update student create/edit to include fee category dropdown
8. Update billing generation to use category amount and store in `fee_amount`
9. Update billing settings page to deprecate global fee setting
10. Add student count and revenue display to fee categories list
11. Update parent billing portal to show fee category info (optional)
12. Run tests / verify end-to-end flow

## Verification

1. Create 2 fee categories ($10 and $15)
2. Assign different students to different categories
3. Generate monthly billing — verify amounts match categories
4. Check that `fee_amount` is stored correctly on `monthly_fees`
5. Verify fee category report shows correct student count and revenue
6. Test edge cases: student with no category, editing category amount (existing billing should be unaffected)
