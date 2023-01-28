<?php
namespace App\Functions;

final class NumberFunctions
{
    public static function clamp(int $value, int $min, int $max): int
    {
        if($value < $min)
            return $min;
        else if($value > $max)
            return $max;
        else
            return $value;
    }

    public static function findDivisors(int $number): array
    {
        if($number < 1)
            throw new \InvalidArgumentException('$number must be at least 1.');
        else if($number === 1)
            return [ 1 ];
        else if($number < 4)
            return [ 1, $number ];

        $largestPossible = (int)sqrt($number);

        $divisors = [ 1, $number ];

        for($check = 2; $check <= $largestPossible; $check++)
        {
            if($number % $check === 0)
            {
                $divisors[] = $check;

                $twin = $number / $check;

                if($twin !== $check)
                    $divisors[] = $twin;
            }
        }

        return $divisors;
    }
}
