<?php
namespace App\Service\PetActivity;

use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Entity\User;
use App\Enum\DistractionLocationEnum;
use App\Enum\GuildEnum;
use App\Enum\MeritEnum;
use App\Enum\MoonPhaseEnum;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Enum\PetActivityStatEnum;
use App\Enum\PetSkillEnum;
use App\Enum\StatusEffectEnum;
use App\Enum\UnlockableFeatureEnum;
use App\Enum\UserStatEnum;
use App\Functions\ActivityHelpers;
use App\Functions\AdventureMath;
use App\Functions\ArrayFunctions;
use App\Functions\DateFunctions;
use App\Functions\InventoryModifierFunctions;
use App\Functions\NumberFunctions;
use App\Model\ComputedPetSkills;
use App\Model\PetChanges;
use App\Repository\ItemRepository;
use App\Repository\MuseumItemRepository;
use App\Repository\PetActivityLogTagRepository;
use App\Repository\UserQuestRepository;
use App\Repository\UserStatsRepository;
use App\Service\CalendarService;
use App\Service\FieldGuideService;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\PetExperienceService;
use App\Service\ResponseService;
use App\Service\Squirrel3;
use App\Service\StatusEffectService;
use App\Service\TransactionService;
use App\Service\WeatherService;

class HuntingService
{
    private ResponseService $responseService;
    private InventoryService $inventoryService;
    private UserStatsRepository $userStatsRepository;
    private CalendarService $calendarService;
    private MuseumItemRepository $museumItemRepository;
    private ItemRepository $itemRepository;
    private UserQuestRepository $userQuestRepository;
    private PetExperienceService $petExperienceService;
    private TransactionService $transactionService;
    private WerecreatureEncounterService $werecreatureEncounterService;
    private WeatherService $weatherService;
    private StatusEffectService $statusEffectService;
    private IRandom $squirrel3;
    private GatheringDistractionService $gatheringDistractions;
    private PetActivityLogTagRepository $petActivityLogTagRepository;
    private FieldGuideService $fieldGuideService;

    public function __construct(
        ResponseService $responseService, InventoryService $inventoryService, UserStatsRepository $userStatsRepository,
        CalendarService $calendarService, MuseumItemRepository $museumItemRepository, ItemRepository $itemRepository,
        UserQuestRepository $userQuestRepository, PetExperienceService $petExperienceService,
        TransactionService $transactionService, Squirrel3 $squirrel3,
        WerecreatureEncounterService $werecreatureEncounterService, WeatherService $weatherService,
        StatusEffectService $statusEffectService, GatheringDistractionService $gatheringDistractions,
        PetActivityLogTagRepository $petActivityLogTagRepository, FieldGuideService $fieldGuideService
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
        $this->squirrel3 = $squirrel3;
        $this->werecreatureEncounterService = $werecreatureEncounterService;
        $this->weatherService = $weatherService;
        $this->statusEffectService = $statusEffectService;
        $this->gatheringDistractions = $gatheringDistractions;
        $this->petActivityLogTagRepository = $petActivityLogTagRepository;
        $this->fieldGuideService = $fieldGuideService;
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
            $activityLog = $this->werecreatureEncounterService->encounterWerecreature($petWithSkills, 'hunting', [ 'Hunting' ]);
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
                    if($this->squirrel3->rngNextBool())
                        $activityLog = $this->huntedOnionBoy($petWithSkills);
                    else
                        $activityLog = $this->huntedBeaver($petWithSkills);
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
        {
            $activityLog->setChanges($changes->compare($pet));
        }

        if(AdventureMath::petAttractsBug($this->squirrel3, $pet, 100))
            $this->inventoryService->petAttractsRandomBug($pet);
    }

    private function canRescueAnotherHouseFairy(User $user): bool
    {
        // if you've unlocked the fireplace, then you can't rescue a second
        if($user->hasUnlockedFeature(UnlockableFeatureEnum::Fireplace))
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

        $activityLog = $this->responseService->createActivityLog($pet, 'While ' . '%pet:' . $pet->getId() . '.name% was out hunting, they spotted a Raccoon and Thieving Magpie fighting over a fairy! %pet:' . $pet->getId() . '.name% jumped in and chased the two creatures off before tending to the fairy\'s wounds.', '')
            ->addTags($this->petActivityLogTagRepository->findByNames([ 'Hunting', 'Fighting', 'Fae-kind' ]))
        ;
        $inventory = $this->inventoryService->petCollectsItem('House Fairy', $pet, 'Rescued from a Raccoon and Thieving Magpie.', $activityLog);

        if($inventory)
            $inventory->setLockedToOwner(true);

        $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::BRAWL ], $activityLog);

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
            $pet
                ->increaseSafety($this->squirrel3->rngNextInt(1, 2))
                ->increaseEsteem($this->squirrel3->rngNextInt(1, 2))
            ;

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% couldn\'t find anything to hunt, so watched some small birds play in the Greenhouse Bird Bath, instead.', 'icons/activity-logs/birb')
                ->addInterestingness(PetActivityLogInterestingnessEnum::UNCOMMON_ACTIVITY)
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Hunting', 'Greenhouse' ]))
            ;

            if($pet->getSkills()->getBrawl() < 5)
                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::BRAWL ], $activityLog);

            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::HUNT, false);
        }
        else
        {
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::HUNT, false);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% went out hunting, but couldn\'t find anything to hunt.', 'icons/activity-logs/confused')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Hunting' ]))
            ;
        }

        return $activityLog;
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
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% ' . $defeated . ' a Dust Bunny, reducing it to Fluff!', 'items/ambiguous/fluff')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Hunting' ]))
            ;
            $this->inventoryService->petCollectsItem('Fluff', $pet, 'The remains of a Dust Bunny that ' . $pet->getName() . ' hunted.', $activityLog);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::BRAWL ], $activityLog);

            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::HUNT, true);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% ' . $chased . ' a Dust Bunny, but wasn\'t able to catch up with it.', '')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Hunting' ]))
            ;
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::BRAWL ], $activityLog);

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
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% ' . $defeated . ' a Plastic Bag, reducing it to Plastic... somehow?', 'items/ambiguous/fluff')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Hunting' ]))
            ;
            $this->inventoryService->petCollectsItem('Plastic', $pet, 'The remains of a vicious Plastic Bag that ' . $pet->getName() . ' hunted!', $activityLog);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::BRAWL ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::HUNT, true);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% ' . $chased . ' a Plastic Bag, but wasn\'t able to catch up with it!', '')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Hunting' ]))
            ;
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::BRAWL ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::HUNT, false);
        }

        return $activityLog;
    }

    private function huntedGoat(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $skill = 10 + $petWithSkills->getStrength()->getTotal() + $petWithSkills->getBrawl(false)->getTotal();

        $pet->increaseFood(-1);

        if($this->squirrel3->rngNextInt(1, $skill) >= 6)
        {
            $pet->increaseEsteem(1);

            if($this->squirrel3->rngNextInt(1, 2) === 1)
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% wrestled a Goat, and won, receiving Creamy Milk.', '')
                    ->addTags($this->petActivityLogTagRepository->findByNames([ 'Fighting' ]))
                ;
                $this->inventoryService->petCollectsItem('Creamy Milk', $pet, $pet->getName() . '\'s prize for out-wrestling a Goat.', $activityLog);
            }
            else
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% wrestled a Goat, and won, receiving Butter.', '')
                    ->addTags($this->petActivityLogTagRepository->findByNames([ 'Fighting' ]))
                ;
                $this->inventoryService->petCollectsItem('Butter', $pet, $pet->getName() . '\'s prize for out-wrestling a Goat.', $activityLog);
            }

            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::HUNT, true);
        }
        else
        {
            if($this->squirrel3->rngNextInt(1, 4) === 1)
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% wrestled a Goat. The Goat won.', '')
                    ->addTags($this->petActivityLogTagRepository->findByNames([ 'Fighting' ]))
                ;
                $this->inventoryService->petCollectsItem('Fluff', $pet, $pet->getName() . ' wrestled a Goat, and lost, but managed to grab a fistful of Fluff.', $activityLog);
            }
            else
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% wrestled a Goat. The Goat won.', '')
                    ->addTags($this->petActivityLogTagRepository->findByNames([ 'Fighting' ]))
                ;
            }

            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::HUNT, false);
        }

        $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::BRAWL ], $activityLog);

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

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% snuck up on a sleeping Deep-fried Dough Golem, and harvested some of its ' . $loot . ', and Oil, without it ever noticing!', '')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Stealth' ]))
            ;
            $this->inventoryService->petCollectsItem($loot, $pet, $pet->getName() . ' stole this off the body of a sleeping Deep-fried Dough Golem.', $activityLog);
            $this->inventoryService->petCollectsItem('Oil', $pet, $pet->getName() . ' stole this off the body of a sleeping Deep-fried Dough Golem.', $activityLog);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::STEALTH ], $activityLog);

            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::HUNT, true);

            return $activityLog;
        }
        else if($stealth > 15)
        {
            $pet->increaseEsteem(1);

            $loot = $this->squirrel3->rngNextFromArray($possibleLoot);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% snuck up on a sleeping Dough Golem, and harvested some of its ' . $loot . ' without it ever noticing!', '')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Stealth' ]))
            ;
            $this->inventoryService->petCollectsItem($loot, $pet, $pet->getName() . ' stole this off the body of a sleeping Dough Golem.', $activityLog);

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::STEALTH ], $activityLog);

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

                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% attacked a rampaging Deep-fried Dough Golem, defeated it, and harvested its ' . $loot . '.', '')
                    ->addTags($this->petActivityLogTagRepository->findByNames([ 'Fighting' ]))
                ;
                $this->inventoryService->petCollectsItem($loot, $pet, $pet->getName() . ' took this from the body of a defeated Deep-fried Dough Golem.', $activityLog);
                $this->inventoryService->petCollectsItem('Oil', $pet, $pet->getName() . ' took this from the body of a defeated Deep-fried Dough Golem.', $activityLog);

                $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::BRAWL ], $activityLog);
            }
            else
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% attacked a rampaging Deep-fried Dough Golem. It was gross and oily, and %pet:' . $pet->getId() . '.name% got Oil all over themselves, but in the end they defeated the creature, and harvested its ' . $loot . '.', '')
                    ->addTags($this->petActivityLogTagRepository->findByNames([ 'Fighting' ]))
                ;
                $this->inventoryService->petCollectsItem($loot, $pet, $pet->getName() . ' took this from the body of a defeated Deep-fried Dough Golem.', $activityLog);
                $this->statusEffectService->applyStatusEffect($pet, StatusEffectEnum::OIL_COVERED, 1);

                $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::BRAWL ], $activityLog);
            }

            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::HUNT, true);
        }
        else if($skillCheck >= 7)
        {
            $pet->increaseEsteem(1);

            $loot = $this->squirrel3->rngNextFromArray($possibleLoot);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% attacked a rampaging Dough Golem, defeated it, and harvested its ' . $loot . '.', '')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Fighting' ]))
            ;
            $this->inventoryService->petCollectsItem($loot, $pet, $pet->getName() . ' took this from the body of a defeated Dough Golem.', $activityLog);

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::BRAWL ], $activityLog);

            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::HUNT, true);
        }
        else
        {
            if($this->squirrel3->rngNextInt(1, 4) === 1)
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% attacked a rampaging Dough Golem, but it released a cloud of defensive flour, and escaped. ' . $pet->getName() . ' picked up some of the flour, and brought it home.', '')
                    ->addTags($this->petActivityLogTagRepository->findByNames([ 'Fighting' ]))
                ;
                $this->inventoryService->petCollectsItem('Wheat Flour', $pet, $pet->getName() . ' got this from a fleeing Dough Golem.', $activityLog);
            }
            else
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% attacked a Dough Golem, but it was really sticky. ' . $pet->getName() . '\'s attacks were useless, and they were forced to retreat.', '')
                    ->addTags($this->petActivityLogTagRepository->findByNames([ 'Fighting' ]))
                ;
            }

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::BRAWL ], $activityLog);

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
            $item = $this->squirrel3->rngNextFromArray([ 'Talon', 'Feathers', 'Giant Turkey Leg', 'Smallish Pumpkin Spice' ]);

            $aOrSome = $item === 'Feathers' ? 'some' : 'a';

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% wrestled a Turkey! The Turkey fled, but not before ' . $pet->getName() . ' took ' . $aOrSome . ' ' . $item . '!', '')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Fighting', 'Special Event', 'Thanksgiving' ]))
            ;
            $this->inventoryService->petCollectsItem($item, $pet, $pet->getName() . ' wrestled this from a Turkey.', $activityLog);

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::BRAWL ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::HUNT, true);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% picked a fight with a Turkey, but lost.', '')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Fighting', 'Special Event', 'Thanksgiving' ]))
            ;
            $pet->increaseEsteem(-2);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::BRAWL ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::HUNT, false);
        }

        return $activityLog;
    }

    private function huntedLargeToad(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        if($this->squirrel3->rngNextInt(1, 20) === 1)
            return $this->gatheringDistractions->adventure($petWithSkills, DistractionLocationEnum::WOODS, 'hunting in the woods');

        $pet = $petWithSkills->getPet();
        $skill = 10 + $petWithSkills->getStrength()->getTotal() + $petWithSkills->getBrawl(false)->getTotal();

        $pet->increaseFood(-1);

        if($this->squirrel3->rngNextInt(1, $skill) >= 6)
        {
            if($this->squirrel3->rngNextInt(1, 4) === 1)
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% beat up a Giant Toad, and took two of its legs.', 'items/animal/meat/legs-frog')
                    ->addTags($this->petActivityLogTagRepository->findByNames([ 'Fighting' ]))
                ;
                $this->inventoryService->petCollectsItem('Toad Legs', $pet, $pet->getName() . ' took these from a Giant Toad. It still has two left, so it\'s probably fine >_>', $activityLog);
            }
            else
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% wrestled a Toadstool off the back of a Giant Toad.', 'items/fungus/toadstool')
                    ->addTags($this->petActivityLogTagRepository->findByNames([ 'Fighting' ]))
                ;
                $this->inventoryService->petCollectsItem('Toadstool', $pet, $pet->getName() . ' wrestled this from a Giant Toad.', $activityLog);
            }

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::BRAWL ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::HUNT, true);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% picked a fight with a Giant Toad, but lost.', '')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Fighting' ]))
            ;
            $pet->increaseEsteem(-2);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::BRAWL ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::HUNT, false);
        }

        return $activityLog;
    }

    private function huntedScarecrow(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        if($this->squirrel3->rngNextInt(1, 20) === 1)
            return $this->gatheringDistractions->adventure($petWithSkills, DistractionLocationEnum::IN_TOWN, 'hunting around town');

        $pet = $petWithSkills->getPet();

        $brawlRoll = $this->squirrel3->rngNextInt(1, 10 + $petWithSkills->getStrength()->getTotal() + $petWithSkills->getBrawl()->getTotal());
        $stealthSkill = $this->squirrel3->rngNextInt(1, 10 + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getStealth()->getTotal());

        $pet->increaseFood(-1);

        if($stealthSkill >= 10)
        {
            $pet->increaseEsteem(1);

            $itemName = $this->squirrel3->rngNextFromArray([ 'Wheat', 'Rice' ]);
            $bodyPart = $this->squirrel3->rngNextFromArray([ 'left', 'right' ]) . ' ' . $this->squirrel3->rngNextFromArray([ 'leg', 'arm' ]);

            $moneys = $this->squirrel3->rngNextInt(1, $this->squirrel3->rngNextInt(2, $this->squirrel3->rngNextInt(3, 5)));

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% snuck up on a Scarecrow, and picked its pockets... and also its ' . $bodyPart . '! ' . $pet->getName() . ' walked away with ' . $moneys . '~~m~~, and some ' . $itemName . '.', '')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Stealth', 'Moneys' ]))
            ;
            $this->inventoryService->petCollectsItem($itemName, $pet, $pet->getName() . ' stole this from a Scarecrow\'s ' . $bodyPart .'.', $activityLog);
            $this->transactionService->getMoney($pet->getOwner(), $moneys, $pet->getName() . ' stole this from a Scarecrow.');
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::STEALTH ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::HUNT, true);
        }
        else if($brawlRoll >= 8)
        {
            $foundPinecone = $this->calendarService->getMonthAndDay() > 1225;

            if($this->squirrel3->rngNextInt(1, 2) === 1)
            {
                if($foundPinecone)
                {
                    $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% beat up a Scarecrow, then took some of the Wheat it was defending. Hm-what? A Pinecone also fell out of the Scarecrow!', '')
                        ->addTags($this->petActivityLogTagRepository->findByNames([ 'Fighting', 'Gathering', 'Special Event' ]))
                    ;
                }
                else
                {
                    $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% beat up a Scarecrow, then took some of the Wheat it was defending.', '')
                        ->addTags($this->petActivityLogTagRepository->findByNames([ 'Fighting', 'Gathering' ]))
                    ;
                }

                $this->inventoryService->petCollectsItem('Wheat', $pet, $pet->getName() . ' took this from a Wheat Farm, after beating up its Scarecrow.', $activityLog);

                if($this->squirrel3->rngNextInt(1, 10 + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getNature()->getTotal()) >= 10)
                {
                    if($this->squirrel3->rngNextInt(1, 2) === 1)
                        $this->inventoryService->petCollectsItem('Wheat', $pet, $pet->getName() . ' took this from a Wheat Farm, after beating up its Scarecrow.', $activityLog);
                    else
                        $this->inventoryService->petCollectsItem('Wheat Flower', $pet, $pet->getName() . ' took this from a Wheat Farm, after beating up its Scarecrow.', $activityLog);

                    $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE ], $activityLog);
                    $pet->increaseEsteem(1);
                }
            }
            else
            {
                if($foundPinecone)
                {
                    $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% beat up a Scarecrow, then took some of the Rice it was defending. Hm-what? A Pinecone also fell out of the Scarecrow!', '')
                        ->addTags($this->petActivityLogTagRepository->findByNames([ 'Fighting', 'Gathering', 'Special Event', 'Stocking Stuffing Season' ]))
                    ;
                }
                else
                {
                    $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% beat up a Scarecrow, then took some of the Rice it was defending.', '')
                        ->addTags($this->petActivityLogTagRepository->findByNames([ 'Fighting', 'Gathering' ]))
                    ;
                }

                $this->inventoryService->petCollectsItem('Rice', $pet, $pet->getName() . ' took this from a Rice Farm, after beating up its Scarecrow.', $activityLog);

                if($this->squirrel3->rngNextInt(1, 10 + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getNature()->getTotal()) >= 10)
                {
                    $this->inventoryService->petCollectsItem('Rice', $pet, $pet->getName() . ' took this from a Rice Farm, after beating up its Scarecrow.', $activityLog);

                    $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE ], $activityLog);
                    $pet->increaseEsteem(1);
                }
            }

            if($foundPinecone)
                $this->inventoryService->petCollectsItem('Pinecone', $pet, 'This fell out of a Scarecrow that ' . $pet->getName() . ' beat up.', $activityLog);

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::BRAWL ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::HUNT, true);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to take out a Scarecrow, but lost.', '')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Fighting' ]))
            ;
            $pet->increaseEsteem(-1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::BRAWL ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::HUNT, false);
        }

        return $activityLog;
    }

    private function huntedOnionBoy(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $skill = 10 + $petWithSkills->getStamina()->getTotal();

        $this->fieldGuideService->maybeUnlock($pet->getOwner(), 'Onion Boy', ActivityHelpers::PetName($pet). ' encountered an Onion Boy at the edge of town...');

        if($pet->hasMerit(MeritEnum::GOURMAND) && $this->squirrel3->rngNextInt(1, 2) === 1)
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% encountered an Onion Boy. The fumes were powerful, but ' . $pet->getName() . ' didn\'t even flinch, and swallowed the Onion Boy whole! (Ah~! A true Gourmand!)', 'items/veggie/onion')
                ->addInterestingness(PetActivityLogInterestingnessEnum::ACTIVITY_USING_MERIT)
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Fighting', 'Eating' ]))
            ;

            $pet
                ->increaseFood($this->squirrel3->rngNextInt(4, 8))
                ->increaseSafety($this->squirrel3->rngNextInt(2, 4))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE, PetSkillEnum::BRAWL ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::HUNT, true);
        }
        else if($pet->getTool() && $pet->getTool()->rangedOnly())
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% encountered an Onion Boy. The fumes were powerful, but ' . $pet->getName() . ' attacked from a distance using their ' . InventoryModifierFunctions::getNameWithModifiers($pet->getTool()) . '! The Onion Boy ran off, dropping an Onion as it ran.', 'items/veggie/onion')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Fighting' ]))
            ;
            $this->inventoryService->petCollectsItem('Onion', $pet, 'Dropped by an Onion Boy that ' . $pet->getName() . ' encountered.', $activityLog);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE, PetSkillEnum::BRAWL ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::HUNT, true);
        }
        else if($this->squirrel3->rngNextInt(1, $skill) >= 7)
        {
            $exp = 2;

            $getClothes = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getBrawl()->getTotal()) >= 20;

            if($getClothes)
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% encountered an Onion Boy. The fumes were powerful, but ' . $pet->getName() . ' powered through it, and grabbed onto its... clothes? The creature ran off, causing it to drop an Onion.', 'items/veggie/onion')
                    ->addTags($this->petActivityLogTagRepository->findByNames([ 'Fighting' ]))
                ;

                $loot = $this->squirrel3->rngNextFromArray([ 'Paper', 'Filthy Cloth' ]);

                $this->inventoryService->petCollectsItem($loot, $pet, 'Snatched off an Onion Boy that ' . $pet->getName() . ' encountered.', $activityLog);

                $exp++;
            }
            else
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% encountered an Onion Boy. The fumes were powerful, but ' . $pet->getName() . ' powered through it, scaring the creature off, causing it to drop an Onion.', 'items/veggie/onion')
                    ->addTags($this->petActivityLogTagRepository->findByNames([ 'Fighting' ]))
                ;
            }

            $this->inventoryService->petCollectsItem('Onion', $pet, 'Dropped by an Onion Boy that ' . $pet->getName() . ' encountered.', $activityLog);

            $this->petExperienceService->gainExp($pet, $exp, [ PetSkillEnum::NATURE, PetSkillEnum::BRAWL ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::HUNT, true);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% encountered an Onion Boy. The fumes were overwhelming, and ' . $pet->getName() . ' fled.', '')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Fighting' ]))
            ;
            $pet->increaseSafety(-2);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::HUNT, false);
        }

        return $activityLog;
    }

    private function huntedBeaver(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        $pet = $petWithSkills->getPet();
        $skill = 20 + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getStrength()->getTotal() + $petWithSkills->getBrawl(false)->getTotal();

        $pet->increaseFood(-2);

        if($this->squirrel3->rngNextInt(1, $skill) >= 15)
        {
            $item = $this->squirrel3->rngNextFromArray([ 'Fluff', 'Castoreum' ]);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% wrestled a beaver! It fled, but not before ' . ActivityHelpers::PetName($pet) . ' took some of its ' . $item . '!', '')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Fighting' ]))
            ;
            $this->inventoryService->petCollectsItem($item, $pet, $pet->getName() . ' wrestled this from a beaver.', $activityLog);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::BRAWL ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::HUNT, true);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% picked a fight with a beaver, but lost.', '')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Fighting' ]))
            ;
            $pet->increaseEsteem(-2);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::BRAWL ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::HUNT, false);
        }

        return $activityLog;
    }

    private function huntedThievingMagpie(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        if($this->squirrel3->rngNextInt(1, 20) === 1)
            return $this->gatheringDistractions->adventure($petWithSkills, DistractionLocationEnum::WOODS, 'hunting in the woods');

        $pet = $petWithSkills->getPet();
        $intSkill = 10 + $petWithSkills->getIntelligence()->getTotal();
        $dexSkill = 10 + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getBrawl()->getTotal();

        $isRanged = $pet->getTool() && $pet->getTool()->rangedOnly() && $pet->getTool()->brawlBonus() > 0;

        if($this->squirrel3->rngNextInt(1, $intSkill) <= 2 && $pet->getOwner()->getMoneys() >= 2)
        {
            $moneysLost = $this->squirrel3->rngNextInt(1, 2);

            if($this->squirrel3->rngNextInt(1, 10) === 1)
            {
                $description = 'who absquatulated with ' . $moneysLost . ' ' . ($moneysLost === 1 ? 'money' : 'moneys') . '!';

                if($this->squirrel3->rngNextInt(1, 10) === 1)
                    $description = ' (Ugh! Everyone\'s least-favorite kind of squatulation!)';
            }
            else
                $description = 'who stole ' . $moneysLost . ' ' . ($moneysLost === 1 ? 'money' : 'moneys') . '.';

            $this->transactionService->spendMoney($pet->getOwner(), $moneysLost, $pet->getName() . ' was outsmarted by a Thieving Magpie, ' . $description, false);

            $this->userStatsRepository->incrementStat($pet->getOwner(), UserStatEnum::MONEYS_STOLEN_BY_THIEVING_MAGPIES, $moneysLost);

            $pet
                ->increaseEsteem(-2)
                ->increaseSafety(-2)
            ;

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% was outsmarted by a Thieving Magpie, ' . $description, '')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Moneys' ]))
            ;

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::BRAWL ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::HUNT, false);
        }
        else if($this->squirrel3->rngNextInt(1, $dexSkill) >= 9)
        {
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
                    $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% shot at a Thieving Magpie; it dropped its ' . $moneys . ' moneys and sped away.', 'icons/activity-logs/moneys')
                        ->addTags($this->petActivityLogTagRepository->findByNames([ 'Hunting', 'Moneys' ]))
                    ;
                }
                else
                {
                    $this->transactionService->getMoney($pet->getOwner(), $moneys, $pet->getName() . ' pounced on a Thieving Magpie, and liberated this money.');
                    $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% pounced on a Thieving Magpie, and liberated its ' . $moneys . ' moneys.', 'icons/activity-logs/moneys')
                        ->addTags($this->petActivityLogTagRepository->findByNames([ 'Hunting', 'Moneys' ]))
                    ;
                }
            }
            else
            {
                if($isRanged)
                {
                    $item = $this->squirrel3->rngNextFromArray([ 'String', 'Rice', 'Plastic' ]);
                    $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% shot at a Thieving Magpie, forcing it to drop some ' . $item . '.', '')
                        ->addTags($this->petActivityLogTagRepository->findByNames([ 'Hunting' ]))
                    ;
                }
                else
                {
                    $item = $this->squirrel3->rngNextFromArray([ 'Egg', 'String', 'Rice', 'Plastic' ]);
                    $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% pounced on a Thieving Magpie, and liberated ' . ($item === 'Egg' ? 'an' : 'some') . ' ' . $item . '.', '')
                        ->addTags($this->petActivityLogTagRepository->findByNames([ 'Hunting' ]))
                    ;
                }

                $this->inventoryService->petCollectsItem($item, $pet, 'Liberated from a Thieving Magpie.', $activityLog);
            }

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::BRAWL ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::HUNT, true);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to take down a Thieving Magpie, but it got away.', '')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Hunting' ]))
            ;
            $pet->increaseSafety(-1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::BRAWL ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::HUNT, false);
        }

        return $activityLog;
    }

    private function huntedGhosts(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        if($this->squirrel3->rngNextInt(1, 20) === 1)
            return $this->gatheringDistractions->adventure($petWithSkills, DistractionLocationEnum::WOODS, 'hunting in the woods');

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
                $pet->getGuildMembership()->increaseReputation();

                $prizeItem = $this->itemRepository->findOneByName($prize);

                $activityLog = $this->responseService->createActivityLog($pet, 'A Pirate Ghost tried to haunt %pet:' . $pet->getId() . '.name%, but %pet:' . $pet->getId() . '.name% was able to calm the spirit! Thankful, the spirit gives %pet:' . $pet->getId() . '.name% ' . $prizeItem->getNameWithArticle() . '.', 'guilds/light-and-shadow')
                    ->addTags($this->petActivityLogTagRepository->findByNames([ 'Guild' ]))
                ;
                $this->inventoryService->petCollectsItem($prize, $pet, $pet->getName() . ' received this from a grateful Pirate Ghost.', $activityLog);

                $pet
                    ->increaseSafety(2)
                    ->increaseEsteem(3)
                ;

                $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::UMBRA ], $activityLog);
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
                $activityLog = $this->responseService->createActivityLog($pet, 'A Pirate Ghost tried to haunt %pet:' . $pet->getId() . '.name%, but %pet:' . $pet->getId() . '.name% was able to dispel it (and got its ' . $prize . ')!', '');
                $this->inventoryService->petCollectsItem($prize, $pet, $pet->getName() . ' collected this from the remains of a Pirate Ghost.', $activityLog);

                $pet
                    ->increaseSafety(3)
                    ->increaseEsteem(2)
                ;

                $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::BRAWL, PetSkillEnum::UMBRA ], $activityLog);
                $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::HUNT, true);

                return $activityLog;
            }
            else if($this->squirrel3->rngNextInt(1, $stealthSkill) >= 10)
            {
                $hidSomehow = $this->squirrel3->rngNextFromArray([
                    'ducked behind a boulder', 'ducked behind a tree',
                    'dove into a bush', 'ducked behind a river bank',
                    'jumped into a hollow log'
                ]);

                $activityLog = $this->responseService->createActivityLog($pet, 'A Pirate Ghost tried to haunt %pet:' . $pet->getId() . '.name%, but %pet:' . $pet->getId() . '.name% ' . $hidSomehow . ', eluding the ghost!', '')
                    ->addTags($this->petActivityLogTagRepository->findByNames([ 'Stealth' ]))
                ;

                $pet->increaseEsteem(2);

                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::STEALTH, PetSkillEnum::UMBRA ], $activityLog);
                $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::HUNT, true);

                return $activityLog;
            }
        }

        $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% went out hunting, and got haunted by a Pirate Ghost! After harassing %pet:' . $pet->getId() . '.name% for a while, the ghost became bored, and left.', '')
            ->addTags($this->petActivityLogTagRepository->findByNames([ 'Hunting' ]))
        ;
        $pet->increaseSafety(-3);
        $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::BRAWL, PetSkillEnum::UMBRA ], $activityLog);
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
                $pet->getGuildMembership()->increaseReputation();

                $item = $this->itemRepository->findOneByName($loot);

                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% encountered a Possessed Turkey! They were able to calm the creature, and set the spirit free. Grateful, the spirit conjured up ' . $item->getNameWithArticle() . ' for ' . $pet->getName() . '!', 'guilds/light-and-shadow')
                    ->addTags($this->petActivityLogTagRepository->findByNames([ 'Guild', 'Special Event', 'Thanksgiving' ]))
                ;
                $this->inventoryService->petCollectsItem($item, $pet, $pet->getName() . ' got this by freeing the spirit possessing a Possessed Turkey.', $activityLog);

                $pet->increaseSafety(2);
                $pet->increaseEsteem(3);

                $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::UMBRA ], $activityLog);
                $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::HUNT, true);

                return $activityLog;
            }
            else
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% encountered a Possessed Turkey. They tried to calm it down, to set the spirit free, but was chased away by a flurry of kicks and pecks!', 'guilds/light-and-shadow')
                    ->addTags($this->petActivityLogTagRepository->findByNames([ 'Guild', 'Special Event', 'Thanksgiving' ]))
                ;
                $pet->increaseEsteem(-$this->squirrel3->rngNextInt(2, 3));
                $pet->increaseSafety(-$this->squirrel3->rngNextInt(1, 3));
                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ], $activityLog);
                $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::HUNT, false);

                return $activityLog;
            }
        }

        if($pet->isInGuild(GuildEnum::THE_UNIVERSE_FORGETS))
        {
            $skill = 10 + $petWithSkills->getIntelligence()->getTotal() * 2 + $petWithSkills->getUmbra()->getTotal();

            if($this->squirrel3->rngNextInt(1, $skill) >= 15)
            {
                $pet->getGuildMembership()->increaseReputation();

                $item = $this->itemRepository->findOneByName($loot);

                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% encountered a Possessed Turkey! They were able to subdue the creature, and banish the spirit forever. (And they got ' . $item->getNameWithArticle() . ' out of it!)', 'guilds/the-universe-forgets')
                    ->addTags($this->petActivityLogTagRepository->findByNames([ 'Guild', 'Special Event', 'Thanksgiving' ]))
                ;
                $this->inventoryService->petCollectsItem($item, $pet, $pet->getName() . ' got this by freeing the spirit possessing a Possessed Turkey.', $activityLog);

                $pet->increaseSafety(3);
                $pet->increaseEsteem(2);

                $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::UMBRA ], $activityLog);
                $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::HUNT, true);

                return $activityLog;
            }
            else
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% encountered a Possessed Turkey. They tried to subdue it, to banish the spirit forever, but was chased away by a flurry of kicks and pecks!', 'guilds/the-universe-forgets')
                    ->addTags($this->petActivityLogTagRepository->findByNames([ 'Guild', 'Special Event', 'Thanksgiving' ]))
                ;
                $pet->increaseEsteem(-$this->squirrel3->rngNextInt(1, 3));
                $pet->increaseSafety(-$this->squirrel3->rngNextInt(2, 3));
                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::UMBRA ], $activityLog);
                $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::HUNT, false);

                return $activityLog;
            }
        }

        $skill = 10 + $petWithSkills->getStrength()->getTotal() + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getBrawl()->getTotal();

        $pet->increaseFood(-1);

        if($this->squirrel3->rngNextInt(1, $skill) >= 15)
        {
            $item = $this->itemRepository->findOneByName($loot);

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% encountered a Possessed Turkey! They fought hard, took ' . $item->getNameWithArticle() . ', and drove the creature away!', '')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Fighting', 'Special Event', 'Thanksgiving' ]))
            ;
            $this->inventoryService->petCollectsItem($item, $pet, $pet->getName() . ' got this by defeating a Possessed Turkey.', $activityLog);
            $pet->increaseSafety(3);
            $pet->increaseEsteem(2);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::BRAWL ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::HUNT, true);

            return $activityLog;
        }

        $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% encountered and fought a Possessed Turkey, but was chased away by a flurry of kicks and pecks!', '')
            ->addTags($this->petActivityLogTagRepository->findByNames([ 'Fighting', 'Special Event', 'Thanksgiving' ]))
        ;
        $pet->increaseEsteem(-$this->squirrel3->rngNextInt(1, 3));
        $pet->increaseSafety(-$this->squirrel3->rngNextInt(2, 4));
        $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::BRAWL ], $activityLog);
        $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::HUNT, false);

        return $activityLog;
    }

    private function huntedSatyr(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        if($this->squirrel3->rngNextInt(1, 20) === 1)
            return $this->gatheringDistractions->adventure($petWithSkills, DistractionLocationEnum::WOODS, 'hunting in the woods');

        $pet = $petWithSkills->getPet();

        $brawlRoll = $this->squirrel3->rngNextInt(1, 10 + $petWithSkills->getStrength()->getTotal() + $petWithSkills->getBrawl()->getTotal());
        $musicSkill = $this->squirrel3->rngNextInt(1, 10 + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getMusic()->getTotal());

        $pet->increaseFood(-1);

        if($pet->hasStatusEffect(StatusEffectEnum::CORDIAL))
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% encountered a Satyr; the Satyr was so enamored by ' . $pet->getName() . '\'s cordiality, they had a simply _wonderful_ time, and offered gifts before leaving in peace.', 'icons/activity-logs/drunk-satyr')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Fae-kind' ]))
                ->addInterestingness(PetActivityLogInterestingnessEnum::UNCOMMON_ACTIVITY)
            ;
            $pet->increaseEsteem(1);
            $this->inventoryService->petCollectsItem('Blackberry Wine', $pet, 'Gifts for ' . $pet->getName() . ', from a Satyr.', $activityLog);

            if($this->squirrel3->rngNextInt(1, 5) === 1)
                $this->inventoryService->petCollectsItem('Quintessence', $pet, 'Gifts for ' . $pet->getName() . ', from a Satyr.', $activityLog);
            else
                $this->inventoryService->petCollectsItem('Plain Yogurt', $pet, 'Gifts for ' . $pet->getName() . ', from a Satyr.', $activityLog);

            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(20, 40), PetActivityStatEnum::HUNT, true);
        }
        else if($pet->hasMerit(MeritEnum::EIDETIC_MEMORY) && $pet->hasMerit(MeritEnum::SOOTHING_VOICE))
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% encountered a Satyr, but remembered that Satyrs love music, so sang a song. The Satyr was so enthralled by ' . $pet->getName() . '\'s Soothing Voice, that it offered gifts before leaving in peace.', 'icons/activity-logs/drunk-satyr')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Fae-kind' ]))
            ;
            $pet->increaseEsteem(1);
            $this->inventoryService->petCollectsItem('Blackberry Wine', $pet, 'Gifts for ' . $pet->getName() . ', from a Satyr.', $activityLog);

            if($this->squirrel3->rngNextInt(1, 5) === 1)
                $this->inventoryService->petCollectsItem('Music Note', $pet, 'Gifts for ' . $pet->getName() . ', from a Satyr.', $activityLog);
            else
                $this->inventoryService->petCollectsItem('Plain Yogurt', $pet, 'Gifts for ' . $pet->getName() . ', from a Satyr.', $activityLog);

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::MUSIC ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::HUNT, true);
        }
        else if($musicSkill > $brawlRoll)
        {
            if($pet->hasMerit(MeritEnum::SOOTHING_VOICE))
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% encountered a Satyr, who upon hearing ' . $pet->getName() . '\'s voice, bade them sing. ' . $pet->getName() . ' did so; the Satyr was so enthralled by their soothing voice, that it offered gifts before leaving in peace.', 'icons/activity-logs/drunk-satyr')
                    ->addTags($this->petActivityLogTagRepository->findByNames([ 'Fae-kind' ]))
                ;
                $pet->increaseEsteem(1);
                $this->inventoryService->petCollectsItem('Blackberry Wine', $pet, 'Gifts for ' . $pet->getName() . ', from a Satyr.', $activityLog);

                if($this->squirrel3->rngNextInt(1, 5) === 1)
                    $this->inventoryService->petCollectsItem('Music Note', $pet, 'Gifts for ' . $pet->getName() . ', from a Satyr.', $activityLog);
                else
                    $this->inventoryService->petCollectsItem('Plain Yogurt', $pet, 'Gifts for ' . $pet->getName() . ', from a Satyr.', $activityLog);

                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::MUSIC ], $activityLog);
                $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::HUNT, true);
            }
            else if($musicSkill >= 15)
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% encountered a Satyr, who challenged ' . $pet->getName() . ' to a sing. It was surprised by ' . $pet->getName() . '\'s musical skill, and apologetically offered gifts before leaving in peace.', 'icons/activity-logs/drunk-satyr')
                    ->addTags($this->petActivityLogTagRepository->findByNames([ 'Fae-kind' ]))
                ;
                $pet->increaseEsteem(2);
                $this->inventoryService->petCollectsItem('Blackberry Wine', $pet, 'Gifts for ' . $pet->getName() . ', from a Satyr.', $activityLog);

                if($this->squirrel3->rngNextInt(1, 5) === 1)
                    $this->inventoryService->petCollectsItem('Music Note', $pet, 'Gifts for ' . $pet->getName() . ', from a Satyr.', $activityLog);
                else
                    $this->inventoryService->petCollectsItem('Plain Yogurt', $pet, 'Gifts for ' . $pet->getName() . ', from a Satyr.', $activityLog);

                $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::MUSIC ], $activityLog);
                $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::HUNT, true);
            }
            else
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% encountered a Satyr, who challenged ' . $pet->getName() . ' to a sing. The Satyr quickly cut ' . $pet->getName() . ' off, complaining loudly, and leaving in a huff.', '')
                    ->addTags($this->petActivityLogTagRepository->findByNames([ 'Fae-kind' ]))
                ;
                $pet->increaseEsteem(-1);
                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::MUSIC ], $activityLog);
                $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::HUNT, false);
            }
        }
        else
        {
            if($brawlRoll >= 15)
            {
                $pet->increaseSafety(3);
                $pet->increaseEsteem(2);
                if($this->squirrel3->rngNextInt(1, 2) === 1)
                {
                    $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% fought a Satyr, and won, receiving its Yogurt (gross), and Wine.', '')
                        ->addTags($this->petActivityLogTagRepository->findByNames([ 'Fighting', 'Fae-kind' ]))
                    ;
                    $this->inventoryService->petCollectsItem('Plain Yogurt', $pet, 'Satyr loot, earned by ' . $pet->getName() . '.', $activityLog);
                    $this->inventoryService->petCollectsItem('Blackberry Wine', $pet, 'Satyr loot, earned by ' . $pet->getName() . '.', $activityLog);
                }
                else
                {
                    $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% fought a Satyr, and won, receiving its Yogurt (gross), and Horn. Er: Talon, I guess.', '')
                        ->addTags($this->petActivityLogTagRepository->findByNames([ 'Fighting', 'Fae-kind' ]))
                    ;
                    $this->inventoryService->petCollectsItem('Plain Yogurt', $pet, 'Satyr loot, earned by ' . $pet->getName() . '.', $activityLog);
                    $this->inventoryService->petCollectsItem('Talon', $pet, 'Satyr loot, earned by ' . $pet->getName() . '.', $activityLog);
                }

                $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::BRAWL ], $activityLog);
                $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::HUNT, true);
            }
            else
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to fight a drunken Satyr, but the Satyr misinterpreted ' . $pet->getName() . '\'s intentions, and it started to get really weird, so ' . $pet->getName() . ' ran away.', 'icons/activity-logs/drunk-satyr')
                    ->addTags($this->petActivityLogTagRepository->findByNames([ 'Fighting', 'Fae-kind' ]))
                ;
                $pet->increaseSafety(-$this->squirrel3->rngNextInt(1, 5));
                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::BRAWL ], $activityLog);
                $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::HUNT, false);
            }
        }

        return $activityLog;
    }

    private function noGoats(Pet $pet): PetActivityLog
    {
        $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::HUNT, false);

        return $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% went out hunting, expecting to find some goats, but there don\'t seem to be any around today...', 'icons/activity-logs/confused')
            ->addTags($this->petActivityLogTagRepository->findByNames([ 'Hunting', 'Special Event', 'Easter' ]))
        ;
    }

    private function huntedPaperGolem(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        if($this->squirrel3->rngNextInt(1, 20) === 1)
            return $this->gatheringDistractions->adventure($petWithSkills, DistractionLocationEnum::IN_TOWN, 'hunting around town');

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
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% snuck up behind a Paper Golem, and unfolded it!', '')
                    ->addTags($this->petActivityLogTagRepository->findByNames([ 'Stealth' ]))
                ;
                $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::STEALTH ], $activityLog);
            }
            else
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% unfolded a Paper Golem!', '')
                    ->addTags($this->petActivityLogTagRepository->findByNames([ 'Fighting', 'Crafting' ]))
                ;

                $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS, PetSkillEnum::BRAWL ], $activityLog);
            }

            $recipe = $this->squirrel3->rngNextFromArray([
                'Stroganoff Recipe',
                'Bananananers Foster Recipe'
            ]);

            if($this->squirrel3->rngNextInt(1, 10) === 1 && $pet->hasMerit(MeritEnum::LUCKY))
            {
                $this->inventoryService->petCollectsItem($recipe, $pet, $pet->getName() . ' got this by unfolding a Paper Golem. Lucky~!', $activityLog);
                $activityLog->addTags($this->petActivityLogTagRepository->findByNames([ 'Lucky~!' ]));
            }
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

            if($this->squirrel3->rngNextInt(1, 30) === 1 && $pet->hasMerit(MeritEnum::LUCKY))
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to unfold a Paper Golem, but got a nasty paper cut! During the fight, however, a small, glowing die rolled out from within the folds of the golem! Lucky~! ' . $pet->getName() . ' grabbed it before fleeing.', '')
                    ->addInterestingness(PetActivityLogInterestingnessEnum::ACTIVITY_USING_MERIT)
                    ->addTags($this->petActivityLogTagRepository->findByNames([ 'Fighting', 'Crafting', 'Lucky~!' ]))
                ;

                $this->inventoryService->petCollectsItem('Glowing Six-sided Die', $pet, 'While ' . $pet->getName() . ' was fighting a Paper Golem, this fell out from it! Lucky~!', $activityLog);
            }
            else if($this->squirrel3->rngNextInt(1, 20) === 1)
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to unfold a Paper Golem, but got a nasty paper cut! During the fight, however, a small, glowing die rolled out from within the folds of the golem. ' . $pet->getName() . ' grabbed it before fleeing.', '')
                    ->addTags($this->petActivityLogTagRepository->findByNames([ 'Fighting', 'Crafting' ]))
                ;

                $this->inventoryService->petCollectsItem('Glowing Six-sided Die', $pet, 'While ' . $pet->getName() . ' was fighting a Paper Golem, this fell out from it.', $activityLog);
            }
            else
            {
                $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% tried to unfold a Paper Golem, but got a nasty paper cut!', '')
                    ->addTags($this->petActivityLogTagRepository->findByNames([ 'Fighting', 'Crafting' ]))
                ;
            }

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::BRAWL ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::HUNT, false);
        }

        return $activityLog;
    }

    private function huntedLeshyDemon(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        if($this->squirrel3->rngNextInt(1, 20) === 1)
            return $this->gatheringDistractions->adventure($petWithSkills, DistractionLocationEnum::WOODS, 'hunting in the woods');

        $pet = $petWithSkills->getPet();

        $skill = 10 + $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getBrawl()->getTotal() + $petWithSkills->getClimbingBonus()->getTotal() * 2;
        $getExtraItem = $this->squirrel3->rngNextInt(1, 20 + $petWithSkills->getNature()->getTotal() + $petWithSkills->getPerception()->getTotal() + $petWithSkills->getGatheringBonus()->getTotal()) >= 15;

        $pet->increaseFood(-1);

        if($this->squirrel3->rngNextInt(1, $skill) >= 18)
        {
            $pet->increaseSafety(1);
            $pet->increaseEsteem(2);

            if($this->squirrel3->rngNextInt(1, 5) === 1)
            {
                $activityLog = $this->responseService->createActivityLog($pet, 'While %pet:' . $pet->getId() . '.name% was out hunting, something started throwing sticks and throwing branches at them! %pet:' . $pet->getId() . '.name% spotted an Argopelter in the trees! They chased after the creature, and defeated it with one of its own sticks!', '')
                    ->addTags($this->petActivityLogTagRepository->findByNames([ 'Fighting' ]))
                ;

                $this->inventoryService->petCollectsItem('Crooked Stick', $pet, $pet->getName() . ' beat up an Argopelter with the help of this stick, which the Argopelter had thrown at them!', $activityLog);
            }
            else
            {
                $activityLog = $this->responseService->createActivityLog($pet, 'While %pet:' . $pet->getId() . '.name% was out hunting, something started throwing sticks and throwing branches at them! %pet:' . $pet->getId() . '.name% spotted an Argopelter in the trees! They chased after the creature, and quickly defeated it before it could get away!', '')
                    ->addTags($this->petActivityLogTagRepository->findByNames([ 'Fighting' ]))
                ;

                $this->inventoryService->petCollectsItem('Crooked Stick', $pet, 'An Argopelter threw this at ' . $pet->getName() . '!', $activityLog);
            }

            $this->fieldGuideService->maybeUnlock($pet->getOwner(), 'Argopelter', 'While ' . $pet->getName() . ' was out hunting, an Argopelter began throwing sticks and thorny branches at them...');

            if($getExtraItem)
            {
                $extraItem = $this->squirrel3->rngNextFromArray([
                    'Crooked Stick',
                    'Feathers',
                    'Quintessence',
                    'Witch-hazel'
                ]);

                $this->inventoryService->petCollectsItem($extraItem, $pet, $pet->getName() . ' took this from a defeated Argopelter.', $activityLog);
            }

            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::CRAFTS, PetSkillEnum::BRAWL, PetSkillEnum::NATURE ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::HUNT, true);
        }
        else
        {
            $pet->increaseSafety(-1);

            if($pet->hasMerit(MeritEnum::EIDETIC_MEMORY))
            {
                $activityLog = $this->responseService->createActivityLog($pet, 'While %pet:' . $pet->getId() . '.name% was out hunting in the woods, something started throwing sticks and thorny branches at them! %pet:' . $pet->getId() . '.name% never saw their tormenter, but it was surely an Agropelter...', '')
                    ->addTags($this->petActivityLogTagRepository->findByNames([ 'Fighting' ]))
                ;
            }
            else
            {
                $activityLog = $this->responseService->createActivityLog($pet, 'While %pet:' . $pet->getId() . '.name% was out hunting in the woods, something started throwing sticks and thorny branches at them! %pet:' . $pet->getId() . '.name% looked around for their tormenter, but didn\'t see anything...', '')
                    ->addTags($this->petActivityLogTagRepository->findByNames([ 'Fighting' ]))
                ;
                $pet->increaseEsteem(-1);
            }

            if($getExtraItem)
            {
                $activityLog->setEntry($activityLog->getEntry() . ' They found one of the sticks that had been thrown at them, and returned home.');

                if($pet->hasMerit(MeritEnum::EIDETIC_MEMORY))
                    $this->inventoryService->petCollectsItem('Crooked Stick', $pet, 'This was thrown at ' . $pet->getName() . ' while they were out hunting, probably by an Argopelter.', $activityLog);
                else
                    $this->inventoryService->petCollectsItem('Crooked Stick', $pet, 'This was thrown at ' . $pet->getName() . ' while they were out hunting, by an unseen assailant.', $activityLog);
            }

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::BRAWL ], $activityLog);
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

            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::BRAWL, PetSkillEnum::NATURE ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::HUNT, true);
        }
        else
        {
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

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::BRAWL ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::HUNT, false);
        }

        $activityLog->addTags($this->petActivityLogTagRepository->findByNames([ 'Fighting', 'Special Event', 'Thanksgiving' ]));

        if($gobbleGobble !== null)
            $pet->removeStatusEffect($gobbleGobble);

        return $activityLog;
    }

    private function huntedEggSaladMonstrosity(ComputedPetSkills $petWithSkills): PetActivityLog
    {
        if($this->squirrel3->rngNextInt(1, 20) === 1)
            return $this->gatheringDistractions->adventure($petWithSkills, DistractionLocationEnum::IN_TOWN, 'hunting around town');

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
            $prize = $this->itemRepository->findOneByName($this->squirrel3->rngNextFromArray($possibleLoot));

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% went out hunting, and encountered an Egg Salad Monstrosity! After a grueling (and sticky) battle, ' . $pet->getName() . ' took a huge bite out of the monster, slaying it! (Ah~! A true Gourmand!) Finally, they dug ' . $prize->getNameWithArticle() . ' out of the lumpy corpse, and brought it home.', '')
                ->addInterestingness(PetActivityLogInterestingnessEnum::ACTIVITY_USING_MERIT)
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Hunting', 'Eating' ]))
            ;

            $this->inventoryService->petCollectsItem($prize, $pet, $pet->getName() . ' collected this from the remains of an Egg Salad Monstrosity.', $activityLog);

            $pet
                ->increaseFood($this->squirrel3->rngNextInt(4, 8))
                ->increaseSafety(4)
                ->increaseEsteem(3)
            ;

            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::BRAWL ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::HUNT, true);
        }
        else if($this->squirrel3->rngNextInt(1, $skill) >= 19)
        {
            $loot = [
                $this->squirrel3->rngNextFromArray($possibleLoot),
                $this->squirrel3->rngNextFromArray($possibleLoot),
            ];

            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% went out hunting, and encountered an Egg Salad Monstrosity! After a grueling (and sticky) battle, ' . $pet->getName() . ' won, and claimed its ' . ArrayFunctions::list_nice($loot) . '!', '')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Hunting' ]))
            ;

            foreach($loot as $itemName)
                $this->inventoryService->petCollectsItem($itemName, $pet, $pet->getName() . ' collected this from the remains of an Egg Salad Monstrosity.', $activityLog);

            $pet->increaseSafety(4);
            $pet->increaseEsteem(3);

            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::BRAWL ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::HUNT, true);
        }
        else
        {
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% went out hunting, and encountered an Egg Salad Monstrosity, which chased ' . $pet->getName() . ' away!', '')
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Hunting' ]))
            ;
            $pet->increaseSafety(-3);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::BRAWL ], $activityLog);
            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(60, 75), PetActivityStatEnum::HUNT, false);
        }

        return $activityLog;
    }
}
