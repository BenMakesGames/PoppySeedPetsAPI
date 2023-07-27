<?php

namespace App\Exceptions;

class PSPTooManyRequests extends PSPException
{
    public function __construct()
    {
        parent::__construct('Too many simultaneous requests. Please try again in a few seconds.');
    }
}