<?php
namespace App\Service;

use App\Entity\Inventory;
use App\Entity\Item;
use App\Entity\ItemFood;
use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Entity\StatusEffect;
use App\Entity\User;
use App\Enum\EnumInvalidValueException;
use App\Enum\FlavorEnum;
use App\Enum\LocationEnum;
use App\Enum\MeritEnum;
use App\Enum\StatusEffectEnum;
use App\Functions\ArrayFunctions;
use App\Functions\GrammarFunctions;
use App\Model\ItemQuantity;
use App\Repository\InventoryRepository;
use App\Repository\ItemRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class InventoryService
{
    private $itemRepository;
    private $em;
    private $responseService;
    private $petExperienceService;
    private $inventoryRepository;

    public function __construct(
        ItemRepository $itemRepository, EntityManagerInterface $em, ResponseService $responseService,
        PetExperienceService $petExperienceService, InventoryRepository $inventoryRepository
    )
    {
        $this->itemRepository = $itemRepository;
        $this->responseService = $responseService;
        $this->em = $em;
        $this->petExperienceService = $petExperienceService;
        $this->inventoryRepository = $inventoryRepository;
    }

    /**
     * @param Item|string|integer $item
     * @throws EnumInvalidValueException
     */
    public function countInventory(User $user, $item, int $location): int
    {
        if(!LocationEnum::isAValue($location))
            throw new EnumInvalidValueException(LocationEnum::class, $location);

        if(is_string($item))
            $itemId = $this->itemRepository->findOneByName($item)->getId();
        else if(is_object($item) && $item instanceof Item)
            $itemId = $item->getId();
        else if(is_integer($item))
            $itemId = $item;
        else
            throw new \InvalidArgumentException('$item must be an Item, string, or integer.');

        return (int)$this->em->createQueryBuilder()
            ->select('COUNT(i.id)')
            ->from(Inventory::class, 'i')
            ->andWhere('i.owner=:owner')
            ->andWhere('i.item=:item')
            ->andWhere('i.location=:location')
            ->setParameter('owner', $user->getId())
            ->setParameter('item', $itemId)
            ->setParameter('location', $location)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    /**
     * @throws EnumInvalidValueException
     */
    public function countTotalInventory(User $user, int $location): int
    {
        if(!LocationEnum::isAValue($location))
            throw new EnumInvalidValueException(LocationEnum::class, $location);

        return (int)$this->em->createQueryBuilder()
            ->select('COUNT(i.id)')
            ->from(Inventory::class, 'i')
            ->andWhere('i.owner=:owner')
            ->andWhere('i.location=:location')
            ->setParameter('owner', $user->getId())
            ->setParameter('location', $location)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    /**
     * @param ItemQuantity[] $requirements
     * @param ItemQuantity[] $inventory
     */
    public function hasRequiredItems($requirements, $inventory)
    {
        foreach($requirements as $requirement)
        {
            if(!array_key_exists($requirement->item->getName(), $inventory) || $inventory[$requirement->item->getName()]->quantity < $requirement->quantity)
                return false;
        }

        return true;
    }

    /**
     * @return ItemQuantity[]
     */
    public function deserializeItemList(string $list)
    {
        if($list === '') return [];

        $quantities = [];

        $items = \explode(',', $list);
        foreach($items as $item)
        {
            [$itemId, $quantity] = \explode(':', $item);
            $itemQuantity = new ItemQuantity();

            $itemQuantity->item = $this->itemRepository->find($itemId);
            $itemQuantity->quantity = (int)$quantity;

            $quantities[] = $itemQuantity;
        }

        return $quantities;
    }

    /**
     * @param ItemQuantity[] $quantities
     */
    public function serializeItemList($quantities): string
    {
        if(count($quantities) === 0) return '';

        \usort($quantities, function(ItemQuantity $a, ItemQuantity $b) {
            return $a->item->getId() <=> $b->item->getId();
        });

        $items = [];

        foreach($quantities as $itemQuantity)
        {
            $items[] = $itemQuantity->item->getId() . ':' . $itemQuantity->quantity;
        }

        return \implode(',', $items);
    }

    /**
     * @param ItemQuantity|ItemQuantity[] $quantities
     * @return Inventory[]
     * @throws EnumInvalidValueException
     */
    public function giveInventory($quantities, User $owner, User $creator, string $comment, int $location)
    {
        if(!is_array($quantities)) $quantities = [ $quantities ];

        $inventory = [];

        foreach($quantities as $itemQuantity)
        {
            for($q = 0; $q < $itemQuantity->quantity; $q++)
            {
                $i = (new Inventory())
                    ->setOwner($owner)
                    ->setCreatedBy($creator)
                    ->setItem($itemQuantity->item)
                    ->addComment($comment)
                    ->setLocation($location)
                ;

                $this->em->persist($i);

                $inventory[] = $i;
            }
        }

        return $inventory;
    }

    public function petCollectsRandomBalloon(Pet $pet, string $message, ?PetActivityLog $log)
    {
        $balloons = [
            'Red Balloon',
            'Orange Balloon',
            'Yellow Balloon',
            'Green Balloon',
            'Blue Balloon',
            'Purple Balloon',
        ];

        return $this->petCollectsItem(ArrayFunctions::pick_one($balloons), $pet, $message, $log);
    }

    /**
     * @param Item|string $item
     */
    public function petCollectsItem($item, Pet $pet, string $comment, ?PetActivityLog $activityLog): ?Inventory
    {
        $item = $this->getItemWithChanceForLuckyTransformation($item);

        // bonus gather from equipment
        if($pet->getTool() && $pet->getTool()->getItem()->getTool()->getWhenGather() && $item->getName() === $pet->getTool()->getItem()->getTool()->getWhenGather()->getName())
        {
            $extraItem = (new Inventory())
                ->setOwner($pet->getOwner())
                ->setCreatedBy($pet->getOwner())
                ->setItem($pet->getTool()->getItem()->getTool()->getWhenGatherAlsoGather())
                ->addComment($pet->getName() . ' got this by obtaining ' . $item->getName() . ' with their ' . $pet->getTool()->getItem()->getName() . '.')
                ->setLocation(LocationEnum::HOME)
            ;

            $this->em->persist($extraItem);
        }

        // bonus gather from equipment enchantment effects
        if($pet->getTool() && $pet->getTool()->getEnchantment() && $pet->getTool()->getEnchantment()->getEffects()->getWhenGather() && $item->getName() === $pet->getTool()->getEnchantment()->getEffects()->getWhenGather()->getName())
        {
            $extraItem = (new Inventory())
                ->setOwner($pet->getOwner())
                ->setCreatedBy($pet->getOwner())
                ->setItem($pet->getTool()->getEnchantment()->getEffects()->getWhenGatherAlsoGather())
                ->addComment($pet->getName() . ' got this by obtaining ' . $item->getName() . ' with their ' . $pet->getTool()->getItem()->getName() . '.')
                ->setLocation(LocationEnum::HOME)
            ;

            $this->em->persist($extraItem);
        }

        if($item->getFood() !== null && count($pet->getLunchboxItems()) === 0 && mt_rand(1, 20) < 10 - $pet->getFood())
        {
            if($this->doEat($pet, $item, $activityLog))
                return null;
        }

        $i = (new Inventory())
            ->setOwner($pet->getOwner())
            ->setCreatedBy($pet->getOwner())
            ->setItem($item)
            ->addComment($comment)
            ->setLocation(LocationEnum::HOME)
        ;

        $this->em->persist($i);

        return $i;
    }

    public function petAttractsRandomBug(Pet $pet, $bugName = null): ?Inventory
    {
        if($pet->getTool() && $pet->getTool()->preventsBugs())
            return null;

        if($bugName === null)
            $bugName = ArrayFunctions::pick_one([ 'Spider', 'Centipede', 'Cockroach', 'Line of Ants', 'Fruit Fly', 'Stink Bug', 'Moth' ]);

        $bug = $this->itemRepository->findOneByName($bugName);

        $attractsBugs = $pet->getTool() && $pet->getTool()->attractsBugs();

        $bugs = $attractsBugs ? 2 : 1;
        $comment = $attractsBugs ? $pet->getName() . ' caught this in their ' . $pet->getTool()->getItem()->getName() . '!' : 'Ah! How\'d this get inside?!';
        $inventory = null;

        for($i = 0; $i < $bugs; $i++)
        {
            $location = (!$attractsBugs && $pet->getOwner()->getUnlockedBasement() && mt_rand(1, 4) === 1)
                ? LocationEnum::BASEMENT
                : LocationEnum::HOME
            ;

            $inventory = $this->receiveItem($bug, $pet->getOwner(), null, $comment, $location);

            if(!$attractsBugs && $bugName === 'Spider')
                $this->receiveItem('Cobweb', $pet->getOwner(), null, 'Cobwebs?! Some Spider must have made this...', $location);
        }

        return $inventory;
    }

    /**
     * @param string|Item $item
     * @return Item
     */
    private function getItemWithChanceForLuckyTransformation($item): Item
    {
        $itemIsString = is_string($item);

        if(mt_rand(1, 200) === 1)
        {
            $itemName = $itemIsString ? $item : $item->getName();

            if($itemName === 'Beans')
                return $this->itemRepository->findOneByName('Magic Beans');
            else if($itemName === 'Feathers')
                return $this->itemRepository->findOneByName('Ruby Feather');
            else if($itemName === 'Toad Legs')
                return $this->itemRepository->findOneByName('Rainbow Toad Legs');
            else
                return $itemIsString ? $this->itemRepository->findOneByName($item) : $item;
        }
        else
            return $itemIsString ? $this->itemRepository->findOneByName($item) : $item;
    }

    /**
     * @param Item|string $item
     * @throws EnumInvalidValueException
     */
    public function receiveItem($item, User $owner, ?User $creator, string $comment, int $location, bool $lockedToOwner = false): Inventory
    {
        $item = $this->getItemWithChanceForLuckyTransformation($item);

        $i = (new Inventory())
            ->setOwner($owner)
            ->setCreatedBy($creator)
            ->setItem($item)
            ->addComment($comment)
            ->setLocation($location)
            ->setLockedToOwner($lockedToOwner)
        ;

        $this->em->persist($i);

        return $i;
    }

    /**
     * @param Item|string $item
     * @param int|int[] $location
     */
    public function loseItem($item, User $owner, $location, int $quantity = 1): int
    {
        if(is_string($item)) $item = $this->itemRepository->findOneByName($item);

        $inventory = $this->inventoryRepository->findBy(
            [
                'owner' => $owner->getId(),
                'item' => $item->getId(),
                'location' => $location
            ],
            null,
            $quantity
        );

        foreach($inventory as $i)
        {
            if($i->getHolder()) $i->getHolder()->setTool(null);
            if($i->getWearer()) $i->getWearer()->setHat(null);

            $this->em->remove($i);
        }

        return count($inventory);
    }

    /**
     * @param Item[]|string[] $itemList
     * @param int|int[] $location
     * @return Item|string|null
     */
    public function loseOneOf($itemList, User $owner, $location)
    {
        shuffle($itemList);

        foreach($itemList as $item)
        {
            if($this->loseItem($item, $owner, $location, 1) > 0)
                return $item;
        }

        return null;
    }

    /**
     * @param ItemQuantity[] $quantities
     */
    public function totalFood($quantities): ItemFood
    {
        $food = new ItemFood();

        foreach($quantities as $quantity)
        {
            $itemFood = $quantity->item->getFood() ?: new ItemFood();
            $food = $food->add($itemFood->multiply($quantity->quantity));
        }

        return $food;
    }

    /**
     * @param Inventory[] $inventory
     */
    public function inventoryInSameLocation(array $inventory): bool
    {
        if(count($inventory) === 0)
            throw new \InvalidArgumentException('$inventory must contain at least 1 element.');

        if(count($inventory) === 1)
            return true;

        $locationOfFirstItem = $inventory[0]->getLocation();

        return ArrayFunctions::all($inventory, function(Inventory $i) use($locationOfFirstItem) {
            return $i->getLocation() === $locationOfFirstItem;
        });
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

    public function applyFoodEffects(Pet $pet, Item $item)
    {
        $food = $item->getFood();

        $pet->increaseAlcohol($food->getAlcohol());

        $caffeine = $food->getCaffeine();

        if($caffeine > 0)
        {
            $pet->increaseCaffeine($caffeine);
            $this->applyStatusEffect($pet, StatusEffectEnum::CAFFEINATED, $caffeine * 60);
        }
        else if($caffeine < 0)
            $pet->increaseCaffeine($caffeine);

        $pet->increasePsychedelic($food->getPsychedelic());
        $pet->increaseFood($food->getFood());
        $pet->increaseJunk($food->getJunk());

        if($food->getGrantedStatusEffect() !== null && $food->getGrantedStatusEffectDuration() > 0)
        {
            $this->applyStatusEffect($pet, $food->getGrantedStatusEffect(), $food->getGrantedStatusEffectDuration());
        }

        if($food->getChanceForBonusItem() !== null && mt_rand(1, 1000) <= $food->getChanceForBonusItem())
        {
            $bonusItem = $this->getBonusItemForLuckyFood();

            $comment =
                'While eating ' . $item->getName() . ', ' . $pet->getName() . ' happened to spot this! ' .
                ArrayFunctions::pick_one([
                    '', '... Sure!', '... Why not?', 'As you do!', 'A happy coincidence!', 'Weird!',
                    'Inexplicable, but not unwelcome!', '(Where was it up until this point, I wonder??)',
                    'These things happen. Apparently.', '👍', 'Wild!', 'How\'s _that_ work?',
                    '(I guess eating ' . $item->getName() . ' really _does_ bring good fortune! Who knew!)'
                ])
            ;

            $this->petCollectsItem($bonusItem, $pet, $comment, null);

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

    public function getStatusEffectMaxDuration(string $status)
    {
        switch($status)
        {
            case StatusEffectEnum::INSPIRED: return 24 * 60;
            case StatusEffectEnum::CAFFEINATED: return 8 * 60;
            default: return 24 * 60;
        }
    }

    /**
     * @throws EnumInvalidValueException
     */
    public function applyStatusEffect(Pet $pet, string $status, int $duration)
    {
        $maxDuration = $this->getStatusEffectMaxDuration($status);

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

    public function unequipPet(Pet $pet)
    {
        if($pet->getTool() === null)
            return;

        $pet->getTool()
            ->setLocation(LocationEnum::HOME)
            ->setModifiedOn()
        ;
        $pet->setTool(null);
    }
}
