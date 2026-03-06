<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecitationSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id', 'supervisor_id', 'type', 'part_id',
        'start_page', 'end_page', 'total_pages',
        'total_errors', 'total_hesitations', 'status',
        'mafsil_type', 'max_score', 'final_score',
        'unmastered_pages_count', 'session_date', 'day', 'notes'
    ];

    protected $casts = [
        'session_date' => 'date',
        'max_score' => 'float',
        'final_score' => 'float',
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

    /**
     * Get Arabic day name from date
     */
    public static function getArabicDay($date): string
    {
        $days = ['الأحد', 'الاثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة', 'السبت'];
        $dateObj = $date instanceof \Carbon\Carbon ? $date : \Carbon\Carbon::parse($date);
        return $days[$dateObj->dayOfWeek];
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

    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'new_memorization' => 'حفظ جديد',
            'review' => 'مراجعة',
            'unmastered' => 'حفظ غير متقن',
            'recitation' => 'سرد',
            'mafsil' => 'مفصل',
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
        // For 'recitation' (سرد) type: 3 hesitations = 1 equivalent error per page
        if ($type === 'recitation') {
            $equivalentErrors = $errors + floor($hesitations / 3);
            if ($equivalentErrors === 0) {
                return ['color' => 'green', 'passed' => true, 'label' => 'ممتاز'];
            } elseif ($equivalentErrors === 1) {
                return ['color' => 'orange', 'passed' => true, 'label' => 'جيد'];
            } elseif ($equivalentErrors === 2) {
                return ['color' => 'red', 'passed' => true, 'label' => 'ضعيف'];
            } else {
                // equivalentErrors >= 3
                return ['color' => 'black', 'passed' => false, 'label' => 'راسب'];
            }
        }

        // For all other types (new_memorization, review, unmastered, mafsil):
        // حساب إجمالي الخطأ المكافئ: 3 ترددات = 1 خطأ دائماً
        // - الأخضر: أقل من 1 خطأ equivalent
        // - البرتقالي: من 1 إلى أقل من 2 خطأ equivalent
        // - الأحمر: 2 خطأ equivalent بالضبط (2 خطأ بدون تردد، أو 6 تردد، أو 1 خطأ+3 تردد)
        // - الأسود: 2 خطأ + 1 تردد أو أكثر، أو 3+ أخطاء

        $totalEquivalent = $errors + floor($hesitations / 3);

        if ($totalEquivalent < 1) {
            return ['color' => 'green', 'passed' => true, 'label' => 'ممتاز'];
        } elseif ($totalEquivalent < 2) {
            return ['color' => 'orange', 'passed' => true, 'label' => 'جيد'];
        } elseif (($totalEquivalent == 2 && $hesitations == 0) || ($hesitations == 6 && $totalEquivalent == 2) || ($hesitations == 3 && $totalEquivalent == 2)) {
            // أحمر: 2 خطأ بدون تردد، أو 6 تردد فقط، أو 3 تردد + 1 خطأ
            return ['color' => 'red', 'passed' => true, 'label' => 'ضعيف'];
        } elseif ($totalEquivalent == 2 && $hesitations >= 1) {
            // أسود: 2 خطأ + 1 تردد أو أكثر
            return ['color' => 'black', 'passed' => false, 'label' => 'راسب'];
        } elseif ($totalEquivalent >= 3) {
            // 3 خطأ equivalent أو أكثر
            return ['color' => 'black', 'passed' => false, 'label' => 'راسب'];
        } else {
            return ['color' => 'green', 'passed' => true, 'label' => 'ممتاز'];
        }
    }

    public static function calculateMafsilScore(float $maxScore, int $errors, int $hesitations): float
    {
        $errorDeduction = $errors * 3;
        $hesitationDeduction = round(floor($hesitations / 3) + (($hesitations % 3) * 0.33), 2);
        $totalDeduction = $errorDeduction + $hesitationDeduction;
        return max(0, 100 - $totalDeduction);
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
