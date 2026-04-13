<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MonthlyFee extends Model
{
    use HasFactory;

    public const STATUS_PAID = 'paid';
    public const STATUS_UNPAID = 'unpaid';

    public const CONFIG_FEE_PER_STUDENT = 'billing_monthly_fee_per_student';
    public const CONFIG_REMINDER_FREQUENCY = 'billing_reminder_frequency_days';
    public const CONFIG_REMINDER_ENABLED = 'billing_reminder_enabled';

    protected $fillable = [
        'parent_id',
        'student_id',
        'year',
        'month',
        'status',
        'paid_at',
        'paid_by',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(ParentProfile::class, 'parent_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function paidBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAID;
    }

    public function markAsPaid(int $adminId): void
    {
        $this->update([
            'status' => self::STATUS_PAID,
            'paid_at' => now(),
            'paid_by' => $adminId,
        ]);
    }

    public function scopeUnpaid($query)
    {
        return $query->where('status', self::STATUS_UNPAID);
    }

    public function scopePaid($query)
    {
        return $query->where('status', self::STATUS_PAID);
    }

    public function scopeForPeriod($query, int $year, int $month)
    {
        return $query->where('year', $year)->where('month', $month);
    }

    public function scopeForParentStudent($query, int $parentId, int $studentId)
    {
        return $query->where('parent_id', $parentId)->where('student_id', $studentId);
    }

    public static function getMonthNames(): array
    {
        return [
            1 => 'يناير', 2 => 'فبراير', 3 => 'مارس', 4 => 'أبريل',
            5 => 'مايو', 6 => 'يونيو', 7 => 'يوليو', 8 => 'أغسطس',
            9 => 'سبتمبر', 10 => 'أكتوبر', 11 => 'نوفمبر', 12 => 'ديسمبر'
        ];
    }

    public static function getMonthName(int $month): string
    {
        return self::getMonthNames()[$month] ?? $month;
    }
}
