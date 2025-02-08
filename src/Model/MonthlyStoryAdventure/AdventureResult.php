<?php
declare(strict_types=1);

namespace App\Model\MonthlyStoryAdventure;

class AdventureResult
{
    public function __construct(
        public readonly string $text,
        public readonly array $loot
    )
    {
    }
}
