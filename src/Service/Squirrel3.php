<?php
namespace App\Service;

use App\Functions\RandomFunctions;

class Squirrel3 implements IRandom
{
    private $seed;
    private $rngIndex = 0;

    public function __construct(?int $seed = null)
    {
        $this->setSeed($seed ?? random_int(0, PHP_INT_MAX));
    }

    public function setSeed(int $seed)
    {
        $this->seed = $seed;
    }

    public function rngNext(): int
    {
        return RandomFunctions::squirrel3Noise($this->rngIndex++, $this->seed);
    }

    public function rngNextFloat(): float
    {
        return $this->rngNext() / 0xffffffff;
    }

    public function rngNextBool(): bool
    {
        return ($this->rngNext() & 1) === 1;
    }

    public function rngNextInt(int $min, int $inclusiveMax): int
    {
        return ($this->rngNext() % ($inclusiveMax - $min + 1)) + $min;
    }

    public function rngNextFromArray(array $array)
    {
        return array_slice($array, $this->rngNextInt(0, count($array) - 1), 1)[0];
    }

    public function rngNextShuffle(array &$array)
    {
        $n = count($array);

        for($i = 0; $i < $n; $i++)
        {
            $r = $i + $this->rngNextInt(0, $n - $i - 1);
            $temp = $array[$r];
            $array[$r] = $array[$i];
            $array[$i] = $temp;
        }
    }

    /**
     * Do not use on huge arrays; it creates another array of equal size.
     */
    public function rngNextSubsetFromArray(array $array, int $number): array
    {
        $indicies = array_keys($array);

        $this->rngNextShuffle($indicies);

        $return = [];

        for($i = 0; $i < $number; $i++)
            $return[] = $array[$indicies[$i]];

        return $return;
    }

    public function rngNextTweakedColor(string $color, int $radius = 12): string
    {
        $newColor = '';

        for($i = 0; $i < 3; $i++)
        {
            $part = hexdec($color[$i * 2] . $color[$i * 2 + 1]);    // get color part as decimal
            $part += $this->rngNextInt(-$radius, $radius);          // randomize
            $part = max(0, min(255, $part));                        // keep between 0 and 255
            $part = str_pad(dechex($part), 2, '0', STR_PAD_LEFT);   // turn back into hex

            $newColor .= $part;
        }

        return $newColor;
    }
}
