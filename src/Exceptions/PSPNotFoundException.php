<?php

namespace App\Exceptions;

class PSPNotFoundException extends PSPException
{
    public function __construct(string $clientMessage)
    {
        parent::__construct($clientMessage);
    }
}