<?php
namespace App\Enum;

class HollowEarthActionTypeEnum
{
    use Enum;

    public const PET_CHALLENGE = 'petChallenge';
    public const MOVE_TO = 'moveTo';
    public const PAY_ITEM = 'payItem?';
    public const PAY_MONEY = 'payMoneys?';
    public const RECEIVE_ITEM = 'receiveItem';
    public const RECEIVE_MONEY = 'receiveMoneys';
}
