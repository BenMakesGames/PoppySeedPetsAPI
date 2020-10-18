<?php
namespace App\Service\PetActivity\Crafting;

use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Enum\LocationEnum;
use App\Enum\PetActivityStatEnum;
use App\Enum\PetSkillEnum;
use App\Model\ActivityCallback;
use App\Repository\ItemRepository;
use App\Service\CalendarService;
use App\Service\InventoryService;
use App\Service\PetExperienceService;
use App\Service\ResponseService;

class EventLanternService
{
    private $inventoryService;
    private $responseService;
    private $petExperienceService;
    private $itemRepository;
    private $calendarService;

    public function __construct(
        InventoryService $inventoryService, ResponseService $responseService, PetExperienceService $petExperienceService,
        ItemRepository $itemRepository, CalendarService $calendarService
    )
    {
        $this->inventoryService = $inventoryService;
        $this->responseService = $responseService;
        $this->petExperienceService = $petExperienceService;
        $this->itemRepository = $itemRepository;
        $this->calendarService = $calendarService;
    }

    /**
     * @return ActivityCallback[]
     */
    public function getCraftingPossibilities(Pet $pet, array $quantities): array
    {
        $now = new \DateTimeImmutable();
        $possibilities = [];

        if(
            array_key_exists('Crooked Fishing Rod', $quantities) &&
            array_key_exists('Paper', $quantities) && (
                array_key_exists('Candle', $quantities) ||
                array_key_exists('Jar of Fireflies', $quantities)
            )
        )
        {
            if($this->calendarService->isHalloweenCrafting())
                $possibilities = new ActivityCallback($this, 'createMoonlightLantern', 10);

            if($this->calendarService->isPiDayCrafting())
                $possibilities = new ActivityCallback($this, 'createPiLantern', 10);

            if((int)$now->format('n') === 12)
                $possibilities = new ActivityCallback($this, 'createTreelightLantern', 10);

            if($this->calendarService->isSaintMartinsDayCrafting())
                $possibilities = new ActivityCallback($this, 'createDapperSwanLantern', 10);
        }

        return $possibilities;
    }

    public function createMoonlightLantern(Pet $pet): PetActivityLog
    {
        return $this->createLantern($pet, 'Moonlight Lantern');
    }

    public function createPiLantern(Pet $pet): PetActivityLog
    {
        return $this->createLantern($pet, 'Pi Lantern');
    }

    public function createTreeLightLantern(Pet $pet): PetActivityLog
    {
        return $this->createLantern($pet, 'Treelight Lantern');
    }

    public function createDapperSwanLantern(Pet $pet): PetActivityLog
    {
        return $this->createLantern($pet, 'Dapper Swan Lantern');
    }

    private function createLantern(Pet $pet, string $lanternName): PetActivityLog
    {
        $roll = mt_rand(1, 20 + $pet->getDexterity() + $pet->getIntelligence() + $pet->getCrafts());

        if($roll <= 2)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::CRAFT, false);
            $this->inventoryService->loseItem('Paper', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            $pet->increaseEsteem(-1);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a seasonal lantern, but accidentally tore the Paper :(', '');
        }
        else if($roll < 15)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::CRAFT, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to make a seasonal lantern, but couldn\'t come up with a fitting design...', 'icons/activity-logs/confused');
        }
        else // success!
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::CRAFT, true);
            $this->inventoryService->loseItem('Crooked Fishing Rod', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseItem('Paper', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->inventoryService->loseOneOf([ 'Jar of Fireflies', 'Candle' ], $pet->getOwner(), LocationEnum::HOME);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS ]);

            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' created a ' . $lanternName . ' out of a Crooked Fishing Rod!', '');

            $this->inventoryService->petCollectsItem($lanternName, $pet, $pet->getName() . ' created this.', $activityLog);

            return $activityLog;
        }
    }
}
