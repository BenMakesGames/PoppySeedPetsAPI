<?php
declare(strict_types=1);

namespace App\Exceptions;

class PSPInvalidOperationException extends PSPException
{
    public function __construct(string $clientMessage)
    {
        parent::__construct($clientMessage);
    }
}