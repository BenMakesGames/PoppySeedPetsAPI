<?php
namespace App\Service\PetActivity;

use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Entity\User;
use App\Enum\MeritEnum;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Enum\PetActivityStatEnum;
use App\Enum\PetSkillEnum;
use App\Enum\UserStatEnum;
use App\Functions\ArrayFunctions;
use App\Functions\NumberFunctions;
use App\Model\PetChanges;
use App\Repository\ItemRepository;
use App\Repository\MuseumItemRepository;
use App\Repository\UserQuestRepository;
use App\Repository\UserStatsRepository;
use App\Service\CalendarService;
use App\Service\InventoryService;
use App\Service\PetExperienceService;
use App\Service\PetService;
use App\Service\ResponseService;

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

    public function __construct(
        ResponseService $responseService, InventoryService $inventoryService, UserStatsRepository $userStatsRepository,
        CalendarService $calendarService, MuseumItemRepository $museumItemRepository, ItemRepository $itemRepository,
        UserQuestRepository $userQuestRepository, PetExperienceService $petExperienceService
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
    }

    public function adventure(Pet $pet)
    {
        $maxSkill = 10 + $pet->getStrength() + $pet->getBrawl() - $pet->getAlcohol() - $pet->getPsychedelic();

        $maxSkill = NumberFunctions::constrain($maxSkill, 1, 20);

        $useThanksgivingPrey = $this->calendarService->isThanksgiving() && mt_rand(1, 2) === 1;

        $roll = mt_rand(1, $maxSkill);

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
                $activityLog = $this->huntedPlasticBag($pet);
                break;
            case 6:
            case 7:
            case 8:
                if($this->canRescueAnotherHouseFairy($pet->getOwner()))
                    $activityLog = $this->rescueHouseFairy($pet);
                else if($useThanksgivingPrey)
                    $activityLog = $this->huntedTurkey($pet);
                else
                    $activityLog = $this->huntedGoat($pet);
                break;
            case 9:
            case 10:
                if($useThanksgivingPrey)
                    $activityLog = $this->huntedTurkey($pet);
                else
                    $activityLog = $this->huntedLargeToad($pet);
                break;
            case 11:
                $activityLog = $this->huntedScarecrow($pet);
                break;
            case 12:
                $activityLog = $this->huntedOnionBoy($pet);
                break;
            case 13:
            case 14:
                $activityLog = $this->huntedThievingMagpie($pet);
                break;
            case 15:
                if($useThanksgivingPrey)
                    $activityLog = $this->huntedPossessedTurkey($pet);
                else
                    $activityLog = $this->huntedGhosts($pet);
                break;
            case 16:
            case 17:
                if($useThanksgivingPrey)
                    $activityLog = $this->huntedPossessedTurkey($pet);
                else
                    $activityLog = $this->huntedSatyr($pet);
                break;
            case 18:
                $activityLog = $this->huntedPaperGolem($pet);
                break;
            case 19:
            case 20:
                if($useThanksgivingPrey)
                    $activityLog = $this->huntedTurkeyDragon($pet);
                else
                    $activityLog = $this->huntedLeshyDemon($pet);
                break;
        }

        if($activityLog)
            $activityLog->setChanges($changes->compare($pet));

        if(mt_rand(1, 100) === 1)
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
        $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::HUNT, true);

        $this->userQuestRepository->findOrCreate($pet->getOwner(), 'Rescued Second House Fairy', false)
            ->setValue(true)
        ;

        $activityLog = $this->responseService->createActivityLog($pet, 'While ' . $pet->getName() . ' was out hunting, they spotted a Raccoon and Thieving Magpie fighting over a fairy! ' . $pet->getName() . ' jumped in and chased the two creatures off before tending to the fairy\'s wounds.', '');
        $inventory = $this->inventoryService->petCollectsItem('House Fairy', $pet, 'Rescued from a Raccoon and Thieving Magpie.', $activityLog);

        if($inventory)
            $inventory->setLockedToOwner(true);

        $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::BRAWL ]);

        $pet->increaseSafety(2);
        $pet->increaseLove(2);
        $pet->increaseEsteem(2);

        return $activityLog;
    }

    private function failedToHunt(Pet $pet): PetActivityLog
    {
        $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::HUNT, false);
        return $this->responseService->createActivityLog($pet, $pet->getName() . ' went out hunting, but couldn\'t find anything to hunt.', 'icons/activity-logs/confused');
    }

    private function huntedDustBunny(Pet $pet): PetActivityLog
    {
        $skill = 10 + $pet->getDexterity() + $pet->getBrawl();

        $pet->increaseFood(-1);
        if(mt_rand(1, $skill) >= 6)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::HUNT, true);

            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' pounced on a Dust Bunny, reducing it to Fluff!', 'items/ambiguous/fluff');
            $this->inventoryService->petCollectsItem('Fluff', $pet, 'The remains of a Dust Bunny that ' . $pet->getName() . ' hunted.', $activityLog);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::BRAWL ]);
        }
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::HUNT, false);

            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' chased a Dust Bunny, but wasn\'t able to catch up with it.', '');
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::BRAWL ]);
        }

        return $activityLog;
    }

    private function huntedPlasticBag(Pet $pet): PetActivityLog
    {
        $skill = 10 + $pet->getDexterity() + $pet->getBrawl();

        $pet->increaseFood(-1);

        if(mt_rand(1, $skill) >= 6)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::HUNT, true);

            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' pounced on a Plastic Bag, reducing it to Plastic... somehow?', 'items/ambiguous/fluff');
            $this->inventoryService->petCollectsItem('Plastic', $pet, 'The remains of a vicious Plastic Bag that ' . $pet->getName() . ' hunted!', $activityLog);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::BRAWL ]);
        }
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::HUNT, false);

            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' chased a Plastic Bag, but wasn\'t able to catch up with it!', '');
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::BRAWL ]);
        }

        return $activityLog;
    }

    private function huntedGoat(Pet $pet): PetActivityLog
    {
        $skill = 10 + $pet->getStrength() + $pet->getBrawl();

        $pet->increaseFood(-1);

        if(mt_rand(1, $skill) >= 6)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::HUNT, true);
            $pet->increaseEsteem(1);

            if(mt_rand(1, 2) === 1)
            {
                $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' wrestled a Goat, and won, receiving Creamy Milk.', '');
                $this->inventoryService->petCollectsItem('Creamy Milk', $pet, $pet->getName() . '\'s prize for out-wrestling a Goat.', $activityLog);
            }
            else
            {
                $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' wrestled a Goat, and won, receiving Butter.', '');
                $this->inventoryService->petCollectsItem('Butter', $pet, $pet->getName() . '\'s prize for out-wrestling a Goat.', $activityLog);
            }
        }
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::HUNT, false);

            if(mt_rand(1, 4) === 1)
            {
                $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' wrestled a Goat. The Goat won.', '');
                $this->inventoryService->petCollectsItem('Fluff', $pet, $pet->getName() . ' wrestled a Goat, and lost, but managed to grab a fistful of Fluff.', $activityLog);
            }
            else
                $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' wrestled a Goat. The Goat won.', '');
        }

        $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::BRAWL ]);

        return $activityLog;
    }

    private function huntedTurkey(Pet $pet): PetActivityLog
    {
        $skill = 10 + $pet->getStrength() + $pet->getBrawl();

        $pet->increaseFood(-1);

        if(mt_rand(1, $skill) >= 6)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::HUNT, true);

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::BRAWL ]);

            $item = ArrayFunctions::pick_one([ 'Talon', 'Feathers', 'Giant Turkey Leg' ]);

            $aOrSome = $item === 'Feathers' ? 'some' : 'a';

            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' wrestled a Turkey! The Turkey fled, but not before ' . $pet->getName() . ' took ' . $aOrSome . ' ' . $item . '!', '');
            $this->inventoryService->petCollectsItem($item, $pet, $pet->getName() . ' wrestled this from a Turkey.', $activityLog);
        }
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::HUNT, false);

            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' picked a fight with a Turkey, but lost.', '');
            $pet->increaseEsteem(-2);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::BRAWL ]);
        }

        return $activityLog;
    }

    private function huntedLargeToad(Pet $pet): PetActivityLog
    {
        $skill = 10 + $pet->getStrength() + $pet->getBrawl();

        $pet->increaseFood(-1);

        if(mt_rand(1, $skill) >= 6)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::HUNT, true);

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::BRAWL ]);

            if(mt_rand(1, 4) === 1)
            {
                $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' beat up a Giant Toad, and took two of its legs.', 'items/animal/meat/legs-frog');
                $this->inventoryService->petCollectsItem('Toad Legs', $pet, $pet->getName() . ' took these from a Giant Toad. It still has two left, so it\'s probably fine >_>', $activityLog);
            }
            else
            {
                $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' wrestled a Toadstool off the back of a Giant Toad.', 'items/fungus/toadstool');
                $this->inventoryService->petCollectsItem('Toadstool', $pet, $pet->getName() . ' wrestled this from a Giant Toad.', $activityLog);
            }
        }
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::HUNT, false);

            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' picked a fight with a Giant Toad, but lost.', '');
            $pet->increaseEsteem(-2);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::BRAWL ]);
        }

        return $activityLog;
    }

    private function huntedScarecrow(Pet $pet): PetActivityLog
    {
        $skill = 10 + $pet->getStrength() + $pet->getBrawl();

        $pet->increaseFood(-1);

        if(mt_rand(1, $skill) >= 7)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::HUNT, true);

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::BRAWL ]);

            if(mt_rand(1, 2) === 1)
            {
                $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' beat up a Scarecrow, then took some of the Wheat it was defending.', '');
                $this->inventoryService->petCollectsItem('Wheat', $pet, $pet->getName() . ' took this from a Wheat Farm, after beating up its Scarecrow.', $activityLog);

                if(mt_rand(1, 10 + $pet->getPerception() + $pet->getNature()) >= 10)
                {
                    $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE ]);

                    if(mt_rand(1, 2) === 1)
                        $this->inventoryService->petCollectsItem('Wheat', $pet, $pet->getName() . ' took this from a Wheat Farm, after beating up its Scarecrow.', $activityLog);
                    else
                        $this->inventoryService->petCollectsItem('Wheat Flower', $pet, $pet->getName() . ' took this from a Wheat Farm, after beating up its Scarecrow.', $activityLog);
                }
            }
            else
            {
                $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' beat up a Scarecrow, then took some of the Rice it was defending.', '');
                $this->inventoryService->petCollectsItem('Rice', $pet, $pet->getName() . ' took this from a Rice Farm, after beating up its Scarecrow', $activityLog);

                if(mt_rand(1, 10 + $pet->getPerception() + $pet->getNature()) >= 10)
                {
                    $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE ]);
                    $this->inventoryService->petCollectsItem('Rice', $pet, $pet->getName() . ' took this from a Rice Farm, after beating up its Scarecrow.', $activityLog);
                }
            }
        }
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::HUNT, false);

            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to take out a Scarecrow, but lost.', '');
            $pet->increaseEsteem(-1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::BRAWL ]);
        }

        return $activityLog;
    }

    private function huntedOnionBoy(Pet $pet): PetActivityLog
    {
        $skill = 10 + $pet->getStamina();

        if(mt_rand(1, $skill) >= 7)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::HUNT, true);

            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' encountered an Onion Boy. The fumes were powerful, but ' . $pet->getName() . ' powered through it.', 'items/veggie/onion');
            $this->inventoryService->petCollectsItem('Onion', $pet, 'The remains of an Onion Boy that ' . $pet->getName() . ' encountered.', $activityLog);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::NATURE ]);
        }
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::HUNT, false);

            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' encountered an Onion Boy. The fumes were overwhelming, and ' . $pet->getName() . ' fled.', '');
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::NATURE ]);
            $pet->increaseSafety(-2);
        }

        return $activityLog;
    }

    private function huntedThievingMagpie(Pet $pet): PetActivityLog
    {
        $intSkill = 10 + $pet->getIntelligence();
        $dexSkill = 10 + $pet->getDexterity() + $pet->getBrawl();

        if(mt_rand(1, $intSkill) <= 2 && $pet->getOwner()->getMoneys() >= 2)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::HUNT, false);

            $moneysLost = mt_rand(1, 2);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::BRAWL ]);
            $pet->getOwner()->increaseMoneys(-$moneysLost);
            $this->userStatsRepository->incrementStat($pet->getOwner(), UserStatEnum::MONEYS_STOLEN_BY_THIEVING_MAGPIES, $moneysLost);
            $pet->increaseEsteem(-2);
            $pet->increaseSafety(-2);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' was outsmarted by a Thieving Magpie, and lost ' . $moneysLost . ' ' . ($moneysLost === 1 ? 'money' : 'moneys') . '.', '');
        }
        else if(mt_rand(1, $dexSkill) >= 9)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::HUNT, true);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::BRAWL ]);
            $pet->increaseEsteem(2);
            $pet->increaseSafety(2);

            if(mt_rand(1, 4) === 1)
            {
                $moneys = mt_rand(2, 5);
                $pet->getOwner()->increaseMoneys($moneys);
                $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' pounced on a Thieving Magpie, and liberated its ' . $moneys . ' moneys.', 'icons/activity-logs/moneys');
            }
            else
            {
                $item = ArrayFunctions::pick_one([ 'Egg', 'String', 'Rice', 'Plastic' ]);
                $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' pounced on a Thieving Magpie, and liberated ' . ($item === 'Egg' ? 'an' : 'some') . ' ' . $item . '.', '');
                $this->inventoryService->petCollectsItem($item, $pet, 'Liberated from a Thieving Magpie.', $activityLog);
            }
        }
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::HUNT, false);

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::BRAWL ]);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to take down a Thieving Magpie, but it got away.', '');
            $pet->increaseSafety(-1);
        }

        return $activityLog;
    }

    private function huntedGhosts(Pet $pet): PetActivityLog
    {
        $skill = 10 + $pet->getIntelligence() + $pet->getBrawl() + $pet->getUmbra();

        if(mt_rand(1, $skill) >= 15)
        {
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::BRAWL, PetSkillEnum::UMBRA ]);

            if(mt_rand(1, 100) === 1)
                $prize = 'Little Strongbox';
            else if(mt_rand(1, 5) === 1)
                $prize = 'Iron Bar';
            else if(mt_rand(1, 8) === 1)
                $prize = 'Fluff';
            else
                $prize = 'Quintessence';

            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::HUNT, true);
            $activityLog = $this->responseService->createActivityLog($pet, 'A Pirate Ghost tried to haunt ' . $pet->getName() . ', but ' . $pet->getName() . ' was able to dispel it (and got its ' . $prize . ')!', '');
            $this->inventoryService->petCollectsItem($prize, $pet, $pet->getName() . ' collected this from the remains of a Pirate Ghost.', $activityLog);
            $pet->increaseSafety(3);
            $pet->increaseEsteem(2);

        }
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(60, 75), PetActivityStatEnum::HUNT, false);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::BRAWL, PetSkillEnum::UMBRA ]);
            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' went out hunting, and got haunted by a Pirate Ghost! After harassing ' . $pet->getName() . ' for a while, the ghost became bored, and left.', '');
            $pet->increaseSafety(-3);
        }

        return $activityLog;
    }

    private function huntedPossessedTurkey(Pet $pet): PetActivityLog
    {
        $skill = 10 + $pet->getIntelligence() + $pet->getBrawl() + $pet->getUmbra();

        $pet->increaseFood(-1);

        if(mt_rand(1, $skill) >= 15)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::HUNT, true);

            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::BRAWL, PetSkillEnum::UMBRA ]);

            $item = ArrayFunctions::pick_one([ 'Quintessence', 'Black Feathers', 'Giant Turkey Leg' ]);

            $aOrSome = $item === 'Black Feathers' || $item === 'Quintessence' ? 'some' : 'a';

            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to exorcise a Possessed Turkey! It worked, and ' . $pet->getName() . ' got ' . $aOrSome . ' ' . $item . ' for their trouble!', '');
            $this->inventoryService->petCollectsItem($item, $pet, $pet->getName() . ' wrestled this from a Turkey.', $activityLog);
            $pet->increaseSafety(3);
            $pet->increaseEsteem(2);
        }
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::HUNT, false);

            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to exorcise a Possessed Turkey, but was chased away by a flurry of kicks and pecks!', '');
            $pet->increaseEsteem(-mt_rand(1, 3));
            $pet->increaseSafety(-mt_rand(2, 4));
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::BRAWL, PetSkillEnum::UMBRA ]);
        }

        return $activityLog;
    }

    private function huntedSatyr(Pet $pet): PetActivityLog
    {
        $brawlRoll = mt_rand(1, 10 + $pet->getStrength() + $pet->getBrawl());
        $musicSkill = mt_rand(1, 10 + $pet->getIntelligence() + $pet->getMusic());

        $pet->increaseFood(-1);

        if($pet->hasMerit(MeritEnum::EIDETIC_MEMORY) && $pet->hasMerit(MeritEnum::SOOTHING_VOICE))
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::HUNT, true);

            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' encountered a Satyr, but remembered that Satyrs love music, so sang a song. The Satyr was so enthralled by ' . $pet->getName() . '\'s Soothing Voice, that it offered gifts before leaving in peace.', 'icons/activity-logs/drunk-satyr');
            $pet->increaseEsteem(1);
            $this->inventoryService->petCollectsItem('Blackberry Wine', $pet, 'Gifts for ' . $pet->getName() . ', from a Satyr.', $activityLog);

            if(mt_rand(1, 5) === 1)
                $this->inventoryService->petCollectsItem('Music Note', $pet, 'Gifts for ' . $pet->getName() . ', from a Satyr.', $activityLog);
            else
                $this->inventoryService->petCollectsItem('Plain Yogurt', $pet, 'Gifts for ' . $pet->getName() . ', from a Satyr.', $activityLog);
        }
        else if($musicSkill > $brawlRoll)
        {
            if($pet->hasMerit(MeritEnum::SOOTHING_VOICE))
            {
                $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::HUNT, true);

                $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' encountered a Satyr, who upon hearing ' . $pet->getName() . '\'s voice, bade them sing. ' . $pet->getName() . ' did so; the Satyr was so enthralled by their soothing voice, that it offered gifts before leaving in peace.', 'icons/activity-logs/drunk-satyr');
                $pet->increaseEsteem(1);
                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::MUSIC ]);
                $this->inventoryService->petCollectsItem('Blackberry Wine', $pet, 'Gifts for ' . $pet->getName() . ', from a Satyr.', $activityLog);

                if(mt_rand(1, 5) === 1)
                    $this->inventoryService->petCollectsItem('Music Note', $pet, 'Gifts for ' . $pet->getName() . ', from a Satyr.', $activityLog);
                else
                    $this->inventoryService->petCollectsItem('Plain Yogurt', $pet, 'Gifts for ' . $pet->getName() . ', from a Satyr.', $activityLog);
            }
            else if($musicSkill >= 15)
            {
                $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::HUNT, true);

                $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' encountered a Satyr, who challenged ' . $pet->getName() . ' to a sing. It was surprised by ' . $pet->getName() . '\'s musical skill, and apologetically offered gifts before leaving in peace.', 'icons/activity-logs/drunk-satyr');
                $pet->increaseEsteem(2);
                $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::MUSIC ]);
                $this->inventoryService->petCollectsItem('Blackberry Wine', $pet, 'Gifts for ' . $pet->getName() . ', from a Satyr.', $activityLog);

                if(mt_rand(1, 5) === 1)
                    $this->inventoryService->petCollectsItem('Music Note', $pet, 'Gifts for ' . $pet->getName() . ', from a Satyr.', $activityLog);
                else
                    $this->inventoryService->petCollectsItem('Plain Yogurt', $pet, 'Gifts for ' . $pet->getName() . ', from a Satyr.', $activityLog);
            }
            else
            {
                $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::HUNT, false);

                $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' encountered a Satyr, who challenged ' . $pet->getName() . ' to a sing. The Satyr quickly cut ' . $pet->getName() . ' off, complaining loudly, and leaving in a huff.', '');
                $pet->increaseEsteem(-1);
                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::MUSIC ]);
            }
        }
        else
        {
            if($brawlRoll >= 15)
            {
                $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::HUNT, true);

                $pet->increaseSafety(3);
                $pet->increaseEsteem(2);
                $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::BRAWL ]);
                if(mt_rand(1, 2) === 1)
                {
                    $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' fought a Satyr, and won, receiving its Yogurt (gross), and Wine.', '');
                    $this->inventoryService->petCollectsItem('Plain Yogurt', $pet, 'Satyr loot, earned by ' . $pet->getName() . '.', $activityLog);
                    $this->inventoryService->petCollectsItem('Blackberry Wine', $pet, 'Satyr loot, earned by ' . $pet->getName() . '.', $activityLog);
                }
                else
                {
                    $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' fought a Satyr, and won, receiving its Yogurt (gross), and Horn. Er: Talon, I guess.', '');
                    $this->inventoryService->petCollectsItem('Plain Yogurt', $pet, 'Satyr loot, earned by ' . $pet->getName() . '.', $activityLog);
                    $this->inventoryService->petCollectsItem('Talon', $pet, 'Satyr loot, earned by ' . $pet->getName() . '.', $activityLog);
                }
            }
            else
            {
                $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::HUNT, false);

                $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to fight a drunken Satyr, but the Satyr misinterpreted ' . $pet->getName() . '\'s intentions, and it started to get really weird, so ' . $pet->getName() . ' ran away.', 'icons/activity-logs/drunk-satyr');
                $pet->increaseSafety(-mt_rand(1, 5));
                $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::BRAWL ]);
            }
        }

        return $activityLog;
    }

    private function huntedPaperGolem(Pet $pet): PetActivityLog
    {
        $skill = 10 + $pet->getDexterity() + $pet->getStamina() + max($pet->getCrafts(), $pet->getBrawl());

        $pet->increaseFood(-1);

        if(mt_rand(1, $skill) >= 17)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::HUNT, true);

            $pet->increaseSafety(1);
            $pet->increaseEsteem(2);
            $this->petExperienceService->gainExp($pet, 2, [ PetSkillEnum::CRAFTS, PetSkillEnum::BRAWL ]);

            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' unfolded a Paper Golem!', '');

            if(mt_rand(1, 10) === 1 && $pet->hasMerit(MeritEnum::LUCKY))
                $this->inventoryService->petCollectsItem('Stroganoff Recipe', $pet, $pet->getName() . ' got this by unfolding a Paper Golem. Lucky~!', $activityLog);
            else if(mt_rand(1, 20) === 1)
                $this->inventoryService->petCollectsItem('Stroganoff Recipe', $pet, $pet->getName() . ' got this by unfolding a Paper Golem.', $activityLog);
            else
                $this->inventoryService->petCollectsItem('Paper', $pet, $pet->getName() . ' got this by unfolding a Paper Golem.', $activityLog);
        }
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::HUNT, false);

            $pet->increaseFood(-1);
            $pet->increaseEsteem(-1);
            $pet->increaseSafety(-1);
            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::BRAWL ]);

            if(mt_rand(1, 30) === 1 && $pet->hasMerit(MeritEnum::LUCKY))
            {
                $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to unfold a Paper Golem, but got a nasty paper cut! During the fight, however, a small, glowing die rolled out from within the folds of the golem! Lucky~! ' . $pet->getName() . ' grabbed it before fleeing.', '')
                    ->addInterestingness(PetActivityLogInterestingnessEnum::ACTIVITY_USING_MERIT)
                ;

                $this->inventoryService->petCollectsItem('Glowing Six-sided Die', $pet, 'While ' . $pet->getName() . ' was fighting a Paper Golem, this fell out from it! Lucky~!', $activityLog);
            }
            else if(mt_rand(1, 20) === 1)
            {
                $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to unfold a Paper Golem, but got a nasty paper cut! During the fight, however, a small, glowing die rolled out from within the folds of the golem. ' . $pet->getName() . ' grabbed it before fleeing.', '');

                $this->inventoryService->petCollectsItem('Glowing Six-sided Die', $pet, 'While ' . $pet->getName() . ' was fighting a Paper Golem, this fell out from it.', $activityLog);
            }
            else
                $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' tried to unfold a Paper Golem, but got a nasty paper cut!', '');
        }

        return $activityLog;
    }

    private function huntedLeshyDemon(Pet $pet): PetActivityLog
    {
        $skill = 10 + $pet->getDexterity() + $pet->getStamina() + max($pet->getCrafts(), $pet->getBrawl());

        $pet->increaseFood(-1);
        $getExtraItem = mt_rand(1, 20 + $pet->getNature() + $pet->getPerception() + $pet->getGathering()) >= 15;

        if(mt_rand(1, $skill) >= 18)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::HUNT, true);

            $pet->increaseSafety(1);
            $pet->increaseEsteem(2);
            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::CRAFTS, PetSkillEnum::BRAWL, PetSkillEnum::NATURE ]);

            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' was attacked by a Leshy Demon, but was able to defeat it.', '');

            $this->inventoryService->petCollectsItem('Crooked Stick', $pet, $pet->getName() . ' plucked this from a Leshy Demon.', $activityLog);

            if($getExtraItem)
            {
                $extraItem = ArrayFunctions::pick_one([
                    'Crooked Stick',
                    'Tea Leaves',
                    'Quintessence',
                    'Witch-hazel'
                ]);

                $this->inventoryService->petCollectsItem($extraItem, $pet, $pet->getName() . ' pulled this out of a Leshy Demon\'s root cage.', $activityLog);
            }
        }
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::HUNT, false);

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::CRAFTS, PetSkillEnum::BRAWL ]);

            if($getExtraItem)
            {
                $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' was attacked by a Leshy Demon! ' . $pet->getName() . ' was able to break off one of its many Crooked Sticks, but was eventually forced to flee.', '');

                $this->inventoryService->petCollectsItem('Crooked Stick', $pet, $pet->getName() . ' broke this off of a Leshy Demon before running from it.', $activityLog);
            }
            else
            {
                $pet->increaseSafety(-1);
                $pet->increaseEsteem(-1);
                $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' was attacked by a Leshy Demon, and forced to flee!', '');
            }
        }

        return $activityLog;
    }

    private function huntedTurkeyDragon(Pet $pet): PetActivityLog
    {
        $skill = 10 + $pet->getDexterity() + $pet->getStamina() + $pet->getBrawl();

        $pet->increaseFood(-1);

        $getExtraItem = mt_rand(1, 20 + $pet->getNature() + $pet->getPerception() + $pet->getGathering()) >= 15;

        $possibleItems = [
            'Giant Turkey Leg',
            'Scales',
            'Feathers',
            'Talon',
            'Quintessence',
            'Charcoal'
        ];

        if(mt_rand(1, $skill) >= 18)
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::HUNT, true);

            $pet->increaseSafety(1);
            $pet->increaseEsteem(2);
            $this->petExperienceService->gainExp($pet, 3, [ PetSkillEnum::BRAWL, PetSkillEnum::NATURE ]);

            $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' was attacked by a Turkeydragon, but was able to defeat it.', '');

            $numItems = $getExtraItem ? 3 : 2;

            for($i = 0; $i < $numItems; $i++)
            {
                $itemName = ArrayFunctions::pick_one($possibleItems);

                $this->inventoryService->petCollectsItem($itemName, $pet, $pet->getName() . ' got this from defeating a Turkeydragon.', $activityLog);
            }
        }
        else
        {
            $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::HUNT, false);

            $this->petExperienceService->gainExp($pet, 1, [ PetSkillEnum::BRAWL ]);

            if($getExtraItem)
            {
                $itemName = ArrayFunctions::pick_one($possibleItems);

                $aSome = in_array($itemName, [ 'Scales', 'Feathers', 'Quintessence', 'Charcoal' ]) ? 'some' : 'a';

                $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' was attacked by a Turkeydragon! ' . $pet->getName() . ' was able to claim ' . $aSome . ' ' . $itemName . ' before fleeing.', '');

                $this->inventoryService->petCollectsItem($itemName, $pet, $pet->getName() . ' nabbed this from a Turkeydragon before running from it.', $activityLog);
            }
            else
            {
                $pet->increaseSafety(-1);
                $pet->increaseEsteem(-1);
                $activityLog = $this->responseService->createActivityLog($pet, $pet->getName() . ' was attacked by a Turkeydragon, and forced to flee!', '');
            }
        }

        return $activityLog;
    }
}