<?php
declare(strict_types=1);

namespace App\Enum;

final class HollowEarthActionTypeEnum
{
    use Enum;

    public const PET_CHALLENGE = 'petChallenge';
    public const MOVE_TO = 'moveTo';
    public const PAY_ITEM = 'payItem?';
    public const PAY_MONEY = 'payMoneys?';
    public const PAY_ITEM_AND_MONEY = 'payItemAndMoneys?';
    public const CHOOSE_ONE = 'chooseOne';
    public const ONWARD = 'onward';
}
