<?php
namespace App\Service;

use App\Entity\Pet;

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

        switch($roll)
        {
            case 1:
            case 2:
            case 3:
            case 4:
                $this->foundNothing($pet, $roll);
                break;
            case 5:
            case 6:
            case 7:
                $this->foundBerryBush($pet);
                break;
            case 8:
                //$this->foundAbandonedGarden($pet);
                //break;
            case 9:
            case 10:
                $this->foundHollowLog($pet);
                break;
            case 11:
                $this->foundNothing($pet, $roll);
                break;
            case 12:
                $this->foundBirdNest($pet);
                break;
        }
    }

    private function foundNothing(Pet $pet, int $roll)
    {
        $this->responseService->createActivityLog($pet, $pet->getName() . ' went out gathering, but couldn\'t find anything.');

        $exp = \ceil($roll / 10);

        $this->petService->gainExp($pet, $exp, ['perception', 'nature']);

        $pet->spendTime(\mt_rand(45, 75));
    }

    private function foundBerryBush(Pet $pet)
    {
        if(\mt_rand(1, 10 + $pet->getSkills()->getStamina()) >= 10)
        {
            $this->responseService->createActivityLog($pet, $pet->getName() . ' harvested berries from a Thorny Berry Bush.');
        }
        else
        {
            $this->responseService->createActivityLog($pet, $pet->getName() . ' got scratched up harvesting berries from a Thorny Berry Bush.');
            $pet->increaseSafety(-mt_rand(2, 4));
        }

        if(\mt_rand(1, 8) >= 6)
        {
            $this->inventoryService->giveCopyOfItem('Blueberries', $pet->getOwner(), $pet->getOwner(), $pet->getName() . ' harvested these from a Thorny Berry Bush.');

            if(\mt_rand(1, 4) == 1)
                $this->inventoryService->giveCopyOfItem('Blueberries', $pet->getOwner(), $pet->getOwner(), $pet->getName() . ' harvested these from a Thorny Berry Bush.');
        }
        else
        {
            $this->inventoryService->giveCopyOfItem('Blackberries', $pet->getOwner(), $pet->getOwner(), $pet->getName() . ' harvested these from a Thorny Berry Bush.');

            if(\mt_rand(1, 3) == 1)
                $this->inventoryService->giveCopyOfItem('Blackberries', $pet->getOwner(), $pet->getOwner(), $pet->getName() . ' harvested these from a Thorny Berry Bush.');
        }

        $this->petService->gainExp($pet, 1, [ 'perception', 'nature', 'stamina' ]);

        $pet->spendTime(\mt_rand(45, 60));
    }

    private function foundHollowLog(Pet $pet)
    {
        if(\mt_rand(1, 4) === 1)
        {
            if(\mt_rand(1, 20 + $pet->getSkills()->getDexterity() + $pet->getSkills()->getStrength() + $pet->getSkills()->getStealth()) >= 15)
            {
                $this->responseService->createActivityLog($pet, $pet->getName() . ' found a Huge Toad inside a Hollow Log, got the jump on it, wrestled it to the ground, and claimed its Toadstool!');
                $this->inventoryService->giveCopyOfItem('Toadstool', $pet->getOwner(), $pet->getOwner(), $pet->getName() . ' harvested this from the back of a Huge Toad found inside a Hollow Log.');
                $this->petService->gainExp($pet, 2, [ 'perception', 'nature', 'dexterity', 'strength', 'stealth' ]);
                $pet->increaseEsteem(\mt_rand(1, 2));
            }
            else
            {
                $this->responseService->createActivityLog($pet, $pet->getName() . ' found a Huge Toad inside a Hollow Log, but it got away!');
                $this->petService->gainExp($pet, 1, [ 'perception', 'nature', 'dexterity', 'strength', 'stealth' ]);
            }

            $pet->spendTime(\mt_rand(45, 60));
        }
        else
        {
            $this->responseService->createActivityLog($pet, $pet->getName() . ' broke a Crooked Branch off of a Hollow Log.');
            $this->inventoryService->giveCopyOfItem('Crooked Stick', $pet->getOwner(), $pet->getOwner(), $pet->getName() . ' broke this off of a Hollow Log.');
            $this->petService->gainExp($pet, 1, [ 'perception', 'nature' ]);

            $pet->spendTime(\mt_rand(30, 45));
        }
    }

    private function foundBirdNest(Pet $pet)
    {
        if(\mt_rand(1, 20 + $pet->getSkills()->getStealth() + $pet->getSkills()->getDexterity()) >= 10)
        {
            $this->responseService->createActivityLog($pet, $pet->getName() . ' stole an Egg from a Bird Nest.');
            $this->inventoryService->giveCopyOfItem('Egg', $pet->getOwner(), $pet->getOwner(), $pet->getName() . ' stole this from a Bird Nest.');

            if(\mt_rand(1, 20 + $pet->getSkills()->getPerception()) >= 10)
                $this->inventoryService->giveCopyOfItem('Fluff', $pet->getOwner(), $pet->getOwner(), $pet->getName() . ' stole this from a Bird Nest, after a fight.');

            $pet->increaseEsteem(\mt_rand(1, 2));
            $this->petService->gainExp($pet, 2, [ 'perception', 'nature', 'stealth', 'dexterity' ]);

            $pet->spendTime(\mt_rand(45, 60));
        }
        else
        {
            if(\mt_rand(1, 20 + $pet->getSkills()->getStrength() + $pet->getSkills()->getDexterity() + $pet->getSkills()->getBrawl()) >= 15)
            {
                $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to steal an Egg from a Bird Nest, was spotted by a parent, and was able to defeat it in combat!');
                $this->inventoryService->giveCopyOfItem('Egg', $pet->getOwner(), $pet->getOwner(), $pet->getName() . ' stole this from a Bird Nest, after a fight.');
                $this->inventoryService->giveCopyOfItem('Fluff', $pet->getOwner(), $pet->getOwner(), $pet->getName() . ' stole this from a Bird Nest, after a fight.');
                $this->petService->gainExp($pet, 2, [ 'perception', 'nature', 'stealth', 'dexterity', 'strength', 'brawl' ]);
                $pet->spendTime(\mt_rand(45, 75));
            }
            else
            {
                $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to steal an Egg from a Bird Nest, but was spotted by a parent, and chased off!');
                $this->petService->gainExp($pet, 1, [ 'perception', 'nature', 'stealth', 'dexterity' ]);
                $pet->increaseEsteem(-\mt_rand(1, 2));
                $pet->spendTime(\mt_rand(45, 75));
            }
        }
    }
}