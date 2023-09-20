<?php
namespace App\Service\PetActivity\Crafting\Helpers;

use App\Entity\PetActivityLog;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Enum\PetActivityStatEnum;
use App\Enum\PetSkillEnum;
use App\Model\ComputedPetSkills;
use App\Repository\PetActivityLogTagRepository;
use App\Service\HouseSimService;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\PetExperienceService;
use App\Service\ResponseService;
use App\Service\Squirrel3;
use Doctrine\ORM\EntityManagerInterface;

class MeteoriteSmithingService
{
    private PetExperienceService $petExperienceService;
    private InventoryService $inventoryService;
    private ResponseService $responseService;
    private IRandom $squirrel3;
    private HouseSimService $houseSimService;
    private EntityManagerInterface $em;

    public function __construct(
        PetExperienceService $petExperienceService, InventoryService $inventoryService, ResponseService $responseService,
        HouseSimService $houseSimService, Squirrel3 $squirrel3, EntityManagerInterface $em
    )
    {
        $this->petExperienceService = $petExperienceService;
        $this->inventoryService = $inventoryService;
        $this->responseService = $responseService;
        $this->squirrel3 = $squirrel3;
        $this->houseSimService = $houseSimService;
        $this->em = $em;
    }

    public function createIlumetsa(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal());

        if($roll <= 2)
        {
            $pet->increaseSafety(-$this->squirrel3->rngNextInt(2, 18));

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% started to make something with a chunk of Meteorite, but burnt themselves trying! :(', 'icons/activity-logs/burn')
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Smithing' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::SMITH, false);
        }
        else if($roll >= 25)
        {
            $this->houseSimService->getState()->loseItem('Iron Bar', 1);
            $this->houseSimService->getState()->loseItem('Gold Bar', 1);
            $this->houseSimService->getState()->loseItem('Meteorite', 1);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% forged Ilumetsa from gold, iron, and a chunk of Meteorite.', 'items/tool/hammer/red')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 25)
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Smithing' ]))
            ;

            $this->inventoryService->petCollectsItem('Ilumetsa', $pet, $pet->getName() . ' forged this from gold, iron, and a chunk of Meteorite.', $activityLog);

            $this->petExperienceService->gainExp($pet, 4, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(60, 75), PetActivityStatEnum::SMITH, true);
            $pet->increaseEsteem(4);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make something with a chunk of Meteorite, but it was being super-difficult to work with!', 'icons/activity-logs/confused')
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Smithing' ]))
            ;

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 75), PetActivityStatEnum::SMITH, false);
        }

        return $activityLog;
    }

    public function createHorizonMirror(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal());

        if($roll >= 25)
        {
            $this->houseSimService->getState()->loseItem('Moon Pearl', 1);
            $this->houseSimService->getState()->loseItem('Dark Mirror', 1);
            $this->houseSimService->getState()->loseItem('Meteorite', 1);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% forged a Horizon Mirror!', 'items/treasure/space-mirror')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 25)
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Smithing' ]))
            ;

            $this->inventoryService->petCollectsItem('Horizon Mirror', $pet, $pet->getName() . ' forged this.', $activityLog);

            $this->petExperienceService->gainExp($pet, 4, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(60, 75), PetActivityStatEnum::SMITH, true);

            $pet->increaseEsteem(4);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make something with a chunk of Meteorite, but it was being super-difficult to work with!', 'icons/activity-logs/confused')
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Smithing' ]))
            ;

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 75), PetActivityStatEnum::SMITH, false);
        }

        return $activityLog;
    }
}
