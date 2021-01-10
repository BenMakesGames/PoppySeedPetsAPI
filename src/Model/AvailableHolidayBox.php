<?php
namespace App\Model;

use App\Entity\UserQuest;

class AvailableHolidayBox
{
    /**
     * @var string
     */
    public $itemName;

    /**
     * @var string
     */
    public $comment;

    /**
     * @var UserQuest
     */
    public $userQuestEntity;

    public function __construct(string $itemName, string $comment, UserQuest $userQuest)
    {
        $this->itemName = $itemName;
        $this->comment = $comment;
        $this->userQuestEntity = $userQuest;
    }
}
