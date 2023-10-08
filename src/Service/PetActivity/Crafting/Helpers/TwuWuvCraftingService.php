<?php
namespace App\Service\PetActivity\Crafting\Helpers;

use App\Entity\PetActivityLog;
use App\Enum\MeritEnum;
use App\Enum\PetActivityStatEnum;
use App\Enum\PetSkillEnum;
use App\Functions\ItemRepository;
use App\Functions\PetActivityLogFactory;
use App\Functions\PetActivityLogTagHelpers;
use App\Model\ComputedPetSkills;
use App\Service\HouseSimService;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\PetExperienceService;
use Doctrine\ORM\EntityManagerInterface;

class TwuWuvCraftingService
{
    private PetExperienceService $petExperienceService;
    private InventoryService $inventoryService;
    private CoinSmithingService $coinSmithingService;
    private SilverSmithingService $silverSmithingService;
    private IRandom $squirrel3;
    private HouseSimService $houseSimService;
    private EntityManagerInterface $em;

    public function __construct(
        PetExperienceService $petExperienceService, InventoryService $inventoryService,
        CoinSmithingService $coinSmithingService, HouseSimService $houseSimService,
        SilverSmithingService $silverSmithingService, IRandom $squirrel3, EntityManagerInterface $em
    )
    {
        $this->petExperienceService = $petExperienceService;
        $this->inventoryService = $inventoryService;
        $this->coinSmithingService = $coinSmithingService;
        $this->silverSmithingService = $silverSmithingService;
        $this->squirrel3 = $squirrel3;
        $this->houseSimService = $houseSimService;
        $this->em = $em;
    }

    public function createWedBawwoon(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getCrafts()->getTotal());

        $makingItem = ItemRepository::findOneByName($this->em, 'Wed Bawwoon');

        if($roll >= 14)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::CRAFT, true);
            $this->houseSimService->getState()->loseItem('Red Balloon', 1);
            $this->houseSimService->getState()->loseItem('Twu Wuv', 1);
            $pet->increaseEsteem(2);
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% made a Wed Bawwoon with the power of Twu Wuv!')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Crafting' ]))
            ;
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->inventoryService->petCollectsItem($makingItem, $pet, $pet->getName() . ' made this with the power of Twu Wuv!', $activityLog);
        }
        else
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to make a Wed Bawwoon, but couldn\'t get the shape right...')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Crafting' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);
        }

        return $activityLog;
    }

    public function createCupid(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal());

        if($pet->hasMerit(MeritEnum::SILVERBLOOD))
            $roll += 5;

        $makingItem = ItemRepository::findOneByName($this->em, 'Cupid');

        if($roll <= 2)
        {
            if($this->squirrel3->rngNextInt(1, 2) === 1)
            {
                $this->houseSimService->getState()->loseItem('String', 1);

                $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to forge Cupid, but broke the String :(')
                    ->setIcon('icons/activity-logs/broke-string')
                    ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Crafting' ]))
                ;

                $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);
                $this->petExperienceService->gainExp($pet, 1, [PetSkillEnum::CRAFTS], $activityLog);

                return $activityLog;
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
            $this->houseSimService->getState()->loseItem('String', 1);
            $this->houseSimService->getState()->loseItem('Silver Bar', 1);
            $this->houseSimService->getState()->loseItem('Twu Wuv', 1);
            $pet->increaseEsteem(2);
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% forged Cupid with the power of Twu Wuv!')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Crafting' ]))
            ;
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->inventoryService->petCollectsItem($makingItem, $pet, $pet->getName() . ' forged this with the power of Twu Wuv!', $activityLog);
            return $activityLog;
        }
        else
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% tried to forge Cupid, but couldn\'t get the shape right...')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Crafting' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);

            return $activityLog;
        }
    }
}
