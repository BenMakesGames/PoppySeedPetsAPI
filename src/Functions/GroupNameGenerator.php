<?php
namespace App\Functions;

use App\Service\IRandom;

class GroupNameGenerator
{
    public static function generateName(IRandom $rng, array $patterns, array $dictionary, int $maxLength): string
    {
        $pattern = $rng->rngNextFromArray($patterns);
        $parts = GroupNameGenerator::getPatternParts($rng, $pattern);

        return GroupNameGenerator::generateNameFromParts($rng, $parts, $dictionary, $maxLength);
    }

    private static function getPatternParts(IRandom $rng, string $pattern): array
    {
        $parts = explode(' ', $pattern);
        $newParts = [];

        foreach($parts as $part)
        {
            if($part[strlen($part) - 1] === '?')
            {
                if($rng->rngNextInt(1, 2) === 1)
                    $part = substr($part, 0, strlen($part) - 1);
                else
                    continue;
            }

            if(strpos($part, '/') !== false)
                $part = $rng->rngNextFromArray(explode('/', $part));

            $newParts[] = $part;
        }

        return $newParts;
    }

    private static function generateNameFromParts(IRandom $rng, array $parts, $dictionary, $maxLength): string
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
                    $chosenWord = $rng->rngNextFromArray($availableWords);

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