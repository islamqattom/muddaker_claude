<?php

namespace App\DTO;

class SupervisorPerformanceData
{
    public function __construct(
        public readonly int $studentsCount,
        public readonly int $evaluationsThisMonth,
        public readonly float $avgScoreThisMonth,
        public readonly int $pendingWarningDrafts,
        public readonly ?float $avgResponseTimeDays,
        public readonly array $monthlyTrend,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            $data['studentsCount'] ?? 0,
            $data['evaluationsThisMonth'] ?? 0,
            $data['avgScoreThisMonth'] ?? 0.0,
            $data['pendingWarningDrafts'] ?? 0,
            $data['avgResponseTimeDays'] ?? null,
            $data['monthlyTrend'] ?? [],
        );
    }
}
