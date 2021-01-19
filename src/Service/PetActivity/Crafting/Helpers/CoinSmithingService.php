<?php
namespace App\Service\PetActivity\Crafting\Helpers;

use App\Entity\Item;
use App\Enum\LocationEnum;
use App\Enum\PetActivityStatEnum;
use App\Enum\PetSkillEnum;
use App\Model\ComputedPetSkills;
use App\Service\InventoryService;
use App\Service\PetExperienceService;
use App\Service\ResponseService;
use App\Service\TransactionService;

class CoinSmithingService
{
    private $petExperienceService;
    private $inventoryService;
    private $transactionService;
    private $responseService;

    public function __construct(
        PetExperienceService $petExperienceService, InventoryService $inventoryService,
        TransactionService $transactionService, ResponseService $responseService
    )
    {
        $this->petExperienceService = $petExperienceService;
        $this->inventoryService = $inventoryService;
        $this->transactionService = $transactionService;
        $this->responseService = $responseService;
    }

    public function makeSilverCoins(ComputedPetSkills $petWithSkills, Item $triedToMake)
    {
        $pet = $petWithSkills->getPet();

        $this->petExperienceService->spendTime($pet, mt_rand(75, 90), PetActivityStatEnum::SMITH, true);
        $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
        $this->inventoryService->loseItem('Silver Bar', $pet->getOwner(), LocationEnum::HOME, 1);

        $moneys = mt_rand(10, 20);
        $this->transactionService->getMoney($pet->getOwner(), $moneys, $pet->getName() . ' made some silver coins after failing to forge ' . $triedToMake->getNameWithArticle() . '.');
        $pet->increaseFood(-1);

        return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to forge ' . $triedToMake->getNameWithArticle() . ' from a Silver Bar, but spilled some of the silver, and almost burned themselves! They used the leftovers to make ' . $moneys . '~~m~~ worth of silver coins, instead.', 'icons/activity-logs/moneys');
    }
}
