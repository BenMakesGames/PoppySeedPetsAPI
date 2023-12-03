<?php
namespace App\Service\PetActivity\Crafting;

use App\Entity\PetActivityLog;
use App\Enum\MeritEnum;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Enum\PetActivityStatEnum;
use App\Enum\PetSkillEnum;
use App\Functions\PetActivityLogFactory;
use App\Functions\PetActivityLogTagHelpers;
use App\Model\ActivityCallback;
use App\Model\ComputedPetSkills;
use App\Model\IActivityCallback;
use App\Model\PetChanges;
use App\Repository\SpiceRepository;
use App\Service\HouseSimService;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\PetExperienceService;
use Doctrine\ORM\EntityManagerInterface;

class NotReallyCraftsService
{
    private InventoryService $inventoryService;
    private PetExperienceService $petExperienceService;
    private EntityManagerInterface $em;
    private IRandom $rng;
    private HouseSimService $houseSimService;

    public function __construct(
        InventoryService $inventoryService, IRandom $rng, PetExperienceService $petExperienceService,
        EntityManagerInterface $em, HouseSimService $houseSimService
    )
    {
        $this->inventoryService = $inventoryService;
        $this->petExperienceService = $petExperienceService;
        $this->em = $em;
        $this->rng = $rng;
        $this->houseSimService = $houseSimService;
    }

    /**
     * @param IActivityCallback[] $possibilities
     */
    public function adventure(ComputedPetSkills $petWithSkills, array $possibilities): PetActivityLog
    {
        if(count($possibilities) === 0)
            throw new \InvalidArgumentException('possibilities must contain at least one item.');

        /** @var IActivityCallback $method */
        $method = $this->rng->rngNextFromArray($possibilities);

        $pet = $petWithSkills->getPet();
        $changes = new PetChanges($pet);

        /** @var PetActivityLog $activityLog */
        $activityLog = $method->getCallable()($petWithSkills);

        if($activityLog)
        {
            $activityLog->setChanges($changes->compare($pet));
        }

        return $activityLog;
    }

    public function getCraftingPossibilities(ComputedPetSkills $petWithSkills): array
    {
        $possibilities = [];

        if($this->houseSimService->hasInventory('Planetary Ring'))
            $possibilities[] = new ActivityCallback($this->siftThroughPlanetaryRing(...), 10);

        return $possibilities;
    }

    private function siftThroughPlanetaryRing(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getScience()->getTotal() + $petWithSkills->getGatheringBonus()->getTotal());

        if($roll >= 16)
        {
            $this->houseSimService->getState()->loseItem('Planetary Ring', 1);

            $lucky = $pet->hasMerit(MeritEnum::LUCKY) && $this->rng->rngNextInt(1, 70) === 1;

            if($this->rng->rngNextInt(1, 70) === 1 || $lucky)
            {
                $loot = 'Meteorite';

                $exclaim = $lucky ? '! Lucky~!' : '!';
            }
            else
            {
                $loot = $this->rng->rngNextFromArray([
                    'Everice',
                    'Silica Grounds',
                    'Iron Ore', 'Iron Ore',
                    $this->rng->rngNextFromArray([ 'Silver Ore', 'Gold Ore' ]),
                    'Dark Matter',
                    'Glowing Six-sided Die',
                    'String',
                    'Icy Moon',
                ]);

                $exclaim = '.';

                if($loot == 'Glowing Six-sided Die')
                    $exclaim .= ' (I guess the gods DO play dice, Einstein!)';
            }

            if($loot === 'String')
                $spice = SpiceRepository::findOneByName($this->em, 'Cosmic');
            else
                $spice = null;

            $pet->increaseEsteem(3);

            $tags = [ 'Gathering', 'Physics' ];
            if($lucky) $tags[] = 'Lucky~!';

            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% sifted through a Planetary Ring, and found ' . $loot . $exclaim)
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 16)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, $tags))
            ;

            $this->inventoryService->petCollectsEnhancedItem($loot, null, $spice, $pet, $pet->getName() . ' found this in a Planetary Ring.', $activityLog);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::SCIENCE, PetSkillEnum::NATURE ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::GATHER, true);
        }
        else
        {
            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% sifted through a Planetary Ring, looking for something interesting, but couldn\'t find anything.')
                ->setIcon('icons/activity-logs/confused')
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Gathering', 'Physics' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::SCIENCE, PetSkillEnum::NATURE ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::GATHER, false);
        }

        return $activityLog;
    }
}
