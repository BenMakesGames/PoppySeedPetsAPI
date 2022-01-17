<?php
namespace App\Service\PetActivity\Crafting;

use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Enum\LocationEnum;
use App\Enum\MeritEnum;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Enum\PetActivityStatEnum;
use App\Enum\PetSkillEnum;
use App\Functions\ArrayFunctions;
use App\Model\ActivityCallback;
use App\Model\ComputedPetSkills;
use App\Model\PetChanges;
use App\Repository\SpiceRepository;
use App\Service\HouseSimService;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\PetExperienceService;
use App\Service\ResponseService;
use App\Service\Squirrel3;

class NotReallyCraftsService
{
    private ResponseService $responseService;
    private InventoryService $inventoryService;
    private PetExperienceService $petExperienceService;
    private SpiceRepository $spiceRepository;
    private IRandom $squirrel3;
    private HouseSimService $houseSimService;

    public function __construct(
        ResponseService $responseService, InventoryService $inventoryService, Squirrel3 $squirrel3,
        PetExperienceService $petExperienceService, SpiceRepository $spiceRepository, HouseSimService $houseSimService
    )
    {
        $this->responseService = $responseService;
        $this->inventoryService = $inventoryService;
        $this->petExperienceService = $petExperienceService;
        $this->spiceRepository = $spiceRepository;
        $this->squirrel3 = $squirrel3;
        $this->houseSimService = $houseSimService;
    }

    /**
     * @param ActivityCallback[] $possibilities
     */
    public function adventure(ComputedPetSkills $petWithSkills, array $possibilities): PetActivityLog
    {
        if(count($possibilities) === 0)
            throw new \InvalidArgumentException('possibilities must contain at least one item.');

        /** @var ActivityCallback $method */
        $method = $this->squirrel3->rngNextFromArray($possibilities);

        $activityLog = null;
        $pet = $petWithSkills->getPet();
        $changes = new PetChanges($pet);

        /** @var PetActivityLog $activityLog */
        $activityLog = ($method->callable)($petWithSkills);

        if($activityLog)
            $activityLog->setChanges($changes->compare($pet));

        return $activityLog;
    }

    public function getCraftingPossibilities(ComputedPetSkills $petWithSkills): array
    {
        $possibilities = [];

        if($this->houseSimService->hasInventory('Planetary Ring'))
            $possibilities[] = new ActivityCallback($this, 'siftThroughPlanetaryRing', 10);

        return $possibilities;
    }

    private function siftThroughPlanetaryRing(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getPerception()->getTotal() + round($petWithSkills->getScience()->getTotal() * 2 / 3 + $petWithSkills->getNature()->getTotal() / 3));

        if($roll >= 16)
        {
            $this->houseSimService->getState()->loseItem('Planetary Ring', 1);

            $lucky = $pet->hasMerit(MeritEnum::LUCKY) && $this->squirrel3->rngNextInt(1, 70) === 1;

            if($this->squirrel3->rngNextInt(1, 70) === 1 || $lucky)
            {
                $loot = 'Meteorite';

                $exclaim = $lucky ? '! Lucky~!' : '!';
            }
            else
            {
                $loot = $this->squirrel3->rngNextFromArray([
                    'Everice',
                    'Silica Grounds',
                    'Iron Ore', 'Iron Ore',
                    'Silver Ore',
                    'Gold Ore',
                    'Dark Matter',
                    'Glowing Six-sided Die',
                    'String'
                ]);

                $exclaim = '.';

                if($loot == 'Glowing Six-sided Die')
                    $exclaim .= ' (I guess the gods DO play dice...)';
            }

            if($loot === 'String')
                $spice = $this->spiceRepository->findOneByName('Cosmic');
            else
                $spice = null;

            $pet->increaseEsteem(3);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::SCIENCE, PetSkillEnum::NATURE ]);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::GATHER, true);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% sifted through a Planetary Ring, and found ' . $loot . $exclaim, '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 16)
            ;

            $this->inventoryService->petCollectsEnhancedItem($loot, null, $spice, $pet, $pet->getName() . ' found this in a Planetary Ring.', $activityLog);

            return $activityLog;
        }
        else
        {
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::SCIENCE, PetSkillEnum::NATURE ]);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::GATHER, false);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% sifted through a Planetary Ring, looking for something interesting, but couldn\'t find anything.', 'icons/activity-logs/confused');
        }
    }

}
