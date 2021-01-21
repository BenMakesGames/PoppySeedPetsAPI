<?php
namespace App\Service\PetActivity\Crafting\Helpers;

use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Enum\LocationEnum;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Enum\PetActivityStatEnum;
use App\Enum\PetSkillEnum;
use App\Functions\ArrayFunctions;
use App\Model\ComputedPetSkills;
use App\Repository\ItemRepository;
use App\Service\InventoryService;
use App\Service\PetExperienceService;
use App\Service\ResponseService;
use App\Service\Squirrel3;

class MeteoriteSmithingService
{
    private $petExperienceService;
    private $inventoryService;
    private $responseService;
    private $itemRepository;
    private $squirrel3;

    public function __construct(
        PetExperienceService $petExperienceService, InventoryService $inventoryService, ResponseService $responseService,
        ItemRepository $itemRepository, Squirrel3 $squirrel3
    )
    {
        $this->petExperienceService = $petExperienceService;
        $this->inventoryService = $inventoryService;
        $this->responseService = $responseService;
        $this->itemRepository = $itemRepository;
        $this->squirrel3 = $squirrel3;
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
            $this->inventoryService->loseItem($lostItem, $pet->getOwner(), LocationEnum::HOME, 1);
            $pet->increaseEsteem(-1);
            $pet->increaseSafety(-$this->squirrel3->rngNextInt(2, 24));
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% started to make something with a chunk of Meteorite, but burnt the ' . $lostItem . '! :(', 'icons/activity-logs/burn');
        }
        else if($roll >= 25)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(60, 75), PetActivityStatEnum::SMITH, true);
            $this->inventoryService->loseItem('Iron Bar', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Gold Bar', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Meteorite', $pet->getOwner(), LocationEnum::HOME, 1);

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
}
