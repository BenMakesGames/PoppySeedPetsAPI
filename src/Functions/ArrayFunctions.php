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
        return $array[array_rand($array)];
    }

    /**
     * @param string[] $strings
     */
    public static function list_nice($strings, string $separator = ', ', string $lastSeparator = ', and ')
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
}
