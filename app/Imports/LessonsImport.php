<?php

namespace App\Imports;

use App\Models\Lesson;
use App\Models\Subject;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Events\AfterImport;

class LessonsImport implements ToCollection, WithStartRow, WithChunkReading, WithEvents
{
    use Importable;

    public int $createdCount = 0;
    public int $skippedCount = 0;
    public array $errors = [];
    protected int $nextOrder = 0;

    public function __construct(public Subject $subject)
    {
        $this->nextOrder = (int) Lesson::where('subject_id', $subject->id)->max('sort_order') + 1;
    }

    public function startRow(): int
    {
        return 2;
    }

    public function chunkSize(): int
    {
        return 100;
    }

    public function collection(Collection $rows)
    {
        $user = Auth::user();
        if (!$user) {
            $this->errors[] = 'لم يتم العثور على المستخدم';
            return;
        }

        if ($user->role !== 'admin' && $this->subject->created_by !== $user->id) {
            $this->errors[] = 'لا تملك صلاحية الإضافة لهذه المادة';
            return;
        }

        foreach ($rows as $index => $row) {
            $rowNum = $index + 2;
            $title = trim((string) ($row[0] ?? ''));
            $content = trim((string) ($row[1] ?? ''));
            $dateRaw = trim((string) ($row[2] ?? ''));
            $startTime = trim((string) ($row[3] ?? ''));
            $endTime = trim((string) ($row[4] ?? ''));
            $status = strtolower(trim((string) ($row[5] ?? 'pending')));
            $notes = trim((string) ($row[6] ?? ''));

            if ($title === '') {
                $this->skippedCount++;
                $this->errors[] = "الصف {$rowNum}: العنوان مطلوب - تم تخطيه";
                continue;
            }

            $lessonDate = null;
            if ($dateRaw !== '') {
                try {
                    if (is_numeric($dateRaw)) {
                        $lessonDate = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($dateRaw)->format('Y-m-d');
                    } else {
                        $lessonDate = Carbon::createFromFormat('Y-m-d', $dateRaw)->format('Y-m-d');
                    }
                } catch (\Exception $e) {
                    try {
                        $lessonDate = Carbon::parse($dateRaw)->format('Y-m-d');
                    } catch (\Exception $e2) {
                        $this->skippedCount++;
                        $this->errors[] = "الصف {$rowNum}: تنسيق التاريخ غير صحيح ('{$dateRaw}') - تم تخطيه";
                        continue;
                    }
                }
            }

            $startTimeFormatted = null;
            if ($startTime !== '') {
                if (is_numeric($startTime) && $startTime < 1) {
                    $seconds = $startTime * 86400;
                    $startTimeFormatted = gmdate('H:i', $seconds);
                } elseif (preg_match('/^(\d{1,2}):(\d{2})$/', $startTime, $m)) {
                    $startTimeFormatted = sprintf('%02d:%02d', $m[1], $m[2]);
                } else {
                    try {
                        $startTimeFormatted = Carbon::parse($startTime)->format('H:i');
                    } catch (\Exception $e) {
                        $this->skippedCount++;
                        $this->errors[] = "الصف {$rowNum}: تنسيق وقت البداية غير صحيح ('{$startTime}') - تم تخطيه";
                        continue;
                    }
                }
            }

            $endTimeFormatted = null;
            if ($endTime !== '') {
                if (is_numeric($endTime) && $endTime < 1) {
                    $seconds = $endTime * 86400;
                    $endTimeFormatted = gmdate('H:i', $seconds);
                } elseif (preg_match('/^(\d{1,2}):(\d{2})$/', $endTime, $m)) {
                    $endTimeFormatted = sprintf('%02d:%02d', $m[1], $m[2]);
                } else {
                    try {
                        $endTimeFormatted = Carbon::parse($endTime)->format('H:i');
                    } catch (\Exception $e) {
                        $endTimeFormatted = null;
                    }
                }
            }

            if (!in_array($status, ['pending', 'completed', 'cancelled'], true)) {
                $status = 'pending';
            }

            try {
                Lesson::create([
                    'subject_id' => $this->subject->id,
                    'created_by' => $user->id,
                    'title' => $title,
                    'content' => $content ?: null,
                    'lesson_date' => $lessonDate,
                    'start_time' => $startTimeFormatted,
                    'end_time' => $endTimeFormatted,
                    'status' => $status,
                    'notes' => $notes ?: null,
                    'sort_order' => $this->nextOrder++,
                    'completed_at' => $status === 'completed' ? now() : null,
                ]);
                $this->createdCount++;
            } catch (\Exception $e) {
                $this->skippedCount++;
                $this->errors[] = "الصف {$rowNum}: فشل الحفظ - {$e->getMessage()}";
            }
        }
    }

    public function registerEvents(): array
    {
        return [
            AfterImport::class => function () {
            },
        ];
    }
}
