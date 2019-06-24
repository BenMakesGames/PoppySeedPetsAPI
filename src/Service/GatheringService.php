<?php
namespace App\Service;

use App\Entity\Pet;
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

        $description = null;
        $changes = new PetChanges($pet);

        switch($roll)
        {
            case 1:
            case 2:
            case 3:
            case 4:
                $description = $this->foundNothing($pet, $roll);
                break;
            case 5:
            case 6:
            case 7:
                $description = $this->foundBerryBush($pet);
                break;
            case 8:
            case 9:
                $description = $this->foundHollowLog($pet);
                break;
            case 10:
                $description = $this->foundAbandonedQuarry($pet);
                break;
            case 11:
                $description = $this->foundNothing($pet, $roll);
                break;
            case 12:
                $description = $this->foundBirdNest($pet);
                break;
        }

        if($description)
            $this->responseService->createActivityLog($pet, $description, $changes->compare($pet));
    }

    private function foundAbandonedQuarry(Pet $pet): string
    {
        if($pet->getSkills()->getStrength() < 2)
        {
            $pet->increaseFood(-1);
            $this->petService->gainExp($pet, 1, [ 'strength' ]);
            $pet->spendTime(\mt_rand(45, 75));

            return $pet->getName() . ' found a huge block of Limestone at an Abandoned Quarry, but isn\'t strong enough to lift it...';
        }
        else if($pet->getSkills()->getStrength() < 4)
        {
            $pet->increaseFood(-1);
            $this->petService->gainExp($pet, 1, [ 'strength' ]);
            $this->inventoryService->petCollectsItem('Limestone', $pet, $pet->getName() . ' found this at an Abandoned Quarry. It was really heavy!');
            $pet->spendTime(\mt_rand(45, 75));

            return $pet->getName() . ' found a huge block of Limestone at an Abandoned Quarry, and, with all their might, carried it home.';
        }
        else
        {
            $this->inventoryService->petCollectsItem('Limestone', $pet, $pet->getName() . ' found this at an Abandoned Quarry.');
            $pet->spendTime(\mt_rand(45, 60));

            return $pet->getName() . ' found a huge block of Limestone at an Abandoned Quarry, and carried it home with ease.';
        }
    }

    private function foundNothing(Pet $pet, int $roll): string
    {
        $exp = \ceil($roll / 10);

        $this->petService->gainExp($pet, $exp, ['perception', 'nature']);

        $pet->spendTime(\mt_rand(45, 75));

        return $pet->getName() . ' went out gathering, but couldn\'t find anything.';
    }

    private function foundBerryBush(Pet $pet): string
    {
        if(\mt_rand(1, 8) >= 6)
        {
            $harvest = 'Blueberries';

            $this->inventoryService->petCollectsItem('Blueberries', $pet, $pet->getName() . ' harvested these from a Thorny Blueberries Bush.');

            if(\mt_rand(1, 4) == 1)
                $this->inventoryService->petCollectsItem('Blueberries', $pet, $pet->getName() . ' harvested these from a Thorny Blueberries Bush.');
        }
        else
        {
            $harvest = 'Blackberries';

            $this->inventoryService->petCollectsItem('Blackberries', $pet, $pet->getName() . ' harvested these from a Thorny Blackberries Bush.');

            if(\mt_rand(1, 3) == 1)
                $this->inventoryService->petCollectsItem('Blackberries', $pet, $pet->getName() . ' harvested these from a Thorny Blackberries Bush.');
        }

        $this->petService->gainExp($pet, 1, [ 'perception', 'nature', 'stamina' ]);

        $pet->spendTime(\mt_rand(45, 60));

        if(\mt_rand(1, 10 + $pet->getSkills()->getStamina()) >= 10)
        {
            return $pet->getName() . ' harvested berries from a Thorny ' . $harvest . ' Bush.';
        }
        else
        {
            $pet->increaseSafety(-mt_rand(2, 4));
            return $pet->getName() . ' got scratched up harvesting berries from a Thorny ' . $harvest . ' Bush.';
        }
    }

    private function foundHollowLog(Pet $pet): string
    {
        if(\mt_rand(1, 4) === 1)
        {
            if(\mt_rand(1, 20 + $pet->getSkills()->getDexterity() + $pet->getSkills()->getStrength() + $pet->getSkills()->getStealth()) >= 15)
            {
                $this->inventoryService->petCollectsItem('Toadstool', $pet, $pet->getName() . ' harvested this from the back of a Huge Toad found inside a Hollow Log.');
                $this->petService->gainExp($pet, 2, [ 'perception', 'nature', 'dexterity', 'strength', 'stealth' ]);
                $pet->increaseEsteem(\mt_rand(1, 2));
                $pet->spendTime(\mt_rand(45, 60));
                return $pet->getName() . ' found a Huge Toad inside a Hollow Log, got the jump on it, wrestled it to the ground, and claimed its Toadstool!';
            }
            else
            {
                $this->petService->gainExp($pet, 1, [ 'perception', 'nature', 'dexterity', 'strength', 'stealth' ]);
                $pet->spendTime(\mt_rand(45, 60));
                return $pet->getName() . ' found a Huge Toad inside a Hollow Log, but it got away!';
            }
        }
        else
        {
            $this->inventoryService->petCollectsItem('Crooked Stick', $pet, $pet->getName() . ' broke this off of a Hollow Log.');
            $this->petService->gainExp($pet, 1, [ 'perception', 'nature' ]);
            $pet->spendTime(\mt_rand(30, 45));
            return $pet->getName() . ' broke a Crooked Branch off of a Hollow Log.';
        }
    }

    private function foundBirdNest(Pet $pet): string
    {
        if(\mt_rand(1, 20 + $pet->getSkills()->getStealth() + $pet->getSkills()->getDexterity()) >= 10)
        {
            $this->inventoryService->petCollectsItem('Egg', $pet, $pet->getName() . ' stole this from a Bird Nest.');

            if(\mt_rand(1, 20 + $pet->getSkills()->getPerception()) >= 10)
                $this->inventoryService->petCollectsItem('Fluff', $pet, $pet->getName() . ' stole this from a Bird Nest, after a fight.');

            $pet->increaseEsteem(\mt_rand(1, 2));
            $this->petService->gainExp($pet, 2, [ 'perception', 'nature', 'stealth', 'dexterity' ]);

            $pet->spendTime(\mt_rand(45, 60));

            return $pet->getName() . ' stole an Egg from a Bird Nest.';
        }
        else
        {
            if(\mt_rand(1, 20 + $pet->getSkills()->getStrength() + $pet->getSkills()->getDexterity() + $pet->getSkills()->getBrawl()) >= 15)
            {
                $this->inventoryService->petCollectsItem('Egg', $pet, $pet->getName() . ' stole this from a Bird Nest, after a fight.');
                $this->inventoryService->petCollectsItem('Fluff', $pet, $pet->getName() . ' stole this from a Bird Nest, after a fight.');
                $this->petService->gainExp($pet, 2, [ 'perception', 'nature', 'stealth', 'dexterity', 'strength', 'brawl' ]);
                $pet->spendTime(\mt_rand(45, 75));
                return $pet->getName() . ' tried to steal an Egg from a Bird Nest, was spotted by a parent, and was able to defeat it in combat!';
            }
            else
            {
                $this->petService->gainExp($pet, 1, [ 'perception', 'nature', 'stealth', 'dexterity' ]);
                $pet->increaseEsteem(-\mt_rand(1, 2));
                $pet->spendTime(\mt_rand(45, 75));
                return $pet->getName() . ' tried to steal an Egg from a Bird Nest, but was spotted by a parent, and chased off!';
            }
        }
    }
}