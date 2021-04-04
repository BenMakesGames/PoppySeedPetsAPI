<?php
namespace App\Service\PetActivity;

use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Entity\User;
use App\Enum\GuildEnum;
use App\Enum\MeritEnum;
use App\Enum\MoonPhaseEnum;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Enum\PetActivityStatEnum;
use App\Enum\PetSkillEnum;
use App\Enum\StatusEffectEnum;
use App\Enum\UserStatEnum;
use App\Functions\ArrayFunctions;
use App\Functions\DateFunctions;
use App\Functions\NumberFunctions;
use App\Model\ComputedPetSkills;
use App\Model\PetChanges;
use App\Repository\ItemRepository;
use App\Repository\MuseumItemRepository;
use App\Repository\UserQuestRepository;
use App\Repository\UserStatsRepository;
use App\Service\CalendarService;
use App\Service\InventoryService;
use App\Service\PetExperienceService;
use App\Service\ResponseService;
use App\Service\InventoryModifierService;
use App\Service\Squirrel3;
use App\Service\TransactionService;
use App\Service\WeatherService;

class HuntingService
{
    private $responseService;
    private $inventoryService;
    private $userStatsRepository;
    private $calendarService;
    private $museumItemRepository;
    private $itemRepository;
    private $userQuestRepository;
    private $petExperienceService;
    private $transactionService;
    private $toolBonusService;
    private $squirrel3;
    private $werecreatureEncounterService;
    private $weatherService;

    public function __construct(
        ResponseService $responseService, InventoryService $inventoryService, UserStatsRepository $userStatsRepository,
        CalendarService $calendarService, MuseumItemRepository $museumItemRepository, ItemRepository $itemRepository,
        UserQuestRepository $userQuestRepository, PetExperienceService $petExperienceService,
        TransactionService $transactionService, InventoryModifierService $toolBonusService, Squirrel3 $squirrel3,
        WerecreatureEncounterService $werecreatureEncounterService, WeatherService $weatherService
    )
    {
        $this->responseService = $responseService;
        $this->inventoryService = $inventoryService;
        $this->userStatsRepository = $userStatsRepository;
        $this->calendarService = $calendarService;
        $this->museumItemRepository = $museumItemRepository;
        $this->itemRepository = $itemRepository;
        $this->userQuestRepository = $userQuestRepository;
        $this->petExperienceService = $petExperienceService;
        $this->transactionService = $transactionService;
        $this->toolBonusService = $toolBonusService;
        $this->squirrel3 = $squirrel3;
        $this->werecreatureEncounterService = $werecreatureEncounterService;
        $this->weatherService = $weatherService;
    }

    public function adventure(ComputedPetSkills $petWithSkills)
    {
        $pet = $petWithSkills->getPet();
        $maxSkill = 10 + $petWithSkills->getStrength()->getTotal() + $petWithSkills->getBrawl()->getTotal() - $pet->getAlcohol() - $pet->getPsychedelic();

        $maxSkill = NumberFunctions::clamp($maxSkill, 1, 22);

        $useThanksgivingPrey = $this->calendarService->isThanksgivingMonsters() && $this->squirrel3->rngNextInt(1, 2) === 1;
        $usePassoverPrey = $this->calendarService->isEaster();

        $roll = $this->squirrel3->rngNextInt(1, $maxSkill);

        $activityLog = null;
        $changes = new PetChanges($pet);

        $weather = $this->weatherService->getWeather(new \DateTimeImmutable(), $pet);
        $isRaining = $weather->getRainfall() > 0;

        if(DateFunctions::moonPhase(new \DateTimeImmutable()) === MoonPhaseEnum::FULL_MOON && $this->squirrel3->rngNextInt(1, 100) === 1)
        {
            $activityLog = $this->werecreatureEncounterService->encounterWerecreature($petWithSkills, 'hunting');
        }
        else
        {
            switch($roll)
            {
                case 1:
                case 2:
                    $activityLog = $this->failedToHunt($petWithSkills);
                    break;
                case 3:
                case 4:
                case 5:
                    if($isRaining && $this->squirrel3->rngNextBool())
                        $activityLog = $this->huntedLargeToad($petWithSkills);
                    else
                        $activityLog = $this->huntedDustBunny($petWithSkills);
                    break;
                case 6:
                    $activityLog = $this->huntedPlasticBag($petWithSkills);
                    break;
                case 7:
                case 8:
                    if($this->canRescueAnotherHouseFairy($pet->getOwner()))
                        $activityLog = $this->rescueHouseFairy($pet);
                    else if($useThanksgivingPrey)
                        $activityLog = $this->huntedTurkey($petWithSkills);
                    else if($usePassoverPrey)
                        $activityLog = $this->noGoats($pet);
                    else
                        $activityLog = $this->huntedGoat($petWithSkills);
                    break;
                case 9:
                    $activityLog = $this->huntedDoughGolem($petWithSkills);
                    break;
                case 10:
                case 11:
                    if($useThanksgivingPrey)
                        $activityLog = $this->huntedTurkey($petWithSkills);
                    else
                        $activityLog = $this->huntedLargeToad($petWithSkills);
                    break;
                case 12:
                    $activityLog = $this->huntedScarecrow($petWithSkills);
                    break;
                case 13:
                    $activityLog = $this->huntedOnionBoy($petWithSkills);
                    break;
                case 14:
                case 15:
                    $activityLog = $this->huntedThievingMagpie($petWithSkills);
                    break;
                case 16:
                    if($useThanksgivingPrey)
                        $activityLog = $this->huntedPossessedTurkey($petWithSkills);
                    else
                        $activityLog = $this->huntedGhosts($petWithSkills);
                    break;
                case 17:
                case 18:
                    if($useThanksgivingPrey)
                        $activityLog = $this->huntedPossessedTurkey($petWithSkills);
                    else if($usePassoverPrey)
                        $activityLog = $this->noGoats($pet);
                    else
                        $activityLog = $this->huntedSatyr($petWithSkills);
                    break;
                case 19:
                case 20:
                    $activityLog = $this->huntedPaperGolem($petWithSkills);
                    break;
                case 21:
                    if($useThanksgivingPrey)
                        $activityLog = $this->huntedTurkeyDragon($petWithSkills);
                    else
                        $activityLog = $this->huntedLeshyDemon($petWithSkills);
                    break;
                case 22:
                    $activityLog = $this->huntedEggSaladMonstrosity($petWithSkills);
                    break;
            }
        }

        if($activityLog)
            $activityLog->setChanges($changes->compare($pet));

        if($this->squirrel3->rngNextInt(1, 100) === 1)
            $this->inventoryService->petAttractsRandomBug($pet);
    }

    private function canRescueAnotherHouseFairy(User $user): bool
    {
        // if you've unlocked the fireplace, then you can't rescue a second
        if($user->getUnlockedFireplace())
            return false;

        // if you haven't donated a fairy, then you can't rescue a second
        if(!$this->museumItemRepository->hasUserDonated(
            $user,
            $this->itemRepository->findOneByName('House Fairy')
        ))
            return false;

        // if you already rescued a second, then you can't rescue a second again :P
        $rescuedASecond = $this->userQuestRepository->findOrCreate($user, 'Rescued Second House Fairy', false);

        if($rescuedASecond->getValue())
            return false;

        return true;
    }

    private function rescueHouseFairy(Pet $pet): PetActivityLog
    {
        $this->userQuestRepository->findOrCreate($pet->getOwner(), 'Rescued Second House Fairy', false)
            ->setValue(true)
        ;

        $activityLog = $this->responseService->createActivityLog($pet, 'While ' . '%pet:' . $pet->getId() . '.name% was out hunting, they spotted a Raccoon and Thieving Magpie fighting over a fairy! %pet:' . $pet->getId() . '.name% jumped in and chased the two creatures off before tending to the fairy\'s wounds.', '');
        $inventory = $this->inventoryService->petCollectsItem('House Fairy', $pet, 'Rescued from a Raccoon and Thieving Magpie.', $activityLog);

        if($inventory)
            $inventory->setLockedToOwner(true);

        $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::BRAWL ]);

        $pet->increaseSafety(2);
        $pet->increaseLove(2);
        $pet->increaseEsteem(2);

        $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::HUNT, true);

        return $activityLog;
    }

    private function failedToHunt(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        if($pet->getOwner()->getGreenhouse() && $pet->getOwner()->getGreenhouse()->getHasBirdBath() && !$pet->getOwner()->getGreenhouse()->getVisitingBird())
        {
            if($pet->getSkills()->getBrawl() < 5)
                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::BRAWL ]);

            $pet
                ->increaseSafety($this->squirrel3->rngNextInt(1, 2))
                ->increaseEsteem($this->squirrel3->rngNextInt(1, 2))
            ;

            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::HUNT, false);

            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% couldn\'t find anything to hunt, so watched some small birds play in the Greenhouse Bird Bath, instead.', 'icons/activity-logs/birb')
                ->addInterestingness(PetActivityLogInterestingnessEnum::UNCOMMON_ACTIVITY)
            ;
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::HUNT, false);

            return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% went out hunting, but couldn\'t find anything to hunt.', 'icons/activity-logs/confused');
        }
    }

    private function huntedDustBunny(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $skill = 10 + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getBrawl()->getTotal();

        $isRanged = $pet->getTool() && $pet->getTool()->rangedOnly() && $pet->getTool()->brawlBonus() > 0;

        $defeated = $isRanged ? 'shot down' : 'pounced on';
        $chased = $isRanged ? 'shot at' : 'chased';

        if(!$isRanged)
            $pet->increaseFood(-1);

        if($this->squirrel3->rngNextInt(1, $skill) >= 6)
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% ' . $defeated . ' a Dust Bunny, reducing it to Fluff!', 'items/ambiguous/fluff');
            $this->inventoryService->petCollectsItem('Fluff', $pet, 'The remains of a Dust Bunny that ' . $pet->getName() . ' hunted.', $activityLog);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::BRAWL ]);

            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::HUNT, true);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% ' . $chased . ' a Dust Bunny, but wasn\'t able to catch up with it.', '');
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::BRAWL ]);

            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::HUNT, false);
        }

        return $activityLog;
    }

    private function huntedPlasticBag(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $skill = 10 + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getBrawl()->getTotal();

        $isRanged = $pet->getTool() && $pet->getTool()->rangedOnly() && $pet->getTool()->brawlBonus() > 0;

        $defeated = $isRanged ? 'shot down' : 'pounced on';
        $chased = $isRanged ? 'shot at' : 'chased';

        if(!$isRanged)
            $pet->increaseFood(-1);

        if($this->squirrel3->rngNextInt(1, $skill) >= 6)
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% ' . $defeated . ' a Plastic Bag, reducing it to Plastic... somehow?', 'items/ambiguous/fluff');
            $this->inventoryService->petCollectsItem('Plastic', $pet, 'The remains of a vicious Plastic Bag that ' . $pet->getName() . ' hunted!', $activityLog);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::BRAWL ]);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::HUNT, true);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% ' . $chased . ' a Plastic Bag, but wasn\'t able to catch up with it!', '');
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::BRAWL ]);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::HUNT, false);
        }

        return $activityLog;
    }

    private function huntedGoat(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $skill = 10 + $petWithSkills->getStrength()->getTotal() + $petWithSkills->getBrawl(false)->getTotal();

        $pet->increaseFood(-1);

        $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::BRAWL ]);

        if($this->squirrel3->rngNextInt(1, $skill) >= 6)
        {
            $pet->increaseEsteem(1);

            if($this->squirrel3->rngNextInt(1, 2) === 1)
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% wrestled a Goat, and won, receiving Creamy Milk.', '');
                $this->inventoryService->petCollectsItem('Creamy Milk', $pet, $pet->getName() . '\'s prize for out-wrestling a Goat.', $activityLog);
            }
            else
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% wrestled a Goat, and won, receiving Butter.', '');
                $this->inventoryService->petCollectsItem('Butter', $pet, $pet->getName() . '\'s prize for out-wrestling a Goat.', $activityLog);
            }

            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::HUNT, true);
        }
        else
        {
            if($this->squirrel3->rngNextInt(1, 4) === 1)
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% wrestled a Goat. The Goat won.', '');
                $this->inventoryService->petCollectsItem('Fluff', $pet, $pet->getName() . ' wrestled a Goat, and lost, but managed to grab a fistful of Fluff.', $activityLog);
            }
            else
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% wrestled a Goat. The Goat won.', '');

            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::HUNT, false);
        }

        return $activityLog;
    }

    private function huntedDoughGolem(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $possibleLoot = [
            'Wheat Flour', 'Oil', 'Butter', 'Yeast', 'Sugar'
        ];

        $possibleLootSansOil = [
            'Wheat Flour', 'Butter', 'Yeast', 'Sugar'
        ];

        $stealth = $this->squirrel3->rngNextInt(1, 10 + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getStealth()->getTotal());

        if($stealth > 25)
        {
            $pet->increaseEsteem($this->squirrel3->rngNextInt(2, 4));

            $loot = $this->squirrel3->rngNextFromArray($possibleLootSansOil);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% snuck up on a sleeping Deep-fried Dough Golem, and harvested some of its ' . $loot . ', and Oil, without it ever noticing!', '');
            $this->inventoryService->petCollectsItem($loot, $pet, $pet->getName() . ' stole this off the body of a sleeping Deep-fried Dough Golem.', $activityLog);
            $this->inventoryService->petCollectsItem('Oil', $pet, $pet->getName() . ' stole this off the body of a sleeping Deep-fried Dough Golem.', $activityLog);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::STEALTH ]);

            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::HUNT, true);

            return $activityLog;
        }
        else if($stealth > 15)
        {
            $pet->increaseEsteem(1);

            $loot = $this->squirrel3->rngNextFromArray($possibleLoot);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% snuck up on a sleeping Dough Golem, and harvested some of its ' . $loot . ' without it ever noticing!', '');
            $this->inventoryService->petCollectsItem($loot, $pet, $pet->getName() . ' stole this off the body of a sleeping Dough Golem.', $activityLog);

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::STEALTH ]);

            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::HUNT, true);

            return $activityLog;
        }

        $skillCheck = $this->squirrel3->rngNextInt(1, 10 + $petWithSkills->getStrength()->getTotal() + $petWithSkills->getBrawl(false)->getTotal());

        $pet->increaseFood(-1);

        if($skillCheck >= 17)
        {
            $dodgeCheck = $this->squirrel3->rngNextInt(1, 10 + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getBrawl(false)->getTotal());

            $loot = $this->squirrel3->rngNextFromArray($possibleLootSansOil);

            if($dodgeCheck >= 15)
            {
                $pet->increaseEsteem($this->squirrel3->rngNextInt(2, 4));

                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% attacked a rampaging Deep-fried Dough Golem, defeated it, and harvested its ' . $loot . '.', '');
                $this->inventoryService->petCollectsItem($loot, $pet, $pet->getName() . ' took this from the body of a defeated Deep-fried Dough Golem.', $activityLog);
                $this->inventoryService->petCollectsItem('Oil', $pet, $pet->getName() . ' took this from the body of a defeated Deep-fried Dough Golem.', $activityLog);

                $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::BRAWL ]);
            }
            else
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% attacked a rampaging Deep-fried Dough Golem. It was gross and oily, and %pet:' . $pet->getId() . '.name% got Oil all over themselves, but in the end they defeated the creature, and harvested its ' . $loot . '.', '');
                $this->inventoryService->petCollectsItem($loot, $pet, $pet->getName() . ' took this from the body of a defeated Deep-fried Dough Golem.', $activityLog);
                $this->inventoryService->applyStatusEffect($pet, StatusEffectEnum::OIL_COVERED, 1);

                $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::BRAWL ]);
            }

            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::HUNT, true);
        }
        else if($skillCheck >= 7)
        {
            $pet->increaseEsteem(1);

            $loot = $this->squirrel3->rngNextFromArray($possibleLoot);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% attacked a rampaging Dough Golem, defeated it, and harvested its ' . $loot . '.', '');
            $this->inventoryService->petCollectsItem($loot, $pet, $pet->getName() . ' took this from the body of a defeated Dough Golem.', $activityLog);

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::BRAWL ]);

            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::HUNT, true);
        }
        else
        {
            if($this->squirrel3->rngNextInt(1, 4) === 1)
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% attacked a rampaging Dough Golem, but it released a cloud of defensive flour, and escaped. ' . $pet->getName() . ' picked up some of the flour, and brought it home.', '');
                $this->inventoryService->petCollectsItem('Wheat Flour', $pet, $pet->getName() . ' got this from a fleeing Dough Golem.', $activityLog);
            }
            else
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% attacked a Dough Golem, but it was really sticky. ' . $pet->getName() . '\'s attacks were useless, and they were forced to retreat.', '');

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::BRAWL ]);

            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::HUNT, false);
        }

        return $activityLog;
    }

    private function huntedTurkey(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $skill = 10 + $petWithSkills->getStrength()->getTotal() + $petWithSkills->getBrawl(false)->getTotal();

        $pet->increaseFood(-1);

        if($this->squirrel3->rngNextInt(1, $skill) >= 6)
        {
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::BRAWL ]);

            $item = $this->squirrel3->rngNextFromArray([ 'Talon', 'Feathers', 'Giant Turkey Leg', 'Smallish Pumpkin Spice' ]);

            $aOrSome = $item === 'Feathers' ? 'some' : 'a';

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% wrestled a Turkey! The Turkey fled, but not before ' . $pet->getName() . ' took ' . $aOrSome . ' ' . $item . '!', '');
            $this->inventoryService->petCollectsItem($item, $pet, $pet->getName() . ' wrestled this from a Turkey.', $activityLog);

            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::HUNT, true);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% picked a fight with a Turkey, but lost.', '');
            $pet->increaseEsteem(-2);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::BRAWL ]);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::HUNT, false);
        }

        return $activityLog;
    }

    private function huntedLargeToad(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $skill = 10 + $petWithSkills->getStrength()->getTotal() + $petWithSkills->getBrawl(false)->getTotal();

        $pet->increaseFood(-1);

        if($this->squirrel3->rngNextInt(1, $skill) >= 6)
        {
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::BRAWL ]);

            if($this->squirrel3->rngNextInt(1, 4) === 1)
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% beat up a Giant Toad, and took two of its legs.', 'items/animal/meat/legs-frog');
                $this->inventoryService->petCollectsItem('Toad Legs', $pet, $pet->getName() . ' took these from a Giant Toad. It still has two left, so it\'s probably fine >_>', $activityLog);
            }
            else
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% wrestled a Toadstool off the back of a Giant Toad.', 'items/fungus/toadstool');
                $this->inventoryService->petCollectsItem('Toadstool', $pet, $pet->getName() . ' wrestled this from a Giant Toad.', $activityLog);
            }

            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::HUNT, true);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% picked a fight with a Giant Toad, but lost.', '');
            $pet->increaseEsteem(-2);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::BRAWL ]);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::HUNT, false);
        }

        return $activityLog;
    }

    private function huntedScarecrow(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $brawlRoll = $this->squirrel3->rngNextInt(1, 10 + $petWithSkills->getStrength()->getTotal() + $petWithSkills->getBrawl()->getTotal());
        $stealthSkill = $this->squirrel3->rngNextInt(1, 10 + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getStealth()->getTotal());

        $pet->increaseFood(-1);

        if($stealthSkill >= 10)
        {
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::STEALTH ]);
            $pet->increaseEsteem(1);

            $itemName = $this->squirrel3->rngNextFromArray([ 'Wheat', 'Rice' ]);
            $bodyPart = $this->squirrel3->rngNextFromArray([ 'left', 'right' ]) . ' ' . $this->squirrel3->rngNextFromArray([ 'leg', 'arm' ]);

            $moneys = $this->squirrel3->rngNextInt(1, $this->squirrel3->rngNextInt(2, $this->squirrel3->rngNextInt(3, 5)));

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% snuck up on a Scarecrow, and picked its pockets... and also its ' . $bodyPart . '! ' . $pet->getName() . ' walked away with ' . $moneys . '~~m~~, and some ' . $itemName . '.', '');
            $this->inventoryService->petCollectsItem($itemName, $pet, $pet->getName() . ' stole this from a Scarecrow\'s ' . $bodyPart .'.', $activityLog);
            $this->transactionService->getMoney($pet->getOwner(), $moneys, $pet->getName() . ' stole this from a Scarecrow.');
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::HUNT, true);
        }
        else if($brawlRoll >= 8)
        {
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::BRAWL ]);

            if($this->squirrel3->rngNextInt(1, 2) === 1)
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% beat up a Scarecrow, then took some of the Wheat it was defending.', '');
                $this->inventoryService->petCollectsItem('Wheat', $pet, $pet->getName() . ' took this from a Wheat Farm, after beating up its Scarecrow.', $activityLog);

                if($this->squirrel3->rngNextInt(1, 10 + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getNature()->getTotal()) >= 10)
                {
                    $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE ]);
                    $pet->increaseEsteem(1);

                    if($this->squirrel3->rngNextInt(1, 2) === 1)
                        $this->inventoryService->petCollectsItem('Wheat', $pet, $pet->getName() . ' took this from a Wheat Farm, after beating up its Scarecrow.', $activityLog);
                    else
                        $this->inventoryService->petCollectsItem('Wheat Flower', $pet, $pet->getName() . ' took this from a Wheat Farm, after beating up its Scarecrow.', $activityLog);
                }
            }
            else
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% beat up a Scarecrow, then took some of the Rice it was defending.', '');
                $this->inventoryService->petCollectsItem('Rice', $pet, $pet->getName() . ' took this from a Rice Farm, after beating up its Scarecrow', $activityLog);

                if($this->squirrel3->rngNextInt(1, 10 + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getNature()->getTotal()) >= 10)
                {
                    $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE ]);
                    $pet->increaseEsteem(1);

                    $this->inventoryService->petCollectsItem('Rice', $pet, $pet->getName() . ' took this from a Rice Farm, after beating up its Scarecrow.', $activityLog);
                }
            }

            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::HUNT, true);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to take out a Scarecrow, but lost.', '');
            $pet->increaseEsteem(-1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::BRAWL ]);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::HUNT, false);
        }

        return $activityLog;
    }

    private function huntedOnionBoy(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $skill = 10 + $petWithSkills->getStamina()->getTotal();

        if($pet->hasMerit(MeritEnum::GOURMAND) && $this->squirrel3->rngNextInt(1, 2) === 1)
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% encountered an Onion Boy. The fumes were powerful, but ' . $pet->getName() . ' didn\'t even flinch, and swallowed the Onionboy whole! (Ah~! A true Gourmand!)', 'items/veggie/onion')
                ->addInterestingness(PetActivityLogInterestingnessEnum::ACTIVITY_USING_MERIT)
            ;

            $pet
                ->increaseFood($this->squirrel3->rngNextInt(4, 8))
                ->increaseSafety($this->squirrel3->rngNextInt(2, 4))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE, PetSkillEnum::BRAWL ]);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::HUNT, true);
        }
        else if($pet->getTool() && $pet->getTool()->rangedOnly())
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% encountered an Onion Boy. The fumes were powerful, but ' . $pet->getName() . ' was able to defeat it from a distance thanks to their ' . $this->toolBonusService->getNameWithModifiers($pet->getTool()) . '!', 'items/veggie/onion');
            $this->inventoryService->petCollectsItem('Onion', $pet, 'The remains of an Onion Boy that ' . $pet->getName() . ' encountered.', $activityLog);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE, PetSkillEnum::BRAWL ]);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::HUNT, true);
        }
        else if($this->squirrel3->rngNextInt(1, $skill) >= 7)
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% encountered an Onion Boy. The fumes were powerful, but ' . $pet->getName() . ' powered through it.', 'items/veggie/onion');
            $this->inventoryService->petCollectsItem('Onion', $pet, 'The remains of an Onion Boy that ' . $pet->getName() . ' encountered.', $activityLog);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::NATURE, PetSkillEnum::BRAWL ]);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::HUNT, true);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% encountered an Onion Boy. The fumes were overwhelming, and ' . $pet->getName() . ' fled.', '');
            $pet->increaseSafety(-2);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE ]);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::HUNT, false);
        }

        return $activityLog;
    }

    private function huntedThievingMagpie(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $intSkill = 10 + $petWithSkills->getIntelligence()->getTotal();
        $dexSkill = 10 + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getBrawl()->getTotal();

        $isRanged = $pet->getTool() && $pet->getTool()->rangedOnly() && $pet->getTool()->brawlBonus() > 0;

        if($this->squirrel3->rngNextInt(1, $intSkill) <= 2 && $pet->getOwner()->getMoneys() >= 2)
        {
            $moneysLost = $this->squirrel3->rngNextInt(1, 2);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::BRAWL ]);
            $this->transactionService->spendMoney($pet->getOwner(), $moneysLost, $pet->getName() . ' was outsmarted by a Thieving Magpie, who stole this money.', false);
            $this->userStatsRepository->incrementStat($pet->getOwner(), UserStatEnum::MONEYS_STOLEN_BY_THIEVING_MAGPIES, $moneysLost);

            $pet
                ->increaseEsteem(-2)
                ->increaseSafety(-2)
            ;

            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::HUNT, false);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% was outsmarted by a Thieving Magpie, and lost ' . $moneysLost . ' ' . ($moneysLost === 1 ? 'money' : 'moneys') . '.', '');
        }
        else if($this->squirrel3->rngNextInt(1, $dexSkill) >= 9)
        {
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::BRAWL ]);

            $pet
                ->increaseEsteem(2)
                ->increaseSafety(2)
            ;

            if($this->squirrel3->rngNextInt(1, 4) === 1)
            {
                $moneys = $this->squirrel3->rngNextInt(2, 5);

                if($isRanged)
                {
                    $this->transactionService->getMoney($pet->getOwner(), $moneys, $pet->getName() . ' shot at a Thieving Magpie, forcing it to drop this money.');
                    $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% shot at a Thieving Magpie; it dropped its ' . $moneys . ' moneys and sped away.', 'icons/activity-logs/moneys');
                }
                else
                {
                    $this->transactionService->getMoney($pet->getOwner(), $moneys, $pet->getName() . ' pounced on a Thieving Magpie, and liberated this money.');
                    $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% pounced on a Thieving Magpie, and liberated its ' . $moneys . ' moneys.', 'icons/activity-logs/moneys');
                }
            }
            else
            {
                if($isRanged)
                {
                    $item = $this->squirrel3->rngNextFromArray([ 'String', 'Rice', 'Plastic' ]);
                    $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% shot at a Thieving Magpie, forcing it to drop some ' . $item . '.', '');
                }
                else
                {
                    $item = $this->squirrel3->rngNextFromArray([ 'Egg', 'String', 'Rice', 'Plastic' ]);
                    $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% pounced on a Thieving Magpie, and liberated ' . ($item === 'Egg' ? 'an' : 'some') . ' ' . $item . '.', '');
                }

                $this->inventoryService->petCollectsItem($item, $pet, 'Liberated from a Thieving Magpie.', $activityLog);
            }

            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::HUNT, true);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to take down a Thieving Magpie, but it got away.', '');
            $pet->increaseSafety(-1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::BRAWL ]);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::HUNT, false);
        }

        return $activityLog;
    }

    private function huntedGhosts(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        if($this->squirrel3->rngNextInt(1, 100) === 1)
            $prize = 'Little Strongbox';
        else if($this->squirrel3->rngNextInt(1, 50) === 1)
            $prize = $this->squirrel3->rngNextFromArray([ 'Rib', 'Stereotypical Bone' ]);
        else if($this->squirrel3->rngNextInt(1, 8) === 1)
            $prize = $this->squirrel3->rngNextFromArray([ 'Iron Bar', 'Silver Bar', 'Filthy Cloth' ]);
        else if($this->squirrel3->rngNextInt(1, 4) === 1)
            $prize = 'Ghost Pepper';
        else
            $prize = 'Quintessence';

        if($pet->isInGuild(GuildEnum::LIGHT_AND_SHADOW))
        {
            $skill = 10 + $petWithSkills->getIntelligence()->getTotal() * 2 + $petWithSkills->getUmbra()->getTotal();

            if($this->squirrel3->rngNextInt(1, $skill) >= 15)
            {
                $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::UMBRA ]);

                $pet->getGuildMembership()->increaseReputation();

                $prizeItem = $this->itemRepository->findOneByName($prize);

                $activityLog = $this->responseService->createActivityLog($pet, 'A Pirate Ghost tried to haunt ' . '%pet:' . $pet->getId() . '.name%, but ' . $pet->getName() . ' was able to calm the spirit! Thankful, the spirit gives ' . $pet->getName() . ' ' . $prizeItem->getNameWithArticle() . '.', 'guilds/light-and-shadow');
                $this->inventoryService->petCollectsItem($prize, $pet, $pet->getName() . ' received this from a grateful Pirate Ghost.', $activityLog);

                $pet
                    ->increaseSafety(2)
                    ->increaseEsteem(3)
                ;

                $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::HUNT, true);

                return $activityLog;
            }
        }
        else
        {
            $brawlSkill = 10 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getBrawl()->getTotal() + $petWithSkills->getUmbra()->getTotal();
            $stealthSkill = 10 + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getStealth()->getTotal();

            if($this->squirrel3->rngNextInt(1, $brawlSkill) >= 15)
            {
                $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::BRAWL, PetSkillEnum::UMBRA ]);

                $activityLog = $this->responseService->createActivityLog($pet, 'A Pirate Ghost tried to haunt ' . '%pet:' . $pet->getId() . '.name%, but ' . $pet->getName() . ' was able to dispel it (and got its ' . $prize . ')!', '');
                $this->inventoryService->petCollectsItem($prize, $pet, $pet->getName() . ' collected this from the remains of a Pirate Ghost.', $activityLog);

                $pet
                    ->increaseSafety(3)
                    ->increaseEsteem(2)
                ;

                $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::HUNT, true);

                return $activityLog;
            }
            else if($this->squirrel3->rngNextInt(1, $stealthSkill) >= 10)
            {
                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::STEALTH, PetSkillEnum::UMBRA ]);

                $hidSomehow = $this->squirrel3->rngNextFromArray([
                    'ducked behind a boulder', 'ducked behind a tree',
                    'dove into a bush', 'ducked behind a river bank',
                    'jumped into a hollow log'
                ]);

                $activityLog = $this->responseService->createActivityLog($pet, 'A Pirate Ghost tried to haunt ' . '%pet:' . $pet->getId() . '.name%, but ' . $pet->getName() . ' ' . $hidSomehow . ', eluding the ghost!', '');

                $pet->increaseEsteem(2);

                $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::HUNT, true);

                return $activityLog;
            }
        }

        $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::BRAWL, PetSkillEnum::UMBRA ]);
        $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% went out hunting, and got haunted by a Pirate Ghost! After harassing ' . $pet->getName() . ' for a while, the ghost became bored, and left.', '');
        $pet->increaseSafety(-3);
        $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(60, 75), PetActivityStatEnum::HUNT, false);

        return $activityLog;
    }

    private function huntedPossessedTurkey(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $loot = $this->squirrel3->rngNextFromArray([
            'Quintessence', 'Black Feathers', 'Giant Turkey Leg', 'Smallish Pumpkin Spice'
        ]);

        if($pet->isInGuild(GuildEnum::LIGHT_AND_SHADOW))
        {
            $skill = 10 + $petWithSkills->getIntelligence()->getTotal() * 2 + $petWithSkills->getUmbra()->getTotal();

            if($this->squirrel3->rngNextInt(1, $skill) >= 15)
            {
                $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::UMBRA ]);

                $pet->getGuildMembership()->increaseReputation();

                $item = $this->itemRepository->findOneByName($loot);

                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% encountered a Possessed Turkey! They were able to calm the creature, and set the spirit free. Grateful, the spirit conjured up ' . $item->getNameWithArticle() . ' for ' . $pet->getName() . '!', 'guilds/light-and-shadow');
                $this->inventoryService->petCollectsItem($item, $pet, $pet->getName() . ' got this by freeing the spirit possessing a Possessed Turkey.', $activityLog);

                $pet->increaseSafety(2);
                $pet->increaseEsteem(3);

                $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::HUNT, true);

                return $activityLog;
            }
            else
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% encountered a Possessed Turkey. They tried to calm it down, to set the spirit free, but was chased away by a flurry of kicks and pecks!', 'guilds/light-and-shadow');
                $pet->increaseEsteem(-$this->squirrel3->rngNextInt(2, 3));
                $pet->increaseSafety(-$this->squirrel3->rngNextInt(1, 3));
                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);
                $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::HUNT, false);

                return $activityLog;
            }
        }

        if($pet->isInGuild(GuildEnum::THE_UNIVERSE_FORGETS))
        {
            $skill = 10 + $petWithSkills->getIntelligence()->getTotal() * 2 + $petWithSkills->getUmbra()->getTotal();

            if($this->squirrel3->rngNextInt(1, $skill) >= 15)
            {
                $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::UMBRA ]);

                $pet->getGuildMembership()->increaseReputation();

                $item = $this->itemRepository->findOneByName($loot);

                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% encountered a Possessed Turkey! They were able to subdue the creature, and banish the spirit forever. (And they got ' . $item->getNameWithArticle() . ' out of it!)', 'guilds/the-universe-forgets');
                $this->inventoryService->petCollectsItem($item, $pet, $pet->getName() . ' got this by freeing the spirit possessing a Possessed Turkey.', $activityLog);

                $pet->increaseSafety(3);
                $pet->increaseEsteem(2);

                $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::HUNT, true);

                return $activityLog;
            }
            else
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% encountered a Possessed Turkey. They tried to subdue it, to banish the spirit forever, but was chased away by a flurry of kicks and pecks!', 'guilds/the-universe-forgets');
                $pet->increaseEsteem(-$this->squirrel3->rngNextInt(1, 3));
                $pet->increaseSafety(-$this->squirrel3->rngNextInt(2, 3));
                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ]);
                $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::HUNT, false);

                return $activityLog;
            }
        }

        $skill = 10 + $petWithSkills->getStrength()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getBrawl()->getTotal();

        $pet->increaseFood(-1);

        if($this->squirrel3->rngNextInt(1, $skill) >= 15)
        {
            $item = $this->itemRepository->findOneByName($loot);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% encountered a Possessed Turkey! They fought hard, took ' . $item->getNameWithArticle() . ', and drove the creature away!', '');
            $this->inventoryService->petCollectsItem($item, $pet, $pet->getName() . ' got this by defeating a Possessed Turkey.', $activityLog);
            $pet->increaseSafety(3);
            $pet->increaseEsteem(2);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::BRAWL ]);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::HUNT, true);

            return $activityLog;
        }

        $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% encountered and fought a Possessed Turkey, but was chased away by a flurry of kicks and pecks!', '');
        $pet->increaseEsteem(-$this->squirrel3->rngNextInt(1, 3));
        $pet->increaseSafety(-$this->squirrel3->rngNextInt(2, 4));
        $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::BRAWL ]);
        $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::HUNT, false);

        return $activityLog;
    }

    private function huntedSatyr(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $brawlRoll = $this->squirrel3->rngNextInt(1, 10 + $petWithSkills->getStrength()->getTotal() + $petWithSkills->getBrawl()->getTotal());
        $musicSkill = $this->squirrel3->rngNextInt(1, 10 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getMusic()->getTotal());

        $pet->increaseFood(-1);

        if($pet->hasMerit(MeritEnum::EIDETIC_MEMORY) && $pet->hasMerit(MeritEnum::SOOTHING_VOICE))
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% encountered a Satyr, but remembered that Satyrs love music, so sang a song. The Satyr was so enthralled by ' . $pet->getName() . '\'s Soothing Voice, that it offered gifts before leaving in peace.', 'icons/activity-logs/drunk-satyr');
            $pet->increaseEsteem(1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::MUSIC ]);
            $this->inventoryService->petCollectsItem('Blackberry Wine', $pet, 'Gifts for ' . $pet->getName() . ', from a Satyr.', $activityLog);

            if($this->squirrel3->rngNextInt(1, 5) === 1)
                $this->inventoryService->petCollectsItem('Music Note', $pet, 'Gifts for ' . $pet->getName() . ', from a Satyr.', $activityLog);
            else
                $this->inventoryService->petCollectsItem('Plain Yogurt', $pet, 'Gifts for ' . $pet->getName() . ', from a Satyr.', $activityLog);

            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::HUNT, true);
        }
        else if($musicSkill > $brawlRoll)
        {
            if($pet->hasMerit(MeritEnum::SOOTHING_VOICE))
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% encountered a Satyr, who upon hearing ' . $pet->getName() . '\'s voice, bade them sing. ' . $pet->getName() . ' did so; the Satyr was so enthralled by their soothing voice, that it offered gifts before leaving in peace.', 'icons/activity-logs/drunk-satyr');
                $pet->increaseEsteem(1);
                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::MUSIC ]);
                $this->inventoryService->petCollectsItem('Blackberry Wine', $pet, 'Gifts for ' . $pet->getName() . ', from a Satyr.', $activityLog);

                if($this->squirrel3->rngNextInt(1, 5) === 1)
                    $this->inventoryService->petCollectsItem('Music Note', $pet, 'Gifts for ' . $pet->getName() . ', from a Satyr.', $activityLog);
                else
                    $this->inventoryService->petCollectsItem('Plain Yogurt', $pet, 'Gifts for ' . $pet->getName() . ', from a Satyr.', $activityLog);

                $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::HUNT, true);
            }
            else if($musicSkill >= 15)
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% encountered a Satyr, who challenged ' . $pet->getName() . ' to a sing. It was surprised by ' . $pet->getName() . '\'s musical skill, and apologetically offered gifts before leaving in peace.', 'icons/activity-logs/drunk-satyr');
                $pet->increaseEsteem(2);
                $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::MUSIC ]);
                $this->inventoryService->petCollectsItem('Blackberry Wine', $pet, 'Gifts for ' . $pet->getName() . ', from a Satyr.', $activityLog);

                if($this->squirrel3->rngNextInt(1, 5) === 1)
                    $this->inventoryService->petCollectsItem('Music Note', $pet, 'Gifts for ' . $pet->getName() . ', from a Satyr.', $activityLog);
                else
                    $this->inventoryService->petCollectsItem('Plain Yogurt', $pet, 'Gifts for ' . $pet->getName() . ', from a Satyr.', $activityLog);

                $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::HUNT, true);
            }
            else
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% encountered a Satyr, who challenged ' . $pet->getName() . ' to a sing. The Satyr quickly cut ' . $pet->getName() . ' off, complaining loudly, and leaving in a huff.', '');
                $pet->increaseEsteem(-1);
                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::MUSIC ]);
                $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::HUNT, false);
            }
        }
        else
        {
            if($brawlRoll >= 15)
            {
                $pet->increaseSafety(3);
                $pet->increaseEsteem(2);
                $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::BRAWL ]);
                if($this->squirrel3->rngNextInt(1, 2) === 1)
                {
                    $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% fought a Satyr, and won, receiving its Yogurt (gross), and Wine.', '');
                    $this->inventoryService->petCollectsItem('Plain Yogurt', $pet, 'Satyr loot, earned by ' . $pet->getName() . '.', $activityLog);
                    $this->inventoryService->petCollectsItem('Blackberry Wine', $pet, 'Satyr loot, earned by ' . $pet->getName() . '.', $activityLog);
                }
                else
                {
                    $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% fought a Satyr, and won, receiving its Yogurt (gross), and Horn. Er: Talon, I guess.', '');
                    $this->inventoryService->petCollectsItem('Plain Yogurt', $pet, 'Satyr loot, earned by ' . $pet->getName() . '.', $activityLog);
                    $this->inventoryService->petCollectsItem('Talon', $pet, 'Satyr loot, earned by ' . $pet->getName() . '.', $activityLog);
                }

                $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::HUNT, true);
            }
            else
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to fight a drunken Satyr, but the Satyr misinterpreted ' . $pet->getName() . '\'s intentions, and it started to get really weird, so ' . $pet->getName() . ' ran away.', 'icons/activity-logs/drunk-satyr');
                $pet->increaseSafety(-$this->squirrel3->rngNextInt(1, 5));
                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::BRAWL ]);
                $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::HUNT, false);
            }
        }

        return $activityLog;
    }

    private function noGoats(Pet $pet): PetActivityLog
    {
        $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::HUNT, false);
        return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% went out hunting, expecting to find some goats, but there don\'t seem to be any around today...', 'icons/activity-logs/confused');
    }

    private function huntedPaperGolem(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $brawlRoll = $this->squirrel3->rngNextInt(1, 10 + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getStamina()->getTotal() + max($petWithSkills->getCrafts()->getTotal(), $petWithSkills->getBrawl()->getTotal()));
        $stealthRoll = $this->squirrel3->rngNextInt(1, 10 + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getStealth()->getTotal());

        $pet->increaseFood(-1);

        if($stealthRoll >= 15 || $brawlRoll >= 17)
        {
            $pet->increaseSafety(1);
            $pet->increaseEsteem(2);

            if($stealthRoll >= 15)
            {
                $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::STEALTH ]);

                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% snuck up behind a Paper Golem, and unfolded it!', '');
            }
            else
            {
                $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS, PetSkillEnum::BRAWL ]);

                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% unfolded a Paper Golem!', '');
            }

            $recipe = $this->squirrel3->rngNextFromArray([
                'Stroganoff Recipe',
                'Bananananers Foster Recipe'
            ]);

            if($this->squirrel3->rngNextInt(1, 10) === 1 && $pet->hasMerit(MeritEnum::LUCKY))
                $this->inventoryService->petCollectsItem($recipe, $pet, $pet->getName() . ' got this by unfolding a Paper Golem. Lucky~!', $activityLog);
            else if($this->squirrel3->rngNextInt(1, 20) === 1)
                $this->inventoryService->petCollectsItem($recipe, $pet, $pet->getName() . ' got this by unfolding a Paper Golem.', $activityLog);
            else
            {
                $this->inventoryService->petCollectsItem('Paper', $pet, $pet->getName() . ' got this by unfolding a Paper Golem.', $activityLog);

                if($stealthRoll + $brawlRoll >= 15 + 17)
                    $this->inventoryService->petCollectsItem('Paper', $pet, $pet->getName() . ' got this by unfolding a Paper Golem.', $activityLog);
            }

            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::HUNT, true);
        }
        else
        {
            $pet->increaseFood(-1);
            $pet->increaseEsteem(-1);
            $pet->increaseSafety(-1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::BRAWL ]);

            if($this->squirrel3->rngNextInt(1, 30) === 1 && $pet->hasMerit(MeritEnum::LUCKY))
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to unfold a Paper Golem, but got a nasty paper cut! During the fight, however, a small, glowing die rolled out from within the folds of the golem! Lucky~! ' . $pet->getName() . ' grabbed it before fleeing.', '')
                    ->addInterestingness(PetActivityLogInterestingnessEnum::ACTIVITY_USING_MERIT)
                ;

                $this->inventoryService->petCollectsItem('Glowing Six-sided Die', $pet, 'While ' . $pet->getName() . ' was fighting a Paper Golem, this fell out from it! Lucky~!', $activityLog);
            }
            else if($this->squirrel3->rngNextInt(1, 20) === 1)
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to unfold a Paper Golem, but got a nasty paper cut! During the fight, however, a small, glowing die rolled out from within the folds of the golem. ' . $pet->getName() . ' grabbed it before fleeing.', '');

                $this->inventoryService->petCollectsItem('Glowing Six-sided Die', $pet, 'While ' . $pet->getName() . ' was fighting a Paper Golem, this fell out from it.', $activityLog);
            }
            else
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to unfold a Paper Golem, but got a nasty paper cut!', '');

            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::HUNT, false);
        }

        return $activityLog;
    }

    private function huntedLeshyDemon(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $skill = 10 + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getStamina()->getTotal() + max($petWithSkills->getCrafts()->getTotal(), $petWithSkills->getBrawl()->getTotal());

        $pet->increaseFood(-1);
        $getExtraItem = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getNature()->getTotal() + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getGatheringBonus()->getTotal()) >= 15;

        if($this->squirrel3->rngNextInt(1, $skill) >= 18)
        {
            $pet->increaseSafety(1);
            $pet->increaseEsteem(2);
            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::CRAFTS, PetSkillEnum::BRAWL, PetSkillEnum::NATURE ]);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% was attacked by a Leshy Demon, but was able to defeat it.', '');

            $this->inventoryService->petCollectsItem('Crooked Stick', $pet, $pet->getName() . ' plucked this from a Leshy Demon.', $activityLog);

            if($getExtraItem)
            {
                $extraItem = $this->squirrel3->rngNextFromArray([
                    'Crooked Stick',
                    'Tea Leaves',
                    'Quintessence',
                    'Witch-hazel'
                ]);

                $this->inventoryService->petCollectsItem($extraItem, $pet, $pet->getName() . ' pulled this out of a Leshy Demon\'s root cage.', $activityLog);
            }

            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::HUNT, true);
        }
        else
        {
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::BRAWL ]);

            if($getExtraItem)
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% was attacked by a Leshy Demon! ' . $pet->getName() . ' was able to break off one of its many Crooked Sticks, but was eventually forced to flee.', '');

                $this->inventoryService->petCollectsItem('Crooked Stick', $pet, $pet->getName() . ' broke this off of a Leshy Demon before running from it.', $activityLog);
            }
            else
            {
                $pet->increaseSafety(-1);
                $pet->increaseEsteem(-1);
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% was attacked by a Leshy Demon, and forced to flee!', '');
            }

            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::HUNT, false);
        }

        return $activityLog;
    }

    public function huntedTurkeyDragon(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $skill = 10 + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getBrawl()->getTotal();

        $gobbleGobble = $pet->getStatusEffect(StatusEffectEnum::GOBBLE_GOBBLE);

        $pet->increaseFood(-1);

        $getExtraItem = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getNature()->getTotal() + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getGatheringBonus()->getTotal()) >= 15;

        $possibleItems = [
            'Giant Turkey Leg',
            'Scales',
            'Feathers',
            'Talon',
            'Quintessence',
            'Charcoal',
            'Smallish Pumpkin Spice',
        ];

        if($this->squirrel3->rngNextInt(1, $skill) >= 18)
        {
            $pet->increaseSafety(1);
            $pet->increaseEsteem(2);
            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::BRAWL, PetSkillEnum::NATURE ]);

            if($gobbleGobble !== null)
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% found the Turkeydragon, and defeated it, claiming its head as a prize! (Dang! Brutal!)', '');
            else
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% was attacked by a Turkeydragon, but was able to defeat it.', '');

            $numItems = $getExtraItem ? 3 : 2;

            for($i = 0; $i < $numItems; $i++)
            {
                $itemName = $this->squirrel3->rngNextFromArray($possibleItems);

                $this->inventoryService->petCollectsItem($itemName, $pet, $pet->getName() . ' got this from defeating a Turkeydragon.', $activityLog);
            }

            if($gobbleGobble !== null)
                $this->inventoryService->petCollectsItem('Turkey King', $pet, $pet->getName() . ' got this from defeating a Turkeydragon.', $activityLog);

            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::HUNT, true);
        }
        else
        {
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::BRAWL ]);

            if($getExtraItem)
            {
                $itemName = $this->squirrel3->rngNextFromArray($possibleItems);

                $aSome = in_array($itemName, [ 'Scales', 'Feathers', 'Quintessence', 'Charcoal' ]) ? 'some' : 'a';

                if($gobbleGobble !== null)
                    $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% found the Turkeydragon, and attacked it! ' . $pet->getName() . ' was able to claim ' . $aSome . ' ' . $itemName . ' before being forced to flee...', '');
                else
                    $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% was attacked by a Turkeydragon! ' . $pet->getName() . ' was able to claim ' . $aSome . ' ' . $itemName . ' before fleeing...', '');

                $this->inventoryService->petCollectsItem($itemName, $pet, $pet->getName() . ' nabbed this from a Turkeydragon before running from it.', $activityLog);
            }
            else
            {
                $pet->increaseSafety(-1);
                $pet->increaseEsteem(-1);

                if($gobbleGobble !== null)
                    $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% found the Turkeydragon, and attacked it, but was forced to flee!', '');
                else
                    $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% was attacked by a Turkeydragon, and forced to flee!', '');
            }

            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::HUNT, false);
        }

        if($gobbleGobble !== null)
            $pet->removeStatusEffect($gobbleGobble);

        return $activityLog;
    }

    private function huntedEggSaladMonstrosity(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();

        $skill = 10 + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getStamina()->getTotal() + $petWithSkills->getBrawl()->getTotal();

        $pet->increaseFood(-1);

        $possibleLoot = [
            'Egg',
            $this->squirrel3->rngNextFromArray([ 'Mayo(nnaise)', 'Egg', 'Vinegar', 'Oil' ]),
            'Celery',
            'Onion',
        ];

        if($pet->hasMerit(MeritEnum::GOURMAND) && $this->squirrel3->rngNextInt(1, 4) === 1)
        {
            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::BRAWL ]);

            $prize = $this->itemRepository->findOneByName($this->squirrel3->rngNextFromArray($possibleLoot));

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% went out hunting, and encountered an Egg Salad Monstrosity! After a grueling (and sticky) battle, ' . $pet->getName() . ' took a huge bite out of the monster, slaying it! (Ah~! A true Gourmand!) Finally, they dug ' . $prize->getNameWithArticle() . ' out of the lumpy corpse, and brought it home.', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::ACTIVITY_USING_MERIT)
            ;

            $this->inventoryService->petCollectsItem($prize, $pet, $pet->getName() . ' collected this from the remains of an Egg Salad Monstrosity.', $activityLog);

            $pet
                ->increaseFood($this->squirrel3->rngNextInt(4, 8))
                ->increaseSafety(4)
                ->increaseEsteem(3)
            ;

            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::HUNT, true);
        }
        else if($this->squirrel3->rngNextInt(1, $skill) >= 19)
        {
            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::BRAWL ]);

            $loot = [
                $this->squirrel3->rngNextFromArray($possibleLoot),
                $this->squirrel3->rngNextFromArray($possibleLoot),
            ];

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% went out hunting, and encountered an Egg Salad Monstrosity! After a grueling (and sticky) battle, ' . $pet->getName() . ' won, and claimed its ' . ArrayFunctions::list_nice($loot) . '!', '');

            foreach($loot as $itemName)
                $this->inventoryService->petCollectsItem($itemName, $pet, $pet->getName() . ' collected this from the remains of an Egg Salad Monstrosity.', $activityLog);

            $pet->increaseSafety(4);
            $pet->increaseEsteem(3);

            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::HUNT, true);
        }
        else
        {
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::BRAWL ]);
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% went out hunting, and encountered an Egg Salad Monstrosity, which chased ' . $pet->getName() . ' away!', '');
            $pet->increaseSafety(-3);

            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(60, 75), PetActivityStatEnum::HUNT, false);
        }

        return $activityLog;
    }
}
