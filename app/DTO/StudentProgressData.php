<?php

namespace App\DTO;

class StudentProgressData
{
    public function __construct(
        public readonly array $velocityWeeks,
        public readonly array $pageDifficulty,
        public readonly int $currentLevelPagesTotal,
        public readonly int $currentLevelPagesDone,
        public readonly float $completionPercent,
        public readonly array $recentSessions,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            $data['velocityWeeks'] ?? [],
            $data['pageDifficulty'] ?? [],
            $data['currentLevelPagesTotal'] ?? 0,
            $data['currentLevelPagesDone'] ?? 0,
            $data['completionPercent'] ?? 0.0,
            $data['recentSessions'] ?? [],
        );
    }
}
