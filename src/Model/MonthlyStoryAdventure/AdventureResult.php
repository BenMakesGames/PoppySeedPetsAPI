<?php

namespace App\Model\MonthlyStoryAdventure;

class AdventureResult
{
    public string $text;
    public array $loot;

    public function __construct(string $text, array $loot)
    {
        $this->text = $text;
        $this->loot = $loot;
    }
}
