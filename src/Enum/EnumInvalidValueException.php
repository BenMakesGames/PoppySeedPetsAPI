<?php
namespace App\Enum;

use Throwable;

class EnumInvalidValueException extends \Exception
{
    public function __construct(string $className, $value, Throwable $previous = null)
    {
        parent::__construct('"' . (string)$value . '" is not a ' . $className . ' value.', 0, $previous);
    }
}