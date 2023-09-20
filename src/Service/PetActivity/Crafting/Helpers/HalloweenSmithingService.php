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
use App\Repository\PetActivityLogTagRepository;
use App\Service\HouseSimService;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\PetExperienceService;
use App\Service\ResponseService;
use App\Service\Squirrel3;
use Doctrine\ORM\EntityManagerInterface;

class HalloweenSmithingService
{
    private PetExperienceService $petExperienceService;
    private InventoryService $inventoryService;
    private ResponseService $responseService;
    private ItemRepository $itemRepository;
    private IRandom $squirrel3;
    private HouseSimService $houseSimService;
    private EntityManagerInterface $em;

    public function __construct(
        PetExperienceService $petExperienceService, InventoryService $inventoryService, ResponseService $responseService,
        ItemRepository $itemRepository, Squirrel3 $squirrel3, HouseSimService $houseSimService,
        EntityManagerInterface $em
    )
    {
        $this->petExperienceService = $petExperienceService;
        $this->inventoryService = $inventoryService;
        $this->responseService = $responseService;
        $this->itemRepository = $itemRepository;
        $this->squirrel3 = $squirrel3;
        $this->houseSimService = $houseSimService;
        $this->em = $em;
    }

    public function createPumpkinBucket(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $buckets = [
            'Small, Yellow Plastic Bucket',
            'Upside-down, Yellow Plastic Bucket'
        ];

        $makes = $this->itemRepository->deprecatedFindOneByName($this->squirrel3->rngNextFromArray([
            'Ecstatic Pumpkin Bucket',
            'Distressed Pumpkin Bucket',
            'Unconvinced Pumpkin Bucket',
        ]));

        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal());

        if($roll >= 15)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::SMITH, true);

            $itemUsed = $this->houseSimService->getState()->loseOneOf($this->squirrel3, $buckets);
            $itemUsedItem = $this->itemRepository->deprecatedFindOneByName($itemUsed);
            $pet->increaseEsteem(2);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% applied heat to ' . $itemUsedItem->getNameWithArticle() . ', and shaped it into ' . $makes->getNameWithArticle() . '!', 'items/' . $makes->getImage())
                ->addInterestingness(PetActivityLogInterestingnessEnum::UNCOMMON_ACTIVITY)
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Crafting', 'Smithing', 'Special Event', 'Halloween' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);

            $this->inventoryService->petCollectsItem($makes, $pet, $pet->getName() . ' created this out of ' . $itemUsedItem->getNameWithArticle() . '.', $activityLog);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to shape a bucket into ' . $makes->getNameWithArticle() . ', but couldn\'t get the heat just right.', 'icons/activity-logs/confused')
                ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Crafting', 'Smithing', 'Special Event', 'Halloween' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::SMITH, false);
        }

        return $activityLog;
    }
}
