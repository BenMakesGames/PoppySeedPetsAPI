<?php
declare(strict_types=1);

/**
 * This file is part of the Poppy Seed Pets API.
 *
 * The Poppy Seed Pets API is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets API is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets API. If not, see <https://www.gnu.org/licenses/>.
 */


namespace App\Service;

use App\Entity\GreenhousePlant;
use App\Entity\LunchboxItem;
use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Entity\PetBaby;
use App\Entity\User;
use App\Enum\ActivityPersonalityEnum;
use App\Enum\GatheringHolidayEnum;
use App\Enum\MeritEnum;
use App\Enum\PetActivityLogInterestingness;
use App\Enum\PetActivityLogTagEnum;
use App\Enum\PetActivityStatEnum;
use App\Enum\PetBadgeEnum;
use App\Enum\StatusEffectEnum;
use App\Enum\UnlockableFeatureEnum;
use App\Enum\UserStat;
use App\Functions\ActivityHelpers;
use App\Functions\ArrayFunctions;
use App\Functions\CalendarFunctions;
use App\Functions\ColorFunctions;
use App\Functions\InventoryModifierFunctions;
use App\Functions\PetActivityLogFactory;
use App\Functions\PetActivityLogTagHelpers;
use App\Functions\PetBadgeHelpers;
use App\Functions\StatusEffectHelpers;
use App\Functions\UserQuestRepository;
use App\Model\ComputedPetSkills;
use App\Model\FoodWithSpice;
use App\Model\PetChanges;
use App\Model\PetChangesSummary;
use App\Service\PetActivity\CachingMeritAdventureService;
use App\Service\PetActivity\Crafting\ElectricalEngineeringService;
use App\Service\PetActivity\Crafting\MagicBindingService;
use App\Service\PetActivity\Crafting\NotReallyCraftsService;
use App\Service\PetActivity\Crafting\PhysicsService;
use App\Service\PetActivity\Crafting\PlasticPrinterService;
use App\Service\PetActivity\Crafting\ProgrammingService;
use App\Service\PetActivity\Crafting\SmithingService;
use App\Service\PetActivity\CraftingService;
use App\Service\PetActivity\DokiDokiService;
use App\Service\PetActivity\DreamingAndDaydreamingService;
use App\Service\PetActivity\EatingService;
use App\Service\PetActivity\FatedAdventureService;
use App\Service\PetActivity\FishingService;
use App\Service\PetActivity\GatheringHolidayAdventureService;
use App\Service\PetActivity\GatheringService;
use App\Service\PetActivity\GenericAdventureService;
use App\Service\PetActivity\GivingTreeGatheringService;
use App\Service\PetActivity\GuildService;
use App\Service\PetActivity\HuntingService;
use App\Service\PetActivity\JumpRopeService;
use App\Service\PetActivity\KappaService;
use App\Service\PetActivity\LetterService;
use App\Service\PetActivity\MortarOrPestleService;
use App\Service\PetActivity\PetCleaningSelfService;
use App\Service\PetActivity\PetSummonedAwayService;
use App\Service\PetActivity\PhilosophersStoneService;
use App\Service\PetActivity\PoopingService;
use App\Service\PetActivity\PregnancyService;
use App\Service\PetActivity\Protocol7Service;
use App\Service\PetActivity\SpecialLocations\BurntForestService;
use App\Service\PetActivity\SpecialLocations\Caerbannog;
use App\Service\PetActivity\SpecialLocations\ChocolateMansion;
use App\Service\PetActivity\SpecialLocations\DeepSeaService;
use App\Service\PetActivity\SpecialLocations\FructalPlaneService;
use App\Service\PetActivity\SpecialLocations\HeartDimensionService;
use App\Service\PetActivity\SpecialLocations\IcyMoonService;
use App\Service\PetActivity\SpecialLocations\LostInTownService;
use App\Service\PetActivity\SpecialLocations\MagicBeanstalkService;
use App\Service\PetActivity\TreasureMapService;
use App\Service\PetActivity\UmbraService;
use Doctrine\ORM\EntityManagerInterface;

class PetActivityService
{
    public function __construct(
        private readonly Clock $clock,
        private readonly EntityManagerInterface $em,
        private readonly ResponseService $responseService,
        private readonly FishingService $fishingService,
        private readonly HeartDimensionService $heartDimensionService,
        private readonly IcyMoonService $icyMoonService,
        private readonly HuntingService $huntingService,
        private readonly GatheringService $gatheringService,
        private readonly CraftingService $craftingService,
        private readonly UserStatsService $userStatsRepository,
        private readonly GenericAdventureService $genericAdventureService,
        private readonly PetSummonedAwayService $petSummonedAwayService,
        private readonly Protocol7Service $protocol7Service,
        private readonly ProgrammingService $programmingService,
        private readonly ElectricalEngineeringService $electricalEngineeringService,
        private readonly PhysicsService $physicsService,
        private readonly UmbraService $umbraService,
        private readonly PoopingService $poopingService,
        private readonly GivingTreeGatheringService $givingTreeGatheringService,
        private readonly PregnancyService $pregnancyService,
        private readonly IRandom $rng,
        private readonly ChocolateMansion $chocolateMansion,
        private readonly PetExperienceService $petExperienceService,
        private readonly DreamingAndDaydreamingService $dreamingAndDaydreamingService,
        private readonly MagicBeanstalkService $beanStalkService,
        private readonly EatingService $eatingService,
        private readonly GatheringHolidayAdventureService $gatheringHolidayAdventureService,
        private readonly GuildService $guildService,
        private readonly BurntForestService $burntForestService,
        private readonly InventoryService $inventoryService,
        private readonly DeepSeaService $deepSeaService,
        private readonly NotReallyCraftsService $notReallyCraftsService,
        private readonly LetterService $letterService,
        private readonly Caerbannog $caerbannog,
        private readonly TreasureMapService $treasureMapService,
        private readonly HouseSimService $houseSimService,
        private readonly MagicBindingService $magicBindingService,
        private readonly SmithingService $smithingService,
        private readonly CravingService $cravingService,
        private readonly PlasticPrinterService $plasticPrinterService,
        private readonly PhilosophersStoneService $philosophersStoneService,
        private readonly KappaService $kappaService,
        private readonly FatedAdventureService $fatedAdventureService,
        private readonly PetCleaningSelfService $petCleaningSelfService,
        private readonly CachingMeritAdventureService $cachingMeritAdventureService,
        private readonly JumpRopeService $jumpRopeService,
        private readonly DokiDokiService $dokiDokiService,
        private readonly LostInTownService $lostInTownService,
        private readonly FructalPlaneService $fructalPlaneService,
        private readonly MortarOrPestleService $mortarOrPestleService
    )
    {
    }

    public function runHour(Pet $pet): void
    {
        $hasEventPersonality = $pet->hasActivityPersonality(ActivityPersonalityEnum::EventsAndMaps);

        if(!$pet->isAtHome())
            throw new \InvalidArgumentException('Trying to run activities for a pet that is not at home! (Ben did something horrible; please let him know.)');

        if($pet->getHouseTime()->getActivityTime() < 60)
            throw new \InvalidArgumentException('Trying to run activities for a pet that does not have enough time! (Ben did something horrible; please let him know.)');

        $this->responseService->setReloadPets();

        if($pet->getTool() && $pet->getTool()->canBeNibbled() && $this->rng->rngNextInt(1, 10) === 1)
        {
            $changes = new PetChangesSummary();
            $changes->food = '+';

            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% nibbled on their ' . InventoryModifierFunctions::getNameWithModifiers($pet->getTool()) . '.')
                ->setIcon('icons/activity-logs/just-the-fork')
                ->setChanges($changes)
                ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Eating' ]))
            ;
        }
        else
            $pet->increaseFood(-1);

        if($pet->getJunk() > 0)
            $pet->increaseJunk(-1);

        if($pet->getPoison() > 0 && $pet->getAlcohol() === 0 && $pet->getCaffeine() === 0 && $pet->getPsychedelic() === 0)
            $pet->increasePoison(-1);

        if($pet->getAlcohol() > 0)
        {
            $pet->increaseAlcohol(-1);

            if($pet->hasMerit(MeritEnum::IRON_STOMACH))
            {
                if($this->rng->rngNextInt(1, 2) === 1)
                    $pet->increasePoison(1);
            }
            else
                $pet->increasePoison(1);
        }

        if($pet->getCaffeine() > 0)
        {
            $pet->increaseCaffeine(-1);

            if($pet->hasMerit(MeritEnum::IRON_STOMACH))
            {
                if($this->rng->rngNextInt(1, 4) === 1)
                    $pet->increasePoison(1);
            }
            else
            {
                if($this->rng->rngNextInt(1, 2) === 1)
                    $pet->increasePoison(1);
            }
        }

        if($pet->getPsychedelic() > 0)
        {
            $pet->increasePsychedelic(-1);

            if($pet->hasMerit(MeritEnum::IRON_STOMACH))
                $pet->increasePoison(1);
            else
                $pet->increasePoison(2);
        }

        $safetyRestingPoint = $pet->hasMerit(MeritEnum::NOTHING_TO_FEAR) ? 8 : 0;

        if($pet->getSafety() > $safetyRestingPoint && $this->rng->rngNextInt(1, 2) === 1)
            $pet->increaseSafety(-1);
        else if($pet->getSafety() < $safetyRestingPoint)
            $pet->increaseSafety(1);

        $loveRestingPoint = $pet->hasMerit(MeritEnum::EVERLASTING_LOVE) ? 8 : 0;

        if($pet->getLove() > $loveRestingPoint && $this->rng->rngNextInt(1, 2) === 1)
            $pet->increaseLove(-1);
        else if($pet->getLove() < $loveRestingPoint && $this->rng->rngNextInt(1, 2) === 1)
            $pet->increaseLove(1);

        $esteemRestingPoint = $pet->hasMerit(MeritEnum::NEVER_EMBARRASSED) ? 8 : 0;

        if($pet->getEsteem() > $esteemRestingPoint)
            $pet->increaseEsteem(-1);
        else if($pet->getEsteem() < $esteemRestingPoint && $this->rng->rngNextInt(1, 2) === 1)
            $pet->increaseEsteem(1);

        $this->cravingService->maybeRemoveCraving($pet);

        $pregnancy = $pet->getPregnancy();

        if($pregnancy)
        {
            if($pet->getFood() < 0) $pregnancy->increaseAffection(-1);
            if($pet->getSafety() < 0 && $this->rng->rngNextInt(1, 2) === 1) $pregnancy->increaseAffection(-1);
            if($pet->getLove() < 0 && $this->rng->rngNextInt(1, 3) === 1) $pregnancy->increaseAffection(-1);
            if($pet->getEsteem() < 0 && $this->rng->rngNextInt(1, 4) === 1) $pregnancy->increaseAffection(-1);

            if($pregnancy->getGrowth() >= PetBaby::PREGNANCY_DURATION)
            {
                $this->pregnancyService->giveBirth($pet);
                return;
            }
        }

        if($pet->getPoison() > 0)
        {
            if($this->rng->rngNextInt(6, 24) < $pet->getPoison())
            {
                $changes = new PetChanges($pet);

                $safetyVom = (int)ceil($pet->getPoison() / 4);

                $pet->increasePoison(-$this->rng->rngNextInt((int)ceil($pet->getPoison() / 4), (int)ceil($pet->getPoison() * 3 / 4)));
                if($pet->getAlcohol() > 0) $pet->increaseAlcohol(-$this->rng->rngNextInt(1, (int)ceil($pet->getAlcohol() / 2)));
                if($pet->getPsychedelic() > 0) $pet->increasePsychedelic(-$this->rng->rngNextInt(1, (int)ceil($pet->getPsychedelic() / 2)));
                if($pet->getCaffeine() > 0) $pet->increaseFood(-$this->rng->rngNextInt(1, (int)ceil($pet->getCaffeine() / 2)));
                if($pet->getJunk() > 0) $pet->increaseJunk(-$this->rng->rngNextInt(1, (int)ceil($pet->getJunk() / 2)));
                if($pet->getFood() > 0) $pet->increaseFood(-$this->rng->rngNextInt(1, (int)ceil($pet->getFood() / 2)));

                $pet->increaseSafety(-$this->rng->rngNextInt(1, $safetyVom));
                $pet->increaseEsteem(-$this->rng->rngNextInt(1, $safetyVom));

                $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(15, 30), PetActivityStatEnum::OTHER, null);

                $log = PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% threw up :(')
                    ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::Sick ]))
                    ->setChanges($changes->compare($pet));

                PetBadgeHelpers::awardBadge($this->em, $pet, PetBadgeEnum::POOPED_SHED_OR_BATHED, $log);

                return;
            }
        }

        if($this->poop($pet))
        {
            $this->poopingService->poopDarkMatter($pet);
        }

        if($pet->hasMerit(MeritEnum::SHEDS) && $this->rng->rngNextInt(1, 180) === 1)
        {
            $this->poopingService->shed($pet);
        }

        if($pet->hasMerit(MeritEnum::HYPERCHROMATIC))
        {
            if($this->rng->rngNextInt(1, 250) === 1)
            {
                $pet
                    ->setColorA(ColorFunctions::RGB2Hex($this->rng->rngNextInt(0, 255), $this->rng->rngNextInt(0, 255), $this->rng->rngNextInt(0, 255)))
                    ->setColorB(ColorFunctions::RGB2Hex($this->rng->rngNextInt(0, 255), $this->rng->rngNextInt(0, 255), $this->rng->rngNextInt(0, 255)))
                ;
            }
            else
            {
                $pet
                    ->setColorA($this->rng->rngNextTweakedColor($pet->getColorA(), 4))
                    ->setColorB($this->rng->rngNextTweakedColor($pet->getColorB(), 4))
                ;
            }
        }

        $petWithSkills = $pet->getComputedSkills();

        if($this->rng->rngNextInt(1, 4000) === 1)
        {
            $this->petSummonedAwayService->adventure($petWithSkills);
            return;
        }

        $hunger = $this->rng->rngNextInt(0, 4);

        if($pet->getFood() + $pet->getJunk() < $hunger && count($pet->getLunchboxItems()) > 0)
        {
            $petChanges = new PetChanges($pet);

            /** @var LunchboxItem[] $sortedLunchboxItems */
            $sortedLunchboxItems = $pet->getLunchboxItems()->filter(function(LunchboxItem $i) {
                return $i->getInventoryItem()->getItem()->getFood() !== null;
            })->toArray();

            // sorted from most-delicious to least-delicious
            usort($sortedLunchboxItems, function(LunchboxItem $a, LunchboxItem $b) use($pet) {
                $aFood = new FoodWithSpice($a->getInventoryItem()->getItem(), $a->getInventoryItem()->getSpice());
                $bFood = new FoodWithSpice($b->getInventoryItem()->getItem(), $b->getInventoryItem()->getSpice());

                $aValue = EatingService::getFavoriteFlavorStrength($pet, $aFood) + $aFood->love;
                $bValue = EatingService::getFavoriteFlavorStrength($pet, $bFood) + $bFood->love;

                if($aValue === $bValue)
                    return $bFood->food <=> $aFood->food;
                else
                    return $bValue <=> $aValue;
            });

            $namesOfItemsEaten = [];
            $namesOfItemsSkipped = [];
            $itemsLeftInLunchbox = count($sortedLunchboxItems);

            while($pet->getFood() < $hunger && count($sortedLunchboxItems) > 0)
            {
                $itemToEat = array_shift($sortedLunchboxItems);

                $food = new FoodWithSpice($itemToEat->getInventoryItem()->getItem(), $itemToEat->getInventoryItem()->getSpice());

                $ateIt = $this->eatingService->doEat($pet, $food, null);

                if($ateIt)
                {
                    $namesOfItemsEaten[] = $food->name;

                    $pet->removeLunchboxItem($itemToEat);

                    $this->em->remove($itemToEat);
                    $this->em->remove($itemToEat->getInventoryItem());

                    $itemsLeftInLunchbox--;
                }
                else
                    $namesOfItemsSkipped[] = $food->name;
            }

            if(count($namesOfItemsEaten) > 0)
            {
                $this->responseService->setReloadInventory();

                $message = '%pet:' . $pet->getId() . '.name% ate ' . ArrayFunctions::list_nice($namesOfItemsEaten) . ' out of their lunchbox.';

                if(count($namesOfItemsSkipped) > 0)
                    $message .= ' (' . ArrayFunctions::list_nice($namesOfItemsSkipped) . ' really isn\'t appealing right now, though.)';
            }
            else
            {
                // none were eaten, but ew know the lunchbox has items in it, therefore items were skipped!
                $message = '%pet:' . $pet->getId() . '.name% looked in their lunchbox for something to eat, but ' . ArrayFunctions::list_nice($namesOfItemsSkipped) . ' really isn\'t appealing right now.';
            }

            if($itemsLeftInLunchbox === 0)
                $message .= ' Their lunchbox is now empty!';

            $lunchboxLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, $message)
                ->setIcon('icons/activity-logs/lunchbox')
                ->setChanges($petChanges->compare($pet))
                ->addInterestingness($itemsLeftInLunchbox === 0 ? PetActivityLogInterestingness::LunchboxEmpty : 1)
            ;

            PetBadgeHelpers::awardBadge($this->em, $pet, PetBadgeEnum::EMPTIED_THEIR_LUNCHBOX, $lunchboxLog);
        }

        if($pet->hasStatusEffect(StatusEffectEnum::Wereform))
        {
            if($this->rng->rngNextInt(1, 10) === 1)
                $pet->removeStatusEffect($pet->getStatusEffect(StatusEffectEnum::Wereform));
        }
        else
        {
            if(
                $pet->hasStatusEffect(StatusEffectEnum::BittenByAWerecreature) &&
                $this->rng->rngNextInt(1, max(20, 50 + $pet->getFood() + $pet->getSafety() * 2 + $pet->getLove() + $pet->getEsteem())) === 1
            )
            {
                StatusEffectHelpers::applyStatusEffect($this->em, $pet, StatusEffectEnum::Wereform, 1);
            }
        }

        if($pet->hasMerit(MeritEnum::CACHING) && $pet->getFullnessPercent() < -0.25)
        {
            if($this->cachingMeritAdventureService->doAdventure($petWithSkills))
                return;
        }

        if($pet->hasStatusEffect(StatusEffectEnum::OilCovered))
        {
            if($this->petCleaningSelfService->cleanUpStatusEffect($pet, StatusEffectEnum::OilCovered, 'Oil'))
                return;
        }

        if($pet->hasStatusEffect(StatusEffectEnum::BubbleGumd))
        {
            if($this->petCleaningSelfService->cleanUpStatusEffect($pet, StatusEffectEnum::BubbleGumd, 'Bubblegum'))
                return;
        }

        if($pet->hasStatusEffect(StatusEffectEnum::GobbleGobble) && $this->rng->rngNextInt(1, 2) === 1)
        {
            $changes = new PetChanges($pet);
            $activityLog = $this->huntingService->huntedTurkeyDragon($petWithSkills);
            $activityLog->setChanges($changes->compare($pet));
            return;
        }

        if($pet->hasStatusEffect(StatusEffectEnum::LapineWhispers) && $this->rng->rngNextInt(1, 2) === 1)
        {
            $changes = new PetChanges($pet);
            $activityLog = $this->umbraService->speakToBunnySpirit($pet);
            $activityLog->setChanges($changes->compare($pet));
            $pet->removeStatusEffect($pet->getStatusEffect(StatusEffectEnum::LapineWhispers));
            return;
        }

        if($this->dreamingAndDaydreamingService->maybeDreamOrDaydream($petWithSkills))
            return;

        if($this->maybeReceiveFairyGodmotherVisit($pet))
            return;

        if($this->maybeReceiveAthenasGift($pet))
            return;

        $itemsInHouse = $this->houseSimService->getState()->getInventoryCount();

        $craftingPossibilities = $this->craftingService->getCraftingPossibilities($petWithSkills);
        $smithingPossibilities = $this->smithingService->getCraftingPossibilities($petWithSkills);
        $magicBindingPossibilities = $this->magicBindingService->getCraftingPossibilities($petWithSkills);
        $programmingPossibilities = $this->programmingService->getCraftingPossibilities($petWithSkills);
        $electricalEngineeringPossibilities = $this->electricalEngineeringService->getCraftingPossibilities($petWithSkills);
        $physicsPossibilities = $this->physicsService->getCraftingPossibilities($petWithSkills);
        $plasticPrinterPossibilities = $this->plasticPrinterService->getCraftingPossibilities($petWithSkills);
        $notCraftingPossibilities = $this->notReallyCraftsService->getCraftingPossibilities($petWithSkills);

        $houseTooFull = $this->rng->rngNextInt(1, 10) > User::MaxHouseInventory - $itemsInHouse;

        if($houseTooFull)
        {
            if($itemsInHouse >= User::MaxHouseInventory)
                $description = '%user:' . $pet->getOwner()->getId() . '.Name\'s% house is crazy-full.';
            else
                $description = '%user:' . $pet->getOwner()->getId() . '.Name\'s% house is getting pretty full.';

            if(
                count($craftingPossibilities) + count($magicBindingPossibilities) + count($programmingPossibilities) +
                count($electricalEngineeringPossibilities) + count($physicsPossibilities) +
                count($notCraftingPossibilities) + count($smithingPossibilities) + count($plasticPrinterPossibilities)
                === 0
            )
            {
                $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(45, 60), PetActivityStatEnum::OTHER, null);

                PetActivityLogFactory::createUnreadLog($this->em, $pet, $description . ' %pet:' . $pet->getId() . '.name% wanted to make something, but couldn\'t find any materials to work with.')
                    ->setIcon('icons/activity-logs/house-too-full')
                    ->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ PetActivityLogTagEnum::House_Too_Full ]))
                ;
            }
            else
            {
                $possibilities = [];

                if(count($craftingPossibilities) > 0) $possibilities[] = [ $this->craftingService, $craftingPossibilities ];
                if(count($magicBindingPossibilities) > 0) $possibilities[] = [ $this->craftingService, $magicBindingPossibilities ];
                if(count($smithingPossibilities) > 0) $possibilities[] = [ $this->craftingService, $smithingPossibilities ];
                if(count($programmingPossibilities) > 0) $possibilities[] = [ $this->programmingService, $programmingPossibilities ];
                if(count($electricalEngineeringPossibilities) > 0) $possibilities[] = [ $this->electricalEngineeringService, $electricalEngineeringPossibilities ];
                if(count($physicsPossibilities) > 0) $possibilities[] = [ $this->physicsService, $physicsPossibilities ];
                if(count($plasticPrinterPossibilities) > 0) $possibilities[] = [ $this->craftingService, $plasticPrinterPossibilities ];
                if(count($notCraftingPossibilities) > 0) $possibilities[] = [ $this->notReallyCraftsService, $notCraftingPossibilities ];

                $do = $this->rng->rngNextFromArray($possibilities);

                /** @var PetActivityLog $activityLog */
                $activityLog = $do[0]->adventure($petWithSkills, $do[1]);
                $activityLog->setEntry($description . ' ' . $activityLog->getEntry());

                PetBadgeHelpers::awardBadge($this->em, $pet, PetBadgeEnum::CRAFTED_WITH_A_FULL_HOUSE, $activityLog);

                if($activityLog->getChanges()->containsLevelUp())
                    $activityLog->addTags(PetActivityLogTagHelpers::findByNames($this->em, [ 'Level-up' ]));
            }

            return;
        }

        if($this->fatedAdventureService->maybeResolveFate($petWithSkills))
            return;

        if($this->rng->rngNextInt(1, $hasEventPersonality ? 48 : 50) === 1)
        {
            if($this->letterService->adventure($petWithSkills))
                return;

            $this->genericAdventureService->adventure($petWithSkills);
            return;
        }

        if($this->discoverNewFeature($pet))
            return;

        if($pet->getTool())
        {
            if($this->considerToolsWhichLeadToAdventure($petWithSkills))
                return;
        }

        if($this->rng->rngNextInt(1, $hasEventPersonality ? 48 : 50) === 1)
        {
            $activityLog = $this->givingTreeGatheringService->gatherFromGivingTree($pet);
            if($activityLog)
                return;
        }

        if($this->rng->rngNextInt(1, 100) <= ($hasEventPersonality ? 24 : 16) && CalendarFunctions::isSaintPatricksDay($this->clock->now))
        {
            $this->gatheringHolidayAdventureService->adventure($petWithSkills, GatheringHolidayEnum::SaintPatricks);
            return;
        }

        if($this->rng->rngNextInt(1, 100) <= ($hasEventPersonality ? 30 : 25) && CalendarFunctions::isEaster($this->clock->now))
        {
            $this->gatheringHolidayAdventureService->adventure($petWithSkills, GatheringHolidayEnum::Easter);
            return;
        }

        if($this->rng->rngNextInt(1, 100) <= ($hasEventPersonality ? 9 : 6) && CalendarFunctions::isChineseNewYear($this->clock->now))
        {
            $this->gatheringHolidayAdventureService->adventure($petWithSkills, GatheringHolidayEnum::LunarNewYear);
            return;
        }

        if($pet->getGuildMembership() && $this->rng->rngNextInt(1, 35) === 1)
        {
            if($this->guildService->doGuildActivity($petWithSkills))
                return;
        }

        $petDesires = [
            'fish' => $this->generateFishingDesire($petWithSkills),
            'hunt' => $this->generateMonsterHuntingDesire($petWithSkills),
            'gather' => $this->generateGatheringDesire($petWithSkills),
            'umbra' => $this->generateExploreUmbraDesire($petWithSkills),
        ];

        if(
            $pet->hasMerit(MeritEnum::PROTOCOL_7) ||
            ($pet->getTool() && $pet->getTool()->getItem()->getTool() && $pet->getTool()->getItem()->getTool()->getAdventureDescription() === 'Project-E')
        )
        {
            $petDesires['hack'] = $this->generateHackingDesire($petWithSkills);
        }

        if($pet->getTool() && $pet->getTool()->getEnchantment() && $pet->getTool()->getEnchantment()->getName() === 'Burnt')
            $petDesires['burntForest'] = $this->generateBurntForestDesire($petWithSkills);

        if($this->houseSimService->hasInventory('Submarine'))
            $petDesires['submarine'] = $this->generateSubmarineDesire($petWithSkills);

        if($this->houseSimService->hasInventory('Icy Moon'))
        {
            $icyMoonDesire = $this->generateIcyMoonDesire($petWithSkills);

            if($icyMoonDesire > 0)
                $petDesires['icyMoon'] = $icyMoonDesire;
        }

        if($pet->getOwner()->getGreenhousePlants()->exists(function(int $key, GreenhousePlant $p) {
            return
                $p->getPlant()->getName() === 'Magic Beanstalk' &&
                $p->getIsAdult() &&
                $p->getProgress() >= 1 &&
                (new \DateTimeImmutable()) >= $p->getCanNextInteract()
            ;
        }))
        {
            $petDesires['beanStalk'] = $this->generateClimbingBeanStalkDesire($petWithSkills);
        }

        if(count($craftingPossibilities) > 0) $petDesires['craft'] = $this->generateCraftingDesire($petWithSkills);
        if(count($magicBindingPossibilities) > 0) $petDesires['magicBinding'] = $this->generateMagicBindingDesire($petWithSkills);
        if(count($smithingPossibilities) > 0) $petDesires['smith'] = $this->generateSmithingDesire($petWithSkills);
        if(count($programmingPossibilities) > 0) $petDesires['program'] = $this->generateProgrammingDesire($petWithSkills);
        if(count($electricalEngineeringPossibilities) > 0) $petDesires['electricalEngineering'] = $this->generateElectricalEngineeringDesire($petWithSkills);
        if(count($physicsPossibilities) > 0) $petDesires['physics'] = $this->generatePhysicsDesire($petWithSkills);
        if(count($plasticPrinterPossibilities) > 0) $petDesires['plasticPrinting'] = $this->generatePlasticPrintingDesire($petWithSkills);
        if(count($notCraftingPossibilities) > 0) $petDesires['notCrafting'] = $this->generateGatheringDesire($petWithSkills);

        $desire = $this->pickDesire($petDesires);

        switch($desire)
        {
            case 'fish': $this->fishingService->adventure($petWithSkills); break;
            case 'hunt': $this->huntingService->adventure($petWithSkills); break;
            case 'gather': $this->gatheringService->adventure($petWithSkills); break;
            case 'craft': $this->craftingService->adventure($petWithSkills, $craftingPossibilities); break;
            case 'magicBinding': $this->craftingService->adventure($petWithSkills, $magicBindingPossibilities); break;
            case 'smith': $this->craftingService->adventure($petWithSkills, $smithingPossibilities); break;
            case 'program': $this->programmingService->adventure($petWithSkills, $programmingPossibilities); break;
            case 'electricalEngineering': $this->electricalEngineeringService->adventure($petWithSkills, $electricalEngineeringPossibilities); break;
            case 'physics': $this->physicsService->adventure($petWithSkills, $physicsPossibilities); break;
            case 'plasticPrinting': $this->craftingService->adventure($petWithSkills, $plasticPrinterPossibilities); break;
            case 'notCrafting': $this->notReallyCraftsService->adventure($petWithSkills, $notCraftingPossibilities); break;
            case 'hack': $this->protocol7Service->adventure($petWithSkills); break;
            case 'umbra': $this->umbraService->adventure($petWithSkills); break;
            case 'beanStalk': $this->beanStalkService->adventure($petWithSkills); break;
            case 'burntForest': $this->burntForestService->adventure($petWithSkills); break;
            case 'submarine': $this->deepSeaService->adventure($petWithSkills); break;
            case 'icyMoon': $this->icyMoonService->adventure($petWithSkills); break;
            default: $this->doNothing($pet); break;
        }
    }

    private function considerToolsWhichLeadToAdventure(ComputedPetSkills $petWithSkills): bool
    {
        $pet = $petWithSkills->getPet();

        switch($pet->getTool()->getItem()->getName())
        {
            case 'Cetgueli\'s Treasure Map':
                $this->treasureMapService->doCetguelisTreasureMap($petWithSkills);
                return true;

            case 'Silver Keyblade':
            case 'Gold Keyblade':
                if($pet->getFood() > 0 && $this->rng->rngNextInt(1, 10) === 1)
                {
                    $this->treasureMapService->doKeybladeTower($petWithSkills);
                    return true;
                }

                break;

            case 'Rainbow Dolphin Plushy':
            case 'Sneqo Plushy':
            case 'Bulbun Plushy':
            case 'Peacock Plushy':
            case 'Phoenix Plushy':
            case '"Roy" Plushy':
                if($this->rng->rngNextInt(1, 6) === 1 || $this->userStatsRepository->getStatValue($pet->getOwner(), UserStat::TradedWithTheFluffmonger) === 0)
                {
                    $this->treasureMapService->doFluffmongerTrade($pet);
                    return true;
                }

                break;

            case '"Gold" Idol':
                $this->treasureMapService->doGoldIdol($pet);
                return true;

            case 'Heartstone':
                if(!$this->heartDimensionService->canAdventure($pet))
                {
                    $this->heartDimensionService->notEnoughAffectionAdventure($pet);
                    return true;
                }
                else if($this->rng->rngNextInt(1, 100) <= $this->heartDimensionService->chanceOfHeartDimensionAdventure($pet))
                {
                    $this->heartDimensionService->adventure($petWithSkills);
                    return true;
                }

                break;

            case '5-leaf Clover':
                $this->treasureMapService->doLeprechaun($petWithSkills);
                return true;

            case 'Aubergine Commander':
                if($this->rng->rngNextInt(1, 80) === 1)
                {
                    $this->treasureMapService->doEggplantCurse($pet);
                    return true;
                }
                break;

            case 'Carrot Key':
                $this->caerbannog->adventure($petWithSkills);
                return true;

            case 'Ceremony of Fire':
                if($this->rng->rngNextInt(1, 20) == 1)
                {
                    $this->philosophersStoneService->seekVesicaHydrargyrum($petWithSkills);
                    return true;
                }
                break;

            case 'Chocolate Key':
                $this->chocolateMansion->adventure($petWithSkills);
                return true;

            case 'Cucumber':
                $this->kappaService->doHuntKappa($petWithSkills);
                return true;

            case 'Diffie-H Key':
                $this->treasureMapService->doUseDiffieHKey($pet);
                return true;

            case 'Fimbulvetr':
                if($this->rng->rngNextInt(1, 20) == 1)
                {
                    $this->philosophersStoneService->seekMetatronsFire($petWithSkills);
                    return true;
                }
                break;

            case 'Fruit Fly on a String':
                $this->treasureMapService->doFruitHunting($pet);
                return true;

            case 'Jump Rope':
                if($this->rng->rngNextInt(1, 4) == 1)
                {
                    $this->jumpRopeService->adventure($petWithSkills);
                    return true;
                }
                break;

            case 'Large Radish':
                if($this->rng->rngNextInt(1, 10) == 1)
                {
                    $this->dokiDokiService->adventure($petWithSkills);
                    return true;
                }
                break;

            case 'Mortar or Pestle':
                if($this->mortarOrPestleService->findTheOtherBit($petWithSkills->getPet()))
                    return true;
                break;

            case 'Saucepan':
                if($this->rng->rngNextInt(1, 10) === 1)
                {
                    $this->treasureMapService->doCookSomething($pet);
                    return true;
                }

                break;

            case 'Shirikodama':
                $this->kappaService->doReturnShirikodama($petWithSkills);
                return true;

            case 'Skewered Marshmallow':
                if($this->rng->rngNextInt(1, 10) == 1)
                {
                    $this->treasureMapService->doToastSkeweredMarshmallow($pet);
                    return true;
                }
                break;

            case 'Snickerblade':
                if($this->rng->rngNextInt(1, 20) == 1)
                {
                    $this->philosophersStoneService->seekEarthsEgg($petWithSkills);
                    return true;
                }
                break;

            case 'Winged Key':
                $this->treasureMapService->doAbundantiasVault($pet);
                return true;

            case 'Woher Cuán Nani-nani':
                $this->lostInTownService->adventure($petWithSkills);
                return true;
        }

        if($pet->getTool()->getEnchantment())
        {
            switch($pet->getTool()->getEnchantment()->getName())
            {
                case 'Searing':
                    if($this->rng->rngNextInt(1, 20) == 1)
                    {
                        if($this->philosophersStoneService->seekMerkabaOfAir($petWithSkills))
                            return true;
                    }
                    break;

                case 'Gooder':
                    $this->fructalPlaneService->adventure($petWithSkills);
                    return true;
            }
        }

        return false;
    }

    private function doNothing(Pet $pet): void
    {
        $this->petExperienceService->spendTime($pet, $this->rng->rngNextInt(30, 60), PetActivityStatEnum::OTHER, null);
        PetActivityLogFactory::createUnreadLog($this->em, $pet, '%pet:' . $pet->getId() . '.name% hung around the house.');
    }

    private function pickDesire(array $petDesires): string
    {
        $totalDesire = array_sum($petDesires);

        $pick = $this->rng->rngNextInt(0, $totalDesire - 1);

        foreach($petDesires as $action=>$desire)
        {
            if($pick < $desire)
                return $action;

            $pick -= $desire;
        }

        return array_key_last($petDesires);
    }

    public function generateFishingDesire(ComputedPetSkills $petWithSkills): int
    {
        $pet = $petWithSkills->getPet();
        $desire = $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getNature()->getTotal() + $petWithSkills->getFishingBonus()->getTotal();

        // when a pet is equipped, the equipment bonus counts twice for affecting a pet's desires
        if($pet->getTool() && $pet->getTool()->getItem()->getTool())
            $desire += $pet->getTool()->getItem()->getTool()->getNature() + $pet->getTool()->getItem()->getTool()->getFishing();

        if($petWithSkills->getPet()->hasActivityPersonality(ActivityPersonalityEnum::Fishing))
            $desire += 4;
        else
            $desire += $this->rng->rngNextInt(1, 4);

        return max(1, (int)round($desire * (1 + $this->rng->rngNextInt(-10, 10) / 100)));
    }

    public function generateIcyMoonDesire(ComputedPetSkills $petWithSkills): int
    {
        $pet = $petWithSkills->getPet();

        if($pet->hasStatusEffect(StatusEffectEnum::Wereform))
            return 0;

        $desire = $petWithSkills->getStamina()->getTotal() + $petWithSkills->getScience()->getTotal() + $petWithSkills->getGatheringBonus()->getTotal() - $pet->getAlcohol();

        // when a pet is equipped, the equipment bonus counts twice for affecting a pet's desires
        if($pet->getTool() && $pet->getTool()->getItem()->getTool())
            $desire += $pet->getTool()->getItem()->getTool()->getScience() + $pet->getTool()->getItem()->getTool()->getGathering();

        if($petWithSkills->getPet()->hasActivityPersonality(ActivityPersonalityEnum::IcyMoon))
            $desire += 4;
        else
            $desire += $this->rng->rngNextInt(1, 4);

        return max(0, (int)round($desire * (1 + $this->rng->rngNextInt(-10, 10) / 100) * 3 / 4));
    }

    public function generateSubmarineDesire(ComputedPetSkills $petWithSkills): int
    {
        $pet = $petWithSkills->getPet();

        if($pet->hasStatusEffect(StatusEffectEnum::Wereform))
            return 0;

        $desire = $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getScience()->getTotal() + $petWithSkills->getFishingBonus()->getTotal();

        // when a pet is equipped, the equipment bonus counts twice for affecting a pet's desires
        if($pet->getTool() && $pet->getTool()->getItem()->getTool())
            $desire += $pet->getTool()->getItem()->getTool()->getScience() + $pet->getTool()->getItem()->getTool()->getFishing();

        if($petWithSkills->getPet()->hasActivityPersonality(ActivityPersonalityEnum::Submarine))
            $desire += 4;
        else
            $desire += $this->rng->rngNextInt(1, 4);

        return max(1, (int)round($desire * (1 + $this->rng->rngNextInt(-10, 10) / 100)));
    }

    public function generateMonsterHuntingDesire(ComputedPetSkills $petWithSkills): int
    {
        $pet = $petWithSkills->getPet();
        $desire = $petWithSkills->getStrength()->getTotal() + $petWithSkills->getBrawl()->getTotal();

        // when a pet is equipped, the equipment bonus counts twice for affecting a pet's desires
        if($pet->getTool() && $pet->getTool()->getItem()->getTool())
            $desire += $pet->getTool()->getItem()->getTool()->getBrawl();

        if($petWithSkills->getPet()->hasActivityPersonality(ActivityPersonalityEnum::Hunting))
            $desire += 4;
        else
            $desire += $this->rng->rngNextInt(1, 4);

        return max(1, (int)round($desire * (1 + $this->rng->rngNextInt(-10, 10) / 100)));
    }

    public function generateCraftingDesire(ComputedPetSkills $petWithSkills): int
    {
        $pet = $petWithSkills->getPet();

        if($pet->hasStatusEffect(StatusEffectEnum::Wereform))
            return 0;

        $desire = $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getCrafts()->getTotal();

        // when a pet is equipped, the equipment bonus counts twice for affecting a pet's desires
        if($pet->getTool() && $pet->getTool()->getItem()->getTool())
            $desire += $pet->getTool()->getItem()->getTool()->getCrafts();

        if($petWithSkills->getPet()->hasActivityPersonality(ActivityPersonalityEnum::CraftingMundane))
            $desire += 4;
        else
            $desire += $this->rng->rngNextInt(1, 4);

        return max(1, (int)round($desire * (1 + $this->rng->rngNextInt(-10, 10) / 100)));
    }

    public function generateMagicBindingDesire(ComputedPetSkills $petWithSkills): int
    {
        $pet = $petWithSkills->getPet();

        if($pet->hasStatusEffect(StatusEffectEnum::Wereform))
            return 0;

        $desire = $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getArcana()->getTotal() + $petWithSkills->getMagicBindingBonus()->getTotal();

        // when a pet is equipped, the equipment bonus counts twice for affecting a pet's desires
        if($pet->getTool() && $pet->getTool()->getItem()->getTool())
            $desire += $pet->getTool()->getItem()->getTool()->getArcana() + $pet->getTool()->getItem()->getTool()->getMagicBinding();

        if($petWithSkills->getPet()->hasActivityPersonality(ActivityPersonalityEnum::CraftingMagic))
            $desire += 4;
        else
            $desire += $this->rng->rngNextInt(1, 4);

        return max(1, (int)round($desire * (1 + $this->rng->rngNextInt(-10, 10) / 100)));
    }

    public function generateSmithingDesire(ComputedPetSkills $petWithSkills): int
    {
        $pet = $petWithSkills->getPet();

        if($pet->hasStatusEffect(StatusEffectEnum::Wereform))
            return 0;

        $desire = $petWithSkills->getStamina()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal();

        // when a pet is equipped, the equipment bonus counts twice for affecting a pet's desires
        if($pet->getTool() && $pet->getTool()->getItem()->getTool())
            $desire += $pet->getTool()->getItem()->getTool()->getCrafts() + $pet->getTool()->getItem()->getTool()->getSmithing();

        if($petWithSkills->getPet()->hasActivityPersonality(ActivityPersonalityEnum::CraftingSmithing))
            $desire += 4;
        else
            $desire += $this->rng->rngNextInt(1, 4);

        return max(1, (int)round($desire * (1 + $this->rng->rngNextInt(-10, 10) / 100)));
    }

    public function generateExploreUmbraDesire(ComputedPetSkills $petWithSkills): int
    {
        $pet = $petWithSkills->getPet();
        $desire = $petWithSkills->getStamina()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getArcana()->getTotal() + $petWithSkills->getUmbraBonus()->getTotal();

        if($pet->getTool() && $pet->getTool()->getItem()->getTool())
            $desire += $pet->getTool()->getItem()->getTool()->getArcana() + $pet->getTool()->getItem()->getTool()->getUmbra();

        if($petWithSkills->getPet()->hasActivityPersonality(ActivityPersonalityEnum::Umbra))
            $desire += 4;
        else
            $desire += $this->rng->rngNextInt(1, 4);

        if(
            $pet->hasMerit(MeritEnum::NATURAL_CHANNEL) ||
            ($pet->getTool() && $pet->getTool()->getItem()->getTool() && $pet->getTool()->getItem()->getTool()->getAdventureDescription() === 'The Umbra')
        )
        {
            if($pet->getPsychedelic() > $pet->getMaxPsychedelic() / 2)
                return (int)ceil($desire * $pet->getPsychedelic() * 2 / $pet->getMaxPsychedelic());
            else
                return $desire;
        }
        else if($pet->getPsychedelic() > 0)
        {
            return (int)ceil($desire * $pet->getPsychedelic() * 2 / $pet->getMaxPsychedelic());
        }
        else
            return 0;
    }

    public function generateBurntForestDesire(ComputedPetSkills $petWithSkills): int
    {
        $umbraDesire = $this->generateExploreUmbraDesire($petWithSkills);
        $brawlDesire = $this->generateMonsterHuntingDesire($petWithSkills);

        return (int)(max($umbraDesire, $brawlDesire) * 3 / 4 + min($umbraDesire, $brawlDesire) / 4);
    }

    public function generateGatheringDesire(ComputedPetSkills $petWithSkills): int
    {
        $pet = $petWithSkills->getPet();
        $desire = $petWithSkills->getPerception()->getTotal() + $petWithSkills->getNature()->getTotal() + $petWithSkills->getGatheringBonus()->getTotal();

        // when a pet is equipped, the equipment bonus counts twice for affecting a pet's desires
        if($pet->getTool() && $pet->getTool()->getItem()->getTool())
            $desire += $pet->getTool()->getItem()->getTool()->getNature() + $pet->getTool()->getItem()->getTool()->getGathering();

        if($petWithSkills->getPet()->hasActivityPersonality(ActivityPersonalityEnum::Gathering))
            $desire += 4;
        else
            $desire += $this->rng->rngNextInt(1, 4);

        return max(1, (int)round($desire * (1 + $this->rng->rngNextInt(-10, 10) / 100)));
    }

    public function generateClimbingBeanstalkDesire(ComputedPetSkills $petWithSkills): int
    {
        $pet = $petWithSkills->getPet();
        $desire = $petWithSkills->getStamina()->getTotal() + $petWithSkills->getNature()->getTotal() + $petWithSkills->getClimbingBonus()->getTotal();

        // when a pet is equipped, the equipment bonus counts twice for affecting a pet's desires
        if($pet->getTool() && $pet->getTool()->getItem()->getTool())
            $desire += $pet->getTool()->getItem()->getTool()->getNature() + $pet->getTool()->getItem()->getTool()->getClimbing();

        if($petWithSkills->getPet()->hasActivityPersonality(ActivityPersonalityEnum::Beanstalk))
            $desire += 4;
        else
            $desire += $this->rng->rngNextInt(1, 4);

        return max(1, (int)round($desire * (1 + $this->rng->rngNextInt(-10, 10) / 100)));
    }

    public function generateHackingDesire(ComputedPetSkills $petWithSkills): int
    {
        $pet = $petWithSkills->getPet();

        if($pet->hasStatusEffect(StatusEffectEnum::Wereform))
            return 0;

        $desire = $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getScience()->getTotal() + $petWithSkills->getHackingBonus()->getTotal();

        // when a pet is equipped, the equipment bonus counts twice for affecting a pet's desires
        if($pet->getTool() && $pet->getTool()->getItem()->getTool())
            $desire += $pet->getTool()->getItem()->getTool()->getScience() + $pet->getTool()->getItem()->getTool()->getHacking();

        if($petWithSkills->getPet()->hasActivityPersonality(ActivityPersonalityEnum::Protocol7))
            $desire += 4;
        else
            $desire += $this->rng->rngNextInt(1, 4);

        return max(1, (int)round($desire * (1 + $this->rng->rngNextInt(-10, 10) / 100)));
    }

    public function generateProgrammingDesire(ComputedPetSkills $petWithSkills): int
    {
        $pet = $petWithSkills->getPet();

        if($pet->hasStatusEffect(StatusEffectEnum::Wereform))
            return 0;

        $desire = $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getScience()->getTotal() + $petWithSkills->getHackingBonus()->getTotal();

        // when a pet is equipped, the equipment bonus counts twice for affecting a pet's desires
        if($pet->getTool() && $pet->getTool()->getItem()->getTool())
            $desire += $pet->getTool()->getItem()->getTool()->getScience() + $pet->getTool()->getItem()->getTool()->getHacking();

        if($petWithSkills->getPet()->hasActivityPersonality(ActivityPersonalityEnum::CraftingScience))
            $desire += 4;
        else
            $desire += $this->rng->rngNextInt(1, 4);

        return max(1, (int)round($desire * (1 + $this->rng->rngNextInt(-10, 10) / 100)));
    }

    public function generateElectricalEngineeringDesire(ComputedPetSkills $petWithSkills): int
    {
        $pet = $petWithSkills->getPet();

        if($pet->hasStatusEffect(StatusEffectEnum::Wereform))
            return 0;

        $desire = $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getScience()->getTotal() + $petWithSkills->getElectronicsBonus()->getTotal();

        // when a pet is equipped, the equipment bonus counts twice for affecting a pet's desires
        if($pet->getTool() && $pet->getTool()->getItem()->getTool())
            $desire += $pet->getTool()->getItem()->getTool()->getScience() + $pet->getTool()->getItem()->getTool()->getElectronics();

        if($petWithSkills->getPet()->hasActivityPersonality(ActivityPersonalityEnum::CraftingScience))
            $desire += 4;
        else
            $desire += $this->rng->rngNextInt(1, 4);

        return max(1, (int)round($desire * (1 + $this->rng->rngNextInt(-10, 10) / 100)));
    }

    public function generatePhysicsDesire(ComputedPetSkills $petWithSkills): int
    {
        $pet = $petWithSkills->getPet();

        if($pet->hasStatusEffect(StatusEffectEnum::Wereform))
            return 0;

        $desire = $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getScience()->getTotal() + $petWithSkills->getPhysicsBonus()->getTotal();

        // when a pet is equipped, the equipment bonus counts twice for affecting a pet's desires
        if($pet->getTool() && $pet->getTool()->getItem()->getTool())
            $desire += $pet->getTool()->getItem()->getTool()->getScience() + $pet->getTool()->getItem()->getTool()->getPhysics();

        if($petWithSkills->getPet()->hasActivityPersonality(ActivityPersonalityEnum::CraftingScience))
            $desire += 4;
        else
            $desire += $this->rng->rngNextInt(1, 4);

        return max(1, (int)round($desire * (1 + $this->rng->rngNextInt(-10, 10) / 100)));
    }

    public function generatePlasticPrintingDesire(ComputedPetSkills $petWithSkills): int
    {
        $pet = $petWithSkills->getPet();

        if($pet->hasStatusEffect(StatusEffectEnum::Wereform))
            return 0;

        $desire = $petWithSkills->getIntelligence()->getTotal() + (int)ceil(($petWithSkills->getScience()->getTotal() + $petWithSkills->getCrafts()->getTotal()) / 2);

        // when a pet is equipped, the equipment bonus counts twice for affecting a pet's desires
        if($pet->getTool() && $pet->getTool()->getItem()->getTool())
            $desire += $pet->getTool()->getItem()->getTool()->getScience();

        if($petWithSkills->getPet()->hasActivityPersonality(ActivityPersonalityEnum::CraftingPlastic))
            $desire += 4;
        else
            $desire += $this->rng->rngNextInt(1, 4);

        return max(1, (int)round($desire * (1 + $this->rng->rngNextInt(-10, 10) / 100)));
    }

    private function poop(Pet $pet): bool
    {
        if($pet->hasMerit(MeritEnum::BLACK_HOLE_TUM) && $this->rng->rngNextInt(1, 180) === 1)
            return true;

        if($pet->getTool() && $this->rng->rngNextInt(1, 180) <= $pet->getTool()->increasesPooping())
            return true;

        return false;
    }

    private function maybeReceiveFairyGodmotherVisit(Pet $pet): bool
    {
        if(!$pet->hasMerit(MeritEnum::FAIRY_GODMOTHER))
            return false;

        if($pet->hasStatusEffect(StatusEffectEnum::BittenByAVampire) && $this->rng->rngNextInt(1, 20) === 1)
        {
            $changes = new PetChanges($pet);

            $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, ActivityHelpers::PetName($pet) . ' was thinking about what to do, when their Fairy Godmother showed up! "Let\'s do something about that nasty Vampire bite!" she said, and with a flick of her wand, ' . ActivityHelpers::PetName($pet) . '\'s vampire bite was healed! "Much better! <small>Nasty creatures, those!</small> You take care, now!"')
                ->addInterestingness(PetActivityLogInterestingness::RareActivity)
            ;

            $pet
                ->increaseSafety(12)
                ->increaseLove(12)
                ->increaseEsteem(12)
            ;

            $pet->removeStatusEffect($pet->getStatusEffect(StatusEffectEnum::BittenByAVampire));

            $this->petExperienceService->spendTime($pet, 15, PetActivityStatEnum::OTHER, null);

            $activityLog->setChanges($changes->compare($pet));

            return true;
        }

        if($this->rng->rngNextInt(1, 650) !== 1)
            return false;

        $randomChat = $this->rng->rngNextFromArray([
            'In the face of darkness, remember that your light shines brightest',
            'Embrace your uniqueness, for it is the key to unlocking your dreams',
            'Believe in yourself, my dear, for magic lies within your heart',
            'The power of imagination will lead you to realms where dreams come true',
            'Let kindness be your wand, and you\'ll create wonders wherever you go',
            'Never underestimate the strength of a kind heart, for it can move mountains',
            'In the garden of life, cultivate gratitude, and watch your blessings bloom',
            'Every day is a new page in the book of your adventures; write it with joy and wonder',
            'In every challenge, there lies a hidden spell of growth and wisdom',
            'The world will try to define you, but remember, you are the only one who can determine your true worth',
            'Change, like the tides, is inevitable and often unpredictable, but it\'s what keeps life\'s oceans alive and vibrant',
            'Your dreams are your soul\'s whispers, guiding you to your true destiny; listen to them attentively',
        ]);

        $randomGoody = $this->rng->rngNextFromArray([
            'Quintessence', 'Berry Cobbler', 'Tile: Mushroom Hunting',
            'Book of Flowers', 'Witch-hazel', 'Blackberry Wine',
            'Piece of Cetgueli\'s Map', 'World\'s Best Sugar Cookie', 'Champignon',
            'Scroll of Fruit', 'Bag of Beans', 'Secret Seashell', 'Trout Yogurt',
            'Sand Dollar', 'Sunflower', 'Sunflower', 'Merigold',
            'Magic Hourglass', 'Brownie', 'Flower Basket', 'Fish Stew',
            'Slice of Pumpkin Pie', 'Mysterious Seed', 'Decorated Flute',
            'Laufabrauð', 'Fisherman\'s Pie', 'Magic Smoke', 'Wings', 'Wings', 'Wings',
            'Coreopsis', 'Harvest Staff', 'Pumpkin Bread', 'White Feathers',
            'Really Big Leaf', 'Caramel-covered Red', 'Largish Bowl of Smallish Pumpkin Soup',
            'Whisper Stone', 'Everybeans', 'Dreamwalker\'s Tea', 'Dreamwalker\'s Tea',
            'Dreamwalker\'s Tea', 'Hat Box', 'Tiny Tea', 'Tremendous Tea',
            'Crystal Ball', 'Moon Dust', 'Magpie Pouch', 'Mericarp',
            'Wolf\'s Bane', 'Wolf\'s Bane', 'Wolf\'s Bane', 'Tawny Ears',
            'Tile: Lovely Haberdashers', 'Treat of Crispy Rice',
        ]);

        $soNice = $this->rng->rngNextFromArray([
            'Gosh dang, she\'s so nice!',
            'How\'d she get so friggin\' sweet!',
            'She\'s just the best!',
        ]);

        $changes = new PetChanges($pet);

        $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, ActivityHelpers::PetName($pet) . ' was thinking about what to do, when their Fairy Godmother showed up! They chatted for a while before she delivered these parting words: "' . $randomChat . '"... and a parting gift: ' . $randomGoody . '. (' . $soNice . ')')
            ->addInterestingness(PetActivityLogInterestingness::RareActivity)
        ;

        $pet
            ->increaseSafety(12)
            ->increaseLove(12)
            ->increaseEsteem(12)
        ;

        $this->inventoryService->petCollectsItem($randomGoody, $pet, $pet->getName() . ' received this from their Fairy Godmother!', $activityLog);
        $this->petExperienceService->spendTime($pet, 90, PetActivityStatEnum::OTHER, null);

        $activityLog->setChanges($changes->compare($pet));

        return true;
    }

    private function maybeReceiveAthenasGift(Pet $pet): bool
    {
        if(!$pet->hasMerit(MeritEnum::ATHENAS_GIFTS))
            return false;

        if($this->rng->rngNextInt(1, 300) !== 1)
            return false;

        $randomExclamation = $this->rng->rngNextFromArray([
            'Neat-o!', 'Rad!', 'Dope!', 'Sweet!', 'Hot diggity!', 'Epic!', 'Let\'s go!',
        ]);

        $activityLog = PetActivityLogFactory::createUnreadLog($this->em, $pet, ActivityHelpers::PetName($pet) . ' was thinking about what to do, when they spotted a Handicrafts Supply Box nearby! (Athena\'s Gifts! ' . $randomExclamation . ')')
            ->addInterestingness(PetActivityLogInterestingness::RareActivity)
        ;

        $this->inventoryService->petCollectsItem('Handicrafts Supply Box', $pet, $pet->getName() . ' received this - a gift from the gods!', $activityLog);
        $this->petExperienceService->spendTime($pet, 30, PetActivityStatEnum::OTHER, null);

        return true;
    }

    private function discoverNewFeature(Pet $pet): ?PetActivityLog
    {
        $hasUnlockedMuseum = $pet->getOwner()->hasUnlockedFeature(UnlockableFeatureEnum::Museum);
        $hasUnlockedBookstore = $pet->getOwner()->hasUnlockedFeature(UnlockableFeatureEnum::Bookstore);
        $hasUnlockedMarket = $pet->getOwner()->hasUnlockedFeature(UnlockableFeatureEnum::Market);
        $hasUnlockedZoologist = $pet->getOwner()->hasUnlockedFeature(UnlockableFeatureEnum::Zoologist);

        if($hasUnlockedMuseum && $hasUnlockedBookstore && $hasUnlockedMarket && $hasUnlockedZoologist)
            return null;

        $progress = UserQuestRepository::findOrCreate($this->em, $pet->getOwner(), 'Feature Discovery Counter', 0);

        if($progress->getValue() < 40)
        {
            $progress->setValue($progress->getValue() + $this->rng->rngNextInt(1, 4));
            return null;
        }

        $progress->setValue(0);

        if(!$pet->getOwner()->hasUnlockedFeature(UnlockableFeatureEnum::Museum))
            return $this->genericAdventureService->discoverFeature($pet, UnlockableFeatureEnum::Museum, 'Museum');

        if(!$pet->getOwner()->hasUnlockedFeature(UnlockableFeatureEnum::Market))
            return $this->genericAdventureService->discoverFeature($pet, UnlockableFeatureEnum::Market, 'Market');

        if(!$pet->getOwner()->hasUnlockedFeature(UnlockableFeatureEnum::Bookstore))
            return $this->genericAdventureService->discoverFeature($pet, UnlockableFeatureEnum::Bookstore, 'Bookstore');

        if(!$pet->getOwner()->hasUnlockedFeature(UnlockableFeatureEnum::Zoologist))
            return $this->genericAdventureService->discoverFeature($pet, UnlockableFeatureEnum::Zoologist, 'Zoologist');

        return null;
    }
}
