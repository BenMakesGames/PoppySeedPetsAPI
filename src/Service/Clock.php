<?php

namespace App\Service;

class Clock
{
    public \DateTimeImmutable $now;

    public function __construct()
    {
        $this->new = new \DateTimeImmutable();
    }

    public function getMonthAndDay()
    {
        return (int)$this->now->format('nd');
    }
}