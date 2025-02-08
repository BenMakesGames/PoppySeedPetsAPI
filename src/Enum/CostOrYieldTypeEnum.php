<?php
declare(strict_types=1);

namespace App\Enum;

final class CostOrYieldTypeEnum
{
    use Enum;

    public const ITEM = 'item';
    public const MONEY = 'money';
    public const RECYCLING_POINTS = 'recyclingPoints';
}
