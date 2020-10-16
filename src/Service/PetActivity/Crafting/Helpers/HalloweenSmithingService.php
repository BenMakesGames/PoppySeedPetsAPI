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

class HalloweenSmithingService
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

    public function createPumpkinBucket(Pet $pet): PetActivityLog
    {
        $buckets = [
            'Small, Yellow Plastic Bucket',
            'Upside-down, Yellow Plastic Bucket'
        ];

        $makes = $this->itemRepository->findOneByName(ArrayFunctions::pick_one([
            'Ecstatic Pumpkin Bucket',
            'Distressed Pumpkin Bucket',
            'Unconvinced Pumpkin Bucket',
        ]));

        $roll = mt_rand(1, 20 + $pet->getIntelligence() + $pet->getDexterity() + $pet->getCrafts() + $pet->getSmithing());

        if($roll >= 15)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::SMITH, true);

            $itemUsed = $this->inventoryService->loseOneOf($buckets, $pet->getOwner(), LocationEnum::HOME);
            $itemUsedItem = $this->itemRepository->findOneByName($itemUsed);

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(2);

            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' applied heat to ' . $itemUsedItem->getNameWithArticle() . ', and shaped it into ' . $makes->getNameWithArticle() . '!', 'items/' . $makes->getImage())
                ->addInterestingness(PetActivityLogInterestingnessEnum::UNCOMMON_ACTIVITY)
            ;

            $this->inventoryService->petCollectsItem($makes, $pet, $pet->getName() . ' created this out of ' . $itemUsedItem->getNameWithArticle() . '.', $activityLog);

            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::SMITH, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to shape a bucket into ' . $makes->getNameWithArticle() . ', but couldn\'t get the heat just right.', 'icons/activity-logs/confused');
        }
    }
}
