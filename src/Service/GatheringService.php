<?php
namespace App\Service;

use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Model\PetChanges;

class GatheringService
{
    private $responseService;
    private $petService;

    public function __construct(
        ResponseService $responseService, InventoryService $inventoryService, PetService $petService
    )
    {
        $this->responseService = $responseService;
        $this->inventoryService = $inventoryService;
        $this->petService = $petService;
    }

    public function adventure(Pet $pet)
    {
        $maxSkill = 10 + $pet->getSkills()->getPerception() + $pet->getSkills()->getNature() - $pet->getWhack() - $pet->getJunk();

        if($maxSkill > 12) $maxSkill = 12;

        $roll = \mt_rand(1, $maxSkill);

        $activityLog = null;
        $changes = new PetChanges($pet);

        switch($roll)
        {
            case 1:
            case 2:
            case 3:
            case 4:
                $activityLog = $this->foundNothing($pet, $roll);
                break;
            case 5:
                $activityLog = $this->foundTeaBush($pet);
                break;
            case 6:
            case 7:
                $activityLog = $this->foundBerryBush($pet);
                break;
            case 8:
            case 9:
                $activityLog = $this->foundHollowLog($pet);
                break;
            case 10:
                $activityLog = $this->foundAbandonedQuarry($pet);
                break;
            case 11:
                $activityLog = $this->foundNothing($pet, $roll);
                break;
            case 12:
                $activityLog = $this->foundBirdNest($pet);
                break;
        }

        if($activityLog)
            $activityLog->setChanges($changes->compare($pet));
    }

    private function foundAbandonedQuarry(Pet $pet): PetActivityLog
    {
        if($pet->getSkills()->getStrength() < 2)
        {
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' found a huge block of Limestone at an Abandoned Quarry, but isn\'t strong enough to lift it...');
            $pet->increaseFood(-1);
            $this->petService->gainExp($pet, 1, [ 'strength' ]);
            $pet->spendTime(\mt_rand(45, 75));
        }
        else if($pet->getSkills()->getStrength() < 4)
        {
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' found a huge block of Limestone at an Abandoned Quarry, and, with all their might, carried it home.');
            $pet->increaseFood(-1);
            $this->petService->gainExp($pet, 1, [ 'strength' ]);
            $this->inventoryService->petCollectsItem('Limestone', $pet, $pet->getName() . ' found this at an Abandoned Quarry. It was really heavy!');
            $pet->spendTime(\mt_rand(45, 75));
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' found a huge block of Limestone at an Abandoned Quarry, and carried it home with ease.');
            $this->inventoryService->petCollectsItem('Limestone', $pet, $pet->getName() . ' found this at an Abandoned Quarry.');
            $pet->spendTime(\mt_rand(45, 60));
        }

        return $activityLog;
    }

    private function foundNothing(Pet $pet, int $roll): PetActivityLog
    {
        $exp = \ceil($roll / 10);

        $this->petService->gainExp($pet, $exp, ['perception', 'nature']);

        $pet->spendTime(\mt_rand(45, 75));

        return $this->responseService->createActivityLog($pet, $pet->getName() . ' went out gathering, but couldn\'t find anything.');
    }

    private function foundTeaBush(Pet $pet): PetActivityLog
    {
        $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' found a Tea Bush, and grabbed a few Tea Leaves.');

        $this->inventoryService->petCollectsItem('Tea Leaves', $pet, $pet->getName() . ' harvested this from a Tea Bush.');
        $this->inventoryService->petCollectsItem('Tea Leaves', $pet, $pet->getName() . ' harvested this from a Tea Bush.');

        if(\mt_rand(1, 2) === 1)
            $this->inventoryService->petCollectsItem('Tea Leaves', $pet, $pet->getName() . ' harvested this from a Tea Bush.');

        $this->petService->gainExp($pet, 1, [ 'perception', 'nature' ]);
        $pet->spendTime(\mt_rand(45, 60));

        return $activityLog;
    }

    private function foundBerryBush(Pet $pet): PetActivityLog
    {
        if(\mt_rand(1, 8) >= 6)
        {
            $harvest = 'Blueberries';
            $additionalHarvest = mt_rand(1, 4) === 1;
        }
        else
        {
            $harvest = 'Blackberries';
            $additionalHarvest = mt_rand(1, 3) === 1;
        }


        if(\mt_rand(1, 10 + $pet->getSkills()->getStamina()) >= 10)
        {
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' harvested berries from a Thorny ' . $harvest . ' Bush.');
        }
        else
        {
            $pet->increaseSafety(-mt_rand(2, 4));
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' got scratched up harvesting berries from a Thorny ' . $harvest . ' Bush.');
        }

        $this->inventoryService->petCollectsItem($harvest, $pet, $pet->getName() . ' harvested these from a Thorny ' . $harvest . ' Bush.');

        if($additionalHarvest)
            $this->inventoryService->petCollectsItem($harvest, $pet, $pet->getName() . ' harvested these from a Thorny ' . $harvest . ' Bush.');

        $this->petService->gainExp($pet, 1, [ 'perception', 'nature', 'stamina' ]);

        $pet->spendTime(\mt_rand(45, 60));

        return $activityLog;
    }

    private function foundHollowLog(Pet $pet): PetActivityLog
    {
        if(\mt_rand(1, 4) === 1)
        {
            if(\mt_rand(1, 20 + $pet->getSkills()->getDexterity() + $pet->getSkills()->getStrength() + $pet->getSkills()->getStealth()) >= 15)
            {
                $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' found a Huge Toad inside a Hollow Log, got the jump on it, wrestled it to the ground, and claimed its Toadstool!');
                $this->inventoryService->petCollectsItem('Toadstool', $pet, $pet->getName() . ' harvested this from the back of a Huge Toad found inside a Hollow Log.');
                $this->petService->gainExp($pet, 2, [ 'perception', 'nature', 'dexterity', 'strength', 'stealth' ]);
                $pet->increaseEsteem(\mt_rand(1, 2));
                $pet->spendTime(\mt_rand(45, 60));
            }
            else
            {
                $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' found a Huge Toad inside a Hollow Log, but it got away!');
                $this->petService->gainExp($pet, 1, [ 'perception', 'nature', 'dexterity', 'strength', 'stealth' ]);
                $pet->spendTime(\mt_rand(45, 60));
            }
        }
        else
        {
            if(\mt_rand(1, 2) === 1)
            {
                $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' broke a Crooked Branch off of a Hollow Log.');
                $this->inventoryService->petCollectsItem('Crooked Stick', $pet, $pet->getName() . ' broke this off of a Hollow Log.');
            }
            else
            {
                $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' found a Grandparoot inside a Hollow Log.');
                $this->inventoryService->petCollectsItem('Grandparoot', $pet, $pet->getName() . ' found this growing inside a Hollow Log.');
            }

            $this->petService->gainExp($pet, 1, [ 'perception', 'nature' ]);
            $pet->spendTime(\mt_rand(30, 45));
        }

        return $activityLog;
    }

    private function foundBirdNest(Pet $pet): PetActivityLog
    {
        if(\mt_rand(1, 20 + $pet->getSkills()->getStealth() + $pet->getSkills()->getDexterity()) >= 10)
        {
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' stole an Egg from a Bird Nest.');
            $this->inventoryService->petCollectsItem('Egg', $pet, $pet->getName() . ' stole this from a Bird Nest.');

            if(\mt_rand(1, 20 + $pet->getSkills()->getPerception()) >= 10)
                $this->inventoryService->petCollectsItem('Fluff', $pet, $pet->getName() . ' stole this from a Bird Nest, after a fight.');

            $pet->increaseEsteem(\mt_rand(1, 2));
            $this->petService->gainExp($pet, 2, [ 'perception', 'nature', 'stealth', 'dexterity' ]);

            $pet->spendTime(\mt_rand(45, 60));
        }
        else
        {
            if(\mt_rand(1, 20 + $pet->getSkills()->getStrength() + $pet->getSkills()->getDexterity() + $pet->getSkills()->getBrawl()) >= 15)
            {
                $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to steal an Egg from a Bird Nest, was spotted by a parent bird, and was able to defeat it in combat!');
                $this->inventoryService->petCollectsItem('Egg', $pet, $pet->getName() . ' stole this from a Bird Nest, after a fight.');
                $this->inventoryService->petCollectsItem('Fluff', $pet, $pet->getName() . ' stole this from a Bird Nest, after a fight.');
                $this->petService->gainExp($pet, 2, [ 'perception', 'nature', 'stealth', 'dexterity', 'strength', 'brawl' ]);
                $pet->spendTime(\mt_rand(45, 75));
            }
            else
            {
                $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to steal an Egg from a Bird Nest, but was spotted by a parent bird, and chased off!');
                $this->petService->gainExp($pet, 1, [ 'perception', 'nature', 'stealth', 'dexterity' ]);
                $pet->increaseEsteem(-\mt_rand(1, 2));
                $pet->spendTime(\mt_rand(45, 75));
            }
        }

        return $activityLog;
    }
}