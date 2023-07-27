<?php

namespace App\Exceptions;

class PSPNotEnoughCurrencyException extends PSPException
{
    public function __construct(string $need, string $have)
    {
        parent::__construct('You need ' . $need . ' to do that, but only have ' . $have . '...');
    }
}