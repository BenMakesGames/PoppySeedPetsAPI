<?php
namespace App\Model;

class ItemFood
{
    /** @var int */ public $food = 0;
    /** @var int */ public $love = 0;
    /** @var int */ public $junk = 0;
    /** @var int */ public $whack = 0;

    public function add(ItemFood $f): ItemFood
    {
        $added = clone $this;

        if($f === null) return $added;

        $added->food += $f->food;
        $added->love += $f->love;
        $added->junk += $f->junk;
        $added->whack += $f->whack;

        return $added;
    }

    public function multiply(int $f): ItemFood
    {
        $multiplied = clone $this;

        $multiplied->food *= $f;
        $multiplied->love *= $f;
        $multiplied->junk *= $f;
        $multiplied->whack *= $f;

        return $multiplied;
    }
}