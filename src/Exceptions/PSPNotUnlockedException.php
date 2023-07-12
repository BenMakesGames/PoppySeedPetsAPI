<?php

namespace App\Exceptions;

class PSPNotUnlockedException extends PSPException
{
    public function __construct(string $featureName)
    {
        parent::__construct('You haven\'t unlocked the ' . $featureName . ' yet!');
    }
}