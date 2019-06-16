<?php
namespace App\Functions;

final class ArrayFunctions
{
    public static function any(array $array, callable $delegate)
    {
        foreach($array as $item)
        {
            if($delegate($item))
                return true;
        }

        return false;
    }

    public static function all(array $array, callable $delegate)
    {
        foreach($array as $item)
        {
            if(!$delegate($item))
                return false;
        }

        return true;
    }

    public static function pick_one(array $array)
    {
        return $array[mt_rand(0, count($array) - 1)];
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
