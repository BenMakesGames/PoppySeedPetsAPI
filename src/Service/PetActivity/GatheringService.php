<?php
namespace App\Service\PetActivity;

use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Enum\EnumInvalidValueException;
use App\Enum\MeritEnum;
use App\Enum\MoonPhaseEnum;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Enum\PetActivityStatEnum;
use App\Enum\PetSkillEnum;
use App\Functions\ArrayFunctions;
use App\Functions\DateFunctions;
use App\Functions\GrammarFunctions;
use App\Functions\NumberFunctions;
use App\Model\ComputedPetSkills;
use App\Model\PetChanges;
use App\Repository\ItemRepository;
use App\Repository\SpiceRepository;
use App\Service\InventoryService;
use App\Service\PetExperienceService;
use App\Service\ResponseService;
use App\Service\Squirrel3;
use App\Service\TransactionService;
use App\Service\WeatherService;

class GatheringService
{
    private $responseService;
    private $inventoryService;
    private $petExperienceService;
    private $transactionService;
    private $itemRepository;
    private $spiceRepository;
    private $squirrel3;
    private $weatherService;

    public function __construct(
        ResponseService $responseService, InventoryService $inventoryService, PetExperienceService $petExperienceService,
        TransactionService $transactionService, ItemRepository $itemRepository, SpiceRepository $spiceRepository,
        Squirrel3 $squirrel3, WeatherService $weatherService
    )
    {
        $this->responseService = $responseService;
        $this->inventoryService = $inventoryService;
        $this->petExperienceService = $petExperienceService;
        $this->transactionService = $transactionService;
        $this->itemRepository = $itemRepository;
        $this->spiceRepository = $spiceRepository;
        $this->squirrel3 = $squirrel3;
        $this->weatherService = $weatherService;
    }

    public function adventure(ComputedPetSkills $petWithSkills)
    {
        $pet = $petWithSkills->getPet();
        $maxSkill = 10 + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getNature()->getTotal() + $petWithSkills->getGatheringBonus()->getTotal() - $pet->getAlcohol() - $pet->getPsychedelic();

        $maxSkill = NumberFunctions::clamp($maxSkill, 1, 23);

        $roll = $this->squirrel3->rngNextInt(1, $maxSkill);

        $activityLog = null;
        $changes = new PetChanges($pet);

        switch($roll)
        {
            case 1:
            case 2:
            case 3:
            case 4:
                $activityLog = $this->foundNothing($pet);
                break;
            case 5:
                $activityLog = $this->foundPaperBag($pet);
                break;
            case 6:
                $activityLog = $this->foundTeaBush($pet);
                break;
            case 7:
            case 8:
                $activityLog = $this->foundBerryBush($petWithSkills);
                break;
            case 9:
            case 10:
                $activityLog = $this->foundHollowLog($petWithSkills);
                break;
            case 11:
                $activityLog = $this->foundAbandonedQuarry($petWithSkills);
                break;
            case 12:
                $activityLog = $this->foundBirdNest($petWithSkills);
                break;
            case 13:
                $activityLog = $this->foundBeach($petWithSkills);
                break;
            case 14:
                $activityLog = $this->foundOvergrownGarden($petWithSkills);
                break;
            case 15:
                $activityLog = $this->foundIronMine($petWithSkills);
                break;
            case 16:
                $activityLog = $this->foundMicroJungle($petWithSkills);
                break;
            case 17:
            case 18:
                $activityLog = $this->foundWildHedgemaze($petWithSkills);
                break;
            case 19:
            case 20:
                $activityLog = $this->foundVolcano($petWithSkills);
                break;
            case 21:
                $activityLog = $this->foundGypsumCave($petWithSkills);
                break;
            case 22:
            case 23:
                $activityLog = $this->foundDeepMicroJungle($petWithSkills);
                break;
        }

        if($activityLog)
            $activityLog->setChanges($changes->compare($pet));

        if($this->squirrel3->rngNextInt(1, 75) === 1)
            $this->inventoryService->petAttractsRandomBug($pet);
    }

    private function foundAbandonedQuarry(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        if($this->squirrel3->rngNextInt(1, 2000) < $petWithSkills->getPerception()->getTotal())
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% went to an Abandoned Quarry, and happened to find a piece of Striped Microcline!', '');

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE ]);
            $this->inventoryService->petCollectsItem('Striped Microcline', $pet, $pet->getName() . ' found this at an Abandoned Quarry.', $activityLog);
            $pet->increaseEsteem(4);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 45), PetActivityStatEnum::GATHER, true);
        }
        else if($this->squirrel3->rngNextInt(1, 150) === 1)
        {
            $bone = $this->squirrel3->rngNextFromArray([ 'Rib', 'Stereotypical Bone' ]);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% went to an Abandoned Quarry, and happened to find a ' . $bone . '!', '');

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::NATURE, PetSkillEnum::SCIENCE ]);
            $this->inventoryService->petCollectsItem($bone, $pet, $pet->getName() . ' found this at an Abandoned Quarry!', $activityLog);
            $pet->increaseEsteem(4);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::GATHER, true);
        }
        else if($petWithSkills->getStrength()->getTotal() < 4)
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% found a huge block of Limestone at an Abandoned Quarry, and, with all their might, pushed, dragged, and "rolled" it home.', 'items/mineral/limestone');
            $pet->increaseFood(-2);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE ]);
            $this->inventoryService->petCollectsItem('Limestone', $pet, $pet->getName() . ' found this at an Abandoned Quarry. It was really heavy!', $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(60, 75), PetActivityStatEnum::GATHER, true);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% found a huge block of Limestone at an Abandoned Quarry, and carried it home.', 'items/mineral/limestone');
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE ]);
            $this->inventoryService->petCollectsItem('Limestone', $pet, $pet->getName() . ' found this at an Abandoned Quarry.', $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::GATHER, true);
        }

        return $activityLog;
    }

    private function foundNothing(Pet $pet): PetActivityLog
    {
        $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 75), PetActivityStatEnum::GATHER, false);

        return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% went out gathering, but couldn\'t find anything.', 'icons/activity-logs/confused');
    }

    private function foundPaperBag(Pet $pet): PetActivityLog
    {
        $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% found a Paper Bag just, like, lyin\' around.', 'items/bag/paper');

        $this->inventoryService->petCollectsItem('Paper Bag', $pet, $pet->getName() . ' found this just lyin\' around.', $activityLog);

        $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE ]);
        $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::GATHER, true);

        return $activityLog;
    }

    private function foundTeaBush(Pet $pet): PetActivityLog
    {
        if($this->weatherService->getWeather(new \DateTimeImmutable(), $pet)->getRainfall() > 0 && $this->squirrel3->rngNextInt(1, 4) === 1)
        {
            $message = '%pet:' . $pet->getId() . '.name% found a Tea Bush, and grabbed a few Tea Leaves, as well as some Worms which had surfaced to escape the rain.';

            $activityLog = $this->responseService->createActivityLog($pet, $message, 'items/veggie/tea-leaves');

            $this->inventoryService->petCollectsItem('Tea Leaves', $pet, $pet->getName() . ' harvested this from a Tea Bush.', $activityLog);
            $this->inventoryService->petCollectsItem('Worms', $pet, $pet->getName() . ' found these under a Tea Bush.', $activityLog);
        }
        else
        {
            $message = '%pet:' . $pet->getId() . '.name% found a Tea Bush, and grabbed a few Tea Leaves.';

            $activityLog = $this->responseService->createActivityLog($pet, $message, 'items/veggie/tea-leaves');

            $this->inventoryService->petCollectsItem('Tea Leaves', $pet, $pet->getName() . ' harvested this from a Tea Bush.', $activityLog);
            $this->inventoryService->petCollectsItem('Tea Leaves', $pet, $pet->getName() . ' harvested this from a Tea Bush.', $activityLog);

            if($this->squirrel3->rngNextBool())
                $this->inventoryService->petCollectsItem('Tea Leaves', $pet, $pet->getName() . ' harvested this from a Tea Bush.', $activityLog);
        }

        $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE ]);
        $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::GATHER, true);

        return $activityLog;
    }

    private function foundBerryBush(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        if($this->squirrel3->rngNextInt(1, 8) >= 6)
        {
            $harvest = 'Blueberries';
            $additionalHarvest = $this->squirrel3->rngNextInt(1, 4) === 1;
        }
        else
        {
            $harvest = 'Blackberries';
            $additionalHarvest = $this->squirrel3->rngNextInt(1, 3) === 1;
        }

        if($this->squirrel3->rngNextInt(1, 10 + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getGatheringBonus()->getTotal()) >= 10)
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% harvested berries from a Thorny ' . $harvest . ' Bush.', '');
        }
        else
        {
            $pet->increaseSafety(-$this->squirrel3->rngNextInt(2, 4));
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% got scratched up harvesting berries from a Thorny ' . $harvest . ' Bush.', 'icons/activity-logs/wounded');
        }

        $this->inventoryService->petCollectsItem($harvest, $pet, $pet->getName() . ' harvested these from a Thorny ' . $harvest . ' Bush.', $activityLog);

        if($additionalHarvest)
            $this->inventoryService->petCollectsItem($harvest, $pet, $pet->getName() . ' harvested these from a Thorny ' . $harvest . ' Bush.', $activityLog);

        $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE ]);

        $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::GATHER, true);

        return $activityLog;
    }

    private function foundHollowLog(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $toadChance = $this->weatherService->getWeather(new \DateTimeImmutable(), $pet)->getRainfall() > 0 ? 75 : 25;

        if($this->squirrel3->rngNextInt(1, 100) <= $toadChance)
        {
            if($petWithSkills->getCanSeeInTheDark()->getTotal() <= 0)
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% found a Hollow Log, but it was too dark inside to see anything.', '');

                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE ]);
                $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 45), PetActivityStatEnum::GATHER, false);
            }
            else if($this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getStrength()->getTotal() + $petWithSkills->getStealth()->getTotal() + $petWithSkills->getBrawl(false)->getTotal()) >= 15)
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% found a Huge Toad inside a Hollow Log, got the jump on it, wrestled it to the ground, and claimed its Toadstool!', 'items/fungus/toadstool');
                $this->inventoryService->petCollectsItem('Toadstool', $pet, $pet->getName() . ' harvested this from the back of a Huge Toad found inside a Hollow Log.', $activityLog);
                $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::NATURE, PetSkillEnum::STEALTH, PetSkillEnum::BRAWL ]);
                $pet->increaseEsteem($this->squirrel3->rngNextInt(1, 2));
                $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::HUNT, true);
            }
            else
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% found a Huge Toad inside a Hollow Log, but it got away!', '');
                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE, PetSkillEnum::STEALTH, PetSkillEnum::BRAWL ]);
                $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::HUNT, true);
            }
        }
        else if($pet->hasMerit(MeritEnum::BEHATTED) && $this->squirrel3->rngNextInt(1, 75) === 1)
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% found a Red Bow inside a Hollow Log!', 'items/hat/bow-red')
                ->addInterestingness(PetActivityLogInterestingnessEnum::UNCOMMON_ACTIVITY)
            ;
            $this->inventoryService->petCollectsItem('Red Bow', $pet, $pet->getName() . ' found this inside a Hollow Log!', $activityLog);

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE ]);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 45), PetActivityStatEnum::GATHER, true);
        }
        else
        {
            $success = true;

            if($this->squirrel3->rngNextBool())
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% broke a Crooked Stick off of a Hollow Log.', 'items/plant/stick-crooked');
                $this->inventoryService->petCollectsItem('Crooked Stick', $pet, $pet->getName() . ' broke this off of a Hollow Log.', $activityLog);
            }
            else
            {
                if($petWithSkills->getCanSeeInTheDark()->getTotal() > 0)
                {
                    $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% found a Grandparoot inside a Hollow Log.', '');
                    $this->inventoryService->petCollectsItem('Grandparoot', $pet, $pet->getName() . ' found this growing inside a Hollow Log.', $activityLog);
                }
                else
                {
                    $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% found a Hollow Log, but it was too dark inside to see anything.', '');
                    $success = false;
                }
            }

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE ]);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 45), PetActivityStatEnum::GATHER, $success);
        }

        return $activityLog;
    }

    private function foundBirdNest(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        if($this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getStealth()->getTotal() + $petWithSkills->getDexterity()->getTotal()) >= 10)
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% stole an Egg from a Bird Nest.', '');
            $this->inventoryService->petCollectsItem('Egg', $pet, $pet->getName() . ' stole this from a Bird Nest.', $activityLog);

            if($this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getPerception()->getTotal()) >= 10)
                $this->inventoryService->petCollectsItem('Fluff', $pet, $pet->getName() . ' stole this from a Bird Nest.', $activityLog);

            $pet->increaseEsteem($this->squirrel3->rngNextInt(1, 2));
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::NATURE, PetSkillEnum::STEALTH ]);

            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::GATHER, true);
        }
        else
        {
            if($this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getStrength()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getBrawl()->getTotal()) >= 15)
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to steal an Egg from a Bird Nest, was spotted by a parent bird, and was able to defeat it in combat!', '');
                $this->inventoryService->petCollectsItem('Egg', $pet, $pet->getName() . ' stole this from a Bird Nest, after a fight.', $activityLog);
                $this->inventoryService->petCollectsItem('Fluff', $pet, $pet->getName() . ' stole this from a Bird Nest, after a fight.', $activityLog);
                $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::NATURE, PetSkillEnum::STEALTH, PetSkillEnum::BRAWL ]);
                $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 75), PetActivityStatEnum::HUNT, true);
            }
            else
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to steal an Egg from a Bird Nest, but was spotted by a parent bird, and chased off!', '');
                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE, PetSkillEnum::STEALTH, PetSkillEnum::BRAWL ]);
                $pet->increaseEsteem(-$this->squirrel3->rngNextInt(1, 2));
                $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 75), PetActivityStatEnum::HUNT, false);
            }
        }

        return $activityLog;
    }

    private function foundBeach(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $loot = [];
        $didWhat = 'found this at a Sandy Beach';

        if($this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getStealth()->getTotal() + $petWithSkills->getDexterity()->getTotal()) < 10)
        {
            $pet->increaseFood(-1);

            if($this->squirrel3->rngNextInt(1, 20) + $petWithSkills->getStrength()->getTotal() + $petWithSkills->getBrawl()->getTotal() >= 15)
            {
                $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 75), PetActivityStatEnum::HUNT, true);

                $loot[] = $this->squirrel3->rngNextFromArray([ 'Fish', 'Crooked Stick', 'Egg' ]);

                if($this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getNature()->getTotal()) >= 25)
                    $loot[] = $this->squirrel3->rngNextFromArray([ 'Feathers', 'Talon' ]);

                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::STEALTH, PetSkillEnum::BRAWL, PetSkillEnum::NATURE ]);
                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::BRAWL ]);
                $pet->increaseEsteem($this->squirrel3->rngNextInt(1, 2));
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% went to a Sandy Beach, but while looking around, was attacked by a Giant Seagull. ' . $pet->getName() . ' defeated the Giant Seagull, and took its ' . ArrayFunctions::list_nice($loot) . '.', '');
                $didWhat = 'defeated a Giant Seagull at the Beach, and got this';
            }
            else
            {
                $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 75), PetActivityStatEnum::HUNT, false);

                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::STEALTH, PetSkillEnum::BRAWL, PetSkillEnum::NATURE ]);
                $pet->increaseEsteem(-$this->squirrel3->rngNextInt(1, 2));
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% went to a Sandy Beach, but was attacked and routed by a Giant Seagull.', '');
            }
        }
        else
        {
            $possibleLoot = [
                'Scales', 'Silica Grounds', 'Seaweed', 'Coconut',
            ];

            $loot[] = $this->squirrel3->rngNextFromArray($possibleLoot);

            if($pet->getTool() && $pet->getTool()->fishingBonus() > 0)
                $loot[] = 'Fish';
            else if($this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getNature()->getTotal()) >= 15)
                $loot[] = $this->squirrel3->rngNextFromArray($possibleLoot);

            if($this->squirrel3->rngNextInt(1, 20) == 1)
                $loot[] = 'Secret Seashell';

            if($this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getNature()->getTotal() + $petWithSkills->getGatheringBonus()->getTotal()) >= 25)
            {
                $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::STEALTH, PetSkillEnum::NATURE ]);
                $moneys = $this->squirrel3->rngNextInt(4, 12);
                $this->transactionService->getMoney($pet->getOwner(), $moneys, $pet->getName() . ' found this on a Sandy Beach.');
                $lootList = $loot;
                $lootList[] = $moneys . '~~m~~';
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% went to a Sandy Beach, and stole ' . ArrayFunctions::list_nice($lootList) . ' while the seagulls weren\'t paying attention.', '');
                $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 75), PetActivityStatEnum::GATHER, true);
            }
            else
            {
                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::STEALTH, PetSkillEnum::NATURE ]);
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% went to a Sandy Beach, and stole ' . ArrayFunctions::list_nice($loot) . ' while the seagulls weren\'t paying attention.', '');
                $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::GATHER, true);
            }
        }

        foreach($loot as $itemName)
            $this->inventoryService->petCollectsItem($itemName, $pet, $pet->getName() . ' ' . $didWhat . '.', $activityLog);

        return $activityLog;
    }

    private function foundOvergrownGarden(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $possibleLoot = [
            'Carrot', 'Onion', 'Celery', 'Tomato', 'Beans',
            'Sweet Beet', 'Sweet Beet', 'Ginger', 'Rice Flower'
        ];

        if($this->weatherService->getWeather(new \DateTimeImmutable(), $pet)->getRainfall() > 0)
            $possibleLoot[] = 'Worms';

        $loot = [];
        $didWhat = 'harvested this from an Overgrown Garden';

        if($pet->hasMerit(MeritEnum::BEHATTED))
        {
            $chanceToGetOrangeBow = 1 + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getGatheringBonus()->getTotal();

            if($this->squirrel3->rngNextInt(1, 100) <= $chanceToGetOrangeBow)
                $loot[] = 'Orange Bow';
        }

        if($this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getStealth()->getTotal() + $petWithSkills->getDexterity()->getTotal()) < 10)
        {
            $pet->increaseFood(-1);

            if($this->squirrel3->rngNextInt(1, 20) + $petWithSkills->getStrength()->getTotal() + $petWithSkills->getBrawl()->getTotal() >= 15)
            {
                $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 75), PetActivityStatEnum::HUNT, true);

                $loot[] = $this->squirrel3->rngNextFromArray($possibleLoot);

                if($this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getNature()->getTotal() + $petWithSkills->getGatheringBonus()->getTotal()) >= 25)
                    $loot[] = $this->squirrel3->rngNextFromArray($possibleLoot);

                if($this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getNature()->getTotal() + $petWithSkills->getGatheringBonus()->getTotal()) >= 15)
                    $loot[] = 'Talon';

                $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::STEALTH, PetSkillEnum::BRAWL, PetSkillEnum::NATURE ]);
                $pet->increaseEsteem($this->squirrel3->rngNextInt(1, 2));
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% found an Overgrown Garden, but while looking for food, was attacked by an Angry Mole. ' . $pet->getName() . ' defeated the Angry Mole, and took its ' . ArrayFunctions::list_nice($loot) . '.', 'icons/activity-logs/overgrown-garden');
                $didWhat = 'defeated an Angry Mole in an Overgrown Garden, and got this';
            }
            else
            {
                $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 75), PetActivityStatEnum::HUNT, false);

                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::STEALTH, PetSkillEnum::BRAWL, PetSkillEnum::NATURE ]);
                $pet->increaseEsteem(-$this->squirrel3->rngNextInt(1, 2));
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% found an Overgrown Garden, but, while looking for food, was attacked and routed by an Angry Mole.', '');
            }
        }
        else
        {
            $loot[] = $this->squirrel3->rngNextFromArray($possibleLoot);

            if($this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getNature()->getTotal() + $petWithSkills->getGatheringBonus()->getTotal()) >= 15)
                $loot[] = $this->squirrel3->rngNextFromArray($possibleLoot);

            if($this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getNature()->getTotal() + $petWithSkills->getGatheringBonus()->getTotal()) >= 25)
                $loot[] = $this->squirrel3->rngNextFromArray([ 'Avocado', 'Red', 'Orange', 'Apricot' ]);

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::STEALTH, PetSkillEnum::NATURE ]);

            $lucky = false;

            if($pet->hasMerit(MeritEnum::LUCKY) && $this->squirrel3->rngNextInt(1, 20) === 1)
            {
                $loot[] = 'Honeydont';
                $lucky = true;
            }
            else if($this->squirrel3->rngNextInt(1, 100) == 1)
                $loot[] = 'Honeydont';

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% found an Overgrown Garden, and harvested ' . ArrayFunctions::list_nice($loot) . '.', 'icons/activity-logs/overgrown-garden');

            if($lucky)
            {
                $activityLog
                    ->setEntry($activityLog->getEntry() . ' (Honeydont?! Lucky~!)')
                    ->addInterestingness(PetActivityLogInterestingnessEnum::ACTIVITY_USING_MERIT)
                ;
            }

            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::GATHER, true);
        }

        foreach($loot as $itemName)
            $this->inventoryService->petCollectsItem($itemName, $pet, $pet->getName() . ' ' . $didWhat . '.', $activityLog);

        return $activityLog;
    }

    private function foundIronMine(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        if($this->squirrel3->rngNextInt(1, 4) === 1 && $petWithSkills->getCanSeeInTheDark()->getTotal() <= 0)
        {
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE ]);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 45), PetActivityStatEnum::GATHER, false);
            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% found an Old Iron Mine, but all the ore must have been hidden deep inside, and ' . $pet->getName() . ' didn\'t have a light.', '');
        }

        if($this->squirrel3->rngNextInt(1, 20) + $petWithSkills->getStrength()->getTotal() + $petWithSkills->getStamina()->getTotal() >= 10)
        {
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::NATURE ]);
            $pet->increaseFood(-1);

            if($pet->hasMerit(MeritEnum::LUCKY) && $this->squirrel3->rngNextInt(1, 20) === 1)
            {
                $pet->increaseEsteem(5);

                if($this->squirrel3->rngNextBool())
                    $loot = 'Gold Ore';
                else
                    $loot = 'Silver Ore';

                $punctuation = '! Lucky~!';
            }
            else if($this->squirrel3->rngNextInt(1, 50) === 1)
            {
                $pet->increaseEsteem(5);
                $loot = 'Gold Ore';
                $punctuation = '!!';
            }
            else if($this->squirrel3->rngNextInt(1, 10) === 1)
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

            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(60, 75), PetActivityStatEnum::GATHER, true);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% found an Old Iron Mine, and dug up some ' . $loot . $punctuation, '');
            $this->inventoryService->petCollectsItem($loot, $pet, $pet->getName() . ' dug this out of an Old Iron Mine' . $punctuation, $activityLog);
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(60, 75), PetActivityStatEnum::GATHER, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE ]);
            $pet->increaseFood(-2);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% found an Old Iron Mine, and tried to do some mining, but got too tired.', '');
        }

        return $activityLog;
    }

    private function foundMicroJungle(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        if(DateFunctions::moonPhase(new \DateTimeImmutable()) === MoonPhaseEnum::FULL_MOON)
            $activityLog = $this->encounterNangTani($petWithSkills);
        else
            $activityLog = $this->doNormalMicroJungle($petWithSkills);

        // more chances to get bugs in the jungle!
        if($this->squirrel3->rngNextInt(1, 25) === 1)
            $this->inventoryService->petAttractsRandomBug($petWithSkills->getPet());

        return $activityLog;
    }

    private function encounterNangTani(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getNature()->getTotal() + $petWithSkills->getUmbra()->getTotal());
        $success = $roll >= 12;

        $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::UMBRA, $success);

        $pet->increaseSafety($this->squirrel3->rngNextInt(2, 4));

        if($success)
        {
            $loot = $this->itemRepository->findOneByName($this->squirrel3->rngNextFromArray([
                'Fishkebab Stew',
                'Grilled Fish',
                'Honeydont Ice Cream',
                'Coconut',
                'Orange',
                'Mango'
            ]));

            $pet->increaseEsteem($this->squirrel3->rngNextInt(2, 4));

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% found a lone Banana Tree in the island\'s Micro-Jungle. They left a small offering for Nang Tani... who appeared out of thin air, and gave them ' . $loot->getNameWithArticle() . '!', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::UNCOMMON_ACTIVITY)
            ;
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::UMBRA ]);

            $this->inventoryService->petCollectsItem($loot, $pet, $pet->getName() . ' received this from Nang Tani while leaving an offering at a lone Banana Tree in the island\'s Micro-Jungle.', $activityLog);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% found a lone Banana Tree in the island\'s Micro-Jungle. They left a small offering for Nang Tani, and left.', '');
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);
        }

        return $activityLog;
    }

    private function doNormalMicroJungle(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $possibleLoot = [
            'Naner', 'Naner', 'Orange', 'Orange', 'Cocoa Beans', 'Cocoa Beans', 'Coffee Beans',
        ];

        $extraLoot = [
            'Nutmeg', 'Spicy Peps'
        ];

        $loot = [];

        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getNature()->getTotal() + $petWithSkills->getGatheringBonus()->getTotal());

        if($roll >= 12)
        {
            $loot[] = $this->squirrel3->rngNextFromArray($possibleLoot);

            if($roll >= 16)
            {
                $loot[] = $this->squirrel3->rngNextFromArray($possibleLoot);

                if($this->squirrel3->rngNextInt(1, 50) === 1)
                    $loot[] = $this->squirrel3->rngNextFromArray([ 'Rib', 'Stereotypical Bone' ]);
            }

            if($roll >= 24)
                $loot[] = $this->squirrel3->rngNextFromArray($extraLoot);

            if($roll >= 30 && $this->squirrel3->rngNextInt(1, 20) === 1)
                $loot[] = 'Silver Ore';
        }

        sort($loot);

        $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60) + count($loot) * 5, PetActivityStatEnum::GATHER, count($loot) > 0);

        if(count($loot) === 0)
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% entered the island\'s Micro-Jungle, but couldn\'t find anything.', 'icons/activity-logs/confused');
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE ]);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% entered the island\'s Micro-Jungle, and got ' . ArrayFunctions::list_nice($loot) . '.', '');

            foreach($loot as $itemName)
                $this->inventoryService->petCollectsItem($itemName, $pet, $pet->getName() . ' found this in the island\'s Micro-Jungle.', $activityLog);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::NATURE ]);
        }

        if($this->squirrel3->rngNextInt(1, 10 + $petWithSkills->getStamina()->getTotal()) < 6)
        {
            if($petWithSkills->getHasProtectionFromHeat()->getTotal() > 0)
            {
                $activityLog->setEntry($activityLog->getEntry() . ' The Micro-Jungle was hot, but their ' . $pet->getTool()->getItem()->getName() . ' protected them.')
                    ->addInterestingness(PetActivityLogInterestingnessEnum::ACTIVITY_USING_MERIT)
                ;
            }
            else
            {
                $pet->increaseFood(-1);
                $pet->increaseSafety(-$this->squirrel3->rngNextInt(1, 2));

                // why need to have unlocked the greenhouse? just testing that you've been playing for a while
                if($this->squirrel3->rngNextInt(1, 20) === 1 && $pet->getOwner()->getUnlockedGreenhouse() !== null)
                    $activityLog->setEntry($activityLog->getEntry() . ' The Micro-Jungle was CRAZY hot, and I don\'t mean in a sexy way; %pet:' . $pet->getId() . '.name% got a bit light-headed.');
                else
                    $activityLog->setEntry($activityLog->getEntry() . ' The Micro-Jungle was CRAZY hot, and %pet:' . $pet->getId() . '.name% got a bit light-headed.');
            }
        }

        return $activityLog;
    }

    private function foundWildHedgemaze(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $possibleLoot = [
            'Smallish Pumpkin', 'Crooked Stick', 'Sweet Beet', 'Toadstool', 'Grandparoot', 'Pamplemousse',
        ];

        if($this->squirrel3->rngNextInt(1, 20) === 1)
        {
            $possibleLoot[] = $this->squirrel3->rngNextFromArray([
                'Glowing Four-sided Die',
                'Glowing Six-sided Die',
                'Glowing Eight-sided Die'
            ]);
        }

        $loot = [];

        if($pet->hasMerit(MeritEnum::EIDETIC_MEMORY) || $petWithSkills->getClimbingBonus()->getTotal() > 0)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 45), PetActivityStatEnum::GATHER, true);

            $loot[] = $this->squirrel3->rngNextFromArray($possibleLoot);
            $loot[] = $this->squirrel3->rngNextFromArray($possibleLoot);

            if($this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getNature()->getTotal() + $petWithSkills->getGatheringBonus()->getTotal()) >= 20)
                $loot[] = $this->squirrel3->rngNextFromArray($possibleLoot);

            $lucky = false;

            if($pet->hasMerit(MeritEnum::LUCKY) && $this->squirrel3->rngNextInt(1, 15) === 1)
            {
                $loot[] = 'Melowatern';
                $lucky = true;
            }
            else if($this->squirrel3->rngNextInt(1, 75) == 1)
                $loot[] = 'Melowatern';

            if($petWithSkills->getClimbingBonus()->getTotal() > 0)
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% went to the Wild Hedgemaze. It turns out mazes are way easier when you can just climb over the walls! ' . $pet->getName() . ' found ' . ArrayFunctions::list_nice($loot) . '.', '');
            else
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% went to the Wild Hedgemaze. It turns out mazes are way easier with a perfect memory! ' . $pet->getName() . ' found ' . ArrayFunctions::list_nice($loot) . '.', '');

            if($lucky)
            {
                $activityLog
                    ->setEntry($activityLog->getEntry() . ' (Melowatern!? Lucky~!)')
                    ->addInterestingness(PetActivityLogInterestingnessEnum::ACTIVITY_USING_MERIT)
                ;
            }

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE ]);
        }
        else if($this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getPerception()->getTotal()) < 15)
        {
            $pet->increaseFood(-1);

            if($this->squirrel3->rngNextInt(1, 20) + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getUmbra()->getTotal() >= 15)
            {
                $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 75), PetActivityStatEnum::GATHER, true);

                $loot[] = $this->squirrel3->rngNextFromArray($possibleLoot);
                $loot[] = $this->squirrel3->rngNextFromArray($possibleLoot);

                if($this->squirrel3->rngNextInt(1, 8) === 1)
                    $loot[] = 'Silver Ore';
                else if($this->squirrel3->rngNextInt(1, 8) === 1)
                    $loot[] = 'Music Note';
                else
                    $loot[] = 'Quintessence';

                if($this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getNature()->getTotal() + $petWithSkills->getGatheringBonus()->getTotal()) >= 25)
                    $loot[] = $this->squirrel3->rngNextFromArray($possibleLoot);

                $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::UMBRA, PetSkillEnum::NATURE ]);
                $pet->increaseEsteem($this->squirrel3->rngNextInt(2, 3));
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% got lost in a Wild Hedgemaze, and ran into a Hedgemaze Sphinx. ' . $pet->getName() . ' was able to solve its riddle, and kept exploring, coming away with ' . ArrayFunctions::list_nice($loot) . '.', '');
            }
            else
            {
                $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 75), PetActivityStatEnum::GATHER, false);

                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA, PetSkillEnum::NATURE ]);
                $pet->increaseEsteem(-$this->squirrel3->rngNextInt(1, 2));
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% got lost in a Wild Hedgemaze, and ran into a Hedgemaze Sphinx. The sphinx asked a really hard question; ' . $pet->getName() . ' wasn\'t able to answer it, and was consequentially ejected from the maze.', '');
            }
        }
        else
        {
            $loot[] = $this->squirrel3->rngNextFromArray($possibleLoot);
            $loot[] = $this->squirrel3->rngNextFromArray($possibleLoot);

            if($this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getNature()->getTotal() + $petWithSkills->getGatheringBonus()->getTotal()) >= 25)
                $loot[] = $this->squirrel3->rngNextFromArray($possibleLoot);

            $lucky = false;

            if($pet->hasMerit(MeritEnum::LUCKY) && $this->squirrel3->rngNextInt(1, 20) === 1)
            {
                $loot[] = 'Melowatern';
                $lucky = true;
            }
            else if($this->squirrel3->rngNextInt(1, 100) == 1)
                $loot[] = 'Melowatern';

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% wandered through a Wild Hedgemaze, and found ' . ArrayFunctions::list_nice($loot) . '.', '');

            if($lucky)
            {
                $activityLog
                    ->setEntry($activityLog->getEntry() . ' (Melowatern!? Lucky~!)')
                    ->addInterestingness(PetActivityLogInterestingnessEnum::ACTIVITY_USING_MERIT)
                ;
            }

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::NATURE ]);

            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::GATHER, true);
        }

        foreach($loot as $itemName)
            $this->inventoryService->petCollectsItem($itemName, $pet, $pet->getName() . ' found this in a Wild Hedgemaze.', $activityLog);

        return $activityLog;
    }

    private function foundVolcano(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $check = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getNature()->getTotal() + $petWithSkills->getGatheringBonus()->getTotal());

        if($check < 15)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::GATHER, false);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% explored the island\'s Volcano, but couldn\'t find anything.', 'icons/activity-logs/confused');
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE ]);
        }
        else if($this->squirrel3->rngNextInt(1, max(10, 50 - $pet->getSkills()->getIntelligence())) === 1)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(60, 75), PetActivityStatEnum::GATHER, true);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% climbed to the top of the island\'s Volcano, and captured some Lightning in a Bottle!', '');

            $this->inventoryService->petCollectsItem('Lightning in a Bottle', $pet, $pet->getName() . ' captured this on the top of the island\'s Volcano!', $activityLog);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::NATURE ]);
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::GATHER, true);

            $loot = $this->itemRepository->findOneByName($this->squirrel3->rngNextFromArray([
                'Iron Ore', 'Silver Ore', 'Liquid-hot Magma', 'Hot Potato'
            ]));

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% explored the island\'s Volcano, and got ' . $loot->getNameWithArticle() . '.', 'items/' . $loot->getImage());

            $this->inventoryService->petCollectsItem($loot, $pet, $pet->getName() . ' found this near the island\'s Volcano.', $activityLog);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::NATURE ]);
        }

        if($this->squirrel3->rngNextInt(1, 10 + $petWithSkills->getStamina()->getTotal()) < 8)
        {
            if($petWithSkills->getHasProtectionFromHeat()->getTotal() > 0)
            {
                $activityLog->setEntry($activityLog->getEntry() . ' The Volcano was hot, but their ' . $pet->getTool()->getItem()->getName() . ' protected them.')
                    ->addInterestingness(PetActivityLogInterestingnessEnum::ACTIVITY_USING_MERIT)
                ;
            }
            else
            {
                $pet->increaseFood(-1);
                $pet->increaseSafety(-$this->squirrel3->rngNextInt(1, 2));

                // why need to have unlocked the greenhouse? just testing that you've been playing for a while
                if($this->squirrel3->rngNextInt(1, 20) === 1 && $pet->getOwner()->getUnlockedGreenhouse() !== null)
                    $activityLog->setEntry($activityLog->getEntry() . ' The Volcano was CRAZY hot, and I don\'t mean in a sexy way; %pet:' . $pet->getId() . '.name% got a bit light-headed.');
                else
                    $activityLog->setEntry($activityLog->getEntry() . ' The Volcano was CRAZY hot, and %pet:' . $pet->getId() . '.name% got a bit light-headed.');
            }
        }

        return $activityLog;
    }

    private function foundGypsumCave(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $eideticMemory = $pet->hasMerit(MeritEnum::EIDETIC_MEMORY);
        $check = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getStrength()->getTotal() + $petWithSkills->getNature()->getTotal() + $petWithSkills->getGatheringBonus()->getTotal());

        if($check >= 15 || $eideticMemory)
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::GATHER, true);

            $loot = [
                'Gypsum'
            ];

            if($check >= 20)
                $loot[] = $this->squirrel3->rngNextFromArray([ 'Iron Ore', 'Toadstool', 'Gypsum', 'Gypsum', 'Gypsum', 'Limestone' ]);

            if($check >= 30)
                $loot[] = $this->squirrel3->rngNextFromArray([ 'Silver Ore', 'Silver Ore', 'Gypsum', 'Gold Ore' ]);

            if($this->squirrel3->rngNextInt(1, 2000) < $petWithSkills->getPerception()->getTotal())
            {
                $loot[] = 'Striped Microcline';
                $pet->increaseEsteem(4);
            }

            if($eideticMemory)
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% explored a huge cave, perfectly memorizing its layout as they went, and found ' . ArrayFunctions::list_nice($loot) . '.', '')
                    ->addInterestingness(PetActivityLogInterestingnessEnum::ACTIVITY_USING_MERIT)
                ;
            }
            else
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% explored a huge cave, and found ' . ArrayFunctions::list_nice($loot) . '.', '');

            foreach($loot as $item)
                $this->inventoryService->petCollectsItem($item, $pet, $pet->getName() . ' found this in a huge cave.', $activityLog);

            $this->petExperienceService->gainExp($pet, max(2, count($loot)), [ PetSkillEnum::NATURE ]);
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(60, 75), PetActivityStatEnum::GATHER, false);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% explored a huge cave, and got lost for a while!', 'icons/activity-logs/confused');
            $pet->increaseSafety(-4);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::NATURE ]);
        }

        return $activityLog;
    }

    private function foundDeepMicroJungle(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        if(DateFunctions::moonPhase(new \DateTimeImmutable()) === MoonPhaseEnum::FULL_MOON)
            $activityLog = $this->encounterNangTani($petWithSkills);
        else
            $activityLog = $this->doNormalDeepMicroJungle($petWithSkills);

        // more chances to get bugs in the jungle!
        if($this->squirrel3->rngNextInt(1, 20) === 1)
            $this->inventoryService->petAttractsRandomBug($petWithSkills->getPet());

        return $activityLog;
    }

    private function doNormalDeepMicroJungle(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $possibleLoot = [
            'Naner', 'Naner', 'Mango', 'Mango', 'Cocoa Beans', 'Coffee Beans',
        ];

        $foodLoot = [];
        $extraLoot = [];

        $roll = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getNature()->getTotal() + $petWithSkills->getGatheringBonus()->getTotal());

        if($roll >= 16)
        {
            $foodLoot[] = $this->squirrel3->rngNextFromArray($possibleLoot);

            if($roll >= 18)
            {
                $foodLoot[] = $this->squirrel3->rngNextFromArray($possibleLoot);

                if($this->squirrel3->rngNextInt(1, 40) === 1)
                    $extraLoot[] = $this->squirrel3->rngNextFromArray([ 'Rib', 'Stereotypical Bone' ]);
            }

            if($roll >= 24)
                $foodLoot[] = $this->squirrel3->rngNextFromArray($possibleLoot);

            if($roll >= 30 && $this->squirrel3->rngNextInt(1, 10) === 1)
                $extraLoot[] = $this->squirrel3->rngNextFromArray([ 'Gold Ore', 'Gold Ore', 'Blackonite', 'Striped Microcline' ]);
        }

        $allLoot = array_merge($foodLoot, $extraLoot);
        sort($allLoot);

        $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60) + count($allLoot) * 5, PetActivityStatEnum::GATHER, count($allLoot) > 0);

        if(count($allLoot) === 0)
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% explored deep in the island\'s Micro-Jungle, but couldn\'t find anything.', 'icons/activity-logs/confused');
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE ]);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% explored deep in the island\'s Micro-Jungle, and got ' . ArrayFunctions::list_nice($allLoot) . '.', '');

            $tropicalSpice = $this->spiceRepository->findOneByName('Tropical');

            foreach($foodLoot as $itemName)
                $this->inventoryService->petCollectsEnhancedItem($itemName, null, $tropicalSpice, $pet, $pet->getName() . ' found this deep in the island\'s Micro-Jungle.', $activityLog);

            foreach($extraLoot as $itemName)
                $this->inventoryService->petCollectsItem($itemName, $pet, $pet->getName() . ' found this deep in the island\'s Micro-Jungle.', $activityLog);

            $this->petExperienceService->gainExp($pet, $this->squirrel3->rngNextInt(2, 3), [ PetSkillEnum::NATURE ]);
        }

        if($this->squirrel3->rngNextInt(1, 10 + $petWithSkills->getStamina()->getTotal()) < 8)
        {
            if($petWithSkills->getHasProtectionFromHeat()->getTotal() > 0)
            {
                $activityLog->setEntry($activityLog->getEntry() . ' The Micro-Jungle was hot, but their ' . $pet->getTool()->getItem()->getName() . ' protected them.')
                    ->addInterestingness(PetActivityLogInterestingnessEnum::ACTIVITY_USING_MERIT)
                ;
            }
            else
            {
                $pet->increaseFood(-1);
                $pet->increaseSafety(-$this->squirrel3->rngNextInt(1, 2));

                // why need to have unlocked the greenhouse? just testing that you've been playing for a while
                if($this->squirrel3->rngNextInt(1, 20) === 1 && $pet->getOwner()->getUnlockedGreenhouse() !== null)
                    $activityLog->setEntry($activityLog->getEntry() . ' The Micro-Jungle was CRAZY hot, and I don\'t mean in a sexy way; %pet:' . $pet->getId() . '.name% got a bit light-headed.');
                else
                    $activityLog->setEntry($activityLog->getEntry() . ' The Micro-Jungle was CRAZY hot, and %pet:' . $pet->getId() . '.name% got a bit light-headed.');
            }
        }

        return $activityLog;
    }

}
