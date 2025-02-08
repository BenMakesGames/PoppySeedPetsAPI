<?php
declare(strict_types=1);

namespace App\Exceptions;

class PSPNotUnlockedException extends PSPException
{
    public function __construct(string $featureName)
    {
        parent::__construct('You haven\'t unlocked the ' . $featureName . ' yet!');
    }
}