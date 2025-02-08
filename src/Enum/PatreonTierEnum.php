<?php
declare(strict_types=1);

namespace App\Enum;

class PatreonTierEnum
{
    use Enum;

    public const DAPPER_SWAN = 'DapperSwan';

    public static function getByRewardId(int $rewardId)
    {
        switch($rewardId)
        {
            case 9967352: return self::DAPPER_SWAN;
            default: throw new \InvalidArgumentException('Invalid rewardId.');
        }
    }
}