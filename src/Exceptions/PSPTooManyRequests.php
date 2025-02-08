<?php
declare(strict_types=1);

namespace App\Exceptions;

class PSPTooManyRequests extends PSPException
{
    public function __construct()
    {
        parent::__construct('Too many simultaneous requests. Please try again in a few seconds.');
    }
}