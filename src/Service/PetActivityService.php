<?php
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
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Enum\PetActivityStatEnum;
use App\Enum\StatusEffectEnum;
use App\Enum\UserStatEnum;
use App\Exceptions\PSPInvalidOperationException;
use App\Functions\ActivityHelpers;
use App\Functions\ArrayFunctions;
use App\Functions\ColorFunctions;
use App\Functions\InventoryModifierFunctions;
use App\Model\ComputedPetSkills;
use App\Model\FoodWithSpice;
use App\Model\PetChanges;
use App\Model\PetChangesSummary;
use App\Repository\PetActivityLogTagRepository;
use App\Repository\UserStatsRepository;
use App\Service\PetActivity\BurntForestService;
use App\Service\PetActivity\Caerbannog;
use App\Service\PetActivity\ChocolateMansion;
use App\Service\PetActivity\Crafting\MagicBindingService;
use App\Service\PetActivity\Crafting\NotReallyCraftsService;
use App\Service\PetActivity\Crafting\PlasticPrinterService;
use App\Service\PetActivity\Crafting\ProgrammingService;
use App\Service\PetActivity\Crafting\SmithingService;
use App\Service\PetActivity\CraftingService;
use App\Service\PetActivity\DeepSeaService;
use App\Service\PetActivity\DreamingService;
use App\Service\PetActivity\EatingService;
use App\Service\PetActivity\FishingService;
use App\Service\PetActivity\GatheringHolidayAdventureService;
use App\Service\PetActivity\GatheringService;
use App\Service\PetActivity\GenericAdventureService;
use App\Service\PetActivity\GivingTreeGatheringService;
use App\Service\PetActivity\GuildService;
use App\Service\PetActivity\HeartDimensionService;
use App\Service\PetActivity\HuntingService;
use App\Service\PetActivity\IcyMoonService;
use App\Service\PetActivity\LetterService;
use App\Service\PetActivity\MagicBeanstalkService;
use App\Service\PetActivity\PetSummonedAwayService;
use App\Service\PetActivity\PhilosophersStoneService;
use App\Service\PetActivity\PoopingService;
use App\Service\PetActivity\PregnancyService;
use App\Service\PetActivity\Protocol7Service;
use App\Service\PetActivity\TreasureMapService;
use App\Service\PetActivity\UmbraService;
use Doctrine\ORM\EntityManagerInterface;

class PetActivityService
{
    private EntityManagerInterface $em;
    private ResponseService $responseService;
    private FishingService $fishingService;
    private HuntingService $huntingService;
    private GatheringService $gatheringService;
    private CraftingService $craftingService;
    private MagicBindingService $magicBindingService;
    private ProgrammingService $programmingService;
    private UserStatsRepository $userStatsRepository;
    private TreasureMapService $treasureMapService;
    private GenericAdventureService $genericAdventureService;
    private Protocol7Service $protocol7Service;
    private UmbraService $umbraService;
    private PoopingService $poopingService;
    private GivingTreeGatheringService $givingTreeGatheringService;
    private PregnancyService $pregnancyService;
    private PetExperienceService $petExperienceService;
    private DreamingService $dreamingService;
    private MagicBeanstalkService $beanStalkService;
    private GatheringHolidayAdventureService $gatheringHolidayAdventureService;
    private CalendarService $calendarService;
    private HeartDimensionService $heartDimensionService;
    private GuildService $guildService;
    private InventoryService $inventoryService;
    private BurntForestService $burntForestService;
    private DeepSeaService $deepSeaService;
    private PetSummonedAwayService $petSummonedAwayService;
    private NotReallyCraftsService $notReallyCraftsService;
    private LetterService $letterService;
    private IRandom $squirrel3;
    private ChocolateMansion $chocolateMansion;
    private WeatherService $weatherService;
    private Caerbannog $caerbannog;
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
        FishingService $fishingService, HeartDimensionService $heartDimensionService, IcyMoonService $icyMoonService,
        HuntingService $huntingService, GatheringService $gatheringService, CraftingService $craftingService,
        UserStatsRepository $userStatsRepository, PetActivityLogTagRepository $petActivityLogTagRepository,
        GenericAdventureService $genericAdventureService, PetSummonedAwayService $petSummonedAwayService,
        Protocol7Service $protocol7Service, ProgrammingService $programmingService, UmbraService $umbraService,
        PoopingService $poopingService, GivingTreeGatheringService $givingTreeGatheringService,
        PregnancyService $pregnancyService, Squirrel3 $squirrel3, ChocolateMansion $chocolateMansion,
        PetExperienceService $petExperienceService, DreamingService $dreamingService,
        MagicBeanstalkService $beanStalkService, GatheringHolidayAdventureService $gatheringHolidayAdventureService,
        GuildService $guildService, BurntForestService $burntForestService, InventoryService $inventoryService,
        DeepSeaService $deepSeaService, NotReallyCraftsService $notReallyCraftsService, LetterService $letterService,
        WeatherService $weatherService, Caerbannog $caerbannog, TreasureMapService $treasureMapService,
        StatusEffectService $statusEffectService, EatingService $eatingService, HouseSimService $houseSimService,
        MagicBindingService $magicBindingService, SmithingService $smithingService, CravingService $cravingService,
        PlasticPrinterService $plasticPrinterService, PhilosophersStoneService $philosophersStoneService
    )
    {
        $this->em = $em;
        $this->squirrel3 = $squirrel3;
        $this->responseService = $responseService;
        $this->calendarService = $calendarService;
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
        $this->petExperienceService = $petExperienceService;
        $this->dreamingService = $dreamingService;
        $this->beanStalkService = $beanStalkService;
        $this->gatheringHolidayAdventureService = $gatheringHolidayAdventureService;
        $this->heartDimensionService = $heartDimensionService;
        $this->guildService = $guildService;
        $this->burntForestService = $burntForestService;
        $this->inventoryService = $inventoryService;
        $this->deepSeaService = $deepSeaService;
        $this->petSummonedAwayService = $petSummonedAwayService;
        $this->notReallyCraftsService = $notReallyCraftsService;
        $this->letterService = $letterService;
        $this->chocolateMansion = $chocolateMansion;
        $this->weatherService = $weatherService;
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

        if(!$pet->isAtHome())
            throw new \InvalidArgumentException('Trying to run activities for a pet that is not at home! (Ben did something horrible; please let him know.)');

        if($pet->getHouseTime()->getActivityTime() < 60)
            throw new \InvalidArgumentException('Trying to run activities for a pet that does not have enough time! (Ben did something horrible; please let him know.)');

        $this->responseService->setReloadPets();

        if($pet->getTool() && $pet->getTool()->canBeNibbled() && $this->squirrel3->rngNextInt(1, 10) === 1)
        {
            $changes = new PetChangesSummary();
            $changes->food = '+';

            $activityLog = $this->responseService->createActivityLog(
                $pet,
                '%pet:' . $pet->getId() . '.name% nibbled on their ' . InventoryModifierFunctions::getNameWithModifiers($pet->getTool()) . '.',
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

            if($pet->hasMerit(MeritEnum::IRON_STOMACH))
            {
                if($this->squirrel3->rngNextInt(1, 2) === 1)
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
                if($this->squirrel3->rngNextInt(1, 4) === 1)
                    $pet->increasePoison(1);
            }
            else
            {
                if($this->squirrel3->rngNextInt(1, 2) === 1)
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
            if($this->cleanUpStatusEffect($pet, StatusEffectEnum::BUBBLEGUMD, 'Bubblegum'))
                return;
        }

        if($pet->hasStatusEffect(StatusEffectEnum::GOBBLE_GOBBLE) && $this->squirrel3->rngNextInt(1, 2) === 1)
        {
            $changes = new PetChanges($pet);
            $activityLog = $this->huntingService->huntedTurkeyDragon($petWithSkills);
            $activityLog->setChanges($changes->compare($pet));
            return;
        }

        if($pet->hasStatusEffect(StatusEffectEnum::LAPINE_WHISPERS) && $this->squirrel3->rngNextInt(1, 2) === 1)
        {
            $changes = new PetChanges($pet);
            $activityLog = $this->umbraService->speakToBunnySpirit($pet);
            $activityLog->setChanges($changes->compare($pet));
            $pet->removeStatusEffect($pet->getStatusEffect(StatusEffectEnum::LAPINE_WHISPERS));
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

        if($this->maybeReceiveAthenasGift($pet) || $this->maybeReceiveFairyGodmotherItem($pet))
            return;

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

                if($activityLog->getChanges()->containsLevelUp())
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

        if($this->squirrel3->rngNextInt(1, 100) <= ($hasEventPersonality ? 9 : 6) && $this->calendarService->isChineseNewYear())
        {
            $this->gatheringHolidayAdventureService->adventure($petWithSkills, GatheringHolidayEnum::CHINESE_NEW_YEAR);
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

                break;

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

                break;

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

                break;

            case 'Saucepan':
                if($this->squirrel3->rngNextInt(1, 10) === 1)
                {
                    $this->treasureMapService->doCookSomething($pet);
                    return true;
                }

                break;

            case 'Diffie-H Key':
                $this->treasureMapService->doUseDiffieHKey($pet);
                return true;

            case 'Aubergine Commander':
                if($this->squirrel3->rngNextInt(1, 100) === 1)
                {
                    $this->treasureMapService->doEggplantCurse($pet);
                    return true;
                }
                break;

            case 'Chocolate Key':
                if($this->squirrel3->rngNextInt(1, 4) === 1)
                {
                    $this->chocolateMansion->adventure($petWithSkills);
                    return true;
                }
                break;

            case 'Carrot Key':
                if($this->squirrel3->rngNextInt(1, 4) === 1)
                {
                    $this->caerbannog->adventure($petWithSkills);
                    return true;
                }
                break;

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
                break;

            case 'Ceremony of Fire':
                if($this->squirrel3->rngNextInt(1, 20) == 1)
                {
                    $this->philosophersStoneService->seekVesicaHydrargyrum($petWithSkills);
                    return true;
                }
                break;

            case 'Snickerblade':
                if($this->squirrel3->rngNextInt(1, 20) == 1)
                {
                    $this->philosophersStoneService->seekEarthsEgg($petWithSkills);
                    return true;
                }
                break;

            case 'Fruit Fly on a String':
                $this->treasureMapService->doFruitHunting($pet);
                return true;
        }

        if($pet->getTool()->getEnchantment())
        {
            switch($pet->getTool()->getEnchantment()->getName())
            {
                case 'Searing':
                    if($this->squirrel3->rngNextInt(1, 20) == 1)
                    {
                        if($this->philosophersStoneService->seekMerkabaOfAir($petWithSkills))
                            return true;
                    }
                    break;
            }
        }

        return false;
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

    private function maybeReceiveFairyGodmotherItem(Pet $pet): bool
    {
        if(!$pet->hasMerit(MeritEnum::FAIRY_GODMOTHER))
            return false;

        if($this->squirrel3->rngNextInt(1, 650) !== 1)
            return false;

        $randomChat = $this->squirrel3->rngNextFromArray([
            'In the face of darkness, remember that your light shines brightest.',
            'Embrace your uniqueness, for it is the key to unlocking your dreams.',
            'Believe in yourself, my dear, for magic lies within your heart.',
            'The power of imagination will lead you to realms where dreams come true.',
            'Let kindness be your wand, and you\'ll create wonders wherever you go.',
            'Never underestimate the strength of a kind heart, for it can move mountains.',
            'In the garden of life, cultivate gratitude, and watch your blessings bloom.',
            'Every day is a new page in the book of your adventures; write it with joy and wonder.',
            'In every challenge, there lies a hidden spell of growth and wisdom.',
        ]);

        $randomGoodie = $this->squirrel3->rngNextFromArray([

        ]);
    }

    private function maybeReceiveAthenasGift(Pet $pet): bool
    {
        if(!$pet->hasMerit(MeritEnum::ATHENAS_GIFTS))
            return false;

        if($this->squirrel3->rngNextInt(1, 300) !== 1)
            return false;

        $randomExclamation = $this->squirrel3->rngNextFromArray([
            'Neat-o!', 'Rad!', 'Dope!', 'Sweet!', 'Hot diggity!', 'Epic!', 'Let\'s go!',
        ]);

        $activityLog = $this->responseService->createActivityLog($pet, ActivityHelpers::PetName($pet) . ' was thinking about what to do, when they spotted a Handicrafts Supply Box nearby! (Athena\'s Gifts! ' . $randomExclamation . ')', '')
            ->addInterestingness(PetActivityLogInterestingnessEnum::RARE_ACTIVITY)
        ;

        $this->inventoryService->petCollectsItem('Handicrafts Supply Box', $pet, $pet->getName() . ' received this - a gift from the gods!', $activityLog);
        $this->petExperienceService->spendTime($pet, 30, PetActivityStatEnum::OTHER, null);

        return true;
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
