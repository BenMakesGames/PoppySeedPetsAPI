<?php
declare(strict_types=1);

namespace App\Exceptions;

class PSPPetNotFoundException extends PSPNotFoundException
{
    public function __construct()
    {
        parent::__construct('There is no such pet.');
    }
}