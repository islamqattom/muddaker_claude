<?php

namespace App\DTO;

class SupervisorPerformanceData
{
    public function __construct(
        public readonly int $studentsCount,
        public readonly int $evaluationsThisMonth,
        public readonly float $avgScoreThisMonth,
        public readonly array $monthlyTrend,
        public readonly float $passRate = 0.0,
        public readonly float $attendanceRate = 0.0,
        public readonly float $evaluationsPerStudent = 0.0,
        public readonly int $passedCount = 0,
        public readonly int $failedCount = 0,
        public readonly int $rank = 0,
        public readonly int $totalSupervisors = 0,
        public readonly string $performanceLevel = 'needs_improvement',
        public readonly float $performanceScore = 0.0,
        public readonly array $weeklyTrend = [],
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            $data['studentsCount'] ?? 0,
            $data['evaluationsThisMonth'] ?? 0,
            $data['avgScoreThisMonth'] ?? 0.0,
            $data['monthlyTrend'] ?? [],
            $data['passRate'] ?? 0.0,
            $data['attendanceRate'] ?? 0.0,
            $data['evaluationsPerStudent'] ?? 0.0,
            $data['passedCount'] ?? 0,
            $data['failedCount'] ?? 0,
            $data['rank'] ?? 0,
            $data['totalSupervisors'] ?? 0,
            $data['performanceLevel'] ?? 'needs_improvement',
            $data['performanceScore'] ?? 0.0,
            $data['weeklyTrend'] ?? [],
        );
    }
}
