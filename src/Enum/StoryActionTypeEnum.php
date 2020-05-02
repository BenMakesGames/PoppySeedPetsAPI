<?php
namespace App\Enum;

final class StoryActionTypeEnum
{
    use Enum;

    public const SET_STEP = 'setStep';
    public const RECEIVE_ITEM = 'receiveItem';
    public const DONATE_ITEM = 'donateItem';
    public const LOSE_ITEM = 'loseItem';
    public const LOSE_CALLING_INVENTORY = 'loseCallingInventory';
    public const INCREMENT_STAT = 'incrementStat';
    public const SET_QUEST_VALUE = 'setQuestValue';
    public const UNLOCK_TRADER = 'unlockTrader';

    public const EXIT = 'exit';
}
