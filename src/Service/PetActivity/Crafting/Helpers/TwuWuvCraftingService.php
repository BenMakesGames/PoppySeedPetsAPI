<?php
namespace App\Service\PetActivity\Crafting\Helpers;

use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Enum\EnumInvalidValueException;
use App\Enum\LocationEnum;
use App\Enum\PetActivityStatEnum;
use App\Enum\PetSkillEnum;
use App\Model\ComputedPetSkills;
use App\Repository\ItemRepository;
use App\Service\InventoryService;
use App\Service\PetExperienceService;
use App\Service\ResponseService;
use App\Service\TransactionService;

class TwuWuvCraftingService
{
    private $petExperienceService;
    private $inventoryService;
    private $responseService;
    private $transactionService;
    private $itemRepository;
    private $coinSmithingService;

    public function __construct(
        PetExperienceService $petExperienceService, InventoryService $inventoryService, ResponseService $responseService,
        TransactionService $transactionService, ItemRepository $itemRepository, CoinSmithingService $coinSmithingService
    )
    {
        $this->petExperienceService = $petExperienceService;
        $this->inventoryService = $inventoryService;
        $this->responseService = $responseService;
        $this->transactionService = $transactionService;
        $this->itemRepository = $itemRepository;
        $this->coinSmithingService = $coinSmithingService;
    }

    public function createCupid(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = mt_rand(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal());

        $makingItem = $this->itemRepository->findOneByName('Cupid');

        if($roll <= 2)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::CRAFT, false);

            if(mt_rand(1, 2) === 1)
            {
                $this->inventoryService->loseItem('String', $pet->getOwner(), LocationEnum::HOME, 1);
                $this->petExperienceService->gainExp($pet, 1, [PetSkillEnum::CRAFTS]);
                return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to forge Cupid, but broke the String :(', 'icons/activity-logs/broke-string');
            }
            else
            {
                $roll = mt_rand(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal());

                if($roll >= 12)
                {
                    return $this->coinSmithingService->makeSilverCoins($petWithSkills, $makingItem);
                }
                else
                {
                    $this->inventoryService->loseItem('Silver Bar', $pet->getOwner(), LocationEnum::HOME, 1);
                    $pet->increaseEsteem(-1);
                    $pet->increaseSafety(-mt_rand(2, 12));
                    $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
                    return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to forge Cupid, but they spilled the silver and burned themselves! :(', 'icons/activity-logs/burn');
                }
            }
        }
        else if($roll >= 16)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::CRAFT, true);
            $this->inventoryService->loseItem('String', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Silver Bar', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Twu Wuv', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% forged Cupid with the power of Twu Wuv!', '');
            $this->inventoryService->petCollectsItem($makingItem, $pet, $pet->getName() . ' forged this with the power of Twu Wuv!', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::CRAFT, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to forge Cupid, but couldn\'t get the shape right...', 'icons/activity-logs/confused');
        }

    }

}
