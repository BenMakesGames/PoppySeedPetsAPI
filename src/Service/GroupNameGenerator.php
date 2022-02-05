<?php
namespace App\Service;

use App\Functions\ArrayFunctions;

class GroupNameGenerator
{
    private IRandom $rng;

    public function __construct(Squirrel3 $rng)
    {
        $this->rng = $rng;
    }

    public function generateName(array $patterns, array $dictionary, int $maxLength)
    {
        $pattern = $this->rng->rngNextFromArray($patterns);
        $parts = $this->getPatternParts($pattern);

        return $this->generateNameFromParts($parts, $dictionary, $maxLength);
    }

    private function getPatternParts(string $pattern): array
    {
        $parts = explode(' ', $pattern);
        $newParts = [];

        foreach($parts as $part)
        {
            if($part[strlen($part) - 1] === '?')
            {
                if($this->rng->rngNextInt(1, 2) === 1)
                    $part = substr($part, 0, strlen($part) - 1);
                else
                    continue;
            }

            if(strpos($part, '/') !== false)
                $part = $this->rng->rngNextFromArray(explode('/', $part));

            $newParts[] = $part;
        }

        return $newParts;
    }

    private function generateNameFromParts(array $parts, $dictionary, $maxLength): string
    {
        while(true)
        {
            $newParts = [];
            $chosenWords = [];

            foreach($parts as $part)
            {
                if($part[0] === '%' && $part[strlen($part) - 1] === '%')
                {
                    $wordType = substr($part, 1, strlen($part) - 2);
                    $availableWords = array_filter($dictionary[$wordType], fn($w) => !in_array($w, $newParts));
                    $chosenWord = $this->rng->rngNextFromArray($availableWords);

                    $chosenWords[$wordType] = $chosenWord;

                    $newParts[] = $chosenWord;
                }
                else
                    $newParts[] = $part;
            }

            $name = str_replace(['_', ' ,', 'the the '], [' ', ',', 'the '], implode(' ', $newParts));

            if(strlen($name) <= $maxLength)
                return ucfirst($name);

            $longestWord = ArrayFunctions::max($chosenWords, fn($a) => strlen($a));

            $longestWordType = array_search($longestWord, $chosenWords);

            $dictionary[$longestWordType] = array_filter($dictionary[$longestWordType], fn($word) =>
                strlen($word) < strlen($longestWord)
            );
        }
    }
}