<?php
namespace App\Service;

use App\Entity\Enchantment;
use App\Entity\Inventory;
use App\Entity\Item;
use App\Entity\ItemFood;
use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Entity\Spice;
use App\Entity\StatusEffect;
use App\Entity\User;
use App\Enum\EnumInvalidValueException;
use App\Enum\FlavorEnum;
use App\Enum\LocationEnum;
use App\Enum\MeritEnum;
use App\Enum\StatusEffectEnum;
use App\Functions\ArrayFunctions;
use App\Functions\GrammarFunctions;
use App\Model\FoodWithSpice;
use App\Model\ItemQuantity;
use App\Repository\InventoryRepository;
use App\Repository\ItemRepository;
use Doctrine\ORM\EntityManagerInterface;

class InventoryService
{
    private $itemRepository;
    private $em;
    private $responseService;
    private $petExperienceService;
    private $inventoryRepository;
    private $squirrel3;

    public function __construct(
        ItemRepository $itemRepository, EntityManagerInterface $em, ResponseService $responseService,
        PetExperienceService $petExperienceService, InventoryRepository $inventoryRepository, Squirrel3 $squirrel3
    )
    {
        $this->itemRepository = $itemRepository;
        $this->responseService = $responseService;
        $this->em = $em;
        $this->petExperienceService = $petExperienceService;
        $this->inventoryRepository = $inventoryRepository;
        $this->squirrel3 = $squirrel3;
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
     * @param Item|string|integer $item
     */
    public function countInventoryAnywhere(User $user, $item): int
    {
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
            ->setParameter('owner', $user->getId())
            ->setParameter('item', $itemId)
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
    public function giveInventory($quantities, User $owner, User $creator, string $comment, int $location): array
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

        $this->responseService->setReloadInventory();

        return $inventory;
    }

    public function petCollectsRandomBalloon(Pet $pet, string $message, ?string $specificBalloon, ?PetActivityLog $log)
    {
        if($specificBalloon)
        {
            $balloon = $specificBalloon;
            $locked = true;
        }
        else
        {
            $balloon = $this->squirrel3->rngNextFromArray([
                'Red Balloon',
                'Orange Balloon',
                'Yellow Balloon',
                'Green Balloon',
                'Blue Balloon',
                'Purple Balloon',
            ]);
            $locked = false;
        }

        $item = $this->petCollectsItem($balloon, $pet, $message, $log);

        $item->setLockedToOwner($locked);

        return $item;
    }

    public function petCollectsEnhancedItem($item, ?Enchantment $bonus, ?Spice $spice, Pet $pet, string $comment, ?PetActivityLog $activityLog): ?Inventory
    {
        $item = $this->getItemWithChanceForLuckyTransformation($item);
        $replacementItemNames = [];
        $cancelGather = false;

        if($pet->getTool() && $pet->getTool()->getItem()->getTool())
        {
            $toolTool = $pet->getTool()->getItem()->getTool();

            // bonus gather from equipment
            if($toolTool->getWhenGather() && $item->getName() === $toolTool->getWhenGather()->getName())
            {
                if($toolTool->getWhenGatherPreventGather())
                    $cancelGather = true;

                if($toolTool->getWhenGatherAlsoGather())
                {
                    $extraItem = (new Inventory())
                        ->setOwner($pet->getOwner())
                        ->setCreatedBy($pet->getOwner())
                        ->setItem($toolTool->getWhenGatherAlsoGather())
                        ->addComment($pet->getName() . ' got this by obtaining ' . $item->getName() . ' with their ' . $pet->getTool()->getItem()->getName() . '.')
                        ->setLocation(LocationEnum::HOME)
                        ->setSpice($spice)
                        ->setEnchantment($bonus)
                    ;

                    $this->em->persist($extraItem);

                    $this->responseService->setReloadInventory();

                    if($toolTool->getWhenGatherPreventGather())
                        $replacementItemNames[] = $extraItem->getItem()->getNameWithArticle();
                }
            }
            else if($toolTool->getAttractsBugs() && $item->getName() === 'Weird Beetle')
            {
                $extraItem = (new Inventory())
                    ->setOwner($pet->getOwner())
                    ->setCreatedBy($pet->getOwner())
                    ->setItem($item)
                    ->addComment($pet->getName() . ' got this by obtaining ' . $item->getName() . ' with their ' . $pet->getTool()->getItem()->getName() . '.')
                    ->setLocation(LocationEnum::HOME)
                    ->setSpice($spice)
                    ->setEnchantment($bonus)
                ;

                $this->responseService->setReloadInventory();

                $this->em->persist($extraItem);
            }
        }

        // bonus gather from equipment enchantment effects
        if($pet->getTool() && $pet->getTool()->getEnchantment())
        {
            $bonusEffects = $pet->getTool()->getEnchantment()->getEffects();

            if($bonusEffects->getWhenGather() && $item->getName() === $bonusEffects->getWhenGather()->getName())
            {
                if($bonusEffects->getWhenGatherPreventGather())
                    $cancelGather = true;

                if($bonusEffects->getWhenGatherAlsoGather())
                {
                    $extraItem = (new Inventory())
                        ->setOwner($pet->getOwner())
                        ->setCreatedBy($pet->getOwner())
                        ->setItem($pet->getTool()->getEnchantment()->getEffects()->getWhenGatherAlsoGather())
                        ->addComment($pet->getName() . ' got this by obtaining ' . $item->getName() . ' with their ' . $pet->getTool()->getItem()->getName() . '.')
                        ->setLocation(LocationEnum::HOME)
                        ->setSpice($spice)
                        ->setEnchantment($bonus)
                    ;

                    $this->em->persist($extraItem);

                    $this->responseService->setReloadInventory();

                    if($bonusEffects->getWhenGatherPreventGather())
                        $replacementItemNames[] = $extraItem->getItem()->getNameWithArticle();
                }
            }
            else if($bonusEffects->getAttractsBugs() && $item->getName() === 'Weird Beetle')
            {
                $extraItem = (new Inventory())
                    ->setOwner($pet->getOwner())
                    ->setCreatedBy($pet->getOwner())
                    ->setItem($item)
                    ->addComment($pet->getName() . ' got this by obtaining ' . $item->getName() . ' with their ' . $pet->getTool()->getItem()->getName() . '.')
                    ->setLocation(LocationEnum::HOME)
                    ->setSpice($spice)
                    ->setEnchantment($bonus)
                ;

                $this->em->persist($extraItem);

                $this->responseService->setReloadInventory();
            }
        }

        if($cancelGather)
        {
            if(count($replacementItemNames) > 0)
                $activityLog->setEntry($activityLog->getEntry() . ' And the ' . $item->getName() . ' transformed into ' . ArrayFunctions::list_nice($replacementItemNames) . '!');
            else
                $activityLog->setEntry($activityLog->getEntry() . ' However, the ' . $item->getName() . ' melted away instantly!');

            return null;
        }

        if($item->getFood() !== null && count($pet->getLunchboxItems()) === 0 && $this->squirrel3->rngNextInt(1, 20) < 10 - $pet->getFood())
        {
            if($this->doEat($pet, new FoodWithSpice($item, $spice), $activityLog))
                return null;
        }

        $i = (new Inventory())
            ->setOwner($pet->getOwner())
            ->setCreatedBy($pet->getOwner())
            ->setItem($item)
            ->addComment($comment)
            ->setLocation(LocationEnum::HOME)
            ->setSpice($spice)
            ->setEnchantment($bonus)
        ;

        $this->em->persist($i);

        $this->responseService->setReloadInventory();

        return $i;
    }

    /**
     * @param Item|string $item
     */
    public function petCollectsItem($item, Pet $pet, string $comment, ?PetActivityLog $activityLog): ?Inventory
    {
        return $this->petCollectsEnhancedItem($item, null, null, $pet, $comment, $activityLog);
    }

    public function petAttractsRandomBug(Pet $pet, $bugName = null): ?Inventory
    {
        if($pet->getTool() && $pet->getTool()->preventsBugs())
            return null;

        if($bugName === null)
            $bugName = $this->squirrel3->rngNextFromArray([ 'Spider', 'Centipede', 'Cockroach', 'Line of Ants', 'Fruit Fly', 'Stink Bug', 'Moth' ]);

        $bug = $this->itemRepository->findOneByName($bugName);

        $attractsBugs = $pet->getTool() && $pet->getTool()->attractsBugs();

        $bugs = $attractsBugs ? 2 : 1;
        $comment = $attractsBugs ? $pet->getName() . ' caught this in their ' . $pet->getTool()->getItem()->getName() . '!' : 'Ah! How\'d this get inside?!';
        $inventory = null;

        for($i = 0; $i < $bugs; $i++)
        {
            $location = (!$attractsBugs && $pet->getOwner()->getUnlockedBasement() && $this->squirrel3->rngNextInt(1, 4) === 1)
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

        if($this->squirrel3->rngNextInt(1, 200) === 1)
        {
            $itemName = $itemIsString ? $item : $item->getName();

            if($itemName === 'Beans')
                return $this->itemRepository->findOneByName('Magic Beans');
            else if($itemName === 'Feathers')
                return $this->itemRepository->findOneByName('Ruby Feather');
            else if($itemName === 'Toad Legs')
                return $this->itemRepository->findOneByName('Rainbow Toad Legs');
            else if($itemName === 'Stink Bug')
                return $this->itemRepository->findOneByName('Stinkier Bug');
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

        $this->responseService->setReloadInventory();

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

        $this->responseService->setReloadInventory();

        return count($inventory);
    }

    /**
     * @param Item[]|string[] $itemList
     * @param int|int[] $location
     * @return Item|string|null
     */
    public function loseOneOf($itemList, User $owner, $location)
    {
        $this->squirrel3->rngNextShuffle($itemList);

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
            $this->applyStatusEffect($pet, StatusEffectEnum::CAFFEINATED, $caffeine * 60);
        }
        else if($caffeine < 0)
            $pet->increaseCaffeine($caffeine);

        $pet->increasePsychedelic($food->psychedelic);
        $pet->increaseFood($food->food);
        $pet->increaseJunk($food->junk);

        foreach($food->grantedStatusEffects as $statusEffect)
        {
            $this->applyStatusEffect($pet, $statusEffect['effect'], $statusEffect['duration']);
        }

        if(count($food->leftovers) > 0)
        {
            $leftoverNames = [];

            foreach($food->leftovers as $leftoverItem)
            {
                $leftoverNames[] = $leftoverItem->getNameWithArticle();
                $this->petCollectsItem($leftoverItem, $pet, $pet->getName() . ' ate ' . GrammarFunctions::indefiniteArticle($food->name) . ' ' . $food->name . '; this was left over.', null);
            }

            $wasOrWere = count($food->leftovers) === 1 ? 'was' : 'were';

            $this->responseService->addFlashMessage('After ' . $pet->getName() . ' ate the ' . $food->name . ', ' . ArrayFunctions::list_nice($leftoverNames) . ' ' . $wasOrWere . ' left over.');
        }

        if($food->chanceForBonusItem > 0 && $this->squirrel3->rngNextInt(1, 1000) <= $food->chanceForBonusItem)
        {
            $bonusItem = $this->getBonusItemForLuckyFood();

            $comment =
                'While eating ' . $food->name . ', ' . $pet->getName() . ' happened to spot this! ' .
                $this->squirrel3->rngNextFromArray([
                    '', '... Sure!', '... Why not?', 'As you do!', 'A happy coincidence!', 'Weird!',
                    'Inexplicable, but not unwelcome!', '(Where was it up until this point, I wonder??)',
                    'These things happen. Apparently.', '👍', 'Wild!', 'How\'s _that_ work?',
                ])
            ;

            $this->petCollectsItem($bonusItem, $pet, $comment, null);

            $naniNani = $this->squirrel3->rngNextFromArray([ 'Convenient!', 'Where\'d that come from??', 'How serendipitous!', 'What are the odds!' ]);

            $this->responseService->addFlashMessage('While eating the ' . $food->name . ', ' . $pet->getName() . ' spotted ' . $bonusItem->getNameWithArticle() . '! (' . $naniNani . ')');
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

    public function getStatusEffectMaxDuration(string $status)
    {
        switch($status)
        {
            case StatusEffectEnum::CAFFEINATED: return 8 * 60;
            case StatusEffectEnum::EGGPLANT_CURSED: return 48 * 60;
            case StatusEffectEnum::HEX_HEXED: return 6 * 60;
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
        return $this->itemRepository->findOneByName($this->squirrel3->rngNextFromArray([
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
