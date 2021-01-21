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
use App\Service\Squirrel3;
use App\Service\TransactionService;

class TwuWuvCraftingService
{
    private $petExperienceService;
    private $inventoryService;
    private $responseService;
    private $transactionService;
    private $itemRepository;
    private $coinSmithingService;
    private $silverSmithingService;
    private $squirrel3;

    public function __construct(
        PetExperienceService $petExperienceService, InventoryService $inventoryService, ResponseService $responseService,
        TransactionService $transactionService, ItemRepository $itemRepository, CoinSmithingService $coinSmithingService,
        SilverSmithingService $silverSmithingService, Squirrel3 $squirrel3
    )
    {
        $this->petExperienceService = $petExperienceService;
        $this->inventoryService = $inventoryService;
        $this->responseService = $responseService;
        $this->transactionService = $transactionService;
        $this->itemRepository = $itemRepository;
        $this->coinSmithingService = $coinSmithingService;
        $this->silverSmithingService = $silverSmithingService;
        $this->squirrel3 = $squirrel3;
    }

    public function createWedBawwoon(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getCrafts()->getTotal());

        $makingItem = $this->itemRepository->findOneByName('Wed Bawwoon');

        if($roll <= 2)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);

            $this->inventoryService->loseItem('Red Balloon', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 1, [PetSkillEnum::CRAFTS]);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a Wed Bawwoon, but accidentally popped the balloon :( Only a String remains.', 'icons/activity-logs/broke-string');
            $this->inventoryService->petCollectsItem('String', $pet, 'The remains of a Red Balloon.', $activityLog);
            return $activityLog;
        }
        else if($roll >= 14)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::CRAFT, true);
            $this->inventoryService->loseItem('Red Balloon', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Twu Wuv', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(2);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% made a Wed Bawwoon with the power of Twu Wuv!', '');
            $this->inventoryService->petCollectsItem($makingItem, $pet, $pet->getName() . ' made this with the power of Twu Wuv!', $activityLog);
            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a Wed Bawwoon, but couldn\'t get the shape right...', 'icons/activity-logs/confused');
        }
    }

    public function createCupid(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal());

        $makingItem = $this->itemRepository->findOneByName('Cupid');

        if($roll <= 2)
        {
            if($this->squirrel3->rngNextInt(1, 2) === 1)
            {
                $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);

                $this->inventoryService->loseItem('String', $pet->getOwner(), LocationEnum::HOME, 1);
                $this->petExperienceService->gainExp($pet, 1, [PetSkillEnum::CRAFTS]);
                return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to forge Cupid, but broke the String :(', 'icons/activity-logs/broke-string');
            }
            else
            {
                $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal());

                if($roll >= 12)
                    return $this->coinSmithingService->makeSilverCoins($petWithSkills, $makingItem);
                else
                    return $this->silverSmithingService->spillSilver($petWithSkills, $makingItem);
            }
        }
        else if($roll >= 16)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::CRAFT, true);
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
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to forge Cupid, but couldn\'t get the shape right...', 'icons/activity-logs/confused');
        }
    }
}
