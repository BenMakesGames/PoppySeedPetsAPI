<?php
namespace App\Service;

interface IRandom
{
    function __construct(?int $seed = null);
    function rngNext(): int;
    function rngNextFloat(): float;
    function rngNextBool(): bool;
    function rngNextInt(int $min, int $inclusiveMax): int;
    function rngNextFromArray(array $array): mixed;
    function rngNextShuffle(array &$array);
    function rngNextSubsetFromArray(array $array, int $number): array;

    // hmm...
    function rngNextTweakedColor(string $color, int $radius = 12): string;
}