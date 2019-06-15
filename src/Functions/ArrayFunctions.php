<?php
namespace App\Functions;

class ArrayFunctions {}

function array_any(array $array, callable $delegate)
{
    foreach($array as $item)
    {
        if($delegate($item))
            return true;
    }

    return false;
}

function array_all(array $array, callable $delegate)
{
    foreach($array as $item)
    {
        if(!$delegate($item))
            return false;
    }

    return true;
}
