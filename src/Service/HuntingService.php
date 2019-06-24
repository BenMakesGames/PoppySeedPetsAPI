<?php
namespace App\Service;

use App\Entity\Pet;
use App\Model\PetChanges;

class HuntingService
{
    private $responseService;
    private $inventoryService;
    private $petService;

    public function __construct(ResponseService $responseService, InventoryService $inventoryService, PetService $petService)
    {
        $this->responseService = $responseService;
        $this->inventoryService = $inventoryService;
        $this->petService = $petService;
    }

    public function adventure(Pet $pet)
    {
        $maxSkill = 10 + $pet->getSkills()->getStrength() + $pet->getSkills()->getBrawl() - $pet->getWhack() - $pet->getJunk();

        if($maxSkill > 10) $maxSkill = 10;

        $roll = \mt_rand(1, $maxSkill);

        $description = null;
        $changes = new PetChanges($pet);

        switch($roll)
        {
            case 1:
            case 2:
            case 3:
            case 4:
                $this->huntedDustBunny($pet);
                break;
            case 5:
            case 6:
            case 7:
                $this->huntedGoat($pet);
                break;
            case 8:
            case 9:
                $this->huntedLargeToad($pet);
                break;
            case 10:
                $this->huntedOnionBoy($pet);
                break;
            /*case 11:
                $this->huntedWindUpGator($pet);
                break;*/
        }

        if($description)
            $this->responseService->createActivityLog($pet, $description, $changes->compare($pet));
    }

    private function huntedDustBunny(Pet $pet): string
    {
        $skill = 10 + $pet->getSkills()->getDexterity() + $pet->getSkills()->getBrawl();

        $pet->increaseFood(-1);

        if(\mt_rand(1, $skill) >= 6)
        {
            $this->inventoryService->petCollectsItem('Fluff', $pet, 'The remains of a Dust Bunny that ' . $pet->getName() . ' hunted.');
            $this->petService->gainExp($pet, 1, [ 'dexterity', 'brawl' ]);
            return $pet->getName() . ' pounced on a Dust Bunny, reducing it to Fluff!';
        }
        else
        {
            $this->petService->gainExp($pet, 1, [ 'dexterity', 'brawl' ]);
            return $pet->getName() . ' chased a Dust Bunny, but wasn\'t able to catch up with it.';
        }
    }

    private function huntedGoat(Pet $pet)
    {
        $skill = 10 + $pet->getSkills()->getStrength() + $pet->getSkills()->getBrawl();

        $pet->increaseFood(-1);
        $this->petService->gainExp($pet, 1, [ 'strength', 'brawl' ]);

        if(\mt_rand(1, $skill) >= 6)
        {
            if(\mt_rand(1, 2) === 1)
            {
                $this->inventoryService->petCollectsItem('Milk', $pet, $pet->getName() . '\'s prize for out-wrestling a Goat.');
                return $pet->getName() . ' wrestled a Goat, and won, receiving Milk.';
            }
            else
            {
                $this->inventoryService->petCollectsItem('Fluff', $pet, $pet->getName() . '\'s prize for out-wrestling a Goat.');
                return $pet->getName() . ' wrestled a Goat, and won, receiving Fluff.';
            }
        }
        else
        {
            $this->petService->gainExp($pet, 1, [ 'strength', 'brawl' ]);
            if(\mt_rand(1, 4) === 1)
            {
                $this->inventoryService->petCollectsItem('Fluff', $pet, $pet->getName() . ' wrestled a Goat, and lost, but managed to grab a bit of Fluff.');
                return $pet->getName() . ' wrestled a Goat. The Goat won.';
            }
            else
                return $pet->getName() . ' wrestled a Goat. The Goat won.';
        }
    }

    private function huntedLargeToad(Pet $pet): string
    {
        $skill = 10 + $pet->getSkills()->getStrength() + $pet->getSkills()->getBrawl();

        $pet->increaseFood(-1);

        if(\mt_rand(1, $skill) >= 6)
        {
            $this->petService->gainExp($pet, 1, [ 'strength', 'brawl' ]);

            if(\mt_rand(1, 4) === 1)
            {
                $this->inventoryService->petCollectsItem('Toad Legs', $pet, $pet->getName() . ' took these from a Giant Toad. It still has two left, so it\'s probably fine >_>');
                return $pet->getName() . ' beat up a Giant Toad, and took two of its legs.';
            }
            else
            {
                $this->inventoryService->petCollectsItem('Toadstool', $pet, $pet->getName() . ' wrestled this from a Giant Toad.');
                return $pet->getName() . ' wrestled a Toadstool off the back of a Giant Toad.';
            }
        }
        else
        {
            $pet->increaseEsteem(-2);
            $this->petService->gainExp($pet, 1, [ 'strength', 'brawl' ]);
            return $pet->getName() . ' picked a fight with a Giant Toad, but lost.';
        }
    }

    private function huntedOnionBoy(Pet $pet): string
    {
        $skill = 10 + $pet->getSkills()->getStamina();

        if(\mt_rand(1, $skill) >= 7)
        {
            $this->inventoryService->petCollectsItem('Onion', $pet, 'The remains of an Onion Boy that ' . $pet->getName() . ' encountered.');
            $this->petService->gainExp($pet, 2, [ 'stamina' ]);
            return $pet->getName() . ' encountered an Onion Boy. The fumes were powerful, but ' . $pet->getName() . ' powered through it.';
        }
        else
        {
            $this->petService->gainExp($pet, 1, [ 'stamina' ]);
            $pet->increaseSafety(-2);
            return $pet->getName() . ' encountered an Onion Boy. The fumes were overwhelming, and ' . $pet->getName() . ' fled.';
        }
    }
}