<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;
use App\Traits\HasArabicDay;

class RecitationSession extends Model
{
    use HasFactory, Auditable, HasArabicDay;

    public const TYPE_NEW_MEMORIZATION = 'new_memorization';
    public const TYPE_REVIEW = 'review';
    public const TYPE_UNMASTERED = 'unmastered';
    public const TYPE_RECITATION = 'recitation';
    public const TYPE_MAFSIL = 'mafsil';
    public const TYPE_NOT_RECITE = 'not_recitate';

    public const STATUS_PASSED = 'passed';
    public const STATUS_FAILED = 'failed';
    public const STATUS_FAILED_CONTINUED = 'failed_continued';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_NOT_RECITE = 'not_recitate';
    public const STATUS_INCOMPLETE = 'incomplete';

    public const TYPES = [
        self::TYPE_NEW_MEMORIZATION,
        self::TYPE_REVIEW,
        self::TYPE_UNMASTERED,
        self::TYPE_RECITATION,
        self::TYPE_MAFSIL,
        self::TYPE_NOT_RECITE,
    ];

    protected $fillable = [
        'student_id', 'student_name', 'mahd_name', 'supervisor_id', 'type', 'part_id',
        'start_page', 'end_page', 'total_pages',
        'total_errors', 'total_hesitations', 'status',
        'mafsil_type', 'max_score', 'final_score',
        'error_score', 'hesitation_score', 'fail_score',
        'unmastered_pages_count', 'session_date', 'day', 'notes',
        'additional_pages', 'repeated_pages'
    ];

    protected $casts = [
        'session_date' => 'date',
        'max_score' => 'float',
        'final_score' => 'float',
        'error_score' => 'float',
        'hesitation_score' => 'float',
        'fail_score' => 'float',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            if ($model->session_date && $model->session_date !== '') {
                $model->day = self::getArabicDay($model->session_date);
            }
        });
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function supervisor()
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    public function part()
    {
        return $this->belongsTo(QuranPart::class, 'part_id');
    }

    public function pages()
    {
        return $this->hasMany(RecitationPage::class, 'session_id');
    }

    public function getTestedPartNumbersAttribute()
    {
        if ($this->type === self::TYPE_MAFSIL) {
            $pages = $this->relationLoaded('pages') ? $this->pages : $this->pages()->get();

            return $pages->pluck('page_number')
                ->filter(fn ($number) => is_numeric($number) && (int) $number >= 1 && (int) $number <= 30)
                ->map(fn ($number) => (int) $number)
                ->unique()
                ->sort()
                ->values();
        }

        if ($this->part) {
            return collect([(int) $this->part->part_number]);
        }

        return collect();
    }

    public function getPartsLabelAttribute(): string
    {
        $partNumbers = $this->tested_part_numbers;

        if ($partNumbers->isNotEmpty()) {
            return $partNumbers
                ->map(fn ($number) => 'الجزء ' . $number)
                ->join('، ');
        }

        if ($this->type === self::TYPE_MAFSIL && $this->mafsil_type) {
            return 'مفصل (' . $this->mafsil_type . ')';
        }

        return '-';
    }

    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'new_memorization' => 'حفظ جديد',
            'review' => 'مراجعة',
            'unmastered' => 'حفظ غير متقن',
            'recitation' => 'سرد',
            'mafsil' => 'مفصل',
            'not_recitate' => 'لم يسمع',
            default => $this->type,
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'passed' => 'ناجح',
            'failed' => 'راسب',
            'failed_continued' => 'راسب ومكمل',
            'in_progress' => 'جارٍ',
            'not_recitate' => 'لم يسمع',
            'incomplete' => 'لم يكمل',
            default => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'passed' => 'success',
            'failed' => 'danger',
            'failed_continued' => 'warning',
            'in_progress' => 'warning',
            'not_recitate' => 'secondary',
            'incomplete' => 'secondary',
            default => 'secondary',
        };
    }

    /**
     * Calculate color for a page based on errors and hesitations
     * نظام الأخطاء والترددات
     *
     * @param int $errors Number of errors
     * @param int $hesitations Number of hesitations
     * @param string|null $type Session type (recitation, new_memorization, review, unmastered, mafsil)
     * @return array
     */
    public static function calculatePageColor(int $errors, int $hesitations, ?string $type = null): array
    {
        // For 'recitation' (سرد) type, ignore hesitations completely - only errors count
        // if ($type === 'recitation') {
        //     if ($errors === 0) {
        //         return ['color' => 'green', 'passed' => true, 'label' => 'ممتاز'];
        //     } elseif ($errors === 1) {
        //         return ['color' => 'orange', 'passed' => true, 'label' => 'جيد'];
        //     } elseif ($errors === 2) {
        //         return ['color' => 'red', 'passed' => true, 'label' => 'ضعيف'];
        //     } else {
        //         // errors >= 3
        //         return ['color' => 'black', 'passed' => false, 'label' => 'راسب'];
        //     }
        // }

        // For all other types (new_memorization, review, unmastered, mafsil):
        // نظام النقاط: كل خطأ = 3 نقاط، كل تردد = 1 نقطة
        // - الأخضر: 0–2 نقاط
        // - البرتقالي: 3–5 نقاط
        // - الأحمر: 6 نقاط بالضبط
        // - الأسود: 7 نقاط فأكثر

        $score = ($errors * 3) + $hesitations;

        if ($score <= 2) {
            return ['color' => 'green', 'passed' => true, 'label' => 'ممتاز'];
        }

        if ($score <= 5) {
            return ['color' => 'orange', 'passed' => true, 'label' => 'جيد'];
        }

        if ($score === 6) {
            return ['color' => 'red', 'passed' => true, 'label' => 'ضعيف'];
        }

        return ['color' => 'black', 'passed' => false, 'label' => 'راسب'];
    }

    public static function calculateMafsilScore(float $maxScore, int $errors, int $hesitations): float
    {
        $errorDeduction = $errors * 3;
        $hesitationDeduction = round(floor($hesitations / 3) + (($hesitations % 3) * 0.33), 2);
        $totalDeduction = $errorDeduction + $hesitationDeduction;
        return max(0, $maxScore - $totalDeduction);
    }

    /**
     * Get max score for mafsil type
     */
    public static function getMafsilMaxScore(int $mafsilType): float
    {
        if (in_array($mafsilType, [3, 6])) return 90;
        if (in_array($mafsilType, [10, 15])) return 80;
        if (in_array($mafsilType, [20, 25, 30])) return 75;
        return 90;
    }

    /**
     * Get passing score percentage for mafsil type
     */
    public static function getMafsilPassingPercentage(int $mafsilType): float
    {
        if (in_array($mafsilType, [3, 6])) return 90;
        if (in_array($mafsilType, [10, 15])) return 80;
        if (in_array($mafsilType, [20, 25, 30])) return 75;
        return 90;
    }
}
