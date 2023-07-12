<?php

namespace App\Exceptions;

class PSPPetNotFoundException extends PSPNotFoundException
{
    public function __construct()
    {
        parent::__construct('There is no such pet.');
    }
}