<?php
namespace App\Model;

use App\Entity\UserQuest;

class AvailableHolidayBox
{
    public string $nameWithQuantity;
    public string $tradeDescription;
    public string $itemName;
    public int $quantity;
    public string $comment;
    public ?UserQuest $userQuestEntity;

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
