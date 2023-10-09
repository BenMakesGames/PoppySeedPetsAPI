<?php
namespace App\Model;

class ActivityCallback
{
    /** @var callable */ public $callable;
    public int $weight;

    public function __construct($object, string $method, int $weight)
    {
        $this->callable = [ $object, $method ];
        $this->weight = $weight;
    }
}
