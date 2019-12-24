<?php
namespace App\Enum;

final class StoryActionTypeEnum
{
    use Enum;

    public const SET_STEP = 'setStep';
    public const RECEIVE_ITEM = 'receiveItem';
    public const EXIT = 'exit';
}