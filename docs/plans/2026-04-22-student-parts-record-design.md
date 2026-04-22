# Student Parts Record Page — Design

## Context

Supervisors need a dedicated page to view a student's complete Quran memorization record broken down by the 30 Quran parts. Currently, student progress is fragmented across recitation sessions and current progress fields. This page consolidates that data, shows completion status and evaluation scores per part, and allows supervisors to manually override auto-calculated values.

## Design

### 1. Page Structure

- **URL**: `/supervisor/student-parts`
- **Top**: Student search/select dropdown (searches by student name or number)
- **Below**: Student info card (name, level, supervisor) — displayed once a student is selected
- **Main content**: Table with 30 rows (one per Quran part):
  - Part number (1–30)
  - Part name (Arabic, e.g. "الجزء الأول")
  - Status (مُكمَل / غير مُكمَل — completed / not completed)
  - Total errors
  - Total hesitations
  - Average score
  - Last recitation date
  - Actions column with edit button per row
- **Edit flow**: Clicking edit opens an inline edit mode or modal for that part's data

### 2. Auto-Fill Logic

On page load for a given student:
1. Query `recitation_sessions` filtered by `student_id`, `type IN ('new_memorization', 'review')`, `status = 'passed'`
2. For each part the student has passed, aggregate errors/hesitations and calculate average score
3. Also check `current_part` and `memorized_parts` fields as secondary confirmation
4. If a supervisor has previously made a manual override for a part (`is_manual_override = true`), show the override values instead of auto-calculated ones

### 3. Data Model

**New table: `student_part_records`**

| Column | Type | Description |
|--------|------|-------------|
| `id` | bigint | Primary key |
| `student_id` | bigint | FK → students |
| `part_number` | tinyint | 1–30 |
| `is_completed` | boolean | Has the student completed this part |
| `total_errors` | int | Aggregated error count |
| `total_hesitations` | int | Aggregated hesitation count |
| `total_score` | decimal(5,2) | Aggregated/average score |
| `last_recitation_date` | date | Date of last recitation for this part |
| `is_manual_override` | boolean | True if supervisor manually edited |
| `created_at` | timestamp | |
| `updated_at` | timestamp | |

**Unique constraint**: `(student_id, part_number)`

### 4. Key Components

| Component | Path |
|-----------|------|
| Controller | `app/Http/Controllers/Supervisor/StudentPartRecordController.php` |
| Model | `app/Models/StudentPartRecord.php` |
| Migration | `database/migrations/YYYY_MM_DD_HHMMSS_create_student_part_records_table.php` |
| View | `resources/views/supervisor/student-parts/index.blade.php` |
| Route | `GET/POST /supervisor/student-parts` (plus store/update methods) |

### 5. Supervisor Edit Behavior

When a supervisor edits a part:
- All fields are editable: status, errors, hesitations, score, last recitation date
- On save, set `is_manual_override = true` to lock the record from future auto-calculation overwrites
- Optional "Reset to Auto" button to clear override and re-calculate from recitation sessions

## Verification

1. Browse to `/supervisor/student-parts`
2. Search and select a student
3. Verify table shows all 30 parts
4. Verify completed parts are auto-filled with data from recitation sessions
5. Click edit on a row, modify fields, save — verify `is_manual_override` is set to true
6. Click "Reset to Auto" — verify auto-calculation re-populates from recitation sessions
7. Verify unauthorized students show empty/incomplete records
