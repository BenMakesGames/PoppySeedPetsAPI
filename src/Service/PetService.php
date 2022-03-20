<?php
namespace App\Service;

use App\Entity\GreenhousePlant;
use App\Entity\LunchboxItem;
use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Entity\PetBaby;
use App\Entity\PetGroup;
use App\Entity\PetRelationship;
use App\Entity\User;
use App\Enum\ActivityPersonalityEnum;
use App\Enum\EnumInvalidValueException;
use App\Enum\GatheringHolidayEnum;
use App\Enum\HolidayEnum;
use App\Enum\MeritEnum;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Enum\PetActivityStatEnum;
use App\Enum\PetLocationEnum;
use App\Enum\PetSkillEnum;
use App\Enum\SocialTimeWantEnum;
use App\Enum\SpiritCompanionStarEnum;
use App\Enum\StatusEffectEnum;
use App\Enum\UserStatEnum;
use App\Functions\ArrayFunctions;
use App\Functions\ColorFunctions;
use App\Functions\GrammarFunctions;
use App\Model\ComputedPetSkills;
use App\Model\FoodWithSpice;
use App\Model\PetChanges;
use App\Model\PetChangesSummary;
use App\Repository\PetActivityLogTagRepository;
use App\Repository\PetRelationshipRepository;
use App\Repository\PetRepository;
use App\Repository\UserStatsRepository;
use App\Service\PetActivity\BurntForestService;
use App\Service\PetActivity\Caerbannog;
use App\Service\PetActivity\ChocolateMansion;
use App\Service\PetActivity\Crafting\MagicBindingService;
use App\Service\PetActivity\Crafting\NotReallyCraftsService;
use App\Service\PetActivity\Crafting\PlasticPrinterService;
use App\Service\PetActivity\Crafting\SmithingService;
use App\Service\PetActivity\CraftingService;
use App\Service\PetActivity\DeepSeaService;
use App\Service\PetActivity\DreamingService;
use App\Service\PetActivity\EatingService;
use App\Service\PetActivity\GatheringHolidayAdventureService;
use App\Service\PetActivity\FishingService;
use App\Service\PetActivity\GatheringService;
use App\Service\PetActivity\GenericAdventureService;
use App\Service\PetActivity\GivingTreeGatheringService;
use App\Service\PetActivity\GuildService;
use App\Service\PetActivity\HeartDimensionService;
use App\Service\PetActivity\HoliService;
use App\Service\PetActivity\HuntingService;
use App\Service\PetActivity\IcyMoonService;
use App\Service\PetActivity\LetterService;
use App\Service\PetActivity\MagicBeanstalkService;
use App\Service\PetActivity\PetSummonedAwayService;
use App\Service\PetActivity\PhilosophersStoneService;
use App\Service\PetActivity\PoopingService;
use App\Service\PetActivity\PregnancyService;
use App\Service\PetActivity\Crafting\ProgrammingService;
use App\Service\PetActivity\Protocol7Service;
use App\Service\PetActivity\TreasureMapService;
use App\Service\PetActivity\UmbraService;
use Doctrine\ORM\EntityManagerInterface;

class PetService
{
    private EntityManagerInterface $em;
    private PetRepository $petRepository;
    private ResponseService $responseService;
    private $petRelationshipService;
    private $fishingService;
    private $huntingService;
    private $gatheringService;
    private CraftingService $craftingService;
    private $magicBindingService;
    private $programmingService;
    private UserStatsRepository $userStatsRepository;
    private TreasureMapService $treasureMapService;
    private $genericAdventureService;
    private $protocol7Service;
    private $umbraService;
    private $poopingService;
    private $givingTreeGatheringService;
    private $pregnancyService;
    private $petGroupService;
    private $petExperienceService;
    private $dreamingService;
    private MagicBeanstalkService $beanStalkService;
    private $gatheringHolidayAdventureService;
    private CalendarService $calendarService;
    private $heartDimensionService;
    private $petRelationshipRepository;
    private $guildService;
    private InventoryService $inventoryService;
    private $burntForestService;
    private DeepSeaService $deepSeaService;
    private $petSummonedAwayService;
    private $toolBonusService;
    private $notReallyCraftsService;
    private $letterService;
    private IRandom $squirrel3;
    private ChocolateMansion $chocolateMansion;
    private WeatherService $weatherService;
    private $holiService;
    private $caerbannog;
    private CravingService $cravingService;
    private StatusEffectService $statusEffectService;
    private EatingService $eatingService;
    private HouseSimService $houseSimService;
    private SmithingService $smithingService;
    private PlasticPrinterService $plasticPrinterService;
    private PhilosophersStoneService $philosophersStoneService;
    private PetActivityLogTagRepository $petActivityLogTagRepository;
    private IcyMoonService $icyMoonService;

    public function __construct(
        EntityManagerInterface $em, ResponseService $responseService, CalendarService $calendarService,
        PetRelationshipService $petRelationshipService, PetRepository $petRepository, FishingService $fishingService,
        HuntingService $huntingService, GatheringService $gatheringService, CraftingService $craftingService,
        UserStatsRepository $userStatsRepository, TreasureMapService $treasureMapService, GenericAdventureService $genericAdventureService,
        Protocol7Service $protocol7Service, ProgrammingService $programmingService, UmbraService $umbraService,
        PoopingService $poopingService, GivingTreeGatheringService $givingTreeGatheringService,
        PregnancyService $pregnancyService, Squirrel3 $squirrel3, ChocolateMansion $chocolateMansion,
        PetGroupService $petGroupService, PetExperienceService $petExperienceService, DreamingService $dreamingService,
        MagicBeanstalkService $beanStalkService, GatheringHolidayAdventureService $gatheringHolidayAdventureService,
        HeartDimensionService $heartDimensionService, PetRelationshipRepository $petRelationshipRepository,
        GuildService $guildService, BurntForestService $burntForestService, InventoryService $inventoryService,
        DeepSeaService $deepSeaService, NotReallyCraftsService $notReallyCraftsService, LetterService $letterService,
        PetSummonedAwayService $petSummonedAwayService, InventoryModifierService $toolBonusService,
        WeatherService $weatherService, HoliService $holiService, Caerbannog $caerbannog, CravingService $cravingService,
        StatusEffectService $statusEffectService, EatingService $eatingService, HouseSimService $houseSimService,
        MagicBindingService $magicBindingService, SmithingService $smithingService, PlasticPrinterService $plasticPrinterService,
        PhilosophersStoneService $philosophersStoneService, PetActivityLogTagRepository $petActivityLogTagRepository,
        IcyMoonService $icyMoonService
    )
    {
        $this->em = $em;
        $this->squirrel3 = $squirrel3;
        $this->petRepository = $petRepository;
        $this->responseService = $responseService;
        $this->calendarService = $calendarService;
        $this->petRelationshipService = $petRelationshipService;
        $this->fishingService = $fishingService;
        $this->huntingService = $huntingService;
        $this->gatheringService = $gatheringService;
        $this->craftingService = $craftingService;
        $this->userStatsRepository = $userStatsRepository;
        $this->treasureMapService = $treasureMapService;
        $this->genericAdventureService = $genericAdventureService;
        $this->protocol7Service = $protocol7Service;
        $this->programmingService = $programmingService;
        $this->umbraService = $umbraService;
        $this->poopingService = $poopingService;
        $this->givingTreeGatheringService = $givingTreeGatheringService;
        $this->pregnancyService = $pregnancyService;
        $this->petGroupService = $petGroupService;
        $this->petExperienceService = $petExperienceService;
        $this->dreamingService = $dreamingService;
        $this->beanStalkService = $beanStalkService;
        $this->gatheringHolidayAdventureService = $gatheringHolidayAdventureService;
        $this->heartDimensionService = $heartDimensionService;
        $this->petRelationshipRepository = $petRelationshipRepository;
        $this->guildService = $guildService;
        $this->burntForestService = $burntForestService;
        $this->inventoryService = $inventoryService;
        $this->deepSeaService = $deepSeaService;
        $this->petSummonedAwayService = $petSummonedAwayService;
        $this->toolBonusService = $toolBonusService;
        $this->notReallyCraftsService = $notReallyCraftsService;
        $this->letterService = $letterService;
        $this->chocolateMansion = $chocolateMansion;
        $this->weatherService = $weatherService;
        $this->holiService = $holiService;
        $this->caerbannog = $caerbannog;
        $this->cravingService = $cravingService;
        $this->statusEffectService = $statusEffectService;
        $this->eatingService = $eatingService;
        $this->houseSimService = $houseSimService;
        $this->magicBindingService = $magicBindingService;
        $this->smithingService = $smithingService;
        $this->plasticPrinterService = $plasticPrinterService;
        $this->philosophersStoneService = $philosophersStoneService;
        $this->petActivityLogTagRepository = $petActivityLogTagRepository;
        $this->icyMoonService = $icyMoonService;
    }

    public function runHour(Pet $pet)
    {
        $hasEventPersonality = $pet->hasActivityPersonality(ActivityPersonalityEnum::EVENTS_AND_MAPS);

        if(!$pet->isAtHome()) throw new \InvalidArgumentException('Pets that aren\'t home cannot be interacted with.');

        if($pet->getHouseTime()->getActivityTime() < 60)
            throw new \InvalidArgumentException('Pet does not have enough Time. (Ben did something horrible; please let him know.)');

        $this->responseService->setReloadPets();

        if($pet->getTool() && $pet->getTool()->canBeNibbled() && $this->squirrel3->rngNextInt(1, 10) === 1)
        {
            $changes = new PetChangesSummary();
            $changes->food = '+';

            $activityLog = $this->responseService->createActivityLog(
                $pet,
                '%pet:' . $pet->getId() . '.name% nibbled on their ' . $this->toolBonusService->getNameWithModifiers($pet->getTool()) . '.',
                'icons/activity-logs/just-the-fork',
                $changes
            );

            $activityLog->addTags($this->petActivityLogTagRepository->findByNames([ 'Eating' ]));
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
            $pet->increasePoison(1);
        }

        if($pet->getCaffeine() > 0)
        {
            $pet->increaseCaffeine(-1);

            if($this->squirrel3->rngNextInt(1, 2) === 1)
                $pet->increasePoison(1);
        }

        if($pet->getPsychedelic() > 0)
        {
            $pet->increasePsychedelic(-1);
            $pet->increasePoison(2);
        }

        $safetyRestingPoint = $pet->hasMerit(MeritEnum::NOTHING_TO_FEAR) ? 8 : 0;

        if($pet->getSafety() > $safetyRestingPoint && $this->squirrel3->rngNextInt(1, 2) === 1)
            $pet->increaseSafety(-1);
        else if($pet->getSafety() < $safetyRestingPoint)
            $pet->increaseSafety(1);

        $loveRestingPoint = $pet->hasMerit(MeritEnum::EVERLASTING_LOVE) ? 8 : 0;

        if($pet->getLove() > $loveRestingPoint && $this->squirrel3->rngNextInt(1, 2) === 1)
            $pet->increaseLove(-1);
        else if($pet->getLove() < $loveRestingPoint && $this->squirrel3->rngNextInt(1, 2) === 1)
            $pet->increaseLove(1);

        $esteemRestingPoint = $pet->hasMerit(MeritEnum::NEVER_EMBARRASSED) ? 8 : 0;

        if($pet->getEsteem() > $esteemRestingPoint)
            $pet->increaseEsteem(-1);
        else if($pet->getEsteem() < $esteemRestingPoint && $this->squirrel3->rngNextInt(1, 2) === 1)
            $pet->increaseEsteem(1);

        $this->cravingService->maybeRemoveCraving($pet);

        $pregnancy = $pet->getPregnancy();

        if($pregnancy)
        {
            if($pet->getFood() < 0) $pregnancy->increaseAffection(-1);
            if($pet->getSafety() < 0 && $this->squirrel3->rngNextInt(1, 2) === 1) $pregnancy->increaseAffection(-1);
            if($pet->getLove() < 0 && $this->squirrel3->rngNextInt(1, 3) === 1) $pregnancy->increaseAffection(-1);
            if($pet->getEsteem() < 0 && $this->squirrel3->rngNextInt(1, 4) === 1) $pregnancy->increaseAffection(-1);

            if($pregnancy->getGrowth() >= PetBaby::PREGNANCY_DURATION)
            {
                $this->pregnancyService->giveBirth($pet);
                return;
            }
        }

        if($pet->getPoison() > 0)
        {
            if($this->squirrel3->rngNextInt(6, 24) < $pet->getPoison())
            {
                $changes = new PetChanges($pet);

                $safetyVom = ceil($pet->getPoison() / 4);

                $pet->increasePoison(-$this->squirrel3->rngNextInt( ceil($pet->getPoison() / 4), ceil($pet->getPoison() * 3 / 4)));
                if($pet->getAlcohol() > 0) $pet->increaseAlcohol(-$this->squirrel3->rngNextInt(1, ceil($pet->getAlcohol() / 2)));
                if($pet->getPsychedelic() > 0) $pet->increasePsychedelic(-$this->squirrel3->rngNextInt(1, ceil($pet->getPsychedelic() / 2)));
                if($pet->getCaffeine() > 0) $pet->increaseFood(-$this->squirrel3->rngNextInt(1, ceil($pet->getCaffeine() / 2)));
                if($pet->getJunk() > 0) $pet->increaseJunk(-$this->squirrel3->rngNextInt(1, ceil($pet->getJunk() / 2)));
                if($pet->getFood() > 0) $pet->increaseFood(-$this->squirrel3->rngNextInt(1, ceil($pet->getFood() / 2)));

                $pet->increaseSafety(-$this->squirrel3->rngNextInt(1, $safetyVom));
                $pet->increaseEsteem(-$this->squirrel3->rngNextInt(1, $safetyVom));

                $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(15, 30), PetActivityStatEnum::OTHER, null);

                $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% threw up :(', '', $changes->compare($pet));

                return;
            }
        }

        if($this->poop($pet))
        {
            $this->poopingService->poopDarkMatter($pet);
        }

        if($pet->hasMerit(MeritEnum::SHEDS) && $this->squirrel3->rngNextInt(1, 180) === 1)
        {
            $this->poopingService->shed($pet);
        }

        if($pet->hasMerit(MeritEnum::HYPERCHROMATIC))
        {
            if($this->squirrel3->rngNextInt(1, 250) === 1)
            {
                $pet
                    ->setColorA(ColorFunctions::RGB2Hex($this->squirrel3->rngNextInt(0, 255), $this->squirrel3->rngNextInt(0, 255), $this->squirrel3->rngNextInt(0, 255)))
                    ->setColorB(ColorFunctions::RGB2Hex($this->squirrel3->rngNextInt(0, 255), $this->squirrel3->rngNextInt(0, 255), $this->squirrel3->rngNextInt(0, 255)))
                ;
            }
            else
            {
                $pet
                    ->setColorA($this->squirrel3->rngNextTweakedColor($pet->getColorA(), 4))
                    ->setColorB($this->squirrel3->rngNextTweakedColor($pet->getColorB(), 4))
                ;
            }
        }

        $petWithSkills = $pet->getComputedSkills();

        if($this->squirrel3->rngNextInt(1, 4000) === 1)
        {
            $activityLog = $this->petSummonedAwayService->adventure($petWithSkills);

            if($activityLog)
                return;
        }

        $hunger = $this->squirrel3->rngNextInt(0, 4);

        if($pet->getFood() + $pet->getJunk() < $hunger && count($pet->getLunchboxItems()) > 0)
        {
            $petChanges = new PetChanges($pet);

            /** @var $sortedLunchboxItems LunchboxItem[] */
            $sortedLunchboxItems = $pet->getLunchboxItems()->filter(function(LunchboxItem $i) {
                return $i->getInventoryItem()->getItem()->getFood() !== null;
            })->toArray();

            // sorted from most-delicious to least-delicious
            usort($sortedLunchboxItems, function(LunchboxItem $a, LunchboxItem $b) use($pet) {
                $aFood = new FoodWithSpice($a->getInventoryItem()->getItem(), $a->getInventoryItem()->getSpice());
                $bFood = new FoodWithSpice($b->getInventoryItem()->getItem(), $b->getInventoryItem()->getSpice());

                $aValue = $this->eatingService->getFavoriteFlavorStrength($pet, $aFood) + $aFood->love;
                $bValue = $this->eatingService->getFavoriteFlavorStrength($pet, $bFood) + $bFood->love;

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

            $this->responseService->createActivityLog($pet, $message, 'icons/activity-logs/lunchbox', $petChanges->compare($pet))
                ->addInterestingness($itemsLeftInLunchbox === 0 ? PetActivityLogInterestingnessEnum::LUNCHBOX_EMPTY : 1)
            ;
        }

        if($pet->hasStatusEffect(StatusEffectEnum::OIL_COVERED))
        {
            if($this->cleanUpStatusEffect($pet, StatusEffectEnum::OIL_COVERED, 'Oil'))
                return;
        }

        if($pet->hasStatusEffect(StatusEffectEnum::BUBBLEGUMD))
        {
            if($this->cleanUpStatusEffect($pet, StatusEffectEnum::OIL_COVERED, 'Bubblegum'))
                return;
        }

        if($pet->hasStatusEffect(StatusEffectEnum::GOBBLE_GOBBLE) && $this->squirrel3->rngNextInt(1, 2) === 1)
        {
            $changes = new PetChanges($pet);
            $activityLog = $this->huntingService->huntedTurkeyDragon($petWithSkills);
            $activityLog->setChanges($changes->compare($pet));
            return;
        }

        if($pet->hasStatusEffect(StatusEffectEnum::ONEIRIC) && $this->squirrel3->rngNextInt(1, 2) === 1)
        {
            $this->dreamingService->dream($pet);
            $pet->removeStatusEffect($pet->getStatusEffect(StatusEffectEnum::ONEIRIC));
            return;
        }

        if($pet->hasStatusEffect(StatusEffectEnum::WEREFORM))
        {
            if($this->squirrel3->rngNextInt(1, 10) === 1)
                $pet->removeStatusEffect($pet->getStatusEffect(StatusEffectEnum::WEREFORM));
        }
        else if(
            $pet->hasStatusEffect(StatusEffectEnum::BITTEN_BY_A_WERECREATURE) &&
            $this->squirrel3->rngNextInt(1, 100) === 1 &&
            !$pet->hasStatusEffect(StatusEffectEnum::WEREFORM)
        )
        {
            $this->statusEffectService->applyStatusEffect($pet, StatusEffectEnum::WEREFORM, 1);
        }

        if($this->dream($pet))
        {
            $this->dreamingService->dream($pet);
            return;
        }

        $itemsInHouse = $this->houseSimService->getState()->getInventoryCount();

        $craftingPossibilities = $this->craftingService->getCraftingPossibilities($petWithSkills);
        $smithingPossibilities = $this->smithingService->getCraftingPossibilities($petWithSkills);
        $magicBindingPossibilities = $this->magicBindingService->getCraftingPossibilities($petWithSkills);
        $programmingPossibilities = $this->programmingService->getCraftingPossibilities($petWithSkills);
        $plasticPrinterPossibilities = $this->plasticPrinterService->getCraftingPossibilities($petWithSkills);
        $notCraftingPossibilities = $this->notReallyCraftsService->getCraftingPossibilities($petWithSkills);

        $houseTooFull = $this->squirrel3->rngNextInt(1, 10) > User::MAX_HOUSE_INVENTORY - $itemsInHouse;

        if($houseTooFull)
        {
            if($itemsInHouse >= User::MAX_HOUSE_INVENTORY)
                $description = '%user:' . $pet->getOwner()->getId() . '.Name\'s% house is crazy-full.';
            else
                $description = '%user:' . $pet->getOwner()->getId() . '.Name\'s% house is getting pretty full.';

            if(
                count($craftingPossibilities) + count($magicBindingPossibilities) + count($programmingPossibilities) +
                count($notCraftingPossibilities) + count($smithingPossibilities) + count($plasticPrinterPossibilities)
                === 0
            )
            {
                $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::OTHER, null);

                $this->responseService->createActivityLog($pet, $description . ' %pet:' . $pet->getId() . '.name% wanted to make something, but couldn\'t find any materials to work with.', 'icons/activity-logs/house-too-full')
                    ->addTags($this->petActivityLogTagRepository->findByNames([ 'House Too Full' ]))
                ;
            }
            else
            {
                $possibilities = [];

                if(count($craftingPossibilities) > 0) $possibilities[] = [ $this->craftingService, $craftingPossibilities ];
                if(count($magicBindingPossibilities) > 0) $possibilities[] = [ $this->craftingService, $magicBindingPossibilities ];
                if(count($smithingPossibilities) > 0) $possibilities[] = [ $this->craftingService, $smithingPossibilities ];
                if(count($programmingPossibilities) > 0) $possibilities[] = [ $this->programmingService, $programmingPossibilities ];
                if(count($plasticPrinterPossibilities) > 0) $possibilities[] = [ $this->craftingService, $plasticPrinterPossibilities ];
                if(count($notCraftingPossibilities) > 0) $possibilities[] = [ $this->notReallyCraftsService, $notCraftingPossibilities ];

                $do = $this->squirrel3->rngNextFromArray($possibilities);

                /** @var PetActivityLog $activityLog */
                $activityLog = $do[0]->adventure($petWithSkills, $do[1]);
                $activityLog->setEntry($description . ' ' . $activityLog->getEntry());

                if($activityLog->getChanges()->level > 0)
                    $activityLog->addTag($this->petActivityLogTagRepository->findOneBy([ 'title' => 'Level-up' ]));
            }

            return;
        }

        if($this->squirrel3->rngNextInt(1, $hasEventPersonality ? 48 : 50) === 1)
        {
            if($this->letterService->adventure($petWithSkills))
                return;

            $this->genericAdventureService->adventure($petWithSkills);
            return;
        }

        if($pet->getTool())
        {
            if($this->considerToolsWhichLeadToAdventure($petWithSkills))
                return;
        }

        if($this->squirrel3->rngNextInt(1, $hasEventPersonality ? 48 : 50) === 1)
        {
            $activityLog = $this->givingTreeGatheringService->gatherFromGivingTree($pet);
            if($activityLog)
                return;
        }

        if($this->squirrel3->rngNextInt(1, 100) <= ($hasEventPersonality ? 24 : 16) && $this->calendarService->isSaintPatricksDay())
        {
            $this->gatheringHolidayAdventureService->adventure($petWithSkills, GatheringHolidayEnum::SAINT_PATRICKS);
            return;
        }

        if($this->squirrel3->rngNextInt(1, 100) <= ($hasEventPersonality ? 30 : 25) && $this->calendarService->isEaster())
        {
            $this->gatheringHolidayAdventureService->adventure($petWithSkills, GatheringHolidayEnum::EASTER);
            return;
        }

        if($pet->getGuildMembership() && $this->squirrel3->rngNextInt(1, 35) === 1)
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

        if($pet->hasMerit(MeritEnum::PROTOCOL_7))
            $petDesires['hack'] = $this->generateHackingDesire($petWithSkills);

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
                if($pet->getFood() > 0 && $this->squirrel3->rngNextInt(1, 10) === 1)
                {
                    $this->treasureMapService->doKeybladeTower($petWithSkills);
                    return true;
                }

                return false;

            case 'Rainbow Dolphin Plushy':
            case 'Sneqo Plushy':
            case 'Bulbun Plushy':
            case 'Peacock Plushy':
            case 'Phoenix Plushy':
            case '"Roy" Plushy':
                if($this->squirrel3->rngNextInt(1, 6) === 1 || $this->userStatsRepository->getStatValue($pet->getOwner(), UserStatEnum::TRADED_WITH_THE_FLUFFMONGER) === 0)
                {
                    $this->treasureMapService->doFluffmongerTrade($pet);
                    return true;
                }

                return false;

            case '"Gold" Idol':
                $this->treasureMapService->doGoldIdol($pet);
                return true;

            case 'Heartstone':
                if($this->squirrel3->rngNextInt(1, 3) === 1)
                {
                    if($this->heartDimensionService->canAdventure($pet))
                        $this->heartDimensionService->adventure($petWithSkills);
                    else
                        $this->heartDimensionService->noAdventuresRemaining($pet);

                    return true;
                }

                return false;

            case 'Saucepan':
                if($this->squirrel3->rngNextInt(1, 10) === 1)
                {
                    $this->treasureMapService->doCookSomething($pet);
                    return true;
                }

                return false;

            case 'Diffie-H Key':
                $this->treasureMapService->doUseDiffieHKey($pet);
                return true;

            case 'Aubergine Commander':
                if($this->squirrel3->rngNextInt(1, 100) === 1)
                {
                    $this->treasureMapService->doEggplantCurse($pet);
                    return true;
                }
                return false;

            case 'Chocolate Key':
                if($this->squirrel3->rngNextInt(1, 4) === 1)
                {
                    $this->chocolateMansion->adventure($petWithSkills);
                    return true;
                }
                return false;

            case 'Carrot Key':
                if($this->squirrel3->rngNextInt(1, 4) === 1)
                {
                    $this->caerbannog->adventure($petWithSkills);
                    return true;
                }
                return false;

            case '5-leaf Clover':
                $this->treasureMapService->doLeprechaun($petWithSkills);
                return true;

            case 'Winged Key':
                $this->treasureMapService->doAbundantiasVault($pet);
                return true;

            case 'Fimbulvetr':
                if($this->squirrel3->rngNextInt(1, 20) == 1)
                {
                    $this->philosophersStoneService->seekMetatronsFire($petWithSkills);
                    return true;
                }
                return false;

            case 'Ceremony of Fire':
                if($this->squirrel3->rngNextInt(1, 20) == 1)
                {
                    $this->philosophersStoneService->seekVesicaHydrargyrum($petWithSkills);
                    return true;
                }
                return false;

            case 'Snickerblade':
                if($this->squirrel3->rngNextInt(1, 20) == 1)
                {
                    $this->philosophersStoneService->seekEarthsEgg($petWithSkills);
                    return true;
                }
                return false;
        }

        if(!$pet->getTool()->getEnchantment())
            return false;

        switch($pet->getTool()->getEnchantment()->getName())
        {
            case 'Searing':
                if($this->squirrel3->rngNextInt(1, 20) == 1)
                {
                    return $this->philosophersStoneService->seekMerkabaOfAir($petWithSkills) !== null;
                }
                return false;
        }

        return false;
    }

    public function runSocialTime(Pet $pet): bool
    {
        if($pet->hasMerit(MeritEnum::AFFECTIONLESS))
        {
            $pet->getHouseTime()->setSocialEnergy(-365 * 24 * 60);
            return true;
        }

        if($pet->getFood() + $pet->getAlcohol() + $pet->getJunk() < 0)
        {
            $pet->getHouseTime()->setCanAttemptSocialHangoutAfter((new \DateTimeImmutable())->modify('+5 minutes'));
            return false;
        }

        if(!$pet->hasStatusEffect(StatusEffectEnum::WEREFORM) && $this->meetRoommates($pet))
        {
            $this->petExperienceService->spendSocialEnergy($pet, PetExperienceService::SOCIAL_ENERGY_PER_HANG_OUT);
            return true;
        }

        $weather = $this->weatherService->getWeather(new \DateTimeImmutable(), $pet);

        if(!$pet->hasStatusEffect(StatusEffectEnum::WEREFORM) && $weather->isHoliday(HolidayEnum::HOLI))
        {
            if($this->holiService->adventure($pet))
                return true;
        }

        $wants = [];

        $wants[] = [ 'activity' => SocialTimeWantEnum::HANG_OUT, 'weight' => 60 ];

        if(!$pet->hasStatusEffect(StatusEffectEnum::WEREFORM))
        {
            $availableGroups = $pet->getGroups()->filter(function(PetGroup $g) {
                return $g->getSocialEnergy() >= PetGroupService::SOCIAL_ENERGY_PER_MEET;
            });

            if(count($availableGroups) > 0)
                $wants[] = [ 'activity' => SocialTimeWantEnum::GROUP, 'weight' => 30 ];

            if(count($pet->getGroups()) < $pet->getMaximumGroups())
                $wants[] = [ 'activity' => SocialTimeWantEnum::CREATE_GROUP, 'weight' => 5 ];
        }

        while(count($wants) > 0)
        {
            $want = ArrayFunctions::pick_one_weighted($wants, fn($want) => $want['weight']);

            $activity = $want['activity'];

            $wants = array_filter($wants, fn($want) => $want['activity'] !== $activity);

            switch($activity)
            {
                case SocialTimeWantEnum::HANG_OUT:
                    if($this->hangOutWithFriend($pet))
                        return true;
                    break;

                case SocialTimeWantEnum::GROUP:
                    $this->petGroupService->doGroupActivity($this->squirrel3->rngNextFromArray($availableGroups->toArray()));
                    return true;

                case SocialTimeWantEnum::CREATE_GROUP:
                    if($this->petGroupService->createGroup($pet) !== null)
                        return true;
                    break;
            }
        }

        $pet->getHouseTime()->setCanAttemptSocialHangoutAfter((new \DateTimeImmutable())->modify('+15 minutes'));

        return false;
    }

    public function recomputeFriendRatings(Pet $pet)
    {
        $friends = $this->petRelationshipRepository->getFriends($pet);

        if(count($friends) == 0)
            return;

        $commitmentLevels = array_map(fn(PetRelationship $r) => $r->getCommitment(), $friends);

        $minCommitment = min($commitmentLevels);
        $maxCommitment = max($minCommitment + 1, max($commitmentLevels));
        $commitmentRange = $maxCommitment - $minCommitment;

        $interpolate = fn($x) => 1 + (int)(9 * ($x - $minCommitment) / $commitmentRange);

        foreach($friends as $friend)
            $friend->setRating($interpolate($friend->getCommitment()));
    }

    private function hangOutWithFriend(Pet $pet): bool
    {
        $relationships = $this->petRelationshipRepository->getRelationshipsToHangOutWith($pet);

        $spiritCompanionAvailable = $pet->hasMerit(MeritEnum::SPIRIT_COMPANION) && ($pet->getSpiritCompanion()->getLastHangOut() === null || $pet->getSpiritCompanion()->getLastHangOut() < (new \DateTimeImmutable())->modify('-12 hours'));

        // no friends available? no spirit companion? GIT OUTTA' HE'E!
        if(count($relationships) === 0 && !$spiritCompanionAvailable)
            return false;

        // maybe hang out with a spirit companion, if you have one
        if($spiritCompanionAvailable && (count($relationships) === 0 || $this->squirrel3->rngNextInt(1, count($relationships) + 1) === 1))
        {
            $this->hangOutWithSpiritCompanion($pet);
            return true;
        }

        $friendRelationshipsByFriendId = $this->getFriendRelationships($pet, $relationships);
        $chosenRelationship = $this->pickRelationshipToHangOutWith($relationships, $friendRelationshipsByFriendId);

        if(!$chosenRelationship)
            return false;

        $friend = $chosenRelationship->getRelationship();

        $friendRelationship = $friendRelationshipsByFriendId[$friend->getId()];

        $skipped = $this->squirrel3->rngNextInt(0, 5);

        foreach($relationships as $r)
        {
            if($r->getId() === $chosenRelationship->getId())
            {
                $r->increaseCommitment($skipped);
                break;
            }

            $r->increaseCommitment(-1);
            $skipped++;
        }

        // hang out with selected pet
        $this->hangOutWithOtherPet($chosenRelationship, $friendRelationship);

        $dislikedRelationships = $this->petRelationshipRepository->getDislikedRelationshipsWithCommitment($pet);

        foreach($dislikedRelationships as $r)
            $r->increaseCommitment(-2);

        return true;
    }

    /**
     * @param PetRelationship[] $relationships
     * @return PetRelationship[]
     */
    private function getFriendRelationships(Pet $pet, array $relationships): array
    {
        /** @var PetRelationship[] $friendRelationships */
        $friendRelationships = $this->petRelationshipRepository->findBy([
            'pet' => array_map(fn(PetRelationship $r) => $r->getRelationship(), $relationships),
            'relationship' => $pet
        ]);

        /** @var $friendRelationshipsByFriendId[] $friendRelationships */
        $friendRelationshipsByFriendId = [];

        foreach($friendRelationships as $fr)
            $friendRelationshipsByFriendId[$fr->getPet()->getId()] = $fr;

        return $friendRelationshipsByFriendId;
    }

    /**
     * @param PetRelationship[] $relationships
     * @param PetRelationship[] $friendRelationshipsByFriendId
     */
    private function pickRelationshipToHangOutWith(array $relationships, array $friendRelationshipsByFriendId): ?PetRelationship
    {
        $relationships = array_filter($relationships, function(PetRelationship $r) use($friendRelationshipsByFriendId) {
            // sanity check (the game isn't always sane...)
            if(!array_key_exists($r->getRelationship()->getId(), $friendRelationshipsByFriendId))
                throw new \Exception($r->getPet()->getName() . ' (#' . $r->getPet()->getId() . ') knows ' . $r->getRelationship()->getName() . ' (#' . $r->getRelationship()->getId() . '), but not the other way around! This is a bug, and should never happen! Make Ben fix it!');

            if($r->getRelationship()->getHouseTime()->getSocialEnergy() >= PetExperienceService::SOCIAL_ENERGY_PER_HANG_OUT * 2)
                return true;

            if($friendRelationshipsByFriendId[$r->getRelationship()->getId()]->getCommitment() >= $r->getCommitment())
                return true;

            $chanceToHangOut = $friendRelationshipsByFriendId[$r->getRelationship()->getId()]->getCommitment() * 1000 / $r->getCommitment();

            return $this->squirrel3->rngNextInt(0, 999) < $chanceToHangOut;
        });

        if(count($relationships) === 0)
            return null;

        return ArrayFunctions::pick_one_weighted($relationships, fn(PetRelationship $r) => $r->getCommitment() + 1);
    }

    private function hangOutWithSpiritCompanion(Pet $pet)
    {
        $changes = new PetChanges($pet);

        $this->petExperienceService->spendSocialEnergy($pet, PetExperienceService::SOCIAL_ENERGY_PER_HANG_OUT);

        $companion = $pet->getSpiritCompanion();

        $companion->setLastHangOut();

        $adjectives = [ 'bizarre', 'impressive', 'surprisingly-graphic', 'whirlwind' ];

        if($this->squirrel3->rngNextInt(1, 3) !== 1 || ($pet->getSafety() > 0 && $pet->getLove() > 0 && $pet->getEsteem() > 0))
        {
            $teachingStat = null;

            switch($companion->getStar())
            {
                case SpiritCompanionStarEnum::ALTAIR:
                    // the flying/fighting eagle
                    if($this->squirrel3->rngNextInt(1, 3) === 1)
                    {
                        $teachingStat = PetSkillEnum::BRAWL;
                        $message = '%pet:' . $pet->getId() . '.name% practiced hunting with ' . $companion->getName() . '!';
                    }
                    else
                    {
                        // hanging-out
                        $message = '%pet:' . $pet->getId() . '.name% taught ' . $companion->getName() . ' more about the physical world.';
                    }
                    break;

                case SpiritCompanionStarEnum::CASSIOPEIA:
                    // sneaky snake
                    if($this->squirrel3->rngNextInt(1, 3) === 1)
                    {
                        $teachingStat = PetSkillEnum::STEALTH;
                        $message = $companion->getName() . ' showed %pet:' . $pet->getId() . '.name% how to take advantage of their surroundings to hide their presence.';
                    }
                    else
                    {
                        if($this->squirrel3->rngNextInt(1, 4) === 1)
                            $message = '%pet:' . $pet->getId() . '.name% listened to ' . $companion->getName() . ' for a little while. They had many, strange secrets to tell, but none really seemed that useful.';
                        else
                            $message = '%pet:' . $pet->getId() . '.name% listened to ' . $companion->getName() . ' for a little while.';
                    }
                    break;

                case SpiritCompanionStarEnum::CEPHEUS:
                    // a king
                    if($this->squirrel3->rngNextInt(1, 3) === 1)
                    {
                        $teachingStat = PetSkillEnum::UMBRA;
                        $message = '%pet:' . $pet->getId() . '.name% listened to ' . $companion->getName() . '\'s stories about the various lands of the near and far Umbra...';
                    }
                    else
                    {
                        $adjective = $this->squirrel3->rngNextFromArray($adjectives);
                        $message = $companion->getName() . ' told ' . GrammarFunctions::indefiniteArticle($adjective) . ' ' . $adjective . ' story they made just for %pet:' . $pet->getId() . '.name%!';
                    }
                    break;

                case SpiritCompanionStarEnum::GEMINI:
                    $message = '%pet:' . $pet->getId() . '.name% played ' . $this->squirrel3->rngNextFromArray([
                        'hide-and-go-seek tag',
                        'hacky sack',
                        'soccer',
                        'three-player checkers',
                        'charades',
                    ]) . ' with the ' . $companion->getName() . ' twins!';
                    break;

                case SpiritCompanionStarEnum::HYDRA:
                    // scary monster; depicted as basically a friendly guard dog
                    $message = '%pet:' . $pet->getId() . '.name% played catch with ' . $companion->getName() . '!';
                    break;

                case SpiritCompanionStarEnum::SAGITTARIUS:
                    // satyr-adjacent
                    if($this->squirrel3->rngNextInt(1, 3) === 1)
                    {
                        // teaches music
                        $teachingStat = PetSkillEnum::MUSIC;
                        $message = '%pet:' . $pet->getId() . '.name% ' . $this->squirrel3->rngNextFromArray([ 'played music', 'danced', 'sang' ]) . ' with ' . $companion->getName() . '!';
                    }
                    else
                    {
                        // hanging-out
                        $message = '%pet:' . $pet->getId() . '.name% went riding with ' . $companion->getName() . ' for a while!';
                    }
                    break;

                default:
                    throw new \Exception('Unknown Spirit Companion Star "' . $companion->getStar() . '"');
            }

            if($teachingStat)
            {
                $pet
                    ->increaseSafety($this->squirrel3->rngNextInt(1, 2))
                    ->increaseLove($this->squirrel3->rngNextInt(1, 2))
                    ->increaseEsteem($this->squirrel3->rngNextInt(1, 2))
                ;

                $this->petExperienceService->gainExp($pet, 1, [ $teachingStat ]);
            }
            else
            {
                $pet
                    ->increaseSafety($this->squirrel3->rngNextInt(2, 4))
                    ->increaseLove($this->squirrel3->rngNextInt(2, 4))
                    ->increaseEsteem($this->squirrel3->rngNextInt(2, 4))
                ;
            }
        }
        else if($pet->getSafety() <= 0)
        {
            switch($companion->getStar())
            {
                case SpiritCompanionStarEnum::ALTAIR:
                case SpiritCompanionStarEnum::CEPHEUS:
                    $pet
                        ->increaseSafety($this->squirrel3->rngNextInt(6, 10))
                        ->increaseLove($this->squirrel3->rngNextInt(2, 4))
                    ;
                    $message = '%pet:' . $pet->getId() . '.name% was feeling nervous, so talked to ' . $companion->getName() . '. ' . $companion->getName() . ' told a ' . $this->squirrel3->rngNextFromArray($adjectives) . ' story about victory in combat, and swore to protect %pet:' . $pet->getId() . '.name%!';
                    break;
                case SpiritCompanionStarEnum::CASSIOPEIA:
                    $pet
                        ->increaseSafety($this->squirrel3->rngNextInt(2, 4))
                    ;
                    $message = '%pet:' . $pet->getId() . '.name% was feeling nervous, so talked to ' . $companion->getName() . '. ' . $companion->getName() . ' whispered odd prophecies, then stared at %pet:' . $pet->getId() . '.name% expectantly. (It\'s the thought that counts...)';
                    break;
                case SpiritCompanionStarEnum::GEMINI:
                    $pet
                        ->increaseSafety($this->squirrel3->rngNextInt(4, 8))
                        ->increaseLove($this->squirrel3->rngNextInt(2, 4))
                        ->increaseEsteem($this->squirrel3->rngNextInt(2, 4))
                    ;
                    $message = '%pet:' . $pet->getId() . '.name% was feeling nervous, so talked to ' . $companion->getName() . '. ' . $companion->getName() . ' smiled, and split into multiple copies of itself, each defending %pet:' . $pet->getId() . '.name% from another angle. They all turned to %pet:' . $pet->getId() . '.name% and gave a sincere thumbs up before recombining.';
                    break;
                case SpiritCompanionStarEnum::SAGITTARIUS:
                    $pet
                        ->increaseSafety($this->squirrel3->rngNextInt(2, 4))
                        ->increaseLove($this->squirrel3->rngNextInt(2, 4))
                    ;
                    $message = '%pet:' . $pet->getId() . '.name% was feeling nervous, so talked to ' . $companion->getName() . '. ' . $companion->getName() . ' tried to distract %pet:' . $pet->getId() . '.name% with ' . $this->squirrel3->rngNextFromArray($adjectives) . ' stories about lavish parties. It kind of worked...';
                    break;
                case SpiritCompanionStarEnum::HYDRA:
                    $pet
                        ->increaseSafety($this->squirrel3->rngNextInt(4, 8))
                        ->increaseLove($this->squirrel3->rngNextInt(4, 8))
                    ;
                    $message = '%pet:' . $pet->getId() . '.name% was feeling nervous, so talked to ' . $companion->getName() . '. Sensing %pet:' . $pet->getId() . '.name%\'s unease, ' . $companion->getName() . ' looked around for potential threats, and roared menacingly.';
                    break;
                default:
                    throw new \Exception('Unknown Spirit Companion Star "' . $companion->getStar() . '"');
            }
        }
        else if($pet->getLove() <= 0)
        {
            switch($companion->getStar())
            {
                case SpiritCompanionStarEnum::ALTAIR:
                case SpiritCompanionStarEnum::CEPHEUS:
                    $pet
                        ->increaseSafety($this->squirrel3->rngNextInt(2, 4))
                        ->increaseLove($this->squirrel3->rngNextInt(2, 4))
                    ;
                    $message = '%pet:' . $pet->getId() . '.name% was feeling lonely, so talked to ' . $companion->getName() . '. ' . $companion->getName() . ' rambled some ' . $this->squirrel3->rngNextFromArray($adjectives) . ' story about victory in combat... (It\'s the thought that counts...)';
                    break;
                case SpiritCompanionStarEnum::CASSIOPEIA:
                    $pet
                        ->increaseSafety($this->squirrel3->rngNextInt(2, 4))
                        ->increaseLove($this->squirrel3->rngNextInt(2, 4))
                    ;
                    $message = '%pet:' . $pet->getId() . '.name% was feeling lonely, so talked to ' . $companion->getName() . '. ' . $companion->getName() . ' whispered odd prophecies, then stared at %pet:' . $pet->getId() . '.name% expectantly. (It\'s the thought that counts...)';
                    break;
                case SpiritCompanionStarEnum::GEMINI:
                    $pet
                        ->increaseSafety($this->squirrel3->rngNextInt(4, 8))
                        ->increaseLove($this->squirrel3->rngNextInt(4, 8))
                    ;
                    $message = '%pet:' . $pet->getId() . '.name% was feeling lonely, so talked to ' . $companion->getName() . '. ' . $companion->getName() . ' smiled, and split into multiple copies of itself, and they all played games together!';
                    break;
                case SpiritCompanionStarEnum::SAGITTARIUS:
                    $pet
                        ->increaseSafety($this->squirrel3->rngNextInt(2, 4))
                        ->increaseLove($this->squirrel3->rngNextInt(4, 8))
                        ->increaseEsteem($this->squirrel3->rngNextInt(2, 4))
                    ;
                    $message = '%pet:' . $pet->getId() . '.name% was feeling lonely, so talked to ' . $companion->getName() . '. The two hosted a party for themselves; %pet:' . $pet->getId() . '.name% had a lot of fun.';
                    break;
                case SpiritCompanionStarEnum::HYDRA:
                    $pet
                        ->increaseSafety($this->squirrel3->rngNextInt(4, 8))
                        ->increaseLove($this->squirrel3->rngNextInt(4, 8))
                    ;
                    $message = '%pet:' . $pet->getId() . '.name% was feeling lonely, so talked to ' . $companion->getName() . '. Sensing %pet:' . $pet->getId() . '.name%\'s unease, ' . $companion->getName() . ' settled into %pet:' . $pet->getId() . '.name%\'s lap.';
                    break;
                default:
                    throw new \Exception('Unknown Spirit Companion Star "' . $companion->getStar() . '"');
            }
        }
        else // low on esteem
        {
            switch($companion->getStar())
            {
                case SpiritCompanionStarEnum::ALTAIR:
                case SpiritCompanionStarEnum::CEPHEUS:
                    $pet
                        ->increaseSafety($this->squirrel3->rngNextInt(2, 4))
                        ->increaseLove($this->squirrel3->rngNextInt(2, 4))
                        ->increaseEsteem($this->squirrel3->rngNextInt(2, 4))
                    ;
                    $message = '%pet:' . $pet->getId() . '.name% was feeling down, so talked to ' . $companion->getName() . '. ' . $companion->getName() . ' listened patiently; in the end, %pet:' . $pet->getId() . '.name% felt a little better.';
                    break;
                case SpiritCompanionStarEnum::CASSIOPEIA:
                    $pet
                        ->increaseEsteem($this->squirrel3->rngNextInt(4, 8))
                    ;
                    $message = '%pet:' . $pet->getId() . '.name% was feeling down, so talked to ' . $companion->getName() . '. ' . $companion->getName() . ' whispered odd prophecies, then stared at %pet:' . $pet->getId() . '.name% expectantly. Somehow, that actually helped!';
                    break;
                case SpiritCompanionStarEnum::GEMINI:
                    $pet
                        ->increaseLove($this->squirrel3->rngNextInt(2, 4))
                    ;
                    $message = '%pet:' . $pet->getId() . '.name% was feeling down, so talked to ' . $companion->getName() . '. ' . $companion->getName() . ' tried to entertain %pet:' . $pet->getId() . '.name% by splitting into copies and dancing around, but it didn\'t really help...';
                    break;
                case SpiritCompanionStarEnum::SAGITTARIUS:
                    $pet
                        ->increaseSafety($this->squirrel3->rngNextInt(2, 4))
                        ->increaseLove($this->squirrel3->rngNextInt(2, 4))
                        ->increaseEsteem($this->squirrel3->rngNextInt(4, 8))
                    ;
                    $message = '%pet:' . $pet->getId() . '.name% was feeling down, so talked to ' . $companion->getName() . '. ' . $companion->getName() . ' empathized completely, having been in similar situations themselves. It was really nice to hear!';
                    break;
                case SpiritCompanionStarEnum::HYDRA:
                    $pet
                        ->increaseSafety($this->squirrel3->rngNextInt(2, 4))
                        ->increaseLove($this->squirrel3->rngNextInt(2, 4))
                        ->increaseEsteem($this->squirrel3->rngNextInt(4, 8))
                    ;
                    $message = $pet->getName() . ' was feeling down, so talked to ' . $companion->getName() . '. Sensing %pet:' . $pet->getId() . '.name%\'s unease, ' . $companion->getName() . ' settled into %pet:' . $pet->getId() . '.name%\'s lap.';
                    break;
                default:
                    throw new \Exception('Unknown Spirit Companion Star "' . $companion->getStar() . '"');
            }
        }

        $this->responseService->createActivityLog($pet, $message, 'companions/' . $companion->getImage(), $changes->compare($pet))
            ->addInterestingness(PetActivityLogInterestingnessEnum::ACTIVITY_USING_MERIT)
            ->addTags($this->petActivityLogTagRepository->findByNames([ 'Spirit Companion' ]))
        ;
    }

    /**
     * @param PetRelationship $pet
     * @param PetRelationship $friend
     * @throws EnumInvalidValueException
     */
    private function hangOutWithOtherPet(PetRelationship $pet, PetRelationship $friend)
    {
        $petChanges = new PetChanges($pet->getPet());
        $friendChanges = new PetChanges($friend->getPet());

        $this->petExperienceService->spendSocialEnergy($pet->getPet(), PetExperienceService::SOCIAL_ENERGY_PER_HANG_OUT);
        $this->petExperienceService->spendSocialEnergy($friend->getPet(), PetExperienceService::SOCIAL_ENERGY_PER_HANG_OUT);

        $petPreviousRelationship = $pet->getCurrentRelationship();
        $friendPreviousRelationship = $friend->getCurrentRelationship();

        [ $petLog, $friendLog ] = $this->petRelationshipService->hangOutPrivately($pet, $friend);

        if($petPreviousRelationship !== $pet->getCurrentRelationship())
        {
            $relationshipMovement =
                abs($this->petRelationshipService->calculateRelationshipDistance($pet->getRelationshipGoal(), $petPreviousRelationship)) -
                abs($this->petRelationshipService->calculateRelationshipDistance($pet->getRelationshipGoal(), $pet->getCurrentRelationship()))
            ;

            $pet->getPet()
                ->increaseLove($relationshipMovement * 2)
                ->increaseEsteem($relationshipMovement)
            ;
        }

        if($friendPreviousRelationship !== $friend->getCurrentRelationship())
        {
            $relationshipMovement =
                abs($this->petRelationshipService->calculateRelationshipDistance($friend->getRelationshipGoal(), $friendPreviousRelationship)) -
                abs($this->petRelationshipService->calculateRelationshipDistance($friend->getRelationshipGoal(), $friend->getCurrentRelationship()))
            ;

            $friend->getPet()
                ->increaseLove($relationshipMovement * 2)
                ->increaseEsteem($relationshipMovement)
            ;
        }

        $petLog
            ->setChanges($petChanges->compare($pet->getPet()))
            ->addTag($this->petActivityLogTagRepository->findOneBy([ 'title' => '1-on-1 Hangout' ]))
        ;

        $friendLog
            ->setChanges($friendChanges->compare($friend->getPet()))
            ->addTag($this->petActivityLogTagRepository->findOneBy([ 'title' => '1-on-1 Hangout' ]))
        ;

        if($petLog->getPet()->getOwner()->getId() === $friendLog->getPet()->getOwner()->getId())
            $friendLog->setViewed();

        $this->em->persist($petLog);
        $this->em->persist($friendLog);
    }

    private function meetRoommates(Pet $pet): bool
    {
        /** @var Pet[] $otherPets */
        $otherPets = $this->petRepository->getRoommates($pet);

        $metNewPet = false;

        foreach($otherPets as $otherPet)
        {
            if($otherPet->hasMerit(MeritEnum::AFFECTIONLESS))
                continue;

            if(!$pet->hasRelationshipWith($otherPet))
            {
                $metNewPet = true;
                $this->petRelationshipService->meetRoommate($pet, $otherPet);
            }

            if(!$otherPet->hasRelationshipWith($pet))
            {
                $metNewPet = true;
                $this->petRelationshipService->meetRoommate($otherPet, $pet);
            }
        }

        return $metNewPet;
    }

    private function doNothing(Pet $pet)
    {
        $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(30, 60), PetActivityStatEnum::OTHER, null);
        $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% hung around the house.', '');
    }

    private function pickDesire(array $petDesires)
    {
        $totalDesire = array_sum($petDesires);

        $pick = $this->squirrel3->rngNextInt(0, $totalDesire - 1);

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

        if($petWithSkills->getPet()->hasActivityPersonality(ActivityPersonalityEnum::FISHING))
            $desire += 4;
        else
            $desire += $this->squirrel3->rngNextInt(1, 4);

        return max(1, round($desire * (1 + $this->squirrel3->rngNextInt(-10, 10) / 100)));
    }

    public function generateIcyMoonDesire(ComputedPetSkills $petWithSkills): int
    {
        $pet = $petWithSkills->getPet();

        if($pet->hasStatusEffect(StatusEffectEnum::WEREFORM))
            return 0;

        $desire = $petWithSkills->getStamina()->getTotal() + $petWithSkills->getScience()->getTotal() + $petWithSkills->getGatheringBonus()->getTotal() - $pet->getAlcohol();

        // when a pet is equipped, the equipment bonus counts twice for affecting a pet's desires
        if($pet->getTool() && $pet->getTool()->getItem()->getTool())
            $desire += $pet->getTool()->getItem()->getTool()->getScience() + $pet->getTool()->getItem()->getTool()->getGathering();

        if($petWithSkills->getPet()->hasActivityPersonality(ActivityPersonalityEnum::ICY_MOON))
            $desire += 4;
        else
            $desire += $this->squirrel3->rngNextInt(1, 4);

        return max(0, round($desire * (1 + $this->squirrel3->rngNextInt(-10, 10) / 100) * 3 / 4));
    }

    public function generateSubmarineDesire(ComputedPetSkills $petWithSkills): int
    {
        $pet = $petWithSkills->getPet();

        if($pet->hasStatusEffect(StatusEffectEnum::WEREFORM))
            return 0;

        $desire = $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getScience()->getTotal() + $petWithSkills->getFishingBonus()->getTotal();

        // when a pet is equipped, the equipment bonus counts twice for affecting a pet's desires
        if($pet->getTool() && $pet->getTool()->getItem()->getTool())
            $desire += $pet->getTool()->getItem()->getTool()->getScience() + $pet->getTool()->getItem()->getTool()->getFishing();

        if($petWithSkills->getPet()->hasActivityPersonality(ActivityPersonalityEnum::SUBMARINE))
            $desire += 4;
        else
            $desire += $this->squirrel3->rngNextInt(1, 4);

        return max(1, round($desire * (1 + $this->squirrel3->rngNextInt(-10, 10) / 100)));
    }

    public function generateMonsterHuntingDesire(ComputedPetSkills $petWithSkills): int
    {
        $pet = $petWithSkills->getPet();
        $desire = $petWithSkills->getStrength()->getTotal() + $petWithSkills->getBrawl()->getTotal();

        // when a pet is equipped, the equipment bonus counts twice for affecting a pet's desires
        if($pet->getTool() && $pet->getTool()->getItem()->getTool())
            $desire += $pet->getTool()->getItem()->getTool()->getBrawl();

        if($petWithSkills->getPet()->hasActivityPersonality(ActivityPersonalityEnum::HUNTING))
            $desire += 4;
        else
            $desire += $this->squirrel3->rngNextInt(1, 4);

        return max(1, round($desire * (1 + $this->squirrel3->rngNextInt(-10, 10) / 100)));
    }

    public function generateCraftingDesire(ComputedPetSkills $petWithSkills): int
    {
        $pet = $petWithSkills->getPet();

        if($pet->hasStatusEffect(StatusEffectEnum::WEREFORM))
            return 0;

        $desire = $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getCrafts()->getTotal();

        // when a pet is equipped, the equipment bonus counts twice for affecting a pet's desires
        if($pet->getTool() && $pet->getTool()->getItem()->getTool())
            $desire += $pet->getTool()->getItem()->getTool()->getCrafts();

        if($petWithSkills->getPet()->hasActivityPersonality(ActivityPersonalityEnum::CRAFTING_MUNDANE))
            $desire += 4;
        else
            $desire += $this->squirrel3->rngNextInt(1, 4);

        return max(1, round($desire * (1 + $this->squirrel3->rngNextInt(-10, 10) / 100)));
    }

    public function generateMagicBindingDesire(ComputedPetSkills $petWithSkills): int
    {
        $pet = $petWithSkills->getPet();

        if($pet->hasStatusEffect(StatusEffectEnum::WEREFORM))
            return 0;

        $desire = $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getUmbra()->getTotal();

        // when a pet is equipped, the equipment bonus counts twice for affecting a pet's desires
        if($pet->getTool() && $pet->getTool()->getItem()->getTool())
            $desire += $pet->getTool()->getItem()->getTool()->getUmbra();

        if($petWithSkills->getPet()->hasActivityPersonality(ActivityPersonalityEnum::CRAFTING_MAGIC))
            $desire += 4;
        else
            $desire += $this->squirrel3->rngNextInt(1, 4);

        return max(1, round($desire * (1 + $this->squirrel3->rngNextInt(-10, 10) / 100)));
    }

    public function generateSmithingDesire(ComputedPetSkills $petWithSkills): int
    {
        $pet = $petWithSkills->getPet();

        if($pet->hasStatusEffect(StatusEffectEnum::WEREFORM))
            return 0;

        $desire = $petWithSkills->getStamina()->getTotal() + $petWithSkills->getCrafts()->getTotal() + $petWithSkills->getSmithingBonus()->getTotal();

        // when a pet is equipped, the equipment bonus counts twice for affecting a pet's desires
        if($pet->getTool() && $pet->getTool()->getItem()->getTool())
            $desire += $pet->getTool()->getItem()->getTool()->getCrafts() + $pet->getTool()->getItem()->getTool()->getSmithing();

        if($petWithSkills->getPet()->hasActivityPersonality(ActivityPersonalityEnum::CRAFTING_SMITHING))
            $desire += 4;
        else
            $desire += $this->squirrel3->rngNextInt(1, 4);

        return max(1, round($desire * (1 + $this->squirrel3->rngNextInt(-10, 10) / 100)));
    }

    public function generateExploreUmbraDesire(ComputedPetSkills $petWithSkills): int
    {
        $pet = $petWithSkills->getPet();
        $desire = $petWithSkills->getStamina()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getUmbra()->getTotal();

        if($pet->getTool() && $pet->getTool()->getItem()->getTool())
            $desire += $pet->getTool()->getItem()->getTool()->getUmbra();

        if($petWithSkills->getPet()->hasActivityPersonality(ActivityPersonalityEnum::UMBRA))
            $desire += 4;
        else
            $desire += $this->squirrel3->rngNextInt(1, 4);

        if(
            $pet->hasMerit(MeritEnum::NATURAL_CHANNEL) ||
            ($pet->getTool() && $pet->getTool()->getItem()->getTool() && $pet->getTool()->getItem()->getTool()->getAdventureDescription() === 'The Umbra')
        )
        {
            if($pet->getPsychedelic() > $pet->getMaxPsychedelic() / 2)
                return ceil($desire * $pet->getPsychedelic() * 2 / $pet->getMaxPsychedelic());
            else
                return $desire;
        }
        else if($pet->getPsychedelic() > 0)
        {
            return ceil($desire * $pet->getPsychedelic() * 2 / $pet->getMaxPsychedelic());
        }
        else
            return 0;
    }

    public function generateBurntForestDesire(ComputedPetSkills $petWithSkills): int
    {
        $umbraDesire = $this->generateExploreUmbraDesire($petWithSkills);
        $brawlDesire = $this->generateMonsterHuntingDesire($petWithSkills);

        return max($umbraDesire, $brawlDesire) * 3 / 4 + min($umbraDesire, $brawlDesire) / 4;
    }

    public function generateGatheringDesire(ComputedPetSkills $petWithSkills): int
    {
        $pet = $petWithSkills->getPet();
        $desire = $petWithSkills->getPerception()->getTotal() + $petWithSkills->getNature()->getTotal() + $petWithSkills->getGatheringBonus()->getTotal();

        // when a pet is equipped, the equipment bonus counts twice for affecting a pet's desires
        if($pet->getTool() && $pet->getTool()->getItem()->getTool())
            $desire += $pet->getTool()->getItem()->getTool()->getNature() + $pet->getTool()->getItem()->getTool()->getGathering();

        if($petWithSkills->getPet()->hasActivityPersonality(ActivityPersonalityEnum::GATHERING))
            $desire += 4;
        else
            $desire += $this->squirrel3->rngNextInt(1, 4);

        return max(1, round($desire * (1 + $this->squirrel3->rngNextInt(-10, 10) / 100)));
    }

    public function generateClimbingBeanstalkDesire(ComputedPetSkills $petWithSkills): int
    {
        $pet = $petWithSkills->getPet();
        $desire = $petWithSkills->getStamina()->getTotal() + $petWithSkills->getNature()->getTotal() + $petWithSkills->getClimbingBonus()->getTotal();

        // when a pet is equipped, the equipment bonus counts twice for affecting a pet's desires
        if($pet->getTool() && $pet->getTool()->getItem()->getTool())
            $desire += $pet->getTool()->getItem()->getTool()->getNature() + $pet->getTool()->getItem()->getTool()->getClimbing();

        if($petWithSkills->getPet()->hasActivityPersonality(ActivityPersonalityEnum::BEANSTALK))
            $desire += 4;
        else
            $desire += $this->squirrel3->rngNextInt(1, 4);

        return max(1, round($desire * (1 + $this->squirrel3->rngNextInt(-10, 10) / 100)));
    }

    public function generateHackingDesire(ComputedPetSkills $petWithSkills): int
    {
        $pet = $petWithSkills->getPet();

        if($pet->hasStatusEffect(StatusEffectEnum::WEREFORM))
            return 0;

        $desire = $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getScience()->getTotal();

        // when a pet is equipped, the equipment bonus counts twice for affecting a pet's desires
        if($pet->getTool() && $pet->getTool()->getItem()->getTool())
            $desire += $pet->getTool()->getItem()->getTool()->getScience();

        if($petWithSkills->getPet()->hasActivityPersonality(ActivityPersonalityEnum::PROTOCOL_7))
            $desire += 4;
        else
            $desire += $this->squirrel3->rngNextInt(1, 4);

        return max(1, round($desire * (1 + $this->squirrel3->rngNextInt(-10, 10) / 100)));
    }

    public function generateProgrammingDesire(ComputedPetSkills $petWithSkills): int
    {
        $pet = $petWithSkills->getPet();

        if($pet->hasStatusEffect(StatusEffectEnum::WEREFORM))
            return 0;

        $desire = $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getScience()->getTotal();

        // when a pet is equipped, the equipment bonus counts twice for affecting a pet's desires
        if($pet->getTool() && $pet->getTool()->getItem()->getTool())
            $desire += $pet->getTool()->getItem()->getTool()->getScience();

        if($petWithSkills->getPet()->hasActivityPersonality(ActivityPersonalityEnum::CRAFTING_SCIENCE))
            $desire += 4;
        else
            $desire += $this->squirrel3->rngNextInt(1, 4);

        return max(1, round($desire * (1 + $this->squirrel3->rngNextInt(-10, 10) / 100)));
    }

    public function generatePlasticPrintingDesire(ComputedPetSkills $petWithSkills): int
    {
        $pet = $petWithSkills->getPet();

        if($pet->hasStatusEffect(StatusEffectEnum::WEREFORM))
            return 0;

        $desire = $petWithSkills->getIntelligence()->getTotal() + ceil(($petWithSkills->getScience()->getTotal() + $petWithSkills->getCrafts()->getTotal()) / 2);

        // when a pet is equipped, the equipment bonus counts twice for affecting a pet's desires
        if($pet->getTool() && $pet->getTool()->getItem()->getTool())
            $desire += $pet->getTool()->getItem()->getTool()->getScience();

        if($petWithSkills->getPet()->hasActivityPersonality(ActivityPersonalityEnum::CRAFTING_PLASTIC))
            $desire += 4;
        else
            $desire += $this->squirrel3->rngNextInt(1, 4);

        return max(1, round($desire * (1 + $this->squirrel3->rngNextInt(-10, 10) / 100)));
    }

    private function poop(Pet $pet): bool
    {
        if($pet->hasMerit(MeritEnum::BLACK_HOLE_TUM) && $this->squirrel3->rngNextInt(1, 180) === 1)
            return true;

        if($pet->getTool() && $pet->getTool()->increasesPooping() && $this->squirrel3->rngNextInt(1, 180) === 1)
            return true;

        return false;
    }

    private function dream(Pet $pet): bool
    {
        if($pet->hasMerit(MeritEnum::DREAMWALKER) && $this->squirrel3->rngNextInt(1, 200) === 1)
            return true;

        if($pet->getTool() && $pet->getTool()->isDreamcatcher() && $this->squirrel3->rngNextInt(1, 200) === 1)
            return true;

        return false;
    }

    private function cleanUpStatusEffect(Pet $pet, string $statusEffect, string $itemOnBody): bool
    {
        $changes = new PetChanges($pet);

        $pet->removeStatusEffect($pet->getStatusEffect($statusEffect));
        $weather = $this->weatherService->getWeather(new \DateTimeImmutable(), $pet);

        if($pet->hasMerit(MeritEnum::GOURMAND))
        {
            $pet
                ->increaseFood($this->squirrel3->rngNextInt(3, 6))
                ->increaseEsteem($this->squirrel3->rngNextInt(2, 4))
            ;

            $this->petExperienceService->spendTime($pet, 5, PetActivityStatEnum::OTHER, null);

            $this->responseService->createActivityLog($pet, '%pet:'. $pet->getId() . '.name% eats the ' . $itemOnBody . ' off their body in no time flat! (Ah~! A true Gourmand!)', '', $changes->compare($pet))
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Eating' ]))
            ;
            return false;
        }
        else if($weather->getRainfall() > 0)
        {
            $pet->increaseEsteem($this->squirrel3->rngNextInt(2, 4));
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:'. $pet->getId() . '.name% spends some time cleaning the ' . $itemOnBody . ' off their body. The rain made it go much faster!', '');

            $this->inventoryService->petCollectsItem($itemOnBody, $pet, $pet->getName() . ' cleaned this off their body with the help of the rain...', $activityLog);

            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(15, 30), PetActivityStatEnum::OTHER, null);

            $activityLog->setChanges($changes->compare($pet));

            return true;
        }
        else
        {
            $pet->increaseEsteem($this->squirrel3->rngNextInt(2, 4));
            $activityLog = $this->responseService->createActivityLog($pet, '%pet:'. $pet->getId() . '.name% spends some time cleaning the ' . $itemOnBody . ' off their body...', '');

            $this->inventoryService->petCollectsItem($itemOnBody, $pet, $pet->getName() . ' cleaned this off their body...', $activityLog);

            $this->petExperienceService->spendTime($pet, $this->squirrel3->rngNextInt(45, 60), PetActivityStatEnum::OTHER, null);

            $activityLog->setChanges($changes->compare($pet));

            return true;
        }
    }
}
