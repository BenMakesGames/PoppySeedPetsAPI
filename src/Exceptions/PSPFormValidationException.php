<?php

namespace App\Exceptions;

class PSPFormValidationException extends PSPException
{
    public function __construct(string $clientMessage)
    {
        parent::__construct($clientMessage);
    }
}