<?php
namespace App\Model;

use App\Entity\Item;
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
     * @var UserQuest|null
     */
    public $userQuestEntity;

    /**
     * @var Item|null
     */
    public $itemToExchange;

    public function __construct(string $itemName, string $comment, ?UserQuest $userQuest, ?Item $itemToExchange)
    {
        $this->itemName = $itemName;
        $this->comment = $comment;
        $this->userQuestEntity = $userQuest;
        $this->itemToExchange = $itemToExchange;
    }
}
