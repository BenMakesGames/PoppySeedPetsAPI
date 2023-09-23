<?php
namespace App\Service\PetActivity\Daydreams;

use App\Entity\PetActivityLog;
use App\Enum\MeritEnum;
use App\Enum\PetActivityStatEnum;
use App\Enum\PetSkillEnum;
use App\Functions\ActivityHelpers;
use App\Functions\PetActivityLogFactory;
use App\Model\ComputedPetSkills;
use App\Model\PetChanges;
use App\Repository\PetActivityLogTagRepository;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\PetExperienceService;
use Doctrine\ORM\EntityManagerInterface;

class IceCreamDaydream
{
    private IRandom $rng;
    private EntityManagerInterface $em;
    private InventoryService $inventoryService;
    private PetExperienceService $petExperienceService;

    public function __construct(
        IRandom $rng, EntityManagerInterface $em, InventoryService $inventoryService,
        PetExperienceService $petExperienceService
    )
    {
        $this->rng = $rng;
        $this->em = $em;
        $this->inventoryService = $inventoryService;
        $this->petExperienceService = $petExperienceService;
    }

    public function doAdventure(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $changes = new PetChanges($pet);

        switch($this->rng->rngNextInt(1, 4))
        {
            case 1: $log = $this->doCityOfFrozenIceCream($petWithSkills); break;
            case 2: $log = $this->doMintChocolateSurfing($petWithSkills); break;
            case 3: $log = $this->doShapeIceCreamLandscapes($petWithSkills); break;
            case 4: $log = $this->doFlavorfulStalactites($petWithSkills); break;
            default: throw new \Exception("Unknown Ice Cream Day Dream adventure! (Ben screwed up!)");
        }

        $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::OTHER, null);

        $log
            ->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Dream' ]))
            ->setChanges($changes->compare($pet));

        return $log;
    }

    private function doCityOfFrozenIceCream(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        if($pet->hasMerit(MeritEnum::GOURMAND))
        {
            $log = PetActivityLogFactory::createUnreadLog(
                $this->em,
                $petWithSkills->getPet(),
                ActivityHelpers::PetName($pet) . ' daydreamed about a city sculpted from ever-frozen ice cream where rivers of Neapolitan Ice Cream flowed. They scooped up some of the neapolitan (and ate some, too - a true Gourmand!) before snapping back to reality.');

            $log->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Eating', 'Gourmand' ]));

            $pet->increaseFood(8);
        }
        else
        {
            $log = PetActivityLogFactory::createUnreadLog(
                $this->em,
                $petWithSkills->getPet(),
                ActivityHelpers::PetName($pet) . ' daydreamed about a city sculpted from ever-frozen ice cream where rivers of Neapolitan Ice Cream flowed. They scooped up some of the neapolitan before snapping back to reality.');
        }

        $this->inventoryService->petCollectsItem('Neapolitan Ice Cream', $pet, $pet->getName() . ' found this in a daydream.', $log);

        return $log;
    }

    private function doMintChocolateSurfing(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $roll = $this->rng->rngNextInt(1, 20 + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getUmbra()->getTotal());

        if($roll >= 15)
        {
            $log = PetActivityLogFactory::createUnreadLog(
                $this->em,
                $petWithSkills->getPet(),
                ActivityHelpers::PetName($pet) . ' daydreamed they were surfing on a wave of mint chocolate ice cream, dodging pieces of cookies as they went. They caught one of the cookies just before it hit their face, then snapped back to reality!');

            $this->inventoryService->petCollectsItem('Mini Chocolate Chip Cookies', $pet, $pet->getName() . ' found this in a daydream.', $log);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::UMBRA ], $log);

            return $log;
        }
        else
        {
            $log = PetActivityLogFactory::createUnreadLog(
                $this->em,
                $petWithSkills->getPet(),
                ActivityHelpers::PetName($pet) . ' daydreamed they were surfing on a wave of mint chocolate ice cream, dodging pieces of cookies as they went. A whole cookie hit them in the face, jolting them back to reality!');

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ], $log);

            return $log;
        }
    }

    private function doShapeIceCreamLandscapes(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        if($pet->hasMerit(MeritEnum::GOURMAND))
        {
            $log = PetActivityLogFactory::createUnreadLog(
                $this->em,
                $petWithSkills->getPet(),
                ActivityHelpers::PetName($pet) . ' daydreamed they were a giant in a world where mountains were scoops of ice cream, shaping new landscapes. They created mountain ranges of rocky road (and ate some, too - a true Gourmand!), then snapped back to reality!');

            $log->addTags(PetActivityLogTagRepository::findByNames($this->em, [ 'Eating', 'Gourmand' ]));

            $pet->increaseFood(8);
        }
        else
        {
            $log = PetActivityLogFactory::createUnreadLog(
                $this->em,
                $petWithSkills->getPet(),
                ActivityHelpers::PetName($pet) . ' daydreamed they were a giant in a world where mountains were scoops of ice cream, shaping new landscapes. They created mountain ranges of rocky road, then snapped back to reality!');
        }

        $this->petExperienceService->gainExp($pet, 5, [ PetSkillEnum::CRAFTS ], $log);

        return $log;
    }

    private function doFlavorfulStalactites(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $log = PetActivityLogFactory::createUnreadLog(
            $this->em,
            $petWithSkills->getPet(),
            ActivityHelpers::PetName($pet) . ' daydreamed they discovered crystal caverns where the stalactites dripped flavors unknown to the modern world. They bottled some up before snapping back to reality...');

        $this->inventoryService->petCollectsItem('Mystery Syrup', $pet, $pet->getName() . ' found this in a daydream.', $log);

        return $log;
    }
}