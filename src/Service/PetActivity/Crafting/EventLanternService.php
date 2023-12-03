<?php
namespace App\Service\PetActivity\Crafting;

use App\Entity\PetActivityLog;
use App\Enum\PetActivityStatEnum;
use App\Enum\PetSkillEnum;
use App\Functions\CalendarFunctions;
use App\Functions\ItemRepository;
use App\Functions\PetActivityLogTagHelpers;
use App\Model\ActivityCallback;
use App\Model\ActivityCallback8;
use App\Model\ComputedPetSkills;
use App\Model\HouseSimRecipe;
use App\Model\IActivityCallback;
use App\Service\Clock;
use App\Service\HouseSimService;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\PetExperienceService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;

class EventLanternService
{
    private InventoryService $inventoryService;
    private ResponseService $responseService;
    private PetExperienceService $petExperienceService;
    private EntityManagerInterface $em;
    private IRandom $squirrel3;
    private HouseSimService $houseSimService;
    private Clock $clock;

    public function __construct(
        InventoryService $inventoryService, ResponseService $responseService, PetExperienceService $petExperienceService,
        EntityManagerInterface $em, IRandom $squirrel3, Clock $clock, HouseSimService $houseSimService
    )
    {
        $this->inventoryService = $inventoryService;
        $this->responseService = $responseService;
        $this->petExperienceService = $petExperienceService;
        $this->em = $em;
        $this->squirrel3 = $squirrel3;
        $this->houseSimService = $houseSimService;
        $this->clock = $clock;
    }

    /**
     * @return IActivityCallback[]
     */
    public function getCraftingPossibilities(ComputedPetSkills $petWithSkills): array
    {
        $now = new \DateTimeImmutable();
        $possibilities = [];

        $recipe = new HouseSimRecipe([
            ItemRepository::findOneByName($this->em, 'Crooked Fishing Rod'),
            ItemRepository::findOneByName($this->em, 'Paper'),
            [ ItemRepository::findOneByName($this->em, 'Candle'), ItemRepository::findOneByName($this->em, 'Jar of Fireflies') ]
        ]);

        $items = $this->houseSimService->getState()->hasInventory($recipe);

        if($items)
        {
            if(CalendarFunctions::isHalloweenCrafting($this->clock->now))
                $possibilities[] = new ActivityCallback8($this->createMoonlightLantern(...), 10);

            if(CalendarFunctions::isPiDayCrafting($this->clock->now))
                $possibilities[] = new ActivityCallback8($this->createPiLantern(...), 10);

            if((int)$now->format('n') === 12)
                $possibilities[] = new ActivityCallback8($this->createTreelightLantern(...), 10);

            if(CalendarFunctions::isSaintMartinsDayCrafting($this->clock->now))
                $possibilities[] = new ActivityCallback8($this->createDapperSwanLantern(...), 10);
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
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Crafting', 'Special Event', $activityTag ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::CRAFT, false);

        }
        else // success!
        {
            $this->houseSimService->getState()->loseItem('Crooked Fishing Rod', 1);
            $this->houseSimService->getState()->loseItem('Paper', 1);
            $this->houseSimService->getState()->loseOneOf($this->squirrel3, [ 'Jar of Fireflies', 'Candle' ]);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% created a ' . $lanternName . ' out of a Crooked Fishing Rod!', '')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Crafting', 'Special Event', $activityTag ]))
            ;

            $this->inventoryService->petCollectsItem($lanternName, $pet, $pet->getName() . ' created this.', $activityLog);

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::CRAFT, true);
        }

        return $activityLog;
    }
}
