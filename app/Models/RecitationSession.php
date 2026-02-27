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
        'unmastered_pages_count', 'session_date', 'notes'
    ];

    protected $casts = [
        'session_date' => 'date',
        'max_score' => 'float',
        'final_score' => 'float',
    ];

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
            'in_progress' => 'جارٍ',
            'stopped' => 'موقوف',
            default => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'passed' => 'success',
            'failed' => 'danger',
            'in_progress' => 'warning',
            'stopped' => 'secondary',
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
        if ($type === 'recitation') {
            if ($errors === 0) {
                return ['color' => 'green', 'passed' => true, 'label' => 'ممتاز'];
            } elseif ($errors === 1) {
                return ['color' => 'orange', 'passed' => true, 'label' => 'جيد'];
            } elseif ($errors === 2) {
                return ['color' => 'red', 'passed' => true, 'label' => 'ضعيف'];
            } else {
                // errors >= 3
                return ['color' => 'black', 'passed' => false, 'label' => 'راسب'];
            }
        }

        // For other types: convert hesitations to error equivalents: 3 hesitations = 1 error equivalent
        $hesitationEquivalent = floor($hesitations / 3);
        $totalEquivalent = $errors + $hesitationEquivalent;

        if ($errors === 0 && $hesitations <= 2) {
            return ['color' => 'green', 'passed' => true, 'label' => 'ممتاز'];
        } elseif ($errors === 1 || ($hesitations >= 3 && $hesitations <= 5)) {
            return ['color' => 'orange', 'passed' => true, 'label' => 'جيد'];
        } elseif ($errors === 2 && $hesitations === 0) {
            return ['color' => 'red', 'passed' => true, 'label' => 'ضعيف'];
        } else {
            // errors >= 2 with any hesitation, or errors >= 3
            return ['color' => 'black', 'passed' => false, 'label' => 'راسب'];
        }
    }

    /**
     * Calculate mafsil score based on errors and hesitations
     */
    public static function calculateMafsilScore(float $maxScore, int $errors, int $hesitations): float
    {
        $deduction = ($errors * 3) + (floor($hesitations / 3) * 1);
        return max(0, 100 - $deduction);
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
