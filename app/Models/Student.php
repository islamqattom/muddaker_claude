<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class Student extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'user_id', 'supervisor_id', 'level_id', 'student_number',
        'national_id', 'birth_date', 'phone', 'address',
        'memorized_parts', 'current_part', 'current_page',
        'enrollment_date', 'notes', 'fee_category_id',
        'current_streak', 'longest_streak', 'last_memorization_date', 'accuracy_rate',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'enrollment_date' => 'date',
        'last_memorization_date' => 'date',
        'memorized_parts' => 'integer',
        'current_part' => 'integer',
        'current_page' => 'integer',
        'current_streak' => 'integer',
        'longest_streak' => 'integer',
        'accuracy_rate' => 'float',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function supervisor()
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    public function level()
    {
        return $this->belongsTo(Level::class);
    }

    public function parents()
    {
        return $this->belongsToMany(ParentProfile::class, 'parent_student', 'student_id', 'parent_id');
    }

    public function feeCategory()
    {
        return $this->belongsTo(FeeCategory::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function recitationSessions()
    {
        return $this->hasMany(RecitationSession::class);
    }

    public function warnings()
    {
        return $this->hasMany(Warning::class);
    }

    public function bookmarks()
    {
        return $this->hasMany(StudentBookmark::class);
    }

    public function bookmarkCategories()
    {
        return $this->hasMany(BookmarkCategory::class);
    }

    public function memorizationGoal()
    {
        return $this->hasOne(MemorizationGoal::class);
    }

    public function dailyProgress()
    {
        return $this->hasMany(DailyProgress::class);
    }

    public function achievements()
    {
        return $this->hasMany(StudentAchievement::class);
    }

    public function reviewItems()
    {
        return $this->hasMany(ReviewItem::class);
    }

    public function recitationReviews()
    {
        return $this->hasMany(RecitationReview::class);
    }

    public function exams(): HasMany
    {
        return $this->hasMany(Exam::class);
    }

    public function pronunciationAttempts()
    {
        return $this->hasMany(PronunciationAttempt::class);
    }

    public function weakAreas()
    {
        return $this->hasMany(WeakArea::class);
    }

    public function leaderboardEntries()
    {
        return $this->hasMany(LeaderboardEntry::class);
    }

    public function challengesAsChallenger()
    {
        return $this->hasMany(Challenge::class, 'challenger_id');
    }

    public function challengesAsChallenged()
    {
        return $this->hasMany(Challenge::class, 'challenged_id');
    }

    public function groupEventParticipations()
    {
        return $this->hasMany(GroupEventParticipant::class);
    }

    public function getNameAttribute(): string
    {
        return $this->user->name;
    }

    public function getAttendanceRateAttribute(): float
    {
        $total = $this->attendances()->count();
        if ($total === 0) return 0;
        $present = $this->attendances()->whereIn('status', ['present', 'late'])->count();
        return round(($present / $total) * 100, 1);
    }

    public function getTodayAttendanceAttribute()
    {
        return $this->attendances()->where('date', today())->first();
    }

    public function getPassRateAttribute(): float
    {
        $total = $this->recitationSessions()->whereIn('status', ['passed', 'failed'])->count();
        if ($total === 0) return 0;
        $passed = $this->recitationSessions()->where('status', 'passed')->count();
        return round(($passed / $total) * 100, 1);
    }

    private const ARABIC_DAYS = ['الأحد', 'الاثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة', 'السبت'];

    public static function getArabicDay($date): string
    {
        $dateObj = $date instanceof \Carbon\Carbon ? $date : \Carbon\Carbon::parse($date);
        return self::ARABIC_DAYS[$dateObj->dayOfWeek];
    }
}
