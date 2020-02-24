<?php
namespace App\Service\PetActivity;

use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Enum\MeritEnum;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Enum\PetActivityStatEnum;
use App\Enum\PetSkillEnum;
use App\Functions\ArrayFunctions;
use App\Model\PetChanges;
use App\Service\InventoryService;
use App\Service\PetExperienceService;
use App\Service\ResponseService;
use App\Service\TransactionService;

class GatheringService
{
    private $responseService;
    private $inventoryService;
    private $petExperienceService;
    private $transactionService;

    public function __construct(
        ResponseService $responseService, InventoryService $inventoryService, PetExperienceService $petExperienceService,
        TransactionService $transactionService
    )
    {
        $this->responseService = $responseService;
        $this->inventoryService = $inventoryService;
        $this->petExperienceService = $petExperienceService;
        $this->transactionService = $transactionService;
    }

    public function adventure(Pet $pet)
    {
        $maxSkill = 10 + $pet->getPerception() + $pet->getNature() + $pet->getGathering() - $pet->getAlcohol() - $pet->getPsychedelic();

        if($maxSkill > 19) $maxSkill = 19;
        else if($maxSkill < 1) $maxSkill = 1;

        $roll = mt_rand(1, $maxSkill);

        $activityLog = null;
        $changes = new PetChanges($pet);

        switch($roll)
        {
            case 1:
            case 2:
            case 3:
                $activityLog = $this->foundNothing($pet, $roll);
                break;
            case 4:
                $activityLog = $this->foundPaperBag($pet);
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
                $activityLog = $this->foundBeach($pet);
                break;
            case 14:
                $activityLog = $this->foundOvergrownGarden($pet);
                break;
            case 15:
                $activityLog = $this->foundIronMine($pet);
                break;
            case 16:
                $activityLog = $this->foundMicroJungle($pet);
                break;
            case 17:
                $activityLog = $this->foundNothing($pet, $roll);
                break;
            case 18:
                $activityLog = $this->foundWildHedgemaze($pet);
                break;
            case 19:
                $activityLog = $this->foundVolcano($pet);
                break;
        }

        if($activityLog)
            $activityLog->setChanges($changes->compare($pet));

        if(mt_rand(1, 75) === 1)
            $this->inventoryService->petAttractsRandomBug($pet);
    }

    private function foundAbandonedQuarry(Pet $pet): PetActivityLog
    {
        if(mt_rand(1, 2000) < $pet->getPerception())
        {
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' went to an Abandoned Quarry, and happened to find a piece of Striped Microcline!', '');

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE ]);
            $this->inventoryService->petCollectsItem('Striped Microcline', $pet, $pet->getName() . ' found this at an Abandoned Quarry.', $activityLog);
            $this->petExperienceService->spendTime($pet, mt_rand(30, 45), PetActivityStatEnum::GATHER, true);
        }
        else if($pet->getStrength() < 4)
        {
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' found a huge block of Limestone at an Abandoned Quarry, and, with all their might, pushed, dragged, and "rolled" it home.', 'items/mineral/limestone');
            $pet->increaseFood(-2);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE ]);
            $this->inventoryService->petCollectsItem('Limestone', $pet, $pet->getName() . ' found this at an Abandoned Quarry. It was really heavy!', $activityLog);
            $this->petExperienceService->spendTime($pet, mt_rand(60, 75), PetActivityStatEnum::GATHER, true);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' found a huge block of Limestone at an Abandoned Quarry, and carried it home.', 'items/mineral/limestone');
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE ]);
            $this->inventoryService->petCollectsItem('Limestone', $pet, $pet->getName() . ' found this at an Abandoned Quarry.', $activityLog);
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::GATHER, true);
        }

        return $activityLog;
    }

    private function foundNothing(Pet $pet, int $roll): PetActivityLog
    {
        $exp = ceil($roll / 10);

        $this->petExperienceService->gainExp($pet, $exp, [ PetSkillEnum::NATURE ]);

        $this->petExperienceService->spendTime($pet, mt_rand(45, 75), PetActivityStatEnum::GATHER, false);

        return $this->responseService->createActivityLog($pet, $pet->getName() . ' went out gathering, but couldn\'t find anything.', 'icons/activity-logs/confused');
    }

    private function foundPaperBag(Pet $pet): PetActivityLog
    {
        $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' found a Paper Bag just, like, lyin\' around.', 'items/bag/paper');

        $this->inventoryService->petCollectsItem('Paper Bag', $pet, $pet->getName() . ' found this just lyin\' around.', $activityLog);

        $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE ]);
        $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::GATHER, true);

        return $activityLog;
    }

    private function foundTeaBush(Pet $pet): PetActivityLog
    {
        $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' found a Tea Bush, and grabbed a few Tea Leaves.', 'items/veggie/tea-leaves');

        $this->inventoryService->petCollectsItem('Tea Leaves', $pet, $pet->getName() . ' harvested this from a Tea Bush.', $activityLog);
        $this->inventoryService->petCollectsItem('Tea Leaves', $pet, $pet->getName() . ' harvested this from a Tea Bush.', $activityLog);

        if(mt_rand(1, 2) === 1)
            $this->inventoryService->petCollectsItem('Tea Leaves', $pet, $pet->getName() . ' harvested this from a Tea Bush.', $activityLog);

        $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE ]);
        $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::GATHER, true);

        return $activityLog;
    }

    private function foundBerryBush(Pet $pet): PetActivityLog
    {
        if(mt_rand(1, 8) >= 6)
        {
            $harvest = 'Blueberries';
            $additionalHarvest = mt_rand(1, 4) === 1;
        }
        else
        {
            $harvest = 'Blackberries';
            $additionalHarvest = mt_rand(1, 3) === 1;
        }


        if(mt_rand(1, 10 + $pet->getStamina() + $pet->getGathering()) >= 10)
        {
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' harvested berries from a Thorny ' . $harvest . ' Bush.', '');
        }
        else
        {
            $pet->increaseSafety(-mt_rand(2, 4));
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' got scratched up harvesting berries from a Thorny ' . $harvest . ' Bush.', 'icons/activity-logs/wounded');
        }

        $this->inventoryService->petCollectsItem($harvest, $pet, $pet->getName() . ' harvested these from a Thorny ' . $harvest . ' Bush.', $activityLog);

        if($additionalHarvest)
            $this->inventoryService->petCollectsItem($harvest, $pet, $pet->getName() . ' harvested these from a Thorny ' . $harvest . ' Bush.', $activityLog);

        $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE ]);

        $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::GATHER, true);

        return $activityLog;
    }

    private function foundHollowLog(Pet $pet): PetActivityLog
    {
        if(mt_rand(1, 4) === 1)
        {
            if(!$pet->canSeeInTheDark())
            {
                $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' found a Hollow Log, but it was too dark inside to see anything.', '');

                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE ]);
                $this->petExperienceService->spendTime($pet, mt_rand(30, 45), PetActivityStatEnum::GATHER, false);
            }
            else if(mt_rand(1, 20 + $pet->getDexterity() + $pet->getStrength() + $pet->getStealth() + $pet->getBrawl(false)) >= 15)
            {
                $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' found a Huge Toad inside a Hollow Log, got the jump on it, wrestled it to the ground, and claimed its Toadstool!', 'items/fungus/toadstool');
                $this->inventoryService->petCollectsItem('Toadstool', $pet, $pet->getName() . ' harvested this from the back of a Huge Toad found inside a Hollow Log.', $activityLog);
                $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::NATURE, PetSkillEnum::STEALTH, PetSkillEnum::BRAWL ]);
                $pet->increaseEsteem(mt_rand(1, 2));
                $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::HUNT, true);
            }
            else
            {
                $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' found a Huge Toad inside a Hollow Log, but it got away!', '');
                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE, PetSkillEnum::STEALTH, PetSkillEnum::BRAWL ]);
                $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::HUNT, true);
            }
        }
        else
        {
            $success = true;

            if(mt_rand(1, 2) === 1)
            {
                $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' broke a Crooked Stick off of a Hollow Log.', 'items/plant/stick-crooked');
                $this->inventoryService->petCollectsItem('Crooked Stick', $pet, $pet->getName() . ' broke this off of a Hollow Log.', $activityLog);
            }
            else
            {
                if($pet->canSeeInTheDark())
                {
                    $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' found a Grandparoot inside a Hollow Log.', '');
                    $this->inventoryService->petCollectsItem('Grandparoot', $pet, $pet->getName() . ' found this growing inside a Hollow Log.', $activityLog);
                }
                else
                {
                    $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' found a Hollow Log, but it was too dark inside to see anything.', '');
                    $success = false;
                }
            }

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE ]);
            $this->petExperienceService->spendTime($pet, mt_rand(30, 45), PetActivityStatEnum::GATHER, $success);
        }

        return $activityLog;
    }

    private function foundBirdNest(Pet $pet): PetActivityLog
    {
        if(mt_rand(1, 20 + $pet->getStealth() + $pet->getDexterity()) >= 10)
        {
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' stole an Egg from a Bird Nest.', '');
            $this->inventoryService->petCollectsItem('Egg', $pet, $pet->getName() . ' stole this from a Bird Nest.', $activityLog);

            if(mt_rand(1, 20 + $pet->getPerception()) >= 10)
                $this->inventoryService->petCollectsItem('Fluff', $pet, $pet->getName() . ' stole this from a Bird Nest.', $activityLog);

            $pet->increaseEsteem(mt_rand(1, 2));
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::NATURE, PetSkillEnum::STEALTH ]);

            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::GATHER, true);
        }
        else
        {
            if(mt_rand(1, 20 + $pet->getStrength() + $pet->getDexterity() + $pet->getBrawl()) >= 15)
            {
                $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to steal an Egg from a Bird Nest, was spotted by a parent bird, and was able to defeat it in combat!', '');
                $this->inventoryService->petCollectsItem('Egg', $pet, $pet->getName() . ' stole this from a Bird Nest, after a fight.', $activityLog);
                $this->inventoryService->petCollectsItem('Fluff', $pet, $pet->getName() . ' stole this from a Bird Nest, after a fight.', $activityLog);
                $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::NATURE, PetSkillEnum::STEALTH, PetSkillEnum::BRAWL ]);
                $this->petExperienceService->spendTime($pet, mt_rand(45, 75), PetActivityStatEnum::HUNT, true);
            }
            else
            {
                $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to steal an Egg from a Bird Nest, but was spotted by a parent bird, and chased off!', '');
                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE, PetSkillEnum::STEALTH, PetSkillEnum::BRAWL ]);
                $pet->increaseEsteem(-mt_rand(1, 2));
                $this->petExperienceService->spendTime($pet, mt_rand(45, 75), PetActivityStatEnum::HUNT, false);
            }
        }

        return $activityLog;
    }

    private function foundBeach(Pet $pet): PetActivityLog
    {
        $loot = [];
        $didWhat = 'found this at a Sandy Beach';

        if(mt_rand(1, 20 + $pet->getStealth() + $pet->getDexterity()) < 10)
        {
            $pet->increaseFood(-1);

            if(mt_rand(1, 20) + $pet->getStrength() + $pet->getBrawl() >= 15)
            {
                $this->petExperienceService->spendTime($pet, mt_rand(45, 75), PetActivityStatEnum::HUNT, true);

                $loot[] = ArrayFunctions::pick_one([ 'Fish', 'Crooked Stick', 'Egg' ]);

                if(mt_rand(1, 20 + $pet->getPerception() + $pet->getNature()) >= 25)
                    $loot[] = ArrayFunctions::pick_one([ 'Feathers', 'Talon' ]);

                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::STEALTH, PetSkillEnum::BRAWL, PetSkillEnum::NATURE ]);
                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::BRAWL ]);
                $pet->increaseEsteem(mt_rand(1, 2));
                $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' went to a Sandy Beach, but while looking around, was attacked by a Giant Seagull. ' . $pet->getName() . ' defeated the Giant Seagull, and took its ' . ArrayFunctions::list_nice($loot) . '.', '');
                $didWhat = 'defeated a Giant Seagull at the Beach, and got this';
            }
            else
            {
                $this->petExperienceService->spendTime($pet, mt_rand(45, 75), PetActivityStatEnum::HUNT, false);

                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::STEALTH, PetSkillEnum::BRAWL, PetSkillEnum::NATURE ]);
                $pet->increaseEsteem(-mt_rand(1, 2));
                $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' went to a Sandy Beach, but was attacked and routed by a Giant Seagull.', '');
            }
        }
        else
        {
            $possibleLoot = [
                'Scales', 'Silica Grounds', 'Seaweed', 'Coconut',
            ];

            $loot[] = ArrayFunctions::pick_one($possibleLoot);

            if($pet->getTool() && $pet->getTool()->getItem()->getTool()->getFishing() > 0)
                $loot[] = 'Fish';
            else if(mt_rand(1, 20 + $pet->getPerception() + $pet->getNature()) >= 15)
                $loot[] = ArrayFunctions::pick_one($possibleLoot);

            if(mt_rand(1, 20) == 1)
                $loot[] = 'Secret Seashell';

            if(mt_rand(1, 20 + $pet->getPerception() + $pet->getNature() + $pet->getGathering()) >= 25)
            {
                $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::STEALTH, PetSkillEnum::NATURE ]);
                $moneys = mt_rand(4, 12);
                $this->transactionService->getMoney($pet->getOwner(), $moneys, $pet->getName() . ' found this on a Sandy Beach.');
                $lootList = $loot;
                $lootList[] = $moneys . '~~m~~';
                $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' went to a Sandy Beach, and stole ' . ArrayFunctions::list_nice($lootList) . ' while the seagulls weren\'t paying attention.', '');
                $this->petExperienceService->spendTime($pet, mt_rand(45, 75), PetActivityStatEnum::GATHER, true);
            }
            else
            {
                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::STEALTH, PetSkillEnum::NATURE ]);
                $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' went to a Sandy Beach, and stole ' . ArrayFunctions::list_nice($loot) . ' while the seagulls weren\'t paying attention.', '');
                $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::GATHER, true);
            }
        }

        foreach($loot as $itemName)
            $this->inventoryService->petCollectsItem($itemName, $pet, $pet->getName() . ' ' . $didWhat . '.', $activityLog);

        return $activityLog;
    }

    private function foundOvergrownGarden(Pet $pet): PetActivityLog
    {
        $possibleLoot = [
            'Carrot', 'Onion', 'Celery', 'Tomato', 'Beans',
            'Sweet Beet', 'Sweet Beet', 'Ginger', 'Rice Flower'
        ];

        $loot = [];
        $didWhat = 'harvested this from an Overgrown Garden';

        if(mt_rand(1, 20 + $pet->getStealth() + $pet->getDexterity()) < 10)
        {
            $pet->increaseFood(-1);

            if(mt_rand(1, 20) + $pet->getStrength() + $pet->getBrawl() >= 15)
            {
                $this->petExperienceService->spendTime($pet, mt_rand(45, 75), PetActivityStatEnum::HUNT, true);

                $loot[] = ArrayFunctions::pick_one($possibleLoot);

                if(mt_rand(1, 20 + $pet->getPerception() + $pet->getNature() + $pet->getGathering()) >= 25)
                    $loot[] = ArrayFunctions::pick_one($possibleLoot);

                if(mt_rand(1, 20 + $pet->getPerception() + $pet->getNature() + $pet->getGathering()) >= 15)
                    $loot[] = 'Talon';

                $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::STEALTH, PetSkillEnum::BRAWL, PetSkillEnum::NATURE ]);
                $pet->increaseEsteem(mt_rand(1, 2));
                $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' found an Overgrown Garden, but while looking for food, was attacked by an Angry Mole. ' . $pet->getName() . ' defeated the Angry Mole, and took its ' . ArrayFunctions::list_nice($loot) . '.', '');
                $didWhat = 'defeated an Angry Mole in an Overgrown Garden, and got this';
            }
            else
            {
                $this->petExperienceService->spendTime($pet, mt_rand(45, 75), PetActivityStatEnum::HUNT, false);

                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::STEALTH, PetSkillEnum::BRAWL, PetSkillEnum::NATURE ]);
                $pet->increaseEsteem(-mt_rand(1, 2));
                $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' found an Overgrown Garden, but, while looking for food, was attacked and routed by an Angry Mole.', '');
            }
        }
        else
        {
            $loot[] = ArrayFunctions::pick_one($possibleLoot);

            if(mt_rand(1, 20 + $pet->getPerception() + $pet->getNature() + $pet->getGathering()) >= 15)
                $loot[] = ArrayFunctions::pick_one($possibleLoot);

            if(mt_rand(1, 20 + $pet->getPerception() + $pet->getNature() + $pet->getGathering()) >= 25)
                $loot[] = ArrayFunctions::pick_one($possibleLoot);

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::STEALTH, PetSkillEnum::NATURE ]);

            $lucky = false;

            if($pet->hasMerit(MeritEnum::LUCKY) && mt_rand(1, 20) === 1)
            {
                $loot[] = 'Honeydont';
                $lucky = true;
            }
            else if(mt_rand(1, 100) == 1)
                $loot[] = 'Honeydont';

            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' found an Overgrown Garden, and harvested ' . ArrayFunctions::list_nice($loot) . '.', '');

            if($lucky)
            {
                $activityLog
                    ->setEntry($activityLog->getEntry() . ' (Honeydont?! Lucky~!)')
                    ->addInterestingness(PetActivityLogInterestingnessEnum::ACTIVITY_USING_MERIT)
                ;
            }

            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::GATHER, true);
        }

        foreach($loot as $itemName)
            $this->inventoryService->petCollectsItem($itemName, $pet, $pet->getName() . ' ' . $didWhat . '.', $activityLog);

        return $activityLog;
    }

    private function foundIronMine(Pet $pet): PetActivityLog
    {
        if(mt_rand(1, 4) === 1 && !$pet->canSeeInTheDark())
        {
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE ]);
            $this->petExperienceService->spendTime($pet, mt_rand(30, 45), PetActivityStatEnum::GATHER, false);
            return $this->responseService->createActivityLog($pet, $pet->getName() . ' found an Old Iron Mine, but all the ore must have been hidden deep inside, and ' . $pet->getName() . ' didn\'t have a light.', '');
        }

        if(mt_rand(1, 20) + $pet->getStrength() + $pet->getStamina() >= 10)
        {
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::NATURE ]);
            $pet->increaseFood(-1);

            if($pet->hasMerit(MeritEnum::LUCKY) && mt_rand(1, 20) === 1)
            {
                $pet->increaseEsteem(5);

                if(mt_rand(1, 2) === 1)
                    $loot = 'Gold Ore';
                else
                    $loot = 'Silver Ore';

                $punctuation = '! Lucky~!';
            }
            else if(mt_rand(1, 50) === 1)
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

            $this->petExperienceService->spendTime($pet, mt_rand(60, 75), PetActivityStatEnum::GATHER, true);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' found an Old Iron Mine, and dug up some ' . $loot . $punctuation, '');
            $this->inventoryService->petCollectsItem($loot, $pet, $pet->getName() . ' dug this out of an Old Iron Mine' . $punctuation, $activityLog);
        }
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(60, 75), PetActivityStatEnum::GATHER, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE ]);
            $pet->increaseFood(-2);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' found an Old Iron Mine, and tried to do some mining, but got too tired.', '');
        }

        return $activityLog;
    }

    private function foundMicroJungle(Pet $pet): PetActivityLog
    {
        $possibleLoot = [
            'Naner', 'Naner', 'Orange', 'Orange', 'Cocoa Beans', 'Cocoa Beans', 'Coffee Beans',
        ];

        $loot = [];

        $roll = mt_rand(1, 20 + $pet->getPerception() + $pet->getNature() + $pet->getGathering());

        if($roll >= 12)
        {
            $loot[] = ArrayFunctions::pick_one($possibleLoot);

            if($roll >= 16)
                $loot[] = ArrayFunctions::pick_one($possibleLoot);

            if($roll >= 24)
                $loot[] = ArrayFunctions::pick_one($possibleLoot);

            if($roll >= 30 && mt_rand(1, 20) === 1)
                $loot[] = 'Gold Ore';
        }

        sort($loot);

        $this->petExperienceService->spendTime($pet, mt_rand(45, 60) + count($loot) * 5, PetActivityStatEnum::GATHER, count($loot) > 0);

        if(count($loot) === 0)
        {
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' entered the island\'s Micro-Jungle, but couldn\'t find anything.', 'icons/activity-logs/confused');
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE ]);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' entered the island\'s Micro-Jungle, and got ' . ArrayFunctions::list_nice($loot) . '.', '');

            foreach($loot as $itemName)
                $this->inventoryService->petCollectsItem($itemName, $pet, $pet->getName() . ' found this in the island\'s Micro-Jungle.', $activityLog);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::NATURE ]);
        }

        if(mt_rand(1, 10 + $pet->getStamina()) < 6)
        {
            if($pet->hasProtectionFromHeat())
            {
                $activityLog->setEntry($activityLog->getEntry() . ' The Micro-Jungle was hot, but their ' . $pet->getTool()->getItem()->getName() . ' protected them.')
                    ->addInterestingness(PetActivityLogInterestingnessEnum::ACTIVITY_USING_MERIT)
                ;
            }
            else
            {
                $pet->increaseFood(-1);
                $pet->increaseSafety(-mt_rand(1, 2));

                // why need to have unlocked the greenhouse? just testing that you've been playing for a while
                if(mt_rand(1, 20) === 1 && $pet->getOwner()->getUnlockedGreenhouse() !== null)
                    $activityLog->setEntry($activityLog->getEntry() . ' The Micro-Jungle was CRAZY hot, and I don\'t mean in a sexy way; ' . $pet->getName() . ' got a bit light-headed.');
                else
                    $activityLog->setEntry($activityLog->getEntry() . ' The Micro-Jungle was CRAZY hot, and ' . $pet->getName() . ' got a bit light-headed.');
            }
        }

        // more chances to get bugs in the jungle!
        if(mt_rand(1, 25) === 1)
            $this->inventoryService->petAttractsRandomBug($pet);

        return $activityLog;
    }

    private function foundWildHedgemaze(Pet $pet): PetActivityLog
    {
        $possibleLoot = [
            'Smallish Pumpkin', 'Crooked Stick', 'Sweet Beet', 'String', 'Grandparoot', 'Pamplemousse',
        ];

        if(mt_rand(1, 20) === 1)
        {
            $possibleLoot[] = ArrayFunctions::pick_one([
                'Glowing Four-sided Die',
                'Glowing Six-sided Die',
                'Glowing Eight-sided Die'
            ]);
        }

        $loot = [];

        if($pet->hasMerit(MeritEnum::EIDETIC_MEMORY))
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 45), PetActivityStatEnum::GATHER, true);

            $loot[] = ArrayFunctions::pick_one($possibleLoot);
            $loot[] = ArrayFunctions::pick_one($possibleLoot);

            if(mt_rand(1, 20 + $pet->getPerception() + $pet->getNature() + $pet->getGathering()) >= 20)
                $loot[] = ArrayFunctions::pick_one($possibleLoot);

            $lucky = false;

            if($pet->hasMerit(MeritEnum::LUCKY) && mt_rand(1, 15) === 1)
            {
                $loot[] = 'Melowatern';
                $lucky = true;
            }
            else if(mt_rand(1, 75) == 1)
                $loot[] = 'Melowatern';

            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' went to the Wild Hedgemaze. It turns out mazes are way easier with a perfect memory! ' . $pet->getName() . ' found ' . ArrayFunctions::list_nice($loot) . '.', '');

            if($lucky)
            {
                $activityLog
                    ->setEntry($activityLog->getEntry() . ' (Melowatern!? Lucky~!)')
                    ->addInterestingness(PetActivityLogInterestingnessEnum::ACTIVITY_USING_MERIT)
                ;
            }

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE ]);
        }
        else if(mt_rand(1, 20 + $pet->getIntelligence() + $pet->getPerception()) < 15)
        {
            $pet->increaseFood(-1);

            if(mt_rand(1, 20) + $pet->getIntelligence() + $pet->getUmbra() >= 15)
            {
                $this->petExperienceService->spendTime($pet, mt_rand(45, 75), PetActivityStatEnum::GATHER, true);

                $loot[] = ArrayFunctions::pick_one($possibleLoot);
                $loot[] = ArrayFunctions::pick_one($possibleLoot);

                if(mt_rand(1, 8) === 1)
                    $loot[] = 'Silver Ore';
                else if(mt_rand(1, 8) === 1)
                    $loot[] = 'Music Note';
                else
                    $loot[] = 'Quintessence';

                if(mt_rand(1, 20 + $pet->getPerception() + $pet->getNature() + $pet->getGathering()) >= 25)
                    $loot[] = ArrayFunctions::pick_one($possibleLoot);

                $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::UMBRA, PetSkillEnum::NATURE ]);
                $pet->increaseEsteem(mt_rand(2, 3));
                $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' got lost in a Wild Hedgemaze, and ran into a Hedgemaze Sphinx. ' . $pet->getName() . ' was able to solve it\'s riddle, and kept exploring, coming away with ' . ArrayFunctions::list_nice($loot) . '.', '');
            }
            else
            {
                $this->petExperienceService->spendTime($pet, mt_rand(45, 75), PetActivityStatEnum::GATHER, false);

                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA, PetSkillEnum::NATURE ]);
                $pet->increaseEsteem(-mt_rand(1, 2));
                $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' got lost in a Wild Hedgemaze, and ran into a Hedgemaze Sphinx. The sphinx asked a really hard question; ' . $pet->getName() . ' wasn\'t able to answer it, and was consequentially ejected from the maze.', '');
            }
        }
        else
        {
            $loot[] = ArrayFunctions::pick_one($possibleLoot);
            $loot[] = ArrayFunctions::pick_one($possibleLoot);

            if(mt_rand(1, 20 + $pet->getPerception() + $pet->getNature() + $pet->getGathering()) >= 25)
                $loot[] = ArrayFunctions::pick_one($possibleLoot);

            $lucky = false;

            if($pet->hasMerit(MeritEnum::LUCKY) && mt_rand(1, 20) === 1)
            {
                $loot[] = 'Melowatern';
                $lucky = true;
            }
            else if(mt_rand(1, 100) == 1)
                $loot[] = 'Melowatern';

            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' wandered through a Wild Hedgemaze, and found ' . ArrayFunctions::list_nice($loot) . '.', '');

            if($lucky)
            {
                $activityLog
                    ->setEntry($activityLog->getEntry() . ' (Melowatern!? Lucky~!)')
                    ->addInterestingness(PetActivityLogInterestingnessEnum::ACTIVITY_USING_MERIT)
                ;
            }

            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::GATHER, true);
        }

        foreach($loot as $itemName)
            $this->inventoryService->petCollectsItem($itemName, $pet, $pet->getName() . ' found this in a Wild Hedgemaze.', $activityLog);

        return $activityLog;
    }

    private function foundVolcano(Pet $pet): PetActivityLog
    {
        $check = mt_rand(1, 20 + $pet->getPerception() + $pet->getNature() + $pet->getGathering());

        if($check < 15)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::GATHER, false);

            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' explored the island\'s Volcano, but couldn\'t find anything.', 'icons/activity-logs/confused');
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE ]);
        }
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::GATHER, true);

            if(mt_rand(1, 50))
                $loot = 'Hot Potato';
            else if(mt_rand(1, 2) === 1)
                $loot = 'Liquid-hot Magma';
            else
                $loot = ArrayFunctions::pick_one([ 'Iron Ore', 'Iron Ore', 'Silver Ore' ]);

            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' explored the island\'s Volcano, and got ' . $loot . '.', '');

            $this->inventoryService->petCollectsItem($loot, $pet, $pet->getName() . ' found this near the island\'s Volcano.', $activityLog);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::NATURE ]);
        }

        if(mt_rand(1, 10 + $pet->getStamina()) < 8)
        {
            if($pet->hasProtectionFromHeat())
            {
                $activityLog->setEntry($activityLog->getEntry() . ' The Volcano was hot, but their ' . $pet->getTool()->getItem()->getName() . ' protected them.')
                    ->addInterestingness(PetActivityLogInterestingnessEnum::ACTIVITY_USING_MERIT)
                ;
            }
            else
            {
                $pet->increaseFood(-1);
                $pet->increaseSafety(-mt_rand(1, 2));

                // why need to have unlocked the greenhouse? just testing that you've been playing for a while
                if(mt_rand(1, 20) === 1 && $pet->getOwner()->getUnlockedGreenhouse() !== null)
                    $activityLog->setEntry($activityLog->getEntry() . ' The Volcano was CRAZY hot, and I don\'t mean in a sexy way; ' . $pet->getName() . ' got a bit light-headed.');
                else
                    $activityLog->setEntry($activityLog->getEntry() . ' The Volcano was CRAZY hot, and ' . $pet->getName() . ' got a bit light-headed.');
            }
        }

        return $activityLog;
    }
}
