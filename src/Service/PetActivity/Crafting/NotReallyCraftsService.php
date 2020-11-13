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
use App\Model\PetChanges;
use App\Repository\SpiceRepository;
use App\Service\InventoryService;
use App\Service\PetExperienceService;
use App\Service\ResponseService;

class NotReallyCraftsService
{
    private $responseService;
    private $inventoryService;
    private $petExperienceService;
    private $spiceRepository;

    public function __construct(
        ResponseService $responseService, InventoryService $inventoryService,
        PetExperienceService $petExperienceService, SpiceRepository $spiceRepository
    )
    {
        $this->responseService = $responseService;
        $this->inventoryService = $inventoryService;
        $this->petExperienceService = $petExperienceService;
        $this->spiceRepository = $spiceRepository;
    }

    /**
     * @param ActivityCallback[] $possibilities
     */
    public function adventure(Pet $pet, array $possibilities): PetActivityLog
    {
        if(count($possibilities) === 0)
            throw new \InvalidArgumentException('possibilities must contain at least one item.');

        /** @var ActivityCallback $method */
        $method = ArrayFunctions::pick_one($possibilities);

        $activityLog = null;
        $changes = new PetChanges($pet);

        /** @var PetActivityLog $activityLog */
        $activityLog = ($method->callable)($pet);

        if($activityLog)
            $activityLog->setChanges($changes->compare($pet));

        return $activityLog;
    }

    public function getCraftingPossibilities(Pet $pet, array $quantities): array
    {
        $possibilities = [];

        if(array_key_exists('Planetary Ring', $quantities))
            $possibilities[] = new ActivityCallback($this, 'siftThroughPlanetaryRing', 10);

        return $possibilities;
    }

    private function siftThroughPlanetaryRing(Pet $pet): PetActivityLog
    {
        $roll = mt_rand(1, 20 + $pet->getIntelligence() + $pet->getPerception() + round($pet->getScience() * 2 / 3 + $pet->getNature() / 3));

        if($roll <= 2)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::GATHER, true);
            $this->inventoryService->loseItem('Planetary Ring', $pet->getOwner(), LocationEnum::HOME, 1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::SCIENCE ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' sifted through a Planetary Ring looking for goodies, but ended up scattering it to dust :(', '');
        }
        else if($roll >= 16)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::GATHER, true);
            $this->inventoryService->loseItem('Planetary Ring', $pet->getOwner(), LocationEnum::HOME, 1);

            $lucky = $pet->hasMerit(MeritEnum::LUCKY) && mt_rand(1, 70) === 1;

            if(mt_rand(1, 70) === 1 || $lucky)
            {
                $loot = 'Meteorite';

                $exclaim = $lucky ? '! Lucky~!' : '!';
            }
            else
            {
                $loot = ArrayFunctions::pick_one([
                    /*'Everice',
                    'Silica Grounds',
                    'Iron Ore', 'Iron Ore',
                    'Silver Ore',
                    'Gold Ore',
                    'Dark Matter',
                    'Glowing Six-sided Die',*/
                    'String'
                ]);

                $exclaim = '.';
            }

            if($loot === 'String')
                $spice = $this->spiceRepository->findOneByName('Cosmic');
            else
                $spice = null;

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::SCIENCE ]);
            $pet->increaseEsteem(3);

            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' sifted through a Planetary Ring, and found ' . $loot . $exclaim, '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 16)
            ;

            $this->inventoryService->petCollectsEnhancedItem($loot, null, $spice, $pet, $pet->getName() . ' found this in a Planetary Ring.', $activityLog);

            return $activityLog;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::GATHER, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::SCIENCE ]);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' sifted through a Planetary Ring, looking for something interesting, but couldn\'t find anything.', 'icons/activity-logs/confused');
        }
    }

}
