<?php
namespace App\Enum;

final class StoryActionTypeEnum
{
    use Enum;

    public const SET_STEP = 'setStep';
    public const RECEIVE_ITEM = 'receiveItem';
    public const LOSE_ITEM = 'loseItem';
    public const INCREMENT_STAT = 'incrementStat';
    public const SET_QUEST_VALUE = 'setQuestValue';

    public const EXIT = 'exit';
}