<?php
declare(strict_types=1);

/**
 * This file is part of the Poppy Seed Pets API.
 *
 * The Poppy Seed Pets API is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets API is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets API. If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Functions;

use App\Service\IRandom;

class GroupNameGenerator
{
    /**
     * @param string[] $patterns
     * @param array<string, string[]> $dictionary
     */
    public static function generateName(IRandom $rng, array $patterns, array $dictionary, int $maxLength): string
    {
        $pattern = $rng->rngNextFromArray($patterns);
        $parts = GroupNameGenerator::getPatternParts($rng, $pattern);

        return GroupNameGenerator::generateNameFromParts($rng, $parts, $dictionary, $maxLength);
    }

    /**
     * @return string[]
     */
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

            if(str_contains($part, '/'))
                $part = $rng->rngNextFromArray(explode('/', $part));

            $newParts[] = $part;
        }

        return $newParts;
    }

    /**
     * @param string[] $parts
     */
    private static function generateNameFromParts(
        IRandom $rng,
        array $parts,
        array $dictionary,
        int $maxLength
    ): string
    {
        while(true)
        {
            /** @var string[] $newParts */
            $newParts = [];
            /** @var array<string, string> $chosenWords */
            $chosenWords = [];

            foreach($parts as $part)
            {
                if($part[0] === '%' && $part[strlen($part) - 1] === '%')
                {
                    $wordType = substr($part, 1, strlen($part) - 2);
                    /** @var string[] $availableWords */
                    $availableWords = array_filter($dictionary[$wordType], fn($w) => !in_array($w, $newParts));
                    $chosenWord = $rng->rngNextFromArray($availableWords);

                    $chosenWords[$wordType] = $chosenWord;

                    $newParts[] = $chosenWord;
                }
                else
                    $newParts[] = $part;
            }

            $name = strtr(
                implode(' ', $newParts),
                [
                    '_' => ' ',
                    ' ,' => ',',
                    'the the ' => 'the '
                ]
            );

            if(strlen($name) <= $maxLength)
                return ucfirst($name);

            $longestWord = ArrayFunctions::max($chosenWords, fn(string $a) => strlen($a));

            if($longestWord === null)
                return ucfirst($name);

            $longestWordType = array_search($longestWord, $chosenWords);

            $dictionary[$longestWordType] = array_filter($dictionary[$longestWordType], fn($word) =>
                strlen($word) < strlen($longestWord)
            );
        }
    }
}