<?php
namespace App\Service\PetActivity\Crafting\Helpers;

use App\Entity\PetActivityLog;
use App\Enum\LocationEnum;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Enum\PetActivityStatEnum;
use App\Enum\PetSkillEnum;
use App\Model\ComputedPetSkills;
use App\Service\HouseSimService;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\PetExperienceService;
use App\Service\ResponseService;
use App\Service\Squirrel3;

class MeteoriteSmithingService
{
    private $petExperienceService;
    private $inventoryService;
    private $responseService;
    private IRandom $squirrel3;
    private HouseSimService $houseSimService;

    public function __construct(
        PetExperienceService $petExperienceService, InventoryService $inventoryService, ResponseService $responseService,
        HouseSimService $houseSimService, Squirrel3 $squirrel3
    )
    {
        $this->petExperienceService = $petExperienceService;
        $this->inventoryService = $inventoryService;
        $this->responseService = $responseService;
        $this->squirrel3 = $squirrel3;
        $this->houseSimService = $houseSimService;
    }

    public function createIlumetsa(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal());

        if($roll <= 2)
        {
            $lostItem = $this->squirrel3->rngNextFromArray([
                'Gold Bar', 'Iron Bar'
            ]);

            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::SMITH, false);
            $this->houseSimService->getState()->loseItem($lostItem, 1);
            $pet->increaseEsteem(-1);
            $pet->increaseSafety(-$this->squirrel3->rngNextInt(2, 24));
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% started to make something with a chunk of Meteorite, but burnt the ' . $lostItem . '! :(', 'icons/activity-logs/burn');
        }
        else if($roll >= 25)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(60, 75), PetActivityStatEnum::SMITH, true);
            $this->houseSimService->getState()->loseItem('Iron Bar', 1);
            $this->houseSimService->getState()->loseItem('Gold Bar', 1);
            $this->houseSimService->getState()->loseItem('Meteorite', 1);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% forged Ilumetsa from gold, iron, and a chunk of Meteorite.', 'items/tool/hammer/red')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 25)
            ;

            $this->inventoryService->petCollectsItem('Ilumetsa', $pet, $pet->getName() . ' forged this from gold, iron, and a chunk of Meteorite.', $activityLog);

            $this->petExperienceService->gainExp($pet, 4, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(4);

            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 75), PetActivityStatEnum::SMITH, false);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make something with a chunk of Meteorite, but it was being super-difficult to work with!', 'icons/activity-logs/confused');
        }
    }

    public function createHorizonMirror(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal());

        if($roll <= 2)
        {
            $lostItem = $this->squirrel3->rngNextFromArray([
                'Moon Pearl', 'Meteorite', 'Dark Mirror'
            ]);

            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::SMITH, false);
            $this->houseSimService->getState()->loseItem($lostItem, 1);
            $pet->increaseEsteem(-3);
            $pet->increaseSafety(-$this->squirrel3->rngNextInt(2, 24));
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);

            if($lostItem === 'Moon Pearl')
            {
                $savingRoll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getUmbra()->getTotal());
                if($savingRoll >= 20)
                {
                    $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% started to make something with a Moon Pearl, but shattered it! :( They were able to capture an escaping Photon, at least...', '');
                    $this->inventoryService->petCollectsItem('Photon', $pet, $pet->getName() . ' recovered this from a Moon Pearl they accidentally broke...', $activityLog);
                    return $activityLog;
                }
                else
                    return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% started to make something with a Moon Pearl, but shattered it! :(', '');
            }
            else if($lostItem === 'Meteorite')
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% started to make something with a Meteorite, but accidentally burnt the volatiles inside! :( Now it\'s little more than a hunk of Iron Ore...', '');
                $this->inventoryService->petCollectsItem('Iron Ore', $pet, $pet->getName() . ' accidentally reduced a Meteorite down to this...', $activityLog);
                return $activityLog;
            }
            else // Dark Mirror
            {
                $savingRoll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getScience()->getTotal());
                if($savingRoll >= 20)
                {
                    $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% started to make something with a Dark Mirror, but accidentally broke it! :( At least the Dark Matter was recoverable...', '');
                    $this->inventoryService->petCollectsItem('Dark Matter', $pet, $pet->getName() . ' salvaged this from a Dark Mirror they accidentally broke...', $activityLog);
                    return $activityLog;
                }
                else
                    return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% started to make something with a Dark Mirror, but accidentally broke it! :(', '');
            }
        }
        else if($roll >= 25)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(60, 75), PetActivityStatEnum::SMITH, true);
            $this->houseSimService->getState()->loseItem('Moon Pearl', 1);
            $this->houseSimService->getState()->loseItem('Dark Mirror', 1);
            $this->houseSimService->getState()->loseItem('Meteorite', 1);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% forged a Horizon Mirror!', 'items/treasure/space-mirror')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 25)
            ;

            $this->inventoryService->petCollectsItem('Horizon Mirror', $pet, $pet->getName() . ' forged this.', $activityLog);

            $this->petExperienceService->gainExp($pet, 4, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(4);

            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 75), PetActivityStatEnum::SMITH, false);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make something with a chunk of Meteorite, but it was being super-difficult to work with!', 'icons/activity-logs/confused');
        }
    }
}
