<?php

namespace App\Exceptions;

class PSPSessionExpired extends PSPException
{
    public function __construct()
    {
        parent::__construct('You have been logged out due to inactivity. Please log in again.');
    }
}