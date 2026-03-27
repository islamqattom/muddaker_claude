# Warning Automation: Threshold-Based Auto-Drafts

## Context

The system tracks Quran recitation and attendance for students across multiple supervisors. Currently, supervisors manually create warnings by navigating to a student, fetching attendance/recitation data, and filling out the warning form — a time-consuming multi-step process. With 50+ students per supervisor, this is a significant workload burden.

**Goal**: Automatically detect at-risk students based on configurable thresholds and generate warning drafts that supervisors can review and approve with one click.

---

## 1. Warning Rules System

### Database: `warning_rules` table

| Field | Type | Description |
|-------|------|-------------|
| `id` | bigint | Primary key |
| `name` | string | Arabic rule name (e.g., "متأخر في الحضور") |
| `type` | enum | `attendance`, `recitation`, `general` |
| `condition_type` | enum | `absent_days`, `late_days`, `failed_sessions`, `not_recitate_count` |
| `threshold` | integer | Minimum count to trigger (e.g., 3) |
| `period_days` | integer | Lookback period in days (e.g., 30 for last month) |
| `level_ids` | json | Optional: only apply to specific levels (null = all) |
| `is_active` | boolean | Enable/disable rule |
| `created_by` | bigint | Admin who created the rule |
| `created_at`, `updated_at` | timestamps | |

**Example rules:**
- If student has **3+ absent days** in last **30 days** → attendance warning draft
- If student has **5+ failed recitation sessions** in last **30 days** → recitation warning draft
- If student has **5+ late arrivals** in last **14 days** → attendance warning draft

### Model: `WarningRule.php`
- Location: `app/Models/WarningRule.php`
- Traits: `HasFactory`, `ActivityLog` (if used), belongsTo `User` (createdBy)
- No special events/methods needed initially

---

## 2. Warning Drafts System

### Database: `warning_drafts` table

| Field | Type | Description |
|-------|------|-------------|
| `id` | bigint | Primary key |
| `warning_rule_id` | bigint | FK to rule that triggered this draft |
| `student_id` | bigint | FK to student |
| `supervisor_id` | bigint | FK to student's assigned supervisor |
| `status` | enum | `pending`, `approved`, `dismissed` |
| `title` | string | Pre-filled title from rule |
| `content` | text | Pre-filled content from rule template |
| `issue_date` | date | Pre-set to today |
| `from_date` | date | Start of data period (today - period_days) |
| `to_date` | date | End of data period (today) |
| `attendance_data` | json | Pre-fetched attendance records matching condition |
| `recitation_data` | json | Pre-fetched recitation sessions matching condition |
| `test_data` | json | Empty array (reserved for future) |
| `triggered_value` | integer | Actual count that triggered (e.g., 4) |
| `triggered_threshold` | integer | Rule threshold (e.g., 3) |
| `reviewed_by` | bigint | FK to supervisor who reviewed (nullable) |
| `reviewed_at` | timestamp | When reviewed (nullable) |
| `created_at`, `updated_at` | timestamps | |

### Model: `WarningDraft.php`
- Location: `app/Models/WarningDraft.php`
- Relationships: belongsTo `WarningRule`, belongsTo `Student`, belongsTo `Supervisor`, belongsTo `User` (reviewedBy)

---

## 3. Rule Evaluation Engine

### Artisan Command: `EvaluateWarningRules`
- Location: `app/Console/Commands/EvaluateWarningRules.php`
- Signature: `warning:evaluate-rules {--rule= : Specific rule ID to evaluate}`
- Runs daily via Laravel scheduler (in `app/Console/Kernel.php`)

**Logic:**
```
1. Load all active WarningRules
2. Group by condition_type for efficient querying
3. For each rule:
   a. Determine date range: to_date = today, from_date = today - period_days
   b. Query students matching condition:
      - absent_days/late_days: COUNT attendances WHERE status = ? AND date BETWEEN from_date AND to_date GROUP BY student_id HAVING COUNT >= threshold
      - failed_sessions/not_recitate_count: COUNT recitation_sessions WHERE status IN (failed, not_recitate) AND session_date BETWEEN from_date AND to_date GROUP BY student_id HAVING COUNT >= threshold
   c. Filter by level_ids if set
   d. For each triggering student:
      - Check no existing pending/approved draft exists for this rule+student
      - Check no dismissed draft from same rule within same period
      - Pre-fetch attendance/recitation data matching condition
      - Create WarningDraft with status=pending
      - Send notification to student's supervisor
```

### Scheduling
In `app/Console/Kernel.php`:
```php
$schedule->command('warning:evaluate-rules')->daily();
```

---

## 4. Supervisor Draft Review UI

### Routes (prefix: `supervisor/warning-drafts`)
| Method | URI | Action |
|--------|-----|--------|
| GET | / | `index` — list pending drafts |
| GET | /{id} | `show` — review draft details |
| POST | /{id}/approve | `approve` — convert to warning |
| POST | /{id}/dismiss | `dismiss` — dismiss draft |

### Controller: `WarningDraftController.php`
- Location: `app/Http/Controllers/Supervisor/WarningDraftController.php`
- Uses existing `Notification` model to notify supervisor of new drafts
- On approve: calls existing `WarningController::store()` logic (reuse the method)
- On dismiss: sets status=dismissed, reviewed_by=auth user, reviewed_at=now

### Views
- Location: `resources/views/supervisor/warning-drafts/`
- `index.blade.php`: Cards showing pending drafts (student name, rule name, triggered value, period)
- `show.blade.php`: Full draft details with attendance/recitation data tables + Approve/Dismiss buttons

---

## 5. Admin Warning Rules UI

### Routes (prefix: `admin/warning-rules`)
| Method | URI | Action |
|--------|-----|--------|
| GET | / | `index` — list all rules |
| GET | /create | `create` — show create form |
| POST | / | `store` — create rule |
| GET | /{id}/edit | `edit` — show edit form |
| PUT | /{id} | `update` — update rule |
| DELETE | /{id} | `destroy` — delete rule |
| POST | /{id}/toggle | `toggle` — enable/disable rule |
| POST | /test | `test` — show which students match a rule |

### Controller: `WarningRuleController.php`
- Location: `app/Http/Controllers/Admin/WarningRuleController.php`

### Views
- Location: `resources/views/admin/warning-rules/`
- `index.blade.php`: Table with toggle switches, edit/delete actions
- `form.blade.php`: Shared create/edit form
- `test-result.blade.php`: Shows list of students that match a rule

---

## 6. Notification

### Class: `WarningDraftCreated.php`
- Location: `app/Notifications/WarningDraftCreated.php`
- Channels: database (in-app)
- Notifies: student's assigned supervisor
- Message: "تنبيه جديد: تم إنشاء مسودة إنذار لـ [اسم الطالب] بناءً على قاعدة [اسم القاعدة]"

---

## 7. Integration with Existing Warning System

### On Draft Approval
When supervisor approves a draft, the system should reuse `WarningController::store()` logic:
1. Create `Warning` record with all the pre-fetched data from draft
2. Set `issued_by` to approving supervisor
3. Trigger parent notification (if enabled)
4. Delete or archive the draft

### Content Templates
Default warning content based on rule type:
- **Attendance (absent)**: "خلال الفترة من [from_date] إلى [to_date]، تغيب الطالب [name] عن [triggered_value] أيام من أصل [total_days]."
- **Attendance (late)**: "خلال الفترة من [from_date] إلى [to_date]، تأخر الطالب [name] في الحضور [triggered_value] مرات."
- **Recitation**: "خلال الفترة من [from_date] إلى [to_date]، لم يجتز الطالب [name] [triggered_value] حصص من أصل [total_sessions]."

---

## 8. Database Migrations

### Migration 1: `create_warning_rules_table`
Standard migration with columns as defined in section 1.

### Migration 2: `create_warning_drafts_table`
Standard migration with columns as defined in section 2.

---

## 9. Route Registration

In `routes/web.php`:
```php
// Supervisor Warning Drafts
Route::middleware(['auth', 'role:supervisor'])->prefix('supervisor')->group(function () {
    Route::resource('warning-drafts', WarningDraftController::class)->only(['index', 'show']);
    Route::post('warning-drafts/{id}/approve', [WarningDraftController::class, 'approve'])->name('warning-drafts.approve');
    Route::post('warning-drafts/{id}/dismiss', [WarningDraftController::class, 'dismiss'])->name('warning-drafts.dismiss');
});

// Admin Warning Rules
Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    Route::resource('warning-rules', WarningRuleController::class);
    Route::post('warning-rules/{id}/toggle', [WarningRuleController::class, 'toggle'])->name('warning-rules.toggle');
    Route::post('warning-rules/test', [WarningRuleController::class, 'test'])->name('warning-rules.test');
});
```

---

## 10. Verification Plan

1. **Admin creates a rule**: Navigate to Admin → Warning Rules → Create. Set "3+ absent days in 30 days" for attendance type. Verify rule appears in list.
2. **Seed test data**: Create attendance records with absent status for a student totaling 3+ in last 30 days.
3. **Run evaluation**: `php artisan warning:evaluate-rules`. Verify draft created for that student.
4. **Supervisor reviews**: Login as student's supervisor. Navigate to Warning Drafts. See draft card with student name and triggered value.
5. **Approve draft**: Click "Approve & Issue Warning". Verify actual Warning created with correct attendance_data populated.
6. **Parent notification**: Verify parent notification appears (if parent notifications enabled).
7. **No duplicate**: Run evaluation again. Verify no new draft created for same student+rule.
8. **Dismiss flow**: Create another draft, click Dismiss. Verify draft marked dismissed, not created again on re-run.
