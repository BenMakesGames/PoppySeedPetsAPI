<?php
namespace App\Service\PetActivity;

use App\Entity\Inventory;
use App\Entity\Item;
use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Enum\EnumInvalidValueException;
use App\Enum\FlavorEnum;
use App\Enum\LocationEnum;
use App\Enum\MeritEnum;
use App\Enum\StatusEffectEnum;
use App\Enum\UserStatEnum;
use App\Functions\ArrayFunctions;
use App\Functions\GrammarFunctions;
use App\Model\FoodWithSpice;
use App\Model\FortuneCookie;
use App\Model\PetChanges;
use App\Repository\ItemRepository;
use App\Repository\PetActivityLogTagRepository;
use App\Repository\UserStatsRepository;
use App\Service\CravingService;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\PetExperienceService;
use App\Service\ResponseService;
use App\Service\Squirrel3;
use App\Service\StatusEffectService;
use Doctrine\ORM\EntityManagerInterface;

class EatingService
{
    private IRandom $squirrel3;
    private StatusEffectService $statusEffectService;
    private CravingService $cravingService;
    private InventoryService $inventoryService;
    private ResponseService $responseService;
    private EntityManagerInterface $em;
    private ItemRepository $itemRepository;
    private PetExperienceService $petExperienceService;
    private UserStatsRepository $userStatsRepository;
    private PetActivityLogTagRepository $petActivityLogTagRepository;

    public function __construct(
        Squirrel3 $squirrel3, StatusEffectService $statusEffectService, CravingService $cravingService,
        InventoryService $inventoryService, ResponseService $responseService, EntityManagerInterface $em,
        ItemRepository $itemRepository, PetExperienceService $petExperienceService,
        UserStatsRepository $userStatsRepository, PetActivityLogTagRepository $petActivityLogTagRepository
    )
    {
        $this->squirrel3 = $squirrel3;
        $this->statusEffectService = $statusEffectService;
        $this->cravingService = $cravingService;
        $this->inventoryService = $inventoryService;
        $this->responseService = $responseService;
        $this->em = $em;
        $this->itemRepository = $itemRepository;
        $this->petExperienceService = $petExperienceService;
        $this->userStatsRepository = $userStatsRepository;
        $this->petActivityLogTagRepository = $petActivityLogTagRepository;
    }

    /**
     * @throws EnumInvalidValueException
     * @return bool
     */
    public function doEat(Pet $pet, FoodWithSpice $food, ?PetActivityLog $activityLog): bool
    {
        // pets will not eat if their stomach is already full
        if($pet->getJunk() + $pet->getFood() >= $pet->getStomachSize())
            return false;

        if($pet->wantsSobriety() && ($food->alcohol || $food->caffeine > 0 || $food->psychedelic > 0))
            return false;

        $this->applyFoodEffects($pet, $food);

        // consider favorite flavor:
        if(!FlavorEnum::isAValue($pet->getFavoriteFlavor()))
            throw new EnumInvalidValueException(FlavorEnum::class, $pet->getFavoriteFlavor());

        $randomFlavor = $food->randomFlavor > 0 ? FlavorEnum::getRandomValue($this->squirrel3) : null;

        $esteemGain = $this->getFavoriteFlavorStrength($pet, $food, $randomFlavor) + $food->love;

        $pet->increaseEsteem($esteemGain);

        if($activityLog)
        {
            if($randomFlavor)
                $activityLog->setEntry($activityLog->getEntry() . ' ' . $pet->getName() . ' immediately ate the ' . $food->name . '. (Ooh! ' . ucwords($randomFlavor) . '!');
            else
                $activityLog->setEntry($activityLog->getEntry() . ' ' . $pet->getName() . ' immediately ate the ' . $food->name . '.');

            $activityLog->addTags($this->petActivityLogTagRepository->findByNames([ 'Eating' ]));
        }

        return true;
    }

    public function getFavoriteFlavorStrength(Pet $pet, FoodWithSpice $food, string $randomFlavor = null): int
    {
        $favoriteFlavorStrength = $food->{$pet->getFavoriteFlavor()};

        if($randomFlavor !== null && $randomFlavor === $pet->getFavoriteFlavor())
            $favoriteFlavorStrength += $food->randomFlavor;

        if($pet->hasMerit(MeritEnum::LOLLIGOVORE))
            $favoriteFlavorStrength += $food->containsTentacles;

        return $favoriteFlavorStrength;
    }

    public function applyFoodEffects(Pet $pet, FoodWithSpice $food)
    {
        $pet->increaseAlcohol($food->alcohol);

        $caffeine = $food->caffeine;

        if($caffeine > 0)
        {
            $pet->increaseCaffeine($caffeine);
            $this->statusEffectService->applyStatusEffect($pet, StatusEffectEnum::CAFFEINATED, $caffeine * 60);
        }
        else if($caffeine < 0)
            $pet->increaseCaffeine($caffeine);

        $pet->increasePsychedelic($food->psychedelic);
        $pet->increaseFood($food->food);

        if($food->junk > 0)
            $pet->increaseJunk($food->junk);
        else if($food->junk < 0)
            $pet->increasePoison($food->junk);

        foreach($food->grantedStatusEffects as $statusEffect)
        {
            $this->statusEffectService->applyStatusEffect($pet, $statusEffect['effect'], $statusEffect['duration']);
        }

        if($food->grantsSelfReflection)
            $pet->increaseSelfReflectionPoint(1);

        if($this->cravingService->foodMeetsCraving($pet, $food->baseItem))
        {
            $this->cravingService->satisfyCraving($pet, $food->baseItem);
        }

        if($food->leftovers)
        {
            $leftoverNames = [];

            foreach($food->leftovers as $leftoverItem)
            {
                $leftoverNames[] = $leftoverItem->getNameWithArticle();
                $this->inventoryService->petCollectsItem($leftoverItem, $pet, $pet->getName() . ' ate ' . GrammarFunctions::indefiniteArticle($food->name) . ' ' . $food->name . '; this was left over.', null);
            }

            $wasOrWere = count($food->leftovers) === 1 ? 'was' : 'were';

            $this->responseService->addFlashMessage('After ' . $pet->getName() . ' ate the ' . $food->name . ', ' . ArrayFunctions::list_nice($leftoverNames) . ' ' . $wasOrWere . ' left over.');
        }

        $bonusItems = [];

        foreach($food->bonusItems as $bonusItem)
        {
            if($this->squirrel3->rngNextInt(1, 1000) <= $bonusItem->chance)
                $bonusItems[] = $this->inventoryService->getRandomItemFromItemGroup($bonusItem->itemGroup);
        }

        if(count($bonusItems) > 0)
        {
            $exclamations = [ 'Convenient!', 'How serendipitous!', 'What are the odds!' ];

            $bonusItemNamesWithArticles = array_map(fn(Item $item) => $item->getNameWithArticle(), $bonusItems);

            if(count($bonusItems) === 1)
                $exclamations[] = 'Where\'d that come from??';
            else
                $exclamations[] = 'Where\'d those come from??';

            $naniNani = $this->squirrel3->rngNextFromArray($exclamations);

            $activityLogText = 'While eating the ' . $food->name . ', ' . $pet->getName() . ' spotted ' . ArrayFunctions::list_nice($bonusItemNamesWithArticles) . '! (' . $naniNani . ')';

            $changes = new PetChanges($pet);

            $activityLog = $this->responseService->createActivityLog($pet, $activityLogText, '', null);

            foreach($bonusItems as $item)
            {
                $comment =
                    'While eating ' . $food->name . ', ' . $pet->getName() . ' happened to spot this! ' .
                    $this->squirrel3->rngNextFromArray([
                        '', '... Sure!', '... Why not?', 'As you do!', 'A happy coincidence!', 'Weird!',
                        'Inexplicable, but not unwelcome!', '(Where was it up until this point, I wonder??)',
                        'These things happen. Apparently.', '👍', 'Wild!', 'How\'s _that_ work?',
                    ])
                ;

                $this->inventoryService->petCollectsItem($item, $pet, $comment, $activityLog);
            }

            $activityLog->setChanges($changes->compare($pet));
        }

        if($pet->hasMerit(MeritEnum::BURPS_MOTHS) && $this->squirrel3->rngNextInt(1, 200) < $food->food + $food->junk)
        {
            $inventory = (new Inventory())
                ->setItem($this->itemRepository->findOneByName('Moth'))
                ->setLocation(LocationEnum::HOME)
                ->setOwner($pet->getOwner())
                ->setCreatedBy($pet->getOwner())
                ->addComment('After eating ' . $food->name . ', ' . $pet->getName() . ' burped this up!')
            ;
            $this->em->persist($inventory);

            $this->responseService->addFlashMessage('After eating ' . $food->name . ', ' . $pet->getName() . ' burped up a Moth!');
        }

        foreach($food->grantedSkills as $skill)
        {
            if($pet->getSkills()->getStat($skill) < 1)
                $pet->getSkills()->increaseStat($skill);
        }
    }

    /**
     * @param Pet $pet
     * @param Inventory[] $inventory
     * @return PetActivityLog
     * @throws EnumInvalidValueException
     */
    public function doFeed(Pet $pet, array $inventory): PetActivityLog
    {
        if(!$pet->isAtHome()) throw new \InvalidArgumentException('Pets that aren\'t home cannot be interacted with.');

        if(ArrayFunctions::any($inventory, fn(Inventory $i) => $i->getItem()->getFood() === null))
            throw new \InvalidArgumentException('At least one of the items selected is not edible!');

        $this->squirrel3->rngNextShuffle($inventory);

        $petChanges = new PetChanges($pet);
        $foodsEaten = [];
        /** @var FoodWithSpice[] $favorites */ $favorites = [];
        $tooPoisonous = [];
        $ateAFortuneCookie = false;

        foreach($inventory as $i)
        {
            $food = new FoodWithSpice($i->getItem(), $i->getSpice());

            $itemName = $food->name;

            if($pet->getJunk() + $pet->getFood() >= $pet->getStomachSize())
                continue;

            if($pet->wantsSobriety() && ($food->alcohol > 0 || $food->caffeine > 0 || $food->psychedelic > 0))
            {
                $tooPoisonous[] = $itemName;
                continue;
            }

            $this->applyFoodEffects($pet, $food);

            // consider favorite flavor:
            if(!FlavorEnum::isAValue($pet->getFavoriteFlavor()))
                throw new EnumInvalidValueException(FlavorEnum::class, $pet->getFavoriteFlavor());

            $randomFlavor = $food->randomFlavor > 0 ? FlavorEnum::getRandomValue($this->squirrel3) : null;

            $favoriteFlavorStrength = $this->getFavoriteFlavorStrength($pet, $food, $randomFlavor);

            $loveAndEsteemGain = $favoriteFlavorStrength + $food->love;

            $pet
                ->increaseLove($loveAndEsteemGain)
                ->increaseEsteem($loveAndEsteemGain)
            ;

            if($favoriteFlavorStrength > 0)
            {
                $this->petExperienceService->gainAffection($pet, $favoriteFlavorStrength);

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
            $gain = $foodGained >> 3; // ">> 3" === "/ 8"

            if ($remainder > 0 && $this->squirrel3->rngNextInt(1, 8) <= $remainder)
                $gain++;

            $pet->increaseSafety($gain);
            $this->petExperienceService->gainAffection($pet, $gain);

            if($pet->getPregnancy())
                $pet->getPregnancy()->increaseAffection($gain);

            $this->userStatsRepository->incrementStat($pet->getOwner(), UserStatEnum::FOOD_HOURS_FED_TO_PETS, $foodGained);

            $this->cravingService->maybeAddCraving($pet);
        }

        if(count($foodsEaten) > 0)
        {
            $message = '%user:' . $pet->getOwner()->getId() . '.Name% fed ' . $pet->getName() . ' ' . ArrayFunctions::list_nice($foodsEaten) . '.';
            $icon = 'icons/activity-logs/mangia';

            if(count($favorites) > 0)
            {
                $icon = 'ui/affection';
                $message .= ' ' . $pet->getName() . ' really liked the ' . $this->squirrel3->rngNextFromArray($favorites)->name . '!';
            }

            if($ateAFortuneCookie)
            {
                $message .= ' "' . $this->squirrel3->rngNextFromArray(FortuneCookie::MESSAGES) . '"';
                if($this->squirrel3->rngNextInt(1, 20) === 1 && $pet->getOwner()->getUnlockedGreenhouse())
                {
                    $message .= ' ... in bed!';

                    if($this->squirrel3->rngNextInt(1, 5) === 1)
                        $message .= ' XD';
                }
            }

            return $this->responseService->createActivityLog($pet, $message, $icon, $petChanges->compare($pet))
                ->addTags($this->petActivityLogTagRepository->findByNames([ 'Eating' ]))
            ;
        }
        else
        {
            if(count($tooPoisonous) > 0)
            {
                return $this->responseService->createActivityLog($pet, '%user:' . $pet->getOwner()->getId() . '.Name% tried to feed ' . '%pet:' . $pet->getId() . '.name%, but ' . $this->squirrel3->rngNextFromArray($tooPoisonous) . ' really isn\'t appealing right now.', '')
                    ->addTags($this->petActivityLogTagRepository->findByNames([ 'Eating' ]))
                ;
            }
            else
            {
                return $this->responseService->createActivityLog($pet, '%user:' . $pet->getOwner()->getId() . '.Name% tried to feed ' . '%pet:' . $pet->getId() . '.name%, but they\'re too full to eat anymore.', '')
                    ->addTags($this->petActivityLogTagRepository->findByNames([ 'Eating' ]))
                ;
            }
        }
    }
}