<?php
namespace App\Enum;

class DevTaskTypeEnum
{
    use Enum;

    public const NEW_FEATURE = 1;
    public const FEATURE_UPDATE = 2;
    public const NEW_CONTENT = 3;
    public const BUG = 4;
}
