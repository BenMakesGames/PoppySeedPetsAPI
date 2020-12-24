<?php
namespace App\Service;

use App\Entity\GreenhousePlant;
use App\Entity\Inventory;
use App\Entity\LunchboxItem;
use App\Entity\Merit;
use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Entity\PetBaby;
use App\Entity\PetGroup;
use App\Entity\PetRelationship;
use App\Enum\EnumInvalidValueException;
use App\Enum\FlavorEnum;
use App\Enum\LocationEnum;
use App\Enum\MeritEnum;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Enum\PetActivityStatEnum;
use App\Enum\PetSkillEnum;
use App\Enum\SocialTimeWantEnum;
use App\Enum\SpiritCompanionStarEnum;
use App\Enum\StatusEffectEnum;
use App\Enum\UserStatEnum;
use App\Functions\ArrayFunctions;
use App\Functions\ColorFunctions;
use App\Model\ComputedPetSkills;
use App\Model\FoodWithSpice;
use App\Model\FortuneCookie;
use App\Model\PetChanges;
use App\Model\PetChangesSummary;
use App\Repository\InventoryRepository;
use App\Repository\PetRelationshipRepository;
use App\Repository\PetRepository;
use App\Repository\UserStatsRepository;
use App\Service\PetActivity\BurntForestService;
use App\Service\PetActivity\Crafting\NotReallyCraftsService;
use App\Service\PetActivity\CraftingService;
use App\Service\PetActivity\DeepSeaService;
use App\Service\PetActivity\DreamingService;
use App\Service\PetActivity\EasterEggHuntingService;
use App\Service\PetActivity\FishingService;
use App\Service\PetActivity\GatheringService;
use App\Service\PetActivity\GenericAdventureService;
use App\Service\PetActivity\GivingTreeGatheringService;
use App\Service\PetActivity\GuildService;
use App\Service\PetActivity\HeartDimensionService;
use App\Service\PetActivity\HuntingService;
use App\Service\PetActivity\LetterService;
use App\Service\PetActivity\MagicBeanstalkService;
use App\Service\PetActivity\PetSummonedAwayService;
use App\Service\PetActivity\PoopingService;
use App\Service\PetActivity\PregnancyService;
use App\Service\PetActivity\Crafting\ProgrammingService;
use App\Service\PetActivity\Protocol7Service;
use App\Service\PetActivity\TreasureMapService;
use App\Service\PetActivity\UmbraService;
use Doctrine\ORM\EntityManagerInterface;

class PetService
{
    private $em;
    private $petRepository;
    private $responseService;
    private $petRelationshipService;
    private $fishingService;
    private $huntingService;
    private $gatheringService;
    private $craftingService;
    private $programmingService;
    private $userStatsRepository;
    private $inventoryRepository;
    private $treasureMapService;
    private $genericAdventureService;
    private $protocol7Service;
    private $umbraService;
    private $poopingService;
    private $givingTreeGatheringService;
    private $pregnancyService;
    private $petActivityStatsService;
    private $petGroupService;
    private $petExperienceService;
    private $dreamingService;
    private $beanStalkService;
    private $easterEggHuntingService;
    private $calendarService;
    private $heartDimensionService;
    private $petRelationshipRepository;
    private $guildService;
    private $inventoryService;
    private $burntForestService;
    private $deepSeaService;
    private $petSummonedAwayService;
    private $toolBonusService;
    private $notReallyCraftsService;
    private $letterService;

    public function __construct(
        EntityManagerInterface $em, ResponseService $responseService, CalendarService $calendarService,
        PetRelationshipService $petRelationshipService, PetRepository $petRepository, FishingService $fishingService,
        HuntingService $huntingService, GatheringService $gatheringService, CraftingService $craftingService,
        UserStatsRepository $userStatsRepository, InventoryRepository $inventoryRepository,
        TreasureMapService $treasureMapService, GenericAdventureService $genericAdventureService,
        Protocol7Service $protocol7Service, ProgrammingService $programmingService, UmbraService $umbraService,
        PoopingService $poopingService, GivingTreeGatheringService $givingTreeGatheringService,
        PregnancyService $pregnancyService, PetActivityStatsService $petActivityStatsService,
        PetGroupService $petGroupService, PetExperienceService $petExperienceService, DreamingService $dreamingService,
        MagicBeanstalkService $beanStalkService, EasterEggHuntingService $easterEggHuntingService,
        HeartDimensionService $heartDimensionService, PetRelationshipRepository $petRelationshipRepository,
        GuildService $guildService, InventoryService $inventoryService, BurntForestService $burntForestService,
        DeepSeaService $deepSeaService, NotReallyCraftsService $notReallyCraftsService, LetterService $letterService,
        PetSummonedAwayService $petSummonedAwayService, InventoryModifierService $toolBonusService
    )
    {
        $this->em = $em;
        $this->petRepository = $petRepository;
        $this->responseService = $responseService;
        $this->calendarService = $calendarService;
        $this->petRelationshipService = $petRelationshipService;
        $this->fishingService = $fishingService;
        $this->huntingService = $huntingService;
        $this->gatheringService = $gatheringService;
        $this->craftingService = $craftingService;
        $this->userStatsRepository = $userStatsRepository;
        $this->inventoryRepository = $inventoryRepository;
        $this->treasureMapService = $treasureMapService;
        $this->genericAdventureService = $genericAdventureService;
        $this->protocol7Service = $protocol7Service;
        $this->programmingService = $programmingService;
        $this->umbraService = $umbraService;
        $this->poopingService = $poopingService;
        $this->givingTreeGatheringService = $givingTreeGatheringService;
        $this->pregnancyService = $pregnancyService;
        $this->petActivityStatsService = $petActivityStatsService;
        $this->petGroupService = $petGroupService;
        $this->petExperienceService = $petExperienceService;
        $this->dreamingService = $dreamingService;
        $this->beanStalkService = $beanStalkService;
        $this->easterEggHuntingService = $easterEggHuntingService;
        $this->heartDimensionService = $heartDimensionService;
        $this->petRelationshipRepository = $petRelationshipRepository;
        $this->guildService = $guildService;
        $this->inventoryService = $inventoryService;
        $this->burntForestService = $burntForestService;
        $this->deepSeaService = $deepSeaService;
        $this->petSummonedAwayService = $petSummonedAwayService;
        $this->toolBonusService = $toolBonusService;
        $this->notReallyCraftsService = $notReallyCraftsService;
        $this->letterService = $letterService;
    }

    /**
     * @param Pet $pet
     * @param int $points
     */
    public function gainAffection(Pet $pet, int $points)
    {
        if($points === 0) return;

        $divideBy = 1;

        if($pet->getFood() + $pet->getAlcohol() < 0) $divideBy++;
        if($pet->getSafety() + $pet->getAlcohol() < 0) $divideBy++;

        $points = ceil($points / $divideBy);

        if($points === 0) return;

        $previousAffectionLevel = $pet->getAffectionLevel();

        $pet->increaseAffectionPoints($points);

        // if a pet's affection level increased, and you haven't unlocked the park, now you get the park!
        if($pet->getAffectionLevel() > $previousAffectionLevel && $pet->getOwner()->getUnlockedPark() === null)
            $pet->getOwner()->setUnlockedPark();
    }

    public function doPet(Pet $pet)
    {
        if($pet->getInDaycare()) throw new \InvalidArgumentException('Pets in daycare cannot be interacted with.');

        $now = new \DateTimeImmutable();

        $changes = new PetChanges($pet);

        if($pet->getLastInteracted() < $now->modify('-48 hours'))
        {
            $pet->setLastInteracted($now->modify('-20 hours'));
            $pet->increaseSafety(15);
            $pet->increaseLove(15);
            $this->gainAffection($pet, 10);
        }
        else if($pet->getLastInteracted() < $now->modify('-20 hours'))
        {
            $pet->setLastInteracted($now->modify('-4 hours'));
            $pet->increaseSafety(10);
            $pet->increaseLove(10);
            $this->gainAffection($pet, 5);
        }
        else if($pet->getLastInteracted() < $now->modify('-4 hours'))
        {
            $pet->setLastInteracted($now);
            $pet->increaseSafety(7);
            $pet->increaseLove(7);
            $this->gainAffection($pet, 1);
        }
        else
            throw new \InvalidArgumentException('You\'ve already interacted with this pet recently.');

        $this->responseService->createActivityLog($pet, '%user:' . $pet->getOwner()->getId() . '.Name% pet ' . '%pet:' . $pet->getId() . '.name%'. '.', 'ui/affection', $changes->compare($pet));
        $this->userStatsRepository->incrementStat($pet->getOwner(), UserStatEnum::PETTED_A_PET);
    }

    public function doPraise(Pet $pet)
    {
        if($pet->getInDaycare()) throw new \InvalidArgumentException('Pets in daycare cannot be interacted with.');

        $now = new \DateTimeImmutable();

        $changes = new PetChanges($pet);

        if($pet->getLastInteracted() < $now->modify('-48 hours'))
        {
            $pet->setLastInteracted($now->modify('-20 hours'));
            $pet->increaseLove(15);
            $pet->increaseEsteem(15);
            $this->gainAffection($pet, 10);
        }
        else if($pet->getLastInteracted() < $now->modify('-20 hours'))
        {
            $pet->setLastInteracted($now->modify('-4 hours'));
            $pet->increaseLove(10);
            $pet->increaseEsteem(10);
            $this->gainAffection($pet, 5);
        }
        else if($pet->getLastInteracted() < $now->modify('-4 hours'))
        {
            $pet->setLastInteracted($now);
            $pet->increaseLove(7);
            $pet->increaseEsteem(7);
            $this->gainAffection($pet, 1);
        }
        else
            throw new \InvalidArgumentException('You\'ve already interacted with this pet recently.');

        $this->responseService->createActivityLog($pet, '%user:' . $pet->getOwner()->getId() . '.Name% praised ' . '%pet:' . $pet->getId() . '.name%'. '.', 'ui/affection', $changes->compare($pet));
        $this->userStatsRepository->incrementStat($pet->getOwner(), UserStatEnum::PRAISED_A_PET);
    }

    /**
     * @param Pet $pet
     * @param Inventory[] $inventory
     * @return PetActivityLog
     * @throws EnumInvalidValueException
     */
    public function doFeed(Pet $pet, array $inventory): PetActivityLog
    {
        if($pet->getInDaycare()) throw new \InvalidArgumentException('Pets in daycare cannot be interacted with.');

        if(ArrayFunctions::any($inventory, function(Inventory $i) { return $i->getItem()->getFood() === null; }))
            throw new \InvalidArgumentException('At least one of the items selected is not edible!');

        shuffle($inventory);

        $petChanges = new PetChanges($pet);
        $foodsEaten = [];
        /** @var FoodWithSpice[] $favorites */ $favorites = [];
        $tooFull = [];
        $tooPoisonous = [];
        $ateAFortuneCookie = false;

        foreach($inventory as $i)
        {
            $food = new FoodWithSpice($i->getItem(), $i->getSpice());

            $itemName = $food->name;

            if($pet->getJunk() + $pet->getFood() >= $pet->getStomachSize())
            {
                $tooFull[] = $itemName;
                continue;
            }

            if($pet->wantsSobriety() && ($food->alcohol > 0 || $food->caffeine > 0 || $food->psychedelic > 0))
            {
                $tooPoisonous[] = $itemName;
                continue;
            }

            $this->inventoryService->applyFoodEffects($pet, $food);

            // consider favorite flavor:
            if(!FlavorEnum::isAValue($pet->getFavoriteFlavor()))
                throw new EnumInvalidValueException(FlavorEnum::class, $pet->getFavoriteFlavor());

            $randomFlavor = $food->randomFlavor > 0 ? FlavorEnum::getRandomValue() : null;

            $favoriteFlavorStrength = $this->inventoryService->getFavoriteFlavorStrength($pet, $food, $randomFlavor);

            $loveAndEsteemGain = $favoriteFlavorStrength + $food->love;

            $pet
                ->increaseLove($loveAndEsteemGain)
                ->increaseEsteem($loveAndEsteemGain)
            ;

            if($favoriteFlavorStrength > 0)
            {
                $this->gainAffection($pet, $favoriteFlavorStrength);

                $favorites[] = $food;
            }

            $this->em->remove($i);

            if($randomFlavor)
                $foodsEaten[] = $itemName . ' (ooh! ' . $randomFlavor . '!)';
            else
                $foodsEaten[] = $itemName;

            if($itemName === 'Fortune Cookie')
                $ateAFortuneCookie = true;
        }

        // gain safety & affection equal to 1/8 food gained, when hand-fed
        $foodGained = $pet->getFood() - $petChanges->food;

        if($foodGained > 0)
        {
            $remainder = $foodGained % 8;
            $gain = floor($foodGained / 8);

            if ($remainder > 0 && mt_rand(1, 8) <= $remainder)
                $gain++;

            $pet->increaseSafety($gain);
            $this->gainAffection($pet, $gain);

            if($pet->getPregnancy())
                $pet->getPregnancy()->increaseAffection($gain);

            $this->userStatsRepository->incrementStat($pet->getOwner(), UserStatEnum::FOOD_HOURS_FED_TO_PETS, $foodGained);
        }

        if(count($foodsEaten) > 0)
        {
            $message = 'You fed ' . $pet->getName() . ' ' . ArrayFunctions::list_nice($foodsEaten) . '.';
            $icon = 'icons/activity-logs/mangia';

            if(count($favorites) > 0)
            {
                $icon = 'ui/affection';
                $message .= ' ' . $pet->getName() . ' really liked the ' . ArrayFunctions::pick_one($favorites)->name . '!';
            }

            if($ateAFortuneCookie)
            {
                $message .= ' "' . ArrayFunctions::pick_one(FortuneCookie::MESSAGES) . '"';
                if(mt_rand(1, 20) === 1 && $pet->getOwner()->getUnlockedGreenhouse())
                {
                    $message .= ' ... in bed!';

                    if(mt_rand(1, 5) === 1)
                        $message .= ' XD';
                }
            }

            return $this->responseService->createActivityLog($pet, $message, $icon, $petChanges->compare($pet));
        }
        else
        {
            if(count($tooPoisonous) > 0)
                return $this->responseService->createActivityLog($pet, '%user:' . $pet->getOwner()->getId() . '.Name% tried to feed ' . '%pet:' . $pet->getId() . '.name%, but ' . ArrayFunctions::pick_one($tooPoisonous) . ' really isn\'t appealing right now.', '');
            else
                return $this->responseService->createActivityLog($pet, '%user:' . $pet->getOwner()->getId() . '.Name% tried to feed ' . '%pet:' . $pet->getId() . '.name%, but they\'re too full to eat anymore.', '');
        }
    }

    public function runHour(Pet $pet)
    {
        if($pet->getInDaycare())
            throw new \InvalidArgumentException('Pets in daycare cannot be interacted with.');

        if($pet->getHouseTime()->getActivityTime() < 60)
            throw new \InvalidArgumentException('Pet does not have enough Time. (Ben did something horrible; please let him know.)');

        $this->responseService->setReloadPets();

        if($pet->getTool() && $pet->getTool()->canBeNibbled() && mt_rand(1, 10) === 1)
        {
            $changes = new PetChangesSummary();
            $changes->food = '+';

            $this->responseService->createActivityLog(
                $pet,
                $pet->getName() . ' nibbled on their ' . $this->toolBonusService->getNameWithModifiers($pet->getTool()) . '.',
                '',
                $changes
            );
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

            if(mt_rand(1, 2) === 1)
                $pet->increasePoison(1);
        }

        if($pet->getPsychedelic() > 0)
        {
            $pet->increasePsychedelic(-1);
            $pet->increasePoison(2);
        }

        $safetyRestingPoint = $pet->hasMerit(MeritEnum::NOTHING_TO_FEAR) ? 8 : 0;

        if($pet->getSafety() > $safetyRestingPoint && mt_rand(1, 2) === 1)
            $pet->increaseSafety(-1);
        else if($pet->getSafety() < $safetyRestingPoint)
            $pet->increaseSafety(1);

        $loveRestingPoint = $pet->hasMerit(MeritEnum::EVERLASTING_LOVE) ? 8 : 0;

        if($pet->getLove() > $loveRestingPoint && mt_rand(1, 2) === 1)
            $pet->increaseLove(-1);
        else if($pet->getLove() < $loveRestingPoint && mt_rand(1, 2) === 1)
            $pet->increaseLove(1);

        $esteemRestingPoint = $pet->hasMerit(MeritEnum::NEVER_EMBARRASSED) ? 8 : 0;

        if($pet->getEsteem() > $esteemRestingPoint)
            $pet->increaseEsteem(-1);
        else if($pet->getEsteem() < $esteemRestingPoint && mt_rand(1, 2) === 1)
            $pet->increaseEsteem(1);

        $pregnancy = $pet->getPregnancy();

        if($pregnancy)
        {
            if($pet->getFood() < 0) $pregnancy->increaseAffection(-1);
            if($pet->getSafety() < 0 && mt_rand(1, 2) === 1) $pregnancy->increaseAffection(-1);
            if($pet->getLove() < 0 && mt_rand(1, 3) === 1) $pregnancy->increaseAffection(-1);
            if($pet->getEsteem() < 0 && mt_rand(1, 4) === 1) $pregnancy->increaseAffection(-1);

            if($pregnancy->getGrowth() >= PetBaby::PREGNANCY_DURATION)
            {
                $this->pregnancyService->giveBirth($pet);
                return;
            }
        }

        if($pet->getPoison() > 0)
        {
            if(mt_rand(6, 24) < $pet->getPoison())
            {
                $changes = new PetChanges($pet);

                $safetyVom = ceil($pet->getPoison() / 4);

                $pet->increasePoison(-mt_rand( ceil($pet->getPoison() / 4), ceil($pet->getPoison() * 3 / 4)));
                if($pet->getAlcohol() > 0) $pet->increaseAlcohol(-mt_rand(1, ceil($pet->getAlcohol() / 2)));
                if($pet->getPsychedelic() > 0) $pet->increasePsychedelic(-mt_rand(1, ceil($pet->getPsychedelic() / 2)));
                if($pet->getCaffeine() > 0) $pet->increaseFood(-mt_rand(1, ceil($pet->getCaffeine() / 2)));
                if($pet->getJunk() > 0) $pet->increaseJunk(-mt_rand(1, ceil($pet->getJunk() / 2)));
                if($pet->getFood() > 0) $pet->increaseFood(-mt_rand(1, ceil($pet->getFood() / 2)));

                $pet->increaseSafety(-mt_rand(1, $safetyVom));
                $pet->increaseEsteem(-mt_rand(1, $safetyVom));

                $this->petExperienceService->spendTime($pet, mt_rand(15, 30), PetActivityStatEnum::OTHER, null);

                $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% threw up :(', '', $changes->compare($pet));

                return;
            }
        }

        if($this->poop($pet))
        {
            $this->poopingService->poopDarkMatter($pet);
        }

        if($pet->hasMerit(MeritEnum::SHEDS) && mt_rand(1, 180) === 1)
        {
            $this->poopingService->shed($pet);
        }

        if($pet->hasMerit(MeritEnum::HYPERCHROMATIC))
        {
            if(mt_rand(1, 250) === 1)
            {
                $pet
                    ->setColorA(ColorFunctions::RGB2Hex(mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255)))
                    ->setColorB(ColorFunctions::RGB2Hex(mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255)))
                ;
            }
            else
            {
                $pet
                    ->setColorA(ColorFunctions::tweakColor($pet->getColorA(), 4))
                    ->setColorB(ColorFunctions::tweakColor($pet->getColorB(), 4))
                ;
            }
        }

        $petWithSkills = $pet->getComputedSkills();

        if(mt_rand(1, 4000) === 1)
        {
            $activityLog = $this->petSummonedAwayService->adventure($petWithSkills);

            if($activityLog)
                return;
        }

        $hunger = mt_rand(0, 4);

        if($pet->getFood() < $hunger && count($pet->getLunchboxItems()) > 0)
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

                $aValue = $this->inventoryService->getFavoriteFlavorStrength($pet, $aFood) + $aFood->love;
                $bValue = $this->inventoryService->getFavoriteFlavorStrength($pet, $bFood) + $bFood->love;

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

                $ateIt = $this->inventoryService->doEat($pet, $food, null);

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

                $message = $pet->getName() . ' ate ' . ArrayFunctions::list_nice($namesOfItemsEaten) . ' out of their lunchbox.';

                if(count($namesOfItemsSkipped) > 0)
                    $message .= ' (' . ArrayFunctions::list_nice($namesOfItemsSkipped) . ' really isn\'t appealing right now, though.)';
            }
            else
            {
                // none were eaten, but ew know the lunchbox has items in it, therefore items were skipped!
                $message = $pet->getName() . ' looked in their lunchbox for something to eat, but ' . ArrayFunctions::list_nice($namesOfItemsSkipped) . ' really isn\'t appealing right now.';
            }

            if($itemsLeftInLunchbox === 0)
                $message .= ' Their lunchbox is now empty!';

            $this->responseService->createActivityLog($pet, $message, 'icons/activity-logs/lunchbox', $petChanges->compare($pet))
                ->addInterestingness($itemsLeftInLunchbox === 0 ? PetActivityLogInterestingnessEnum::LUNCHBOX_EMPTY : 1)
            ;
        }

        if($pet->hasStatusEffect(StatusEffectEnum::GOBBLE_GOBBLE) && mt_rand(1, 2) === 1)
        {
            $changes = new PetChanges($pet);
            $activityLog = $this->huntingService->huntedTurkeyDragon($petWithSkills);
            $activityLog->setChanges($changes->compare($pet));
            return;
        }
        else if($pet->hasStatusEffect(StatusEffectEnum::ONEIRIC) && mt_rand(1, 2) === 1)
        {
            $this->dreamingService->dream($pet);
            $pet->removeStatusEffect($pet->getStatusEffect(StatusEffectEnum::ONEIRIC));
            return;
        }
        else if($this->dream($pet))
        {
            $this->dreamingService->dream($pet);
            return;
        }

        $itemsInHouse = (int)$this->inventoryRepository->countItemsInLocation($pet->getOwner(), LocationEnum::HOME);

        $quantities = $this->inventoryRepository->getInventoryQuantities($pet->getOwner(), LocationEnum::HOME, 'name');

        $craftingPossibilities = $this->craftingService->getCraftingPossibilities($petWithSkills, $quantities);
        $programmingPossibilities = $this->programmingService->getCraftingPossibilities($petWithSkills, $quantities);
        $notCraftingPossibilities = $this->notReallyCraftsService->getCraftingPossibilities($petWithSkills, $quantities);

        $houseTooFull = mt_rand(1, 10) > $pet->getOwner()->getMaxInventory() - $itemsInHouse;

        if($houseTooFull)
        {
            if($itemsInHouse >= $pet->getOwner()->getMaxInventory())
                $description = 'The house is crazy-full.';
            else
                $description = 'The house is getting pretty full.';

            if(count($craftingPossibilities) === 0 && count($programmingPossibilities) === 0 && count($notCraftingPossibilities) === 0)
            {
                $this->petExperienceService->spendTime($pet, mt_rand(45, 60), PetActivityStatEnum::OTHER, null);

                $this->responseService->createActivityLog($pet, $description . ' ' . $pet->getName() . ' wanted to make something, but couldn\'t find any materials to work with.', 'icons/activity-logs/house-too-full');
            }
            else
            {
                $possibilities = [];

                if(count($craftingPossibilities) > 0) $possibilities[] = [ $this->craftingService, $craftingPossibilities ];
                if(count($programmingPossibilities) > 0) $possibilities[] = [ $this->programmingService, $programmingPossibilities ];
                if(count($notCraftingPossibilities) > 0) $possibilities[] = [ $this->notReallyCraftsService, $notCraftingPossibilities ];

                $do = ArrayFunctions::pick_one($possibilities);

                /** @var PetActivityLog $activityLog */
                $activityLog = $do[0]->adventure($petWithSkills, $do[1]);
                $activityLog->setEntry($description . ' ' . $activityLog->getEntry());
            }

            return;
        }

        if(true)//mt_rand(1, 50) === 1)
        {
            if($this->letterService->adventure($petWithSkills))
                return;

            $this->genericAdventureService->adventure($petWithSkills);
            return;
        }

        if($pet->getTool())
        {
            if($this->doTreasureMapAdventure($petWithSkills))
                return;
        }

        if(mt_rand(1, 50) === 1)
        {
            $activityLog = $this->givingTreeGatheringService->gatherFromGivingTree($pet);
            if($activityLog)
                return;
        }

        if(mt_rand(1, 4) === 1 && $this->calendarService->isEaster())
        {
            $this->easterEggHuntingService->adventure($petWithSkills);
            return;
        }

        if($pet->getGuildMembership() && mt_rand(1, 35) === 1)
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

        if(array_key_exists('Submarine', $quantities))
            $petDesires['submarine'] = $this->generateSubmarineDesire($petWithSkills);

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
        if(count($programmingPossibilities) > 0) $petDesires['program'] = $this->generateProgrammingDesire($petWithSkills);
        if(count($notCraftingPossibilities) > 0) $petDesires['notCrafting'] = $this->generateGatheringDesire($petWithSkills);

        $desire = $this->pickDesire($petDesires);

        switch($desire)
        {
            case 'fish': $this->fishingService->adventure($petWithSkills); break;
            case 'hunt': $this->huntingService->adventure($petWithSkills); break;
            case 'gather': $this->gatheringService->adventure($petWithSkills); break;
            case 'craft': $this->craftingService->adventure($petWithSkills, $craftingPossibilities); break;
            case 'program': $this->programmingService->adventure($petWithSkills, $programmingPossibilities); break;
            case 'notCrafting': $this->notReallyCraftsService->adventure($petWithSkills, $notCraftingPossibilities); break;
            case 'hack': $this->protocol7Service->adventure($petWithSkills); break;
            case 'umbra': $this->umbraService->adventure($petWithSkills); break;
            case 'beanStalk': $this->beanStalkService->adventure($petWithSkills); break;
            case 'burntForest': $this->burntForestService->adventure($petWithSkills); break;
            case 'submarine': $this->deepSeaService->adventure($petWithSkills); break;
            default: $this->doNothing($pet); break;
        }
    }

    private function doTreasureMapadventure(ComputedPetSkills $petWithSkills): bool
    {
        $pet = $petWithSkills->getPet();

        switch($pet->getTool()->getItem()->getName())
        {
            case 'Cetgueli\'s Treasure Map':
                $this->treasureMapService->doCetguelisTreasureMap($petWithSkills);
                return true;

            case 'Silver Keyblade':
            case 'Gold Keyblade':
                if($pet->getFood() > 0 && mt_rand(1, 10) === 1)
                {
                    $this->treasureMapService->doKeybladeTower($petWithSkills);
                    return true;
                }

                return false;

            case 'Rainbow Dolphin Plushy':
            case 'Sneqo Plushy':
            case 'Bulbun Plushy':
            case 'Peacock Plushy':
                if(mt_rand(1, 6) === 1 || $this->userStatsRepository->getStatValue($pet->getOwner(), UserStatEnum::TRADED_WITH_THE_FLUFFMONGER) === 0)
                {
                    $this->treasureMapService->doFluffmongerTrade($pet);
                    return true;
                }

                return false;

            case '"Gold" Idol':
                $this->treasureMapService->doGoldIdol($pet);
                return true;

            case 'Heartstone':
                if(mt_rand(1, 3) === 1)
                {
                    if($this->heartDimensionService->canAdventure($pet))
                        $this->heartDimensionService->adventure($petWithSkills);
                    else
                        $this->heartDimensionService->noAdventuresRemaining($pet);

                    return true;
                }

                return false;

            case 'Saucepan':
                if(mt_rand(1, 10) === 1)
                {
                    $this->treasureMapService->doCookSomething($pet);
                    return true;
                }

                return false;

            case 'Diffie-H Key':
                $this->treasureMapService->doUseDiffieHKey($pet);
                return true;

            case 'Aubergine Commander':
                if(mt_rand(1, 100) === 1)
                {
                    $this->treasureMapService->doEggplantCurse($pet);
                    return true;
                }
                return false;
        }

        return false;
    }

    public function runSocialTime(Pet $pet): bool
    {
        $pet->getHouseTime()->setLastSocialHangoutAttempt();

        if($pet->getFood() + $pet->getAlcohol() + $pet->getJunk() < 0)
            return false;

        if($this->meetRoommates($pet))
        {
            $this->petExperienceService->spendSocialEnergy($pet, PetExperienceService::SOCIAL_ENERGY_PER_HANG_OUT);
            return true;
        }

        $wants = [];

        $wants[] = [ 'activity' => SocialTimeWantEnum::HANG_OUT, 'weight' => 60 ];

        $availableGroups = $pet->getGroups()->filter(function(PetGroup $g) {
            return $g->getSocialEnergy() >= PetGroupService::SOCIAL_ENERGY_PER_MEET;
        });

        if(count($availableGroups) > 0)
            $wants[] = [ 'activity' => SocialTimeWantEnum::GROUP, 'weight' => 30 ];

        if(count($pet->getGroups()) < $pet->getMaximumGroups())
            $wants[] = [ 'activity' => SocialTimeWantEnum::CREATE_GROUP, 'weight' => 5 ];

        while(count($wants) > 0)
        {
            $want = ArrayFunctions::pick_one_weighted($wants, function($want) {
                return $want['weight'];
            });

            $activity = $want['activity'];

            $wants = array_filter($wants, function($want) use($activity) {
                return $want['activity'] !== $activity;
            });

            switch($activity)
            {
                case SocialTimeWantEnum::HANG_OUT:
                    if($this->hangOutWithFriend($pet))
                        return true;
                    break;

                case SocialTimeWantEnum::GROUP:
                    $this->petGroupService->doGroupActivity(ArrayFunctions::pick_one($availableGroups->toArray()));
                    return true;

                case SocialTimeWantEnum::CREATE_GROUP:
                    if($this->petGroupService->createGroup($pet) !== null)
                        return true;
                    break;
            }
        }

        return false;
    }

    private function hangOutWithFriend(Pet $pet): bool
    {
        $relationships = $this->petRelationshipRepository->getRelationshipsToHangOutWith($pet);

        $spiritCompanionAvailable = $pet->hasMerit(MeritEnum::SPIRIT_COMPANION) && ($pet->getSpiritCompanion()->getLastHangOut() === null || $pet->getSpiritCompanion()->getLastHangOut() < (new \DateTimeImmutable())->modify('-12 hours'));

        // no friends available? no spirit companion? GIT OUTTA' HE'E!
        if(count($relationships) === 0 && !$spiritCompanionAvailable)
            return false;

        // maybe hang out with a spirit companion, if you have one
        if($spiritCompanionAvailable && (count($relationships) === 0 || mt_rand(1, count($relationships) + 1) === 1))
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

        $skipped = mt_rand(0, 5);

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
            'pet' => array_map(function(PetRelationship $r) { return $r->getRelationship(); }, $relationships),
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

            return mt_rand(0, 999) < $chanceToHangOut;
        });

        if(count($relationships) === 0)
            return null;

        return ArrayFunctions::pick_one_weighted($relationships, function(PetRelationship $r) {
            return $r->getCommitment() + 1;
        });
    }

    private function hangOutWithSpiritCompanion(Pet $pet)
    {
        $changes = new PetChanges($pet);

        $this->petExperienceService->spendSocialEnergy($pet, PetExperienceService::SOCIAL_ENERGY_PER_HANG_OUT);

        $companion = $pet->getSpiritCompanion();

        $companion->setLastHangOut();

        $adjectives = [ 'bizarre', 'impressive', 'surprisingly-graphic', 'whirlwind' ];

        if(mt_rand(1, 3) !== 1 || ($pet->getSafety() > 0 && $pet->getLove() > 0 && $pet->getEsteem() > 0))
        {
            $teachingStat = null;

            switch($companion->getStar())
            {
                case SpiritCompanionStarEnum::ALTAIR:
                    // the flying/fighting eagle
                    if(mt_rand(1, 3) === 1)
                    {
                        $teachingStat = PetSkillEnum::BRAWL;
                        $message = $pet->getName() . ' practiced hunting with ' . $companion->getName() . '!';
                    }
                    else
                    {
                        // hanging-out
                        $message = $pet->getName() . ' taught ' . $companion->getName() . ' more about the physical world.';
                    }
                    break;

                case SpiritCompanionStarEnum::CASSIOPEIA:
                    // sneaky snake
                    if(mt_rand(1, 3) === 1)
                    {
                        $teachingStat = PetSkillEnum::STEALTH;
                        $message = $companion->getName() . ' showed ' . $pet->getName() . ' how to take advantage of their surroundings to hide their presence.';
                    }
                    else
                    {
                        if(mt_rand(1, 4) === 1)
                            $message = $pet->getName() . ' listened to ' . $companion->getName() . ' for a little while. They had many, strange secrets to tell, but none really seemed that useful.';
                        else
                            $message = $pet->getName() . ' listened to ' . $companion->getName() . ' for a little while.';
                    }
                    break;

                case SpiritCompanionStarEnum::CEPHEUS:
                    // a king
                    if(mt_rand(1, 3) === 1)
                    {
                        $teachingStat = PetSkillEnum::UMBRA;
                        $message = $pet->getName() . ' listened to ' . $companion->getName() . '\'s stories about the various lands of the near and far Umbra...';
                    }
                    else
                    {
                        $message = $companion->getName() . ' told a ' . ArrayFunctions::pick_one($adjectives) . ' story they made just for ' . $pet->getName() . '!';
                    }
                    break;

                case SpiritCompanionStarEnum::GEMINI:
                    $message = $pet->getName() . ' played ' . ArrayFunctions::pick_one([
                        'hide-and-go-seek tag',
                        'hacky sack',
                        'soccer',
                        'three-player checkers',
                        'charades',
                    ]) . ' with the ' . $companion->getName() . ' twins!';
                    break;

                case SpiritCompanionStarEnum::HYDRA:
                    // scary monster; depicted as basically a friendly guard dog
                    $message = $pet->getName() . ' played catch with ' . $companion->getName() . '!';
                    break;

                case SpiritCompanionStarEnum::SAGITTARIUS:
                    // satyr-adjacent
                    if(mt_rand(1, 3) === 1)
                    {
                        // teaches music
                        $teachingStat = PetSkillEnum::MUSIC;
                        $message = $pet->getName() . ' ' . ArrayFunctions::pick_one([ 'played music', 'danced', 'sang' ]) . ' with ' . $companion->getName() . '!';
                    }
                    else
                    {
                        // hanging-out
                        $message = $pet->getName() . ' went riding with ' . $companion->getName() . ' for a while!';
                    }
                    break;

                default:
                    throw new \Exception('Unknown Spirit Companion Star "' . $companion->getStar() . '"');
            }

            if($teachingStat)
            {
                $pet
                    ->increaseSafety(mt_rand(1, 2))
                    ->increaseLove(mt_rand(1, 2))
                    ->increaseEsteem(mt_rand(1, 2))
                ;

                $this->petExperienceService->gainExp($pet, 1, [ $teachingStat ]);
            }
            else
            {
                $pet
                    ->increaseSafety(mt_rand(2, 4))
                    ->increaseLove(mt_rand(2, 4))
                    ->increaseEsteem(mt_rand(2, 4))
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
                        ->increaseSafety(mt_rand(6, 10))
                        ->increaseLove(mt_rand(2, 4))
                    ;
                    $message = $pet->getName() . ' was feeling nervous, so talked to ' . $companion->getName() . '. ' . $companion->getName() . ' told a ' . ArrayFunctions::pick_one($adjectives) . ' story about victory in combat, and swore to protect ' . $pet->getName() . '!';
                    break;
                case SpiritCompanionStarEnum::CASSIOPEIA:
                    $pet
                        ->increaseSafety(mt_rand(2, 4))
                    ;
                    $message = $pet->getName() . ' was feeling nervous, so talked to ' . $companion->getName() . '. ' . $companion->getName() . ' whispered odd prophecies, then stared at ' . $pet->getName() . ' expectantly. (It\'s the thought that counts...)';
                    break;
                case SpiritCompanionStarEnum::GEMINI:
                    $pet
                        ->increaseSafety(mt_rand(4, 8))
                        ->increaseLove(mt_rand(2, 4))
                        ->increaseEsteem(mt_rand(2, 4))
                    ;
                    $message = $pet->getName() . ' was feeling nervous, so talked to ' . $companion->getName() . '. ' . $companion->getName() . ' smiled, and split into multiple copies of itself, each defending ' . $pet->getName() . ' from another angle. They all turned to ' . $pet->getName() . ' and gave a sincere thumbs up before recombining.';
                    break;
                case SpiritCompanionStarEnum::SAGITTARIUS:
                    $pet
                        ->increaseSafety(mt_rand(2, 4))
                        ->increaseLove(mt_rand(2, 4))
                    ;
                    $message = $pet->getName() . ' was feeling nervous, so talked to ' . $companion->getName() . '. ' . $companion->getName() . ' tried to distract ' . $pet->getName() . ' with ' . ArrayFunctions::pick_one($adjectives) . ' stories about lavish parties. It kind of worked...';
                    break;
                case SpiritCompanionStarEnum::HYDRA:
                    $pet
                        ->increaseSafety(mt_rand(4, 8))
                        ->increaseLove(mt_rand(4, 8))
                    ;
                    $message = $pet->getName() . ' was feeling nervous, so talked to ' . $companion->getName() . '. Sensing ' . $pet->getName() . '\'s unease, ' . $companion->getName() . ' looked around for potential threats, and roared menacingly.';
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
                        ->increaseSafety(mt_rand(2, 4))
                        ->increaseLove(mt_rand(2, 4))
                    ;
                    $message = $pet->getName() . ' was feeling lonely, so talked to ' . $companion->getName() . '. ' . $companion->getName() . ' rambled some ' . ArrayFunctions::pick_one($adjectives) . ' story about victory in combat... (It\'s the thought that counts...)';
                    break;
                case SpiritCompanionStarEnum::CASSIOPEIA:
                    $pet
                        ->increaseSafety(mt_rand(2, 4))
                        ->increaseLove(mt_rand(2, 4))
                    ;
                    $message = $pet->getName() . ' was feeling lonely, so talked to ' . $companion->getName() . '. ' . $companion->getName() . ' whispered odd prophecies, then stared at ' . $pet->getName() . ' expectantly. (It\'s the thought that counts...)';
                    break;
                case SpiritCompanionStarEnum::GEMINI:
                    $pet
                        ->increaseSafety(mt_rand(4, 8))
                        ->increaseLove(mt_rand(4, 8))
                    ;
                    $message = $pet->getName() . ' was feeling lonely, so talked to ' . $companion->getName() . '. ' . $companion->getName() . ' smiled, and split into multiple copies of itself, and they all played games together!';
                    break;
                case SpiritCompanionStarEnum::SAGITTARIUS:
                    $pet
                        ->increaseSafety(mt_rand(2, 4))
                        ->increaseLove(mt_rand(4, 8))
                        ->increaseEsteem(mt_rand(2, 4))
                    ;
                    $message = $pet->getName() . ' was feeling lonely, so talked to ' . $companion->getName() . '. The two hosted a party for themselves; ' . $pet->getName() . ' had a lot of fun.';
                    break;
                case SpiritCompanionStarEnum::HYDRA:
                    $pet
                        ->increaseSafety(mt_rand(4, 8))
                        ->increaseLove(mt_rand(4, 8))
                    ;
                    $message = $pet->getName() . ' was feeling lonely, so talked to ' . $companion->getName() . '. Sensing ' . $pet->getName() . '\'s unease, ' . $companion->getName() . ' settled into ' . $pet->getName() . '\'s lap.';
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
                        ->increaseSafety(mt_rand(2, 4))
                        ->increaseLove(mt_rand(2, 4))
                        ->increaseEsteem(mt_rand(2, 4))
                    ;
                    $message = $pet->getName() . ' was feeling down, so talked to ' . $companion->getName() . '. ' . $companion->getName() . ' listened patiently; in the end, ' . $pet->getName() . ' felt a little better.';
                    break;
                case SpiritCompanionStarEnum::CASSIOPEIA:
                    $pet
                        ->increaseEsteem(mt_rand(4, 8))
                    ;
                    $message = $pet->getName() . ' was feeling down, so talked to ' . $companion->getName() . '. ' . $companion->getName() . ' whispered odd prophecies, then stared at ' . $pet->getName() . ' expectantly. Somehow, that actually helped!';
                    break;
                case SpiritCompanionStarEnum::GEMINI:
                    $pet
                        ->increaseLove(mt_rand(2, 4))
                    ;
                    $message = $pet->getName() . ' was feeling down, so talked to ' . $companion->getName() . '. ' . $companion->getName() . ' tried to entertain ' . $pet->getName() . ' by splitting into copies and dancing around, but it didn\'t really help...';
                    break;
                case SpiritCompanionStarEnum::SAGITTARIUS:
                    $pet
                        ->increaseSafety(mt_rand(2, 4))
                        ->increaseLove(mt_rand(2, 4))
                        ->increaseEsteem(mt_rand(4, 8))
                    ;
                    $message = $pet->getName() . ' was feeling down, so talked to ' . $companion->getName() . '. ' . $companion->getName() . ' empathized completely, having been in similar situations themselves. It was really nice to hear!';
                    break;
                case SpiritCompanionStarEnum::HYDRA:
                    $pet
                        ->increaseSafety(mt_rand(2, 4))
                        ->increaseLove(mt_rand(2, 4))
                        ->increaseEsteem(mt_rand(4, 8))
                    ;
                    $message = $pet->getName() . ' was feeling down, so talked to ' . $companion->getName() . '. Sensing ' . $pet->getName() . '\'s unease, ' . $companion->getName() . ' settled into ' . $pet->getName() . '\'s lap.';
                    break;
                default:
                    throw new \Exception('Unknown Spirit Companion Star "' . $companion->getStar() . '"');
            }
        }

        $this->responseService->createActivityLog($pet, $message, 'companions/' . $companion->getImage(), $changes->compare($pet))
            ->addInterestingness(PetActivityLogInterestingnessEnum::ACTIVITY_USING_MERIT)
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
            $relationshipMovement = $this->petRelationshipService->calculateRelationshipDistance($petPreviousRelationship, $pet->getCurrentRelationship());

            $pet->getPet()
                ->increaseLove($relationshipMovement * 2)
                ->increaseEsteem($relationshipMovement)
            ;
        }

        if($friendPreviousRelationship !== $friend->getCurrentRelationship())
        {
            $relationshipMovement = $this->petRelationshipService->calculateRelationshipDistance($friendPreviousRelationship, $friend->getCurrentRelationship());

            $friend->getPet()
                ->increaseLove($relationshipMovement * 2)
                ->increaseEsteem($relationshipMovement)
            ;
        }

        $petLog->setChanges($petChanges->compare($pet->getPet()));
        $friendLog->setChanges($friendChanges->compare($friend->getPet()));

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
        $this->petExperienceService->spendTime($pet, mt_rand(30, 60), PetActivityStatEnum::OTHER, null);
        $this->responseService->createActivityLog($pet, '%pet:' . $pet->getId() . '.name% hung around the house.', '');
    }

    private function pickDesire(array $petDesires)
    {
        $totalDesire = array_sum($petDesires);

        $pick = mt_rand(0, $totalDesire - 1);

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
        $desire = $petWithSkills->getDexterity()->getTotal() + $petWithSkills->getNature()->getTotal() + $petWithSkills->getFishingBonus()->getTotal() + mt_rand(1, 4);

        // when a pet is equipped, the equipment bonus counts twice for affecting a pet's desires
        if($pet->getTool())
            $desire += $pet->getTool()->getItem()->getTool()->getNature() + $pet->getTool()->getItem()->getTool()->getFishing();

        return max(1, round($desire * (1 + mt_rand(-10, 10) / 100)));
    }

    public function generateSubmarineDesire(ComputedPetSkills $petWithSkills): int
    {
        $pet = $petWithSkills->getPet();
        $desire = $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getScience()->getTotal() + $petWithSkills->getFishingBonus()->getTotal() + mt_rand(1, 4);

        // when a pet is equipped, the equipment bonus counts twice for affecting a pet's desires
        if($pet->getTool())
            $desire += $pet->getTool()->getItem()->getTool()->getScience() + $pet->getTool()->getItem()->getTool()->getFishing();

        return max(1, round($desire * (1 + mt_rand(-10, 10) / 100)));
    }

    public function generateMonsterHuntingDesire(ComputedPetSkills $petWithSkills): int
    {
        $pet = $petWithSkills->getPet();
        $desire = $petWithSkills->getStrength()->getTotal() + $petWithSkills->getBrawl()->getTotal() + mt_rand(1, 4);

        // when a pet is equipped, the equipment bonus counts twice for affecting a pet's desires
        if($pet->getTool())
            $desire += $pet->getTool()->getItem()->getTool()->getBrawl();

        return max(1, round($desire * (1 + mt_rand(-10, 10) / 100)));
    }

    public function generateCraftingDesire(ComputedPetSkills $petWithSkills): int
    {
        $pet = $petWithSkills->getPet();
        $desire = $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getCrafts()->getTotal() + mt_rand(1, 4);

        // when a pet is equipped, the equipment bonus counts twice for affecting a pet's desires
        if($pet->getTool())
            $desire += $pet->getTool()->getItem()->getTool()->getCrafts();

        return max(1, round($desire * (1 + mt_rand(-10, 10) / 100)));
    }

    public function generateExploreUmbraDesire(ComputedPetSkills $petWithSkills): int
    {
        $pet = $petWithSkills->getPet();
        $desire = $petWithSkills->getStamina()->getTotal() + $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getUmbra()->getTotal() + mt_rand(1, 4);

        if($pet->getTool())
            $desire += $pet->getTool()->getItem()->getTool()->getUmbra();

        if($pet->hasMerit(MeritEnum::NATURAL_CHANNEL))
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
        $desire = $petWithSkills->getPerception()->getTotal() + $petWithSkills->getNature()->getTotal() + $petWithSkills->getGatheringBonus()->getTotal() + mt_rand(1, 4);

        // when a pet is equipped, the equipment bonus counts twice for affecting a pet's desires
        if($pet->getTool())
            $desire += $pet->getTool()->getItem()->getTool()->getNature() + $pet->getTool()->getItem()->getTool()->getGathering();

        return max(1, round($desire * (1 + mt_rand(-10, 10) / 100)));
    }

    public function generateClimbingBeanstalkDesire(ComputedPetSkills $petWithSkills): int
    {
        $pet = $petWithSkills->getPet();
        $desire = floor(($petWithSkills->getStrength()->getTotal() + $petWithSkills->getStamina()->getTotal()) * 1.5) + ceil($petWithSkills->getNature()->getTotal() / 2) + mt_rand(1, 4);

        // when a pet is equipped, the equipment bonus counts twice for affecting a pet's desires
        if($pet->getTool())
            $desire += $pet->getTool()->getItem()->getTool()->getNature();

        return max(1, round($desire * (1 + mt_rand(-10, 10) / 100)));
    }

    public function generateHackingDesire(ComputedPetSkills $petWithSkills): int
    {
        $pet = $petWithSkills->getPet();
        $desire = $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getScience()->getTotal() + mt_rand(1, 4);

        // when a pet is equipped, the equipment bonus counts twice for affecting a pet's desires
        if($pet->getTool())
            $desire += $pet->getTool()->getItem()->getTool()->getScience();

        return max(1, round($desire * (1 + mt_rand(-10, 10) / 100)));
    }

    public function generateProgrammingDesire(ComputedPetSkills $petWithSkills): int
    {
        $pet = $petWithSkills->getPet();
        $desire = $petWithSkills->getIntelligence()->getTotal() + $petWithSkills->getScience()->getTotal() + mt_rand(1, 4);

        // when a pet is equipped, the equipment bonus counts twice for affecting a pet's desires
        if($pet->getTool())
            $desire += $pet->getTool()->getItem()->getTool()->getScience();

        return max(1, round($desire * (1 + mt_rand(-10, 10) / 100)));
    }

    private function poop(Pet $pet): bool
    {
        if($pet->hasMerit(MeritEnum::BLACK_HOLE_TUM) && mt_rand(1, 180) === 1)
            return true;

        if($pet->getTool() && $pet->getTool()->increasesPooping() && mt_rand(1, 180) === 1)
            return true;

        return false;
    }

    private function dream(Pet $pet): bool
    {
        if($pet->hasMerit(MeritEnum::DREAMWALKER) && mt_rand(1, 200) === 1)
            return true;

        if($pet->getTool() && $pet->getTool()->isDreamcatcher() && mt_rand(1, 200) === 1)
            return true;

        return false;
    }
}
