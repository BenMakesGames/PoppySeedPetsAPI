<?php
namespace App\Model;

use App\Entity\UserQuest;

class AvailableHolidayBox
{
    /**
     * @var string
     */
    public $nameWithQuantity;

    /**
     * @var string
     */
    public $tradeDescription;

    /**
     * @var string
     */
    public $itemName;

    /**
     * @var int
     */
    public $quantity;

    /**
     * @var string
     */
    public $comment;

    /**
     * @var UserQuest|null
     */
    public $userQuestEntity;

    public function __construct(string $nameWithQuantity, string $tradeDescription, string $itemName, int $quantity, string $comment, ?UserQuest $userQuest)
    {
        $this->nameWithQuantity = $nameWithQuantity;
        $this->tradeDescription = $tradeDescription;
        $this->itemName = $itemName;
        $this->quantity = $quantity;
        $this->comment = $comment;
        $this->userQuestEntity = $userQuest;
    }
}
