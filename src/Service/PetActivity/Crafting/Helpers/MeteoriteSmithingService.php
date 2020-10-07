<?php
namespace App\Service\PetActivity\Crafting\Helpers;

use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Enum\LocationEnum;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Enum\PetActivityStatEnum;
use App\Enum\PetSkillEnum;
use App\Functions\ArrayFunctions;
use App\Repository\ItemRepository;
use App\Service\InventoryService;
use App\Service\PetExperienceService;
use App\Service\ResponseService;

class MeteoriteSmithingService
{
    private $petExperienceService;
    private $inventoryService;
    private $responseService;
    private $itemRepository;

    public function __construct(
        PetExperienceService $petExperienceService, InventoryService $inventoryService, ResponseService $responseService,
        ItemRepository $itemRepository
    )
    {
        $this->petExperienceService = $petExperienceService;
        $this->inventoryService = $inventoryService;
        $this->responseService = $responseService;
        $this->itemRepository = $itemRepository;
    }

    public function createIlumetsa(Pet $pet): PetActivityLog
    {
        $roll = mt_rand(1, 20 + $pet->getIntelligence() + $pet->getStamina() + $pet->getCrafts() + $pet->getSmithing());

        if($roll <= 2)
        {
            $lostItem = ArrayFunctions::pick_one([
                'Gold Bar', 'Iron Bar'
            ]);

            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::SMITH, false);
            $this->inventoryService->loseItem($lostItem, $pet->getOwner(), LocationEnum::HOME, 1);
            $pet->increaseEsteem(-1);
            $pet->increaseSafety(-mt_rand(2, 24));
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' started to make something with a chunk of Meteorite, but burnt the ' . $lostItem . '! :(', 'icons/activity-logs/burn');
        }
        else if($roll >= 25)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(60, 75), PetActivityStatEnum::SMITH, true);
            $this->inventoryService->loseItem('Iron Bar', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Gold Bar', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Meteorite', $pet->getOwner(), LocationEnum::HOME, 1);

            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' forged Ilumetsa from gold, iron, and a chunk of Meteorite.', 'items/tool/hammer/red')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 25)
            ;

            $this->inventoryService->petCollectsItem('Ilumetsa', $pet, $pet->getName() . ' forged this from gold, iron, and a chunk of Meteorite.', $activityLog);

            $this->petExperienceService->gainExp($pet, 4, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(4);

            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 75), PetActivityStatEnum::SMITH, false);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make something with a chunk of Meteorite, but it was being super-difficult to work with!', 'icons/activity-logs/confused');
        }
    }
}
