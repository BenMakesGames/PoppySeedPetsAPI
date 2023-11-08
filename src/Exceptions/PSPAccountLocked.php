<?php

namespace App\Exceptions;

class PSPAccountLocked extends PSPException
{
    public function __construct()
    {
        parent::__construct('This account has been locked.');
    }
}