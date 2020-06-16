<?php
namespace App\Model;

class ActivityCallback
{
    public $callable;
    public $weight;

    public function __construct($object, string $method, int $weight)
    {
        $this->callable = [ $object, $method ];
        $this->weight = $weight;
    }
}
