<?php
namespace App\Service;

use App\Entity\Pet;
use App\Entity\PetActivityLog;
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

        $activityLog = null;
        $changes = new PetChanges($pet);

        switch($roll)
        {
            case 1:
            case 2:
            case 3:
            case 4:
                $activityLog = $this->huntedDustBunny($pet);
                break;
            case 5:
            case 6:
            case 7:
                $activityLog = $this->huntedGoat($pet);
                break;
            case 8:
            case 9:
                $activityLog = $this->huntedLargeToad($pet);
                break;
            case 10:
                $activityLog = $this->huntedOnionBoy($pet);
                break;
            /*case 11:
                $this->huntedWindUpGator($pet);
                break;*/
        }

        if($activityLog)
            $activityLog->setChanges($changes->compare($pet));
    }

    private function huntedDustBunny(Pet $pet): PetActivityLog
    {
        $skill = 10 + $pet->getSkills()->getDexterity() + $pet->getSkills()->getBrawl();

        $pet->increaseFood(-1);

        if(\mt_rand(1, $skill) >= 6)
        {
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' pounced on a Dust Bunny, reducing it to Fluff!');
            $this->inventoryService->petCollectsItem('Fluff', $pet, 'The remains of a Dust Bunny that ' . $pet->getName() . ' hunted.');
            $this->petService->gainExp($pet, 1, [ 'dexterity', 'brawl' ]);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' chased a Dust Bunny, but wasn\'t able to catch up with it.');
            $this->petService->gainExp($pet, 1, [ 'dexterity', 'brawl' ]);
        }

        return $activityLog;
    }

    private function huntedGoat(Pet $pet): PetActivityLog
    {
        $skill = 10 + $pet->getSkills()->getStrength() + $pet->getSkills()->getBrawl();

        $pet->increaseFood(-1);

        if(\mt_rand(1, $skill) >= 6)
        {
            $pet->increaseEsteem(1);
            if(\mt_rand(1, 2) === 1)
            {
                $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' wrestled a Goat, and won, receiving Milk.');
                $this->inventoryService->petCollectsItem('Milk', $pet, $pet->getName() . '\'s prize for out-wrestling a Goat.');
            }
            else
            {
                $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' wrestled a Goat, and won, receiving Butter.');
                $this->inventoryService->petCollectsItem('Butter', $pet, $pet->getName() . '\'s prize for out-wrestling a Goat.');
            }
        }
        else
        {
            if(\mt_rand(1, 4) === 1)
            {
                $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' wrestled a Goat. The Goat won.');
                $this->inventoryService->petCollectsItem('Fluff', $pet, $pet->getName() . ' wrestled a Goat, and lost, but managed to grab a fistful of Fluff.');
            }
            else
                $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' wrestled a Goat. The Goat won.');
        }

        $this->petService->gainExp($pet, 1, [ 'strength', 'brawl' ]);

        return $activityLog;
    }

    private function huntedLargeToad(Pet $pet): PetActivityLog
    {
        $skill = 10 + $pet->getSkills()->getStrength() + $pet->getSkills()->getBrawl();

        $pet->increaseFood(-1);

        if(\mt_rand(1, $skill) >= 6)
        {
            $this->petService->gainExp($pet, 1, [ 'strength', 'brawl' ]);

            if(\mt_rand(1, 4) === 1)
            {
                $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' beat up a Giant Toad, and took two of its legs.');
                $this->inventoryService->petCollectsItem('Toad Legs', $pet, $pet->getName() . ' took these from a Giant Toad. It still has two left, so it\'s probably fine >_>');
            }
            else
            {
                $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' wrestled a Toadstool off the back of a Giant Toad.');
                $this->inventoryService->petCollectsItem('Toadstool', $pet, $pet->getName() . ' wrestled this from a Giant Toad.');
            }
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' picked a fight with a Giant Toad, but lost.');
            $pet->increaseEsteem(-2);
            $this->petService->gainExp($pet, 1, [ 'strength', 'brawl' ]);
        }

        return $activityLog;
    }

    private function huntedOnionBoy(Pet $pet): PetActivityLog
    {
        $skill = 10 + $pet->getSkills()->getStamina();

        if(\mt_rand(1, $skill) >= 7)
        {
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' encountered an Onion Boy. The fumes were powerful, but ' . $pet->getName() . ' powered through it.');
            $this->inventoryService->petCollectsItem('Onion', $pet, 'The remains of an Onion Boy that ' . $pet->getName() . ' encountered.');
            $this->petService->gainExp($pet, 2, [ 'stamina' ]);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' encountered an Onion Boy. The fumes were overwhelming, and ' . $pet->getName() . ' fled.');
            $this->petService->gainExp($pet, 1, [ 'stamina' ]);
            $pet->increaseSafety(-2);
        }

        return $activityLog;
    }
}