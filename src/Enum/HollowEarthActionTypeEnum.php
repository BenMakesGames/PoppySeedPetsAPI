<?php
namespace App\Enum;

final class HollowEarthActionTypeEnum
{
    use Enum;

    public const PET_CHALLENGE = 'petChallenge';
    public const MOVE_TO = 'moveTo';
    public const PAY_ITEM = 'payItem?';
    public const PAY_MONEY = 'payMoneys?';
    public const CHOOSE_ONE = 'chooseOne';
}
