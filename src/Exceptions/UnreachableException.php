<?php

namespace App\Exceptions;

class UnreachableException extends \RuntimeException
{
    public function __construct()
    {
        parent::__construct('Unreachable code... was reached?!??');
    }
}
