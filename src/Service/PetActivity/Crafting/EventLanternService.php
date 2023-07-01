<?php
namespace App\Service\PetActivity\Crafting;

use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Enum\LocationEnum;
use App\Enum\PetActivityStatEnum;
use App\Enum\PetSkillEnum;
use App\Model\ActivityCallback;
use App\Model\ComputedPetSkills;
use App\Model\HouseSimRecipe;
use App\Repository\ItemRepository;
use App\Repository\PetActivityLogTagRepository;
use App\Service\CalendarService;
use App\Service\HouseSimService;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\PetExperienceService;
use App\Service\ResponseService;
use App\Service\Squirrel3;

class EventLanternService
{
    private InventoryService $inventoryService;
    private ResponseService $responseService;
    private PetExperienceService $petExperienceService;
    private ItemRepository $itemRepository;
    private CalendarService $calendarService;
    private IRandom $squirrel3;
    private HouseSimService $houseSimService;
    private PetActivityLogTagRepository $petActivityLogTagRepository;

    public function __construct(
        InventoryService $inventoryService, ResponseService $responseService, PetExperienceService $petExperienceService,
        ItemRepository $itemRepository, CalendarService $calendarService, Squirrel3 $squirrel3,
        HouseSimService $houseSimService, PetActivityLogTagRepository $petActivityLogTagRepository
    )
    {
        $this->inventoryService = $inventoryService;
        $this->responseService = $responseService;
        $this->petExperienceService = $petExperienceService;
        $this->itemRepository = $itemRepository;
        $this->calendarService = $calendarService;
        $this->squirrel3 = $squirrel3;
        $this->houseSimService = $houseSimService;
        $this->petActivityLogTagRepository = $petActivityLogTagRepository;
    }

    /**
     * @return ActivityCallback[]
     */
    public function getCraftingPossibilities(ComputedPetSkills $petWithSkills): array
    {
        $now = new \DateTimeImmutable();
        $possibilities = [];

        $recipe = new HouseSimRecipe([
            $this->itemRepository->findOneByName('Crooked Fishing Rod'),
            $this->itemRepository->findOneByName('Paper'),
            $this->itemRepository->findBy([ 'name' => [ 'Candle', 'Jar of Fireflies' ]])
        ]);

        $items = $this->houseSimService->getState()->hasInventory($recipe);

        if($items)
        {
            if($this->calendarService->isHalloweenCrafting())
                $possibilities[] = new ActivityCallback($this, 'createMoonlightLantern', 10);

            if($this->calendarService->isPiDayCrafting())
                $possibilities[] = new ActivityCallback($this, 'createPiLantern', 10);

            if((int)$now->format('n') === 12)
                $possibilities[] = new ActivityCallback($this, 'createTreelightLantern', 10);

            if($this->calendarService->isSaintMartinsDayCrafting())
                $possibilities[] = new ActivityCallback($this, 'createDapperSwanLantern', 10);
        }

        return $possibilities;
    }

    public function createMoonlightLantern(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        return $this->createLantern($petWithSkills, 'Moonlight Lantern', 'Halloween');
    }

    public function createPiLantern(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        return $this->createLantern($petWithSkills, 'Pi Lantern', 'Pi Day');
    }

    public function createTreeLightLantern(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        return $this->createLantern($petWithSkills, 'Treelight Lantern', 'Stocking Stuffing Season');
    }

    public function createDapperSwanLantern(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        return $this->createLantern($petWithSkills, 'Dapper Swan Lantern', 'St. Martin\'s');
    }

    private function createLantern(ComputedPetSkills $petWithSkills, string $lanternName, string $activityTag): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getCrafts()->getTotal());

        if($roll < 15)
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to make a seasonal lantern, but couldn\'t come up with a fitting design...', 'icons/activity-logs/confused')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting', 'Special Event', $activityTag ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);

        }
        else // success!
        {
            $this->houseSimService->getState()->loseItem('Crooked Fishing Rod', 1);
            $this->houseSimService->getState()->loseItem('Paper', 1);
            $this->houseSimService->getState()->loseOneOf([ 'Jar of Fireflies', 'Candle' ]);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created a ' . $lanternName . ' out of a Crooked Fishing Rod!', '')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Crafting', 'Special Event', $activityTag ]))
            ;

            $this->inventoryService->petCollectsItem($lanternName, $pet, $pet->getName() . ' created this.', $activityLog);

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::CRAFT, true);
        }

        return $activityLog;
    }
}
