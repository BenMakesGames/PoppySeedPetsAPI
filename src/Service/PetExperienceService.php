<?php
namespace App\Service;

use App\Entity\Inventory;
use App\Entity\Item;
use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Entity\StatusEffect;
use App\Enum\EnumInvalidValueException;
use App\Enum\FlavorEnum;
use App\Enum\LocationEnum;
use App\Enum\MeritEnum;
use App\Enum\StatusEffectEnum;
use App\Functions\ArrayFunctions;
use App\Functions\GrammarFunctions;
use App\Repository\ItemRepository;
use Doctrine\ORM\EntityManagerInterface;

class PetExperienceService
{
    public const SOCIAL_ENERGY_PER_HANG_OUT = 576; // 2.5 hangouts per day (for average pets)

    private $petActivityStatsService;
    private $em;
    private $responseService;
    private $itemRepository;

    public function __construct(
        PetActivityStatsService $petActivityStatsService, EntityManagerInterface $em,
        ResponseService $responseService, ItemRepository $itemRepository
    )
    {
        $this->petActivityStatsService = $petActivityStatsService;
        $this->em = $em;
        $this->responseService = $responseService;
        $this->itemRepository = $itemRepository;
    }

    /**
     * @param string[] $stats
     */
    public function gainExp(Pet $pet, int $exp, array $stats)
    {
        if($pet->hasStatusEffect(StatusEffectEnum::INSPIRED))
            $exp++;

        if($exp < 0) return;

        $possibleStats = array_filter($stats, function($stat) use($pet) {
            return ($pet->{'get' . $stat}() < 20);
        });

        if(count($possibleStats) === 0) return;

        if($pet->getTool() && $pet->getTool()->getItem()->getTool()->getFocusSkill())
        {
            if(in_array($pet->getTool()->getItem()->getTool()->getFocusSkill(), $possibleStats))
                $exp++;
        }

        $divideBy = 1;

        if($pet->getFood() + $pet->getAlcohol() < 0) $divideBy++;
        if($pet->getSafety() + $pet->getAlcohol() < 0) $divideBy++;
        if($pet->getLove() + $pet->getAlcohol() < 0) $divideBy++;
        if($pet->getEsteem() + $pet->getAlcohol() < 0) $divideBy++;

        $divideBy += 1 + ($pet->getAlcohol() / $pet->getStomachSize());

        $exp = round($exp / $divideBy);

        if($exp === 0) return;

        $pet->increaseExperience($exp);

        while($pet->getExperience() >= $pet->getExperienceToLevel())
        {
            $pet->decreaseExperience($pet->getExperienceToLevel());
            $pet->getSkills()->increaseStat(ArrayFunctions::pick_one($possibleStats));
        }
    }

    public function spendSocialEnergy(Pet $pet, int $energy)
    {
        if(mt_rand(1, 10) === 1)
        {
            // smallish chance to consume WAY less energy. this was added to help jiggle pets out of a situation where
            // two pets owned by the same account are always offset in social energy such that they're never able to hang
            // out with each other.
            $energy = mt_rand(ceil($energy / 4), ceil($energy * 3 / 4));
        }
        else
        {
            // always add a LITTLE random jiggle, though:
            $energy = mt_rand(ceil($energy * 8 / 10), ceil($energy * 12 / 10));
        }

        // a small drift based on number of extra friends a pet can have
        $energy = ceil($energy * (20 - $pet->getBonusMaximumFriends()) / 20);

        // introverted pets spend more social energy; extroverted pets spend less
        if($pet->getExtroverted() < 0)
        {
            $energy = ceil($energy * 5 / 4);
        }
        else if($pet->getExtroverted() > 0)
        {
            $energy = ceil($energy * 3 / 4);
        }

        if($energy < 0)
            throw new \Exception('Somehow, the game tried to spend negative social energy. This is bad, and Ben should fix it.');

        $pet->spendSocialEnergy($energy);
    }

    /**
     * @throws EnumInvalidValueException
     */
    public function spendTime(Pet $pet, int $time, string $activityStat, ?bool $success)
    {
        $pet->spendTime($time);
        $this->petActivityStatsService->logStat($pet, $activityStat, $success, $time);

        if($pet->getPregnancy())
            $pet->getPregnancy()->increaseGrowth($time);

        /** @var StatusEffect[] $statusEffects */
        $statusEffects = array_values($pet->getStatusEffects()->toArray());

        for($i = count($statusEffects) - 1; $i >= 0; $i--)
        {
            $statusEffects[$i]->spendTime($time);

            // some status effects TRANSFORM when they run out (like caffeinated -> tired)
            if($statusEffects[$i]->getTimeRemaining() <= 0)
            {
                if($statusEffects[$i]->getStatus() === StatusEffectEnum::CAFFEINATED)
                {
                    $newTotal = ceil($statusEffects[$i]->getTotalDuration() / 2);
                    $statusEffects[$i]
                        ->setStatus(StatusEffectEnum::TIRED)
                        ->setTimeRemaining($statusEffects[$i]->getTimeRemaining() + $newTotal)
                        ->setTotalDuration($newTotal)
                    ;
                }
            }

            // if the status effect didn't transform (or was still out of time AFTER transforming), then delete it
            if($statusEffects[$i]->getTimeRemaining() <= 0)
                $pet->removeStatusEffect($statusEffects[$i]);
        }
    }

    /**
     * @throws EnumInvalidValueException
     * @return bool
     */
    public function doEat(Pet $pet, Item $item, ?PetActivityLog $activityLog): bool
    {
        // pets will not eat if their stomach is already full
        if($pet->getJunk() + $pet->getFood() >= $pet->getStomachSize())
            return false;

        $food = $item->getFood();

        if($pet->wantsSobriety() && ($food->getAlcohol() > 0 || $food->getCaffeine() > 0 || $food->getPsychedelic() > 0))
            return false;

        $this->applyFoodEffects($pet, $item);

        // consider favorite flavor:
        if(!FlavorEnum::isAValue($pet->getFavoriteFlavor()))
            throw new EnumInvalidValueException(FlavorEnum::class, $pet->getFavoriteFlavor());

        $randomFlavor = $item->getFood()->getRandomFlavor() > 0 ? FlavorEnum::getRandomValue() : null;

        $esteemGain = $this->getFavoriteFlavorStrength($pet, $item, $randomFlavor) + $item->getFood()->getLove();

        $pet->increaseEsteem($esteemGain);

        if($activityLog)
        {
            if($randomFlavor)
                $activityLog->setEntry($activityLog->getEntry() . ' ' . $pet->getName() . ' immediately ate the ' . $item->getName() . '. (Ooh! ' . ucwords($randomFlavor) . '!');
            else
                $activityLog->setEntry($activityLog->getEntry() . ' ' . $pet->getName() . ' immediately ate the ' . $item->getName() . '.');
        }

        return true;
    }

    public function getFavoriteFlavorStrength(Pet $pet, Item $item, string $randomFlavor = null): int
    {
        if(!$item->getFood())
            return 0;

        $favoriteFlavorStrength = $item->getFood()->{'get' . $pet->getFavoriteFlavor()}();

        if($randomFlavor !== null && $randomFlavor === $pet->getFavoriteFlavor())
            $favoriteFlavorStrength += $item->getFood()->getRandomFlavor();

        if($pet->hasMerit(MeritEnum::LOLLIGOVORE) && $item->getFood()->getContainsTentacles())
            $favoriteFlavorStrength += 2;

        return $favoriteFlavorStrength;
    }

    /**
     * @throws EnumInvalidValueException
     */
    public function applyStatusEffect(Pet $pet, string $status, int $duration, int $maxDuration)
    {
        $statusEffect = $pet->getStatusEffect($status);

        if(!$statusEffect)
        {
            $statusEffect = (new StatusEffect())
                ->setStatus($status)
            ;

            $pet->addStatusEffect($statusEffect);

            $this->em->persist($statusEffect);
        }

        $statusEffect
            ->setTotalDuration(min($maxDuration, $statusEffect->getTotalDuration() + $duration))
            ->setTimeRemaining(min($statusEffect->getTotalDuration(), $statusEffect->getTimeRemaining() + $duration))
        ;

    }

    public function applyFoodEffects(Pet $pet, Item $item)
    {
        $food = $item->getFood();

        $pet->increaseAlcohol($food->getAlcohol());

        $caffeine = $food->getCaffeine();

        if($caffeine > 0)
        {
            $pet->increaseCaffeine($caffeine);
            $this->applyStatusEffect($pet, StatusEffectEnum::CAFFEINATED, $caffeine * 60, 8 * 60);
        }
        else if($caffeine < 0)
            $pet->increaseCaffeine($caffeine);

        $pet->increasePsychedelic($food->getPsychedelic());
        $pet->increaseFood($food->getFood());
        $pet->increaseJunk($food->getJunk());

        if($food->getChanceForBonusItem() !== null && mt_rand(1, 1000) <= $food->getChanceForBonusItem())
        {
            $bonusItem = $this->getBonusItemForLuckyFood();
            $inventory = (new Inventory())
                ->setItem($bonusItem)
                ->setLocation(LocationEnum::HOME)
                ->setOwner($pet->getOwner())
                ->setCreatedBy($pet->getOwner())
                ->addComment(
                    'While eating ' . $item->getName() . ', ' . $pet->getName() . ' happened to spot this! ' .
                    ArrayFunctions::pick_one([
                        '', '... Sure!', '... Why not?', 'As you do!', 'A happy coincidence!', 'Weird!',
                        'Inexplicable, but not unwelcome!', '(Where was it up until this point, I wonder??)',
                        'These things happen. Apparently.', '👍', 'Wild!', 'How\'s _that_ work?',
                        '(I guess eating ' . $item->getName() . ' really _does_ bring good fortune! Who knew!)'
                    ])
                )
            ;
            $this->em->persist($inventory);

            $naniNani = ArrayFunctions::pick_one([ 'Convenient!', 'Where\'d that come from??', 'How serendipitous!', 'What are the odds!' ]);

            $this->responseService->addFlashMessage((new PetActivityLog())->setEntry('While eating the ' . $item->getName() . ', ' . $pet->getName() . ' spotted ' . GrammarFunctions::indefiniteArticle($bonusItem->getName()) . ' ' . $bonusItem->getName() . '! (' . $naniNani . ')'));
        }

        if($pet->hasMerit(MeritEnum::BURPS_MOTHS) && mt_rand(1, 200) < $food->getFood() + $food->getJunk())
        {
            $inventory = (new Inventory())
                ->setItem($this->itemRepository->findOneByName('Moth'))
                ->setLocation(LocationEnum::HOME)
                ->setOwner($pet->getOwner())
                ->setCreatedBy($pet->getOwner())
                ->addComment('After eating ' . $item->getName() . ', ' . $pet->getName() . ' burped this up!')
            ;
            $this->em->persist($inventory);

            $this->responseService->addFlashMessage((new PetActivityLog())->setEntry('After eating ' . $item->getName() . ', ' . $pet->getName() . ' burped up a Moth!'));
        }

        if($food->getGrantedSkill() && $pet->getSkills()->getStat($food->getGrantedSkill()) < 1)
            $pet->getSkills()->increaseStat($food->getGrantedSkill());
    }

    private function getBonusItemForLuckyFood(): Item
    {
        return $this->itemRepository->findOneByName(ArrayFunctions::pick_one([
            'Fluff',
            'Mermaid Egg',
            'Paper',
            'Iron Bar',
            'Silver Ore',
            'Gold Ore',
            'Quintessence',
            'Feathers',
            'Beans',
            'Paper Bag',
            'Renaming Scroll',
            'Behatting Scroll',
            'White Cloth'
        ]));
    }
}
