<?php

namespace App\Exceptions;

class PSPHoursMustBeRun extends PSPException
{
    public function __construct()
    {
        parent::__construct('House hours must be run.');
    }
}