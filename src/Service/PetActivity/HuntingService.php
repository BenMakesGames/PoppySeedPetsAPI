<?php
namespace App\Service\PetActivity;

use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Enum\UserStatEnum;
use App\Model\PetChanges;
use App\Repository\UserStatsRepository;
use App\Service\InventoryService;
use App\Service\PetService;
use App\Service\ResponseService;

class HuntingService
{
    private $responseService;
    private $inventoryService;
    private $petService;
    private $userStatsRepository;

    public function __construct(
        ResponseService $responseService, InventoryService $inventoryService, PetService $petService,
        UserStatsRepository $userStatsRepository
    )
    {
        $this->responseService = $responseService;
        $this->inventoryService = $inventoryService;
        $this->petService = $petService;
        $this->userStatsRepository = $userStatsRepository;
    }

    public function adventure(Pet $pet)
    {
        $maxSkill = 10 + $pet->getSkills()->getStrength() + $pet->getSkills()->getBrawl() - $pet->getWhack() - $pet->getJunk();

        if($maxSkill > 12) $maxSkill = 12;
        else if($maxSkill < 1) $maxSkill = 1;

        $roll = \mt_rand(1, $maxSkill);

        $activityLog = null;
        $changes = new PetChanges($pet);

        switch($roll)
        {
            case 1:
                $activityLog = $this->failedToHunt($pet);
                break;
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
            case 11:
            case 12:
                $activityLog = $this->huntedThievingMagpie($pet);
                break;
        }

        if($activityLog)
            $activityLog->setChanges($changes->compare($pet));
    }

    private function failedToHunt(Pet $pet): PetActivityLog
    {
        $pet->spendTime(mt_rand(30, 60));
        return $this->responseService->createActivityLog($pet, $pet->getName() . ' went out hunting, but couldn\'t find anything to hunt.');
    }

    private function huntedDustBunny(Pet $pet): PetActivityLog
    {
        $skill = 10 + $pet->getSkills()->getDexterity() + $pet->getSkills()->getBrawl();

        $pet->increaseFood(-1);
        $pet->spendTime(mt_rand(30, 60));

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
        $pet->spendTime(mt_rand(45, 60));

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

        $pet->spendTime(mt_rand(45, 60));

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

        $pet->spendTime(mt_rand(30, 60));

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

    private function huntedThievingMagpie(Pet $pet): PetActivityLog
    {
        $intSkill = 10 + $pet->getSkills()->getIntelligence();
        $dexSkill = 10 + $pet->getSkills()->getDexterity() + $pet->getSkills()->getBrawl();

        $pet->spendTime(mt_rand(45, 60));

        if(\mt_rand(1, $intSkill) <= 2 && $pet->getOwner()->getMoneys() >= 2)
        {
            $moneysLost = \mt_rand(1, 2);
            $this->petService->gainExp($pet, 1, [ 'intelligence', 'brawl' ]);
            $pet->getOwner()->increaseMoneys(-$moneysLost);
            $this->userStatsRepository->incrementStat($pet->getOwner(), UserStatEnum::MONEYS_STOLEN_BY_THIEVING_MAGPIES, $moneysLost);
            $pet->increaseEsteem(-2);
            $pet->increaseSafety(-2);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' was outsmarted by a Thieving Magpie, and lost ' . $moneysLost . ' ' . ($moneysLost === 1 ? 'money' : 'moneys') . '.');
        }
        else if(\mt_rand(1, $dexSkill) >= 9)
        {
            $this->petService->gainExp($pet, 2, [ 'intelligence', 'dexterity', 'brawl' ]);
            $pet->increaseEsteem(2);
            $pet->increaseSafety(2);

            if(mt_rand(1, 4) === 1)
            {
                $moneys = \mt_rand(2, 5);
                $pet->getOwner()->increaseMoneys($moneys);
                $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' pounced on a Thieving Magpie, and liberated its ' . $moneys . ' moneys.');
            }
            else
            {
                $item = [ 'Egg', 'String', 'Rice', 'Plastic' ][mt_rand(0, 3)];
                $this->inventoryService->petCollectsItem($item, $pet, 'Liberated from a Thieving Magpie.');
                $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' pounced on a Thieving Magpie, and liberated ' . ($item === 'Egg' ? 'an' : 'some') . ' ' . $item . '.');
            }
        }
        else
        {
            $this->petService->gainExp($pet, 1, [ 'intelligence', 'dexterity', 'brawl' ]);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to take down a Thieving Magpie, but it got away.');
            $pet->increaseSafety(-1);
        }

        return $activityLog;
    }
}