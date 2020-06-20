<?php
namespace App\Model;

class ActivityCallback
{
    /** @var callable */ public $callable;
    /** @var int */ public $weight;

    public function __construct($object, string $method, int $weight)
    {
        $this->callable = [ $object, $method ];
        $this->weight = $weight;
    }
}
