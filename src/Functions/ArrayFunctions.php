<?php
namespace App\Functions;

final class ArrayFunctions
{
    public static function any(iterable $array, callable $delegate)
    {
        foreach($array as $item)
        {
            if($delegate($item))
                return true;
        }

        return false;
    }

    public static function all(iterable $array, callable $delegate)
    {
        foreach($array as $item)
        {
            if(!$delegate($item))
                return false;
        }

        return true;
    }

    /**
     * @return mixed|null
     */
    public static function find_one(iterable $array, callable $delegate)
    {
        foreach($array as $item)
        {
            if($delegate($item))
                return $item;
        }

        return null;
    }

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

        $index = mt_rand(0, $total - 1);

        foreach($items as $item)
        {
            if($index < $item['weight'])
                return $item['item'];
            else
                $index -= $item['weight'];
        }

        throw new \Exception('This should not be possible.');
    }

    public static function pick_one(array $array)
    {
        if(count($array) === 0) return null;

        return $array[array_rand($array)];
    }

    public static function list_nice_quantities(array $quantities, string $separator = ', ', string $lastSeparator = ', and ')
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

    /**
     * @param string[] $strings
     */
    public static function list_nice(iterable $strings, string $separator = ', ', string $lastSeparator = ', and ')
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

    public static function sum(array $values, callable $getter)
    {
        return array_reduce(
            $values,
            function($carry, $value) use($getter) { return $carry + $getter($value); },
            0
        );
    }

    public static function average(array $values, callable $getter)
    {
        return ArrayFunctions::sum($values, $getter) / count($values);
    }

    /**
     * Return one of the items from the array of $values which has the LARGEST value, as returned by the $getter
     */
    public static function max(iterable $values, callable $getter)
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
     */
    public static function min(iterable $values, callable $getter)
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
}
