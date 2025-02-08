<?php
declare(strict_types=1);

namespace App\Exceptions;

class PSPException extends \Exception
{
    protected function __construct($clientMessage)
    {
        parent::__construct($clientMessage);
    }
}