<?php
declare(strict_types=1);

namespace App\Model;

interface IActivityCallback
{
    public function getWeight(): int;
    public function getCallable(): callable;
}
