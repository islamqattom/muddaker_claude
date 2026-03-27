<?php

namespace App\Services;

use App\Models\Student;
use App\Models\AchievementBadge;
use App\Models\StudentAchievement;
use Illuminate\Support\Collection;

class AchievementService
{
    private array $badgeDefinitions = [
        'first_page' => [
            'code' => 'first_page',
            'name_ar' => 'أول صفحة',
            'name_en' => 'First Page',
            'description_ar' => 'حفظت أول صفحة من القرآن الكريم',
            'description_en' => 'Memorized your first page of the Holy Quran',
            'icon' => 'fa-star',
            'color' => '#FFD700',
            'type' => 'milestone',
            'criteria' => ['min_pages' => 1],
        ],
        'five_pages' => [
            'code' => 'five_pages',
            'name_ar' => 'خمس صفحات',
            'name_en' => 'Five Pages',
            'description_ar' => 'حفظت خمس صفحات من القرآن الكريم',
            'description_en' => 'Memorized five pages of the Holy Quran',
            'icon' => 'fa-star-half-alt',
            'color' => '#C0C0C0',
            'type' => 'milestone',
            'criteria' => ['min_pages' => 5],
        ],
        'ten_pages' => [
            'code' => 'ten_pages',
            'name_ar' => 'عشر صفحات',
            'name_en' => 'Ten Pages',
            'description_ar' => 'حفظت عشر صفحات من القرآن الكريم',
            'description_en' => 'Memorized ten pages of the Holy Quran',
            'icon' => 'fa-star-of-life',
            'color' => '#CD7F32',
            'type' => 'milestone',
            'criteria' => ['min_pages' => 10],
        ],
        'part_mastered' => [
            'code' => 'part_mastered',
            'name_ar' => 'جزء متقن',
            'name_en' => 'Part Mastered',
            'description_ar' => 'أتممت حفظ جزء كامل من القرآن الكريم',
            'description_en' => 'Completed memorization of one full part of the Quran',
            'icon' => 'fa-book',
            'color' => '#B4802C',
            'type' => 'milestone',
            'criteria' => ['min_parts' => 1],
        ],
        'week_streak' => [
            'code' => 'week_streak',
            'name_ar' => 'أسبوع متتالي',
            'name_en' => 'Week Streak',
            'description_ar' => 'حفظت لمدة أسبوع متتالي',
            'description_en' => 'Maintained a memorization streak for one week',
            'icon' => 'fa-fire',
            'color' => '#FF6B35',
            'type' => 'streak',
            'criteria' => ['min_streak' => 7],
        ],
        'month_streak' => [
            'code' => 'month_streak',
            'name_ar' => 'شهر متتالي',
            'name_en' => 'Month Streak',
            'description_ar' => 'حفظت لمدة شهر متتالي',
            'description_en' => 'Maintained a memorization streak for one month',
            'icon' => 'fa-fire-alt',
            'color' => '#FF4500',
            'type' => 'streak',
            'criteria' => ['min_streak' => 30],
        ],
        'perfect_attendance' => [
            'code' => 'perfect_attendance',
            'name_ar' => 'حضور مثالي',
            'name_en' => 'Perfect Attendance',
            'description_ar' => 'حصلت على حضور مثالي لمدة شهر كامل',
            'description_en' => 'Achieved perfect attendance for one full month',
            'icon' => 'fa-award',
            'color' => '#00CED1',
            'type' => 'accuracy',
            'criteria' => ['perfect_months' => 1],
        ],
        'high_accuracy' => [
            'code' => 'high_accuracy',
            'name_ar' => 'دقة عالية',
            'name_en' => 'High Accuracy',
            'description_ar' => 'حصلت على معدل دقة 95% في الجلسات',
            'description_en' => 'Achieved 95% accuracy rate in recitation sessions',
            'icon' => 'fa-crosshairs',
            'color' => '#32CD32',
            'type' => 'accuracy',
            'criteria' => ['min_accuracy' => 95],
        ],
    ];

    public function checkAllBadges(Student $student): array
    {
        $newlyEarned = [];

        foreach ($this->badgeDefinitions as $definition) {
            if ($this->hasEarnedBadge($student, $definition['code'])) {
                continue;
            }

            if ($this->evaluateBadge($student, $definition)) {
                $this->awardBadge($student, $definition['code']);
                $newlyEarned[] = $definition['code'];
            }
        }

        return $newlyEarned;
    }

    public function hasEarnedBadge(Student $student, string $badgeCode): bool
    {
        return StudentAchievement::where('student_id', $student->id)
            ->whereHas('badge', function ($q) use ($badgeCode) {
                $q->where('code', $badgeCode);
            })
            ->exists();
    }

    public function getEarnedBadges(Student $student): Collection
    {
        return StudentAchievement::where('student_id', $student->id)
            ->with('badge')
            ->orderBy('earned_at', 'desc')
            ->get()
            ->map(fn($sa) => $sa->badge);
    }

    public function getNextEarnableBadges(Student $student): Collection
    {
        $earnedCodes = $this->getEarnedBadges($student)->pluck('code')->toArray();

        return collect($this->badgeDefinitions)
            ->filter(fn($def) => !in_array($def['code'], $earnedCodes))
            ->map(function ($def) use ($student) {
                $progress = $this->getProgressToBadge($student, $def['code']);
                return array_merge($def, ['progress' => $progress]);
            })
            ->sortBy(fn($def) => $def['progress']['percent']);
    }

    public function getProgressToBadge(Student $student, string $badgeCode): array
    {
        $definition = $this->badgeDefinitions[$badgeCode] ?? null;

        if (!$definition) {
            return ['current' => 0, 'required' => 0, 'percent' => 0];
        }

        $criteria = $definition['criteria'];
        $current = 0;
        $required = 0;

        if (isset($criteria['min_pages'])) {
            $required = $criteria['min_pages'];
            $current = min($student->memorized_parts * 20 + $student->current_page, $required);
        } elseif (isset($criteria['min_parts'])) {
            $required = $criteria['min_parts'];
            $current = min($student->memorized_parts, $required);
        } elseif (isset($criteria['min_streak'])) {
            $required = $criteria['min_streak'];
            $current = min($student->current_streak, $required);
        } elseif (isset($criteria['min_accuracy'])) {
            $required = $criteria['min_accuracy'];
            $current = min($student->accuracy_rate, $required);
        }

        $percent = $required > 0 ? min(100, round(($current / $required) * 100)) : 0;

        return [
            'current' => $current,
            'required' => $required,
            'percent' => $percent,
        ];
    }

    private function evaluateBadge(Student $student, array $definition): bool
    {
        $criteria = $definition['criteria'];

        if (isset($criteria['min_pages'])) {
            $totalPages = $student->memorized_parts * 20 + $student->current_page;
            return $totalPages >= $criteria['min_pages'];
        }

        if (isset($criteria['min_parts'])) {
            return $student->memorized_parts >= $criteria['min_parts'];
        }

        if (isset($criteria['min_streak'])) {
            return $student->current_streak >= $criteria['min_streak'];
        }

        if (isset($criteria['min_accuracy'])) {
            return $student->accuracy_rate >= $criteria['min_accuracy'];
        }

        return false;
    }

    private function awardBadge(Student $student, string $badgeCode): void
    {
        $badge = AchievementBadge::where('code', $badgeCode)->first();

        if (!$badge) {
            return;
        }

        StudentAchievement::create([
            'student_id' => $student->id,
            'badge_id' => $badge->id,
            'earned_at' => now(),
        ]);
    }

    public function seedDefaultBadges(): void
    {
        foreach ($this->badgeDefinitions as $definition) {
            AchievementBadge::updateOrCreate(
                ['code' => $definition['code']],
                [
                    'name_ar' => $definition['name_ar'],
                    'name_en' => $definition['name_en'],
                    'description_ar' => $definition['description_ar'],
                    'description_en' => $definition['description_en'],
                    'icon' => $definition['icon'],
                    'color' => $definition['color'],
                    'type' => $definition['type'],
                    'criteria' => $definition['criteria'],
                ]
            );
        }
    }
}
