<?php
declare(strict_types=1);

namespace App\Exceptions;

class PSPNotEnoughCurrencyException extends PSPException
{
    public function __construct(string $need, string|int $have)
    {
        parent::__construct('You need ' . $need . ' to do that, but only have ' . $have . '...');
    }
}