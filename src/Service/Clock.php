<?php
declare(strict_types=1);

namespace App\Service;

class Clock
{
    public \DateTimeImmutable $now;

    public function __construct()
    {
        $this->now = new \DateTimeImmutable();
    }

    public function getMonthAndDay(): int
    {
        return (int)$this->now->format('nd');
    }
}