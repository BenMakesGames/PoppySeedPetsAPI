<?php
namespace App\Service;

class RandomService
{
    public function roll(int $num, int $sides)
    {
        $roll = 0;

        for($i = 0; $i < $num; $i++)
            $roll += mt_rand(1, $sides);

        return $roll;
    }
}