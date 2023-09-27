<?php
namespace App\Service\PetActivity\Crafting;

use App\Entity\PetActivityLog;
use App\Enum\MeritEnum;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Enum\PetActivityStatEnum;
use App\Enum\PetSkillEnum;
use App\Model\ActivityCallback;
use App\Model\ComputedPetSkills;
use App\Model\PetChanges;
use App\Repository\PetActivityLogTagRepository;
use App\Repository\SpiceRepository;
use App\Service\HouseSimService;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\PetExperienceService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;

class NotReallyCraftsService
{
    private ResponseService $responseService;
    private InventoryService $inventoryService;
    private PetExperienceService $petExperienceService;
    private EntityManagerInterface $em;
    private IRandom $squirrel3;
    private HouseSimService $houseSimService;
    private PetActivityLogTagRepository $petActivityLogTagRepository;

    public function __construct(
        ResponseService $responseService, InventoryService $inventoryService, IRandom $squirrel3,
        PetExperienceService $petExperienceService, EntityManagerInterface $em, HouseSimService $houseSimService,
        PetActivityLogTagRepository $petActivityLogTagRepository
    )
    {
        $this->responseService = $responseService;
        $this->inventoryService = $inventoryService;
        $this->petExperienceService = $petExperienceService;
        $this->em = $em;
        $this->squirrel3 = $squirrel3;
        $this->houseSimService = $houseSimService;
        $this->petActivityLogTagRepository = $petActivityLogTagRepository;
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

        $pet = $petWithSkills->getPet();
        $changes = new PetChanges($pet);

        /** @var PetActivityLog $activityLog */
        $activityLog = ($method->callable)($petWithSkills);

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
            $possibilities[] = new ActivityCallback($this, 'siftThroughPlanetaryRing', 10);

        return $possibilities;
    }

    private function siftThroughPlanetaryRing(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getScience()->getTotal() + $petWithSkills->getGatheringBonus()->getTotal());

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
                    $this->squirrel3->rngNextFromArray([ 'Silver Ore', 'Gold Ore' ]),
                    'Dark Matter',
                    'Glowing Six-sided Die',
                    'String',
                    'Icy Moon',
                ]);

                $exclaim = '.';

                if($loot == 'Glowing Six-sided Die')
                    $exclaim .= ' (I guess the gods DO play dice...)';
            }

            if($loot === 'String')
                $spice = SpiceRepository::findOneByName($this->em, 'Cosmic');
            else
                $spice = null;

            $pet->increaseEsteem(3);

            $tags = [ 'Gathering', 'Physics' ];
            if($lucky) $tags[] = 'Lucky~!';

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% sifted through a Planetary Ring, and found ' . $loot . $exclaim, '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::HO_HUM + 16)
                ->addTags($this->petActivityLogTagRepository->deprecatedFindByNames($tags))
            ;

            $this->inventoryService->petCollectsEnhancedItem($loot, null, $spice, $pet, $pet->getName() . ' found this in a Planetary Ring.', $activityLog);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::SCIENCE, PetSkillEnum::NATURE ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::GATHER, true);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% sifted through a Planetary Ring, looking for something interesting, but couldn\'t find anything.', 'icons/activity-logs/confused')
                ->addTags($this->petActivityLogTagRepository->deprecatedFindByNames([ 'Gathering', 'Physics' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::SCIENCE, PetSkillEnum::NATURE ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::GATHER, false);
        }

        return $activityLog;
    }
}
