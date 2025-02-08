<?php
declare(strict_types=1);

namespace App\Model;

final class ActivityCallback implements IActivityCallback
{
    /** @var callable */ public $callable;
    public int $weight;

    public function __construct(callable $callable, int $weight)
    {
        $this->callable = $callable;
        $this->weight = $weight;
    }

    public function getWeight(): int
    {
        return $this->weight;
    }

    public function getCallable(): callable
    {
        return $this->callable;
    }
}
