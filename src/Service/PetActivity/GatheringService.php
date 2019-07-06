<?php
namespace App\Service\PetActivity;

use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Functions\ArrayFunctions;
use App\Model\PetChanges;
use App\Service\InventoryService;
use App\Service\PetService;
use App\Service\ResponseService;

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

        if($maxSkill > 15) $maxSkill = 15;
        else if($maxSkill < 1) $maxSkill = 1;

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
            case 13:
            case 14:
                $activityLog = $this->foundOvergrownGarden($pet);
                break;
            case 15:
                $activityLog = $this->foundIronMine($pet);
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

    private function foundOvergrownGarden(Pet $pet): PetActivityLog
    {
        $possibleLoot = [
            'Carrot', 'Onion', 'Celery',
            'Carrot', 'Onion', 'Celery',
            'Corn', 'Ginger', 'Sweet Beet',
        ];

        $loot = [];
        $didWhat = 'harvested this from an Overgrown Garden';

        if(\mt_rand(1, 20 + $pet->getSkills()->getStealth() + $pet->getSkills()->getDexterity()) < 10)
        {
            $pet->spendTime(\mt_rand(45, 75));
            $pet->increaseFood(-1);

            if(\mt_rand(1, 20) + $pet->getSkills()->getStrength() + $pet->getSkills()->getBrawl() >= 15)
            {
                $loot[] = $possibleLoot[array_rand($possibleLoot)];

                if(\mt_rand(1, 20 + $pet->getSkills()->getPerception() + $pet->getSkills()->getNature()) >= 25)
                    $loot[] = $possibleLoot[array_rand($possibleLoot)];

                if(\mt_rand(1, 20 + $pet->getSkills()->getPerception() + $pet->getSkills()->getNature()) >= 15)
                    $loot[] = 'Talon';

                $this->petService->gainExp($pet, 1, [ 'stealth', 'dexterity', 'strength', 'brawl', 'nature', 'perception' ]);
                $this->petService->gainExp($pet, 1, [ 'strength', 'brawl' ]);
                $pet->increaseEsteem(\mt_rand(1, 2));
                $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' found an Overgrown Garden, but while looking for food, was attacked by an Angry Mole. ' . $pet->getName() . ' defeated the Angry Mole, and took its ' . ArrayFunctions::list_nice($loot) . '.');
                $didWhat = 'defeated an Angry Mole in an Overgrown Garden, and got this';
            }
            else
            {
                $this->petService->gainExp($pet, 1, [ 'stealth', 'dexterity', 'strength', 'brawl', 'nature', 'perception' ]);
                $pet->increaseEsteem(-\mt_rand(1, 2));
                $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' found an Overgrown Garden, but, while looking for food, was attacked and routed by an Angry Mole.');
            }
        }
        else
        {
            $loot[] = $possibleLoot[array_rand($possibleLoot)];

            if(\mt_rand(1, 20 + $pet->getSkills()->getPerception() + $pet->getSkills()->getNature()) >= 15)
                $loot[] = $possibleLoot[array_rand($possibleLoot)];

            if(\mt_rand(1, 20 + $pet->getSkills()->getPerception() + $pet->getSkills()->getNature()) >= 25)
                $loot[] = $possibleLoot[array_rand($possibleLoot)];

            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' found an Overgrown Garden, and harvested ' . ArrayFunctions::list_nice($loot) . '.');
            $pet->spendTime(\mt_rand(45, 60));
        }

        foreach($loot as $itemName)
            $this->inventoryService->petCollectsItem($itemName, $pet, $pet->getName() . ' ' . $didWhat . '.');

        return $activityLog;
    }

    private function foundIronMine(Pet $pet): PetActivityLog
    {
        $pet->spendTime(\mt_rand(60, 75));

        if(\mt_rand(1, 20) + $pet->getSkills()->getStrength() + $pet->getSkills()->getStamina() >= 10)
        {
            $this->petService->gainExp($pet, 2, [ 'strength', 'stamina', 'nature', 'perception' ]);
            $pet->increaseFood(-1);
            if(mt_rand(1, 50) === 1)
            {
                $pet->increaseEsteem(5);
                $loot = 'Gold Ore';
                $punctuation = '!!';
            }
            else if(mt_rand(1, 10) === 1)
            {
                $pet->increaseEsteem(3);
                $loot = 'Silver Ore';
                $punctuation = '!';
            }
            else
            {
                $pet->increaseEsteem(1);
                $loot = 'Iron Ore';
                $punctuation = '.';
            }

            $this->inventoryService->petCollectsItem($loot, $pet, $pet->getName() . ' dug this out of an Old Iron Mine' . $punctuation);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' found an Old Iron Mine, and dug up some ' . $loot . $punctuation);
        }
        else
        {
            $this->petService->gainExp($pet, 1, [ 'strength', 'stamina', 'nature', 'perception' ]);
            $pet->increaseFood(-2);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' found an Old Iron Mine, and tried to do some mining, but got too tired.');
        }

        return $activityLog;
    }
}