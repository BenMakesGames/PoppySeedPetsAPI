<?php
namespace App\Model;

interface IActivityCallback
{
    public function getWeight(): int;
    public function getCallable(): callable;
}
