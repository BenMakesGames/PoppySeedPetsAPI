<?php
namespace App\Service\PetActivity;

use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Enum\MeritEnum;
use App\Enum\PetActivityStatEnum;
use App\Enum\PetSkillEnum;
use App\Functions\ArrayFunctions;
use App\Functions\NumberFunctions;
use App\Model\PetChanges;
use App\Service\InventoryService;
use App\Service\PetExperienceService;
use App\Service\ResponseService;

class MagicBeanstalkService
{
    private $responseService;
    private $petExperienceService;
    private $inventoryService;

    public function __construct(
        ResponseService $responseService, InventoryService $inventoryService, PetExperienceService $petExperienceService
    )
    {
        $this->responseService = $responseService;
        $this->inventoryService = $inventoryService;
        $this->petExperienceService = $petExperienceService;
    }

    public function adventure(Pet $pet)
    {
        $maxSkill = 10 + floor(($pet->getStrength() + $pet->getStamina()) * 1.5) + ceil($pet->getNature() / 2) + $pet->getClimbing() - $pet->getAlcohol() * 2;

        $maxSkill = NumberFunctions::constrain($maxSkill, 1, 21);

        $roll = mt_rand(1, $maxSkill);

        $activityLog = null;
        $changes = new PetChanges($pet);

        switch($roll)
        {
            case 1:
            case 2:
            case 3:
            case 4:
                $activityLog = $this->badClimber($pet);
                break;
            case 5:
            case 6:
                $activityLog = $this->getBeans($pet);
                break;
            case 7:
                $activityLog = $this->getReallyBigLeaf($pet);
                break;
            case 8:
            case 9:
            case 10:
                $activityLog = $this->foundBirdNest($pet, $roll);
                break;
            case 11:
                if(mt_rand(1, 4) === 1)
                    $activityLog = $this->foundBugSwarm($pet);
                else
                    $activityLog = $this->foundBirdNest($pet, $roll);
                break;
            case 12:
            case 13:
            case 14:
                $activityLog = $this->foundNothing($pet);
                break;
            case 15:
            case 16:
            case 17:
                $activityLog = $this->foundPegasusNest($pet);
                break;
            case 18:
                $activityLog = $this->foundLightning($pet);
                break;
            case 19:
                $activityLog = $this->foundEverice($pet);
                break;
            case 20:
            case 21:
                $activityLog = $this->foundGiantCastle($pet);
                break;
        }

        if($activityLog)
            $activityLog->setChanges($changes->compare($pet));

        if(mt_rand(1, 75) === 1)
            $this->inventoryService->petAttractsRandomBug($pet);
    }

    private function badClimber(Pet $pet): PetActivityLog
    {
        $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE ]);

        $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::GATHER, false);

        return $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to climb the magic bean-stalk in your greenhouse, but wasn\'t able to make any progress...', 'icons/activity-logs/confused');
    }

    private function getBeans(Pet $pet): PetActivityLog
    {
        $meters = mt_rand(10, 16) / 2;

        $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' climbed your magic bean-stalk, getting as high as ~' . $meters . ' meters. There, perhaps unsurprisingly, they found some Beans.', 'items/legume/beans');

        $this->inventoryService->petCollectsItem('Beans', $pet, $pet->getName() . ' harvested this from your magic bean-stalk.', $activityLog);
        $this->inventoryService->petCollectsItem('Beans', $pet, $pet->getName() . ' harvested this from your magic bean-stalk.', $activityLog);

        $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE ]);
        $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::GATHER, true);

        return $activityLog;
    }

    private function getReallyBigLeaf(Pet $pet): PetActivityLog
    {
        $meters = mt_rand(12, 20) / 2;

        $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' climbed your magic bean-stalk, getting as high as ~' . $meters . ' meters. They didn\'t dare go any higher, but decided to pluck a Really Big Leaf on their way back down.', '');

        $this->inventoryService->petCollectsItem('Really Big Leaf', $pet, $pet->getName() . ' harvested this from your magic bean-stalk.', $activityLog);

        $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE ]);
        $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::GATHER, true);

        return $activityLog;
    }

    private function foundMagicLeaf(Pet $pet, bool $lucky = false): PetActivityLog
    {
        $meters = mt_rand(300, 1800) / 2;

        if($lucky)
        {
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' climbed your magic bean-stalk, getting as high as ~' . $meters . ' meters. There, they spotted a Magic Leaf! Lucky~!', '');

            $this->inventoryService->petCollectsItem('Magic Leaf', $pet, $pet->getName() . ' harvested this from your magic bean-stalk! Lucky~!', $activityLog);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' climbed your magic bean-stalk, getting as high as ~' . $meters . ' meters. There, they spotted a Magic Leaf, so plucked it, and headed back down.', '');

            $this->inventoryService->petCollectsItem('Magic Leaf', $pet, $pet->getName() . ' harvested this from your magic bean-stalk.', $activityLog);
        }

        $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::NATURE ]);
        $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::GATHER, true);

        return $activityLog;
    }

    private function foundBirdNest(Pet $pet, $roll)
    {
        $meters = mt_rand(7 + $roll, 6 + $roll * 2) / 2;

        if(mt_rand(1, 20 + $pet->getStealth() + $pet->getDexterity()) >= 10)
        {
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' climbed your magic bean-stalk, getting as high as ~' . $meters . ' meters. There, they found a bird\'s nest, which they raided.', '');

            $this->inventoryService->petCollectsItem('Egg', $pet, $pet->getName() . ' stole this from a Bird Nest.', $activityLog);

            $perceptionRoll = mt_rand(1, 20 + $pet->getPerception());

            if($perceptionRoll >= 25)
                $this->inventoryService->petCollectsItem('Black Feathers', $pet, $pet->getName() . ' stole this from a Bird Nest.', $activityLog);
            else if($perceptionRoll >= 18)
                $this->inventoryService->petCollectsItem('Feathers', $pet, $pet->getName() . ' stole this from a Bird Nest.', $activityLog);
            else if($perceptionRoll >= 10)
                $this->inventoryService->petCollectsItem('Fluff', $pet, $pet->getName() . ' stole this from a Bird Nest.', $activityLog);

            $pet->increaseEsteem(mt_rand(1, 2));
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE, PetSkillEnum::STEALTH ]);

            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::GATHER, true);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' climbed your magic bean-stalk, getting as high as ~' . $meters . ' meters. They found a bird nest, but the mother bird was around, and it didn\'t seem safe to pick a fight up there, so ' . $pet->getName() . ' left it alone.', '');

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE, PetSkillEnum::STEALTH ]);

            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::GATHER, true);
        }

        return $activityLog;
    }

    private function foundBugSwarm(Pet $pet): PetActivityLog
    {
        $meters = mt_rand(100, 200) / 2;

        $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' climbed your magic bean-stalk, getting as high as ~' . $meters . ' meters! A huge swarm of bugs flew by, and ' . $pet->getName() . ' had to hold on for dear life!', '');

        $numBugs = mt_rand(2, 5);

        for($i = 0; $i < $numBugs; $i++)
            $this->inventoryService->petCollectsItem('Stink Bug', $pet, 'A swarm of these flew past ' . $pet->getName() . ' while they were climbing your magic bean-stalk. I guess this one hitched a ride back down.', $activityLog);

        $pet->increaseSafety(-mt_rand(2, 8));

        $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE ]);
        $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::GATHER, true);

        return $activityLog;
    }

    private function foundNothing(Pet $pet): PetActivityLog
    {
        if(mt_rand(1, 50) === 1)
            return $this->foundMagicLeaf($pet);
        else if($pet->hasMerit(MeritEnum::LUCKY) && mt_rand(1, 10) === 1)
            return $this->foundMagicLeaf($pet, true);

        $meters = mt_rand(300, 1800) / 2;

        $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' climbed your magic bean-stalk, getting as high as ~' . $meters . ' meters! There wasn\'t anything noteworthy up there, but it was a good work-out!', '');

        $pet->increaseEsteem(mt_rand(2, 4));

        $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::NATURE ]);
        $this->petExperienceService->spendTime($pet, mt_rand(45, 75), PetActivityStatEnum::GATHER, true);

        return $activityLog;
    }

    private function foundPegasusNest(Pet $pet)
    {
        $meters = mt_rand(2000, 3000) / 2;

        if(mt_rand(1, 20 + $pet->getStealth() + $pet->getDexterity()) >= 18)
        {
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' climbed your magic bean-stalk, getting as high as ~' . $meters . ' meters! There, they found a white pegasus\' nest, which they raided.', '');

            $this->inventoryService->petCollectsItem('Egg', $pet, $pet->getName() . ' stole this from a white Pegasus nest.', $activityLog);

            $perceptionRoll = mt_rand(1, 20 + $pet->getPerception());

            if($perceptionRoll >= 18)
                $this->inventoryService->petCollectsItem('White Feathers', $pet, $pet->getName() . ' stole this from a white Pegasus nest.', $activityLog);
            else if($perceptionRoll >= 10)
                $this->inventoryService->petCollectsItem('Fluff', $pet, $pet->getName() . ' stole this from a white Pegasus nest.', $activityLog);

            $pet->increaseEsteem(mt_rand(1, 2));
            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::NATURE, PetSkillEnum::STEALTH ]);

            $this->petExperienceService->spendTime($pet, mt_rand(45, 75), PetActivityStatEnum::GATHER, true);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' climbed your magic bean-stalk, getting as high as ~' . $meters . ' meters! They found a white pegasus\' nest, but the mother was around, and it didn\'t seem safe to pick a fight up there, so ' . $pet->getName() . ' left it alone.', '');

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::NATURE, PetSkillEnum::STEALTH ]);

            $this->petExperienceService->spendTime($pet, mt_rand(45, 75), PetActivityStatEnum::GATHER, true);
        }

        return $activityLog;
    }

    private function foundEverice(Pet $pet)
    {
        $meters = mt_rand(3200, 3800) / 2;

        if(mt_rand(1, 20 + $pet->getStrength() + $pet->getNature() + $pet->getGathering()) >= 18)
        {
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' climbed your magic bean-stalk, getting as high as ~' . $meters . ' meters! There, they found some Everice stuck to part of the stalk, and pried a piece off.', '');

            $this->inventoryService->petCollectsItem('Everice', $pet, $pet->getName() . ' pried this off your magic bean-stalk.', $activityLog);

            $pet->increaseEsteem(mt_rand(1, 2));
            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::NATURE, PetSkillEnum::STEALTH ]);

            $this->petExperienceService->spendTime($pet, mt_rand(45, 75), PetActivityStatEnum::GATHER, true);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' climbed your magic bean-stalk, getting as high as ~' . $meters . ' meters! There, they found some Everice stuck to part of the stalk, but were unable to pry any off...', '');

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::NATURE, PetSkillEnum::STEALTH ]);

            $this->petExperienceService->spendTime($pet, mt_rand(45, 75), PetActivityStatEnum::GATHER, true);
        }

        return $activityLog;
    }

    private function foundLightning(Pet $pet)
    {
        $meters = mt_rand(3200, 3800) / 2;

        if(mt_rand(1, 20 + $pet->getDexterity() + $pet->getScience() + $pet->getGathering()) >= 20)
        {
            if(mt_rand(1, 10) === 1)
            {
                $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' climbed your magic bean-stalk, getting as high as ~' . $meters . ' meters! A dark cloud swirled overhead, and ' . $pet->getName() . ' was nearly struck by lightning, but managed to capture it in a bottle, instead! Oh, but wait, it wasn\'t lightning, at all! Merely lightning _bugs!_', '');

                $this->inventoryService->petCollectsItem('Jar of Fireflies', $pet, $pet->getName() . ' captured this while climbing your magic bean-stalk.', $activityLog);
            }
            else
            {
                $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' climbed your magic bean-stalk, getting as high as ~' . $meters . ' meters! A dark cloud swirled overhead, and ' . $pet->getName() . ' was nearly struck by lightning, but managed to capture it in a bottle, instead!', '');

                $this->inventoryService->petCollectsItem('Lightning in a Bottle', $pet, $pet->getName() . ' captured this while climbing your magic bean-stalk.', $activityLog);
            }

            $pet->increaseEsteem(3);
            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::NATURE, PetSkillEnum::SCIENCE ]);

            $this->petExperienceService->spendTime($pet, mt_rand(45, 75), PetActivityStatEnum::GATHER, true);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' climbed your magic bean-stalk, getting as high as ~' . $meters . ' meters! A dark cloud swirled overhead, and ' . $pet->getName() . ' was nearly struck by lightning!', '');

            $pet->increaseSafety(-mt_rand(4, 8));
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::NATURE, PetSkillEnum::SCIENCE ]);

            $this->petExperienceService->spendTime($pet, mt_rand(45, 75), PetActivityStatEnum::GATHER, true);
        }

        return $activityLog;
    }

    private function foundGiantCastle(Pet $pet)
    {
        if(mt_rand(1, 20 + $pet->getStealth() + $pet->getDexterity()) >= 20)
        {
            $possibleLoot = [
                'Wheat Flour', 'Gold Bar', 'Linens and Things', 'Pamplemousse', 'Cheese', 'Fig', 'Puddin\' Rec\'pes',
            ];

            $loot = [
                'Fluff',
                ArrayFunctions::pick_one($possibleLoot),
            ];

            if(mt_rand(1, 40 - $pet->getPerception()) === 1)
                $loot[] = 'Very Strongbox';
            else if(mt_rand(1, 20 + $pet->getPerception()) >= 20)
                $loot[] = ArrayFunctions::pick_one($possibleLoot);

            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' climbed your magic bean-stalk all the way to the clouds, and found a huge castle! They explored it for a little while, eventually making off with ' . ArrayFunctions::list_nice($loot) . '!', '');

            foreach($loot as $item)
                $this->inventoryService->petCollectsItem($item, $pet, $pet->getName() . ' stole this from a giant castle above the clouds!', $activityLog);

            $this->petExperienceService->gainExp($pet, 4, [ PetSkillEnum::NATURE, PetSkillEnum::STEALTH ]);

            $this->petExperienceService->spendTime($pet, mt_rand(45, 75), PetActivityStatEnum::GATHER, true);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' climbed your magic bean-stalk all the way to the clouds, and found a huge castle! They explored it for a little while, but were spotted by a giant, and forced to flee!', '');

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::NATURE, PetSkillEnum::STEALTH ]);

            $this->petExperienceService->spendTime($pet, mt_rand(45, 75), PetActivityStatEnum::GATHER, true);
        }

        return $activityLog;
    }

}
