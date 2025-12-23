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

final class ArrayFunctions
{
    /**
     * @template T
     * @param iterable<T> $array
     * @param callable(T): bool $delegate
     * @return bool
     */
    public static function any(iterable $array, callable $delegate): bool
    {
        foreach($array as $item)
        {
            if($delegate($item))
                return true;
        }

        return false;
    }

    /**
     * @template T
     * @param iterable<T> $array
     * @param callable(T): bool $delegate
     */
    public static function count(iterable $array, callable $delegate): int
    {
        $count = 0;

        foreach($array as $item)
        {
            if($delegate($item))
                $count++;
        }

        return $count;
    }

    /**
     * @template T
     * @param iterable<T> $array
     * @param callable(T): bool $delegate
     */
    public static function all(iterable $array, callable $delegate): bool
    {
        foreach($array as $item)
        {
            if(!$delegate($item))
                return false;
        }

        return true;
    }

    /**
     * @template T
     * @param iterable<T> $array
     * @param callable(T): bool $delegate
     * @return T[]
     */
    public static function unique(iterable $array, callable $delegate): array
    {
        $result = [];

        foreach($array as $item)
        {
            $key = $delegate($item);

            if(!isset($result[$key]))
                $result[$key] = $item;
        }

        return array_values($result);
    }

    /**
     * @template T
     * @param iterable<T> $array
     * @param callable(T): bool $delegate
     * @return T|null
     */
    public static function find_one(iterable $array, callable $delegate): mixed
    {
        foreach($array as $item)
        {
            if($delegate($item))
                return $item;
        }

        return null;
    }

    /**
     * @template T
     * @param iterable<T> $array
     * @param callable(T): bool $delegate
     * @return T[]
     */
    public static function find_n(iterable $array, callable $delegate, int $quantity): array
    {
        $results = [];

        foreach($array as $item)
        {
            if($delegate($item))
            {
                $results[] = $item;

                if(count($results) == $quantity)
                    break;
            }
        }

        return $results;
    }

    /**
     * @template T
     * @param iterable<T> $array
     * @param callable(T): int $weightingDelegate
     * @return T
     */
    public static function pick_one_weighted(iterable $array, callable $weightingDelegate)
    {
        $items = [];
        $total = 0;

        foreach($array as $item)
        {
            $weight = $weightingDelegate($item);

            if($weight < 0)
                throw new \InvalidArgumentException('An item\'s weight was less than 0. This is not allowed.');

            $items[] = [
                'item' => $item,
                'weight' => $weight
            ];

            $total += $weight;
        }

        if($total < 1)
            throw new \InvalidArgumentException('The total weight of all items was less than 1. This is not allowed.');

        $index = random_int(0, $total - 1);

        foreach($items as $item)
        {
            if($index < $item['weight'])
                return $item['item'];
            else
                $index -= $item['weight'];
        }

        throw new \Exception('This should not be possible.');
    }

    /**
     * @param array<string, int> $quantities
     */
    public static function list_nice_quantities(array $quantities, string $separator = ', ', string $lastSeparator = ', and '): string
    {
        $list = [];

        foreach($quantities as $item=>$quantity)
        {
            if($quantity == 1)
                $list[] = $item;
            else
                $list[] = $quantity . '× ' . $item;
        }

        return self::list_nice($list, $separator, $lastSeparator);
    }

    public static function list_nice_sorted(iterable $strings, string $separator = ', ', string $lastSeparator = ', and '): string
    {
        $list = iterator_to_array($strings);
        sort($list);

        return self::list_nice($list, $separator, $lastSeparator);
    }


    /**
     * @param string[] $strings
     */
    public static function list_nice(iterable $strings, string $separator = ', ', string $lastSeparator = ', and '): string
    {
        if(count($strings) === 0)
            return '';
        else if(count($strings) === 1)
            return reset($strings);

        $list = '';

        $length = count($strings);
        $index = 0;

        foreach($strings as $string)
        {
            if($index === $length - 1)
                $list .= $lastSeparator;
            else if($index > 0)
                $list .= $separator;

            $list .= $string;

            $index++;
        }

        return $list;
    }

    /**
     * @template T
     * @param T[] $values
     * @param callable(T): mixed $getter
     */
    public static function sum(array $values, callable $getter): mixed
    {
        return array_reduce(
            $values,
            fn($carry, $value) => $carry + $getter($value),
            0
        );
    }

    public static function average(array $values, callable $getter): float
    {
        return ArrayFunctions::sum($values, $getter) / count($values);
    }

    /**
     * Return one of the items from the array of $values which has the LARGEST value, as returned by the $getter
     * @template T
     * @param iterable<T> $values
     * @param callable(T): mixed $getter
     * @return T|null
     */
    public static function max(iterable $values, callable $getter): mixed
    {
        $max = null;
        $maxValue = null;

        foreach($values as $value)
        {
            $currentValue = $getter($value);

            if($max === null || $currentValue > $maxValue)
            {
                $max = $value;
                $maxValue = $currentValue;
            }
        }

        return $max;
    }

    /**
     * Return one of the items from the array of $values which has the SMALLEST value, as returned by the $getter
     * @template T
     * @param iterable<T> $values
     * @param callable(T): mixed $getter
     * @return T|null
     */
    public static function min(iterable $values, callable $getter): mixed
    {
        $min = null;
        $minValue = null;

        foreach($values as $value)
        {
            $currentValue = $getter($value);

            if($min === null || $currentValue < $minValue)
            {
                $min = $value;
                $minValue = $currentValue;
            }
        }

        return $min;
    }

    /**
     * Example use: Assume a list of $people and $children, which are arrays of objects of type Person.
     * We wish to find all $people that AREN'T in the list of $children. We'll determine if two Person
     * elements are the same by comparing their "ssn" property:
     *
     *     $adults = ArrayFunctions::except($people, $children, function(Person $p) { return $p->ssn; });
     *
     * @return array All items in $values that do not appear in $toExclude; the $getter is used to compare items.
     */
    public static function except(array $values, array $toExclude, callable $getter): array
    {
        $filteredValues = [];
        $excludeValues = array_map(fn($v) => $getter($v), $toExclude);

        foreach($values as $value)
        {
            if(!in_array($getter($value), $excludeValues))
                $filteredValues[] = $value;
        }

        return $filteredValues;
    }
}
