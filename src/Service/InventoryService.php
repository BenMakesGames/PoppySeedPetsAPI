<?php
namespace App\Service;

use App\Entity\Enchantment;
use App\Entity\Inventory;
use App\Entity\Item;
use App\Entity\ItemFood;
use App\Entity\ItemGroup;
use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Entity\Spice;
use App\Entity\User;
use App\Enum\EnumInvalidValueException;
use App\Enum\LocationEnum;
use App\Enum\MeritEnum;
use App\Enum\StatusEffectEnum;
use App\Functions\ArrayFunctions;
use App\Functions\DateFunctions;
use App\Model\FoodWithSpice;
use App\Model\HouseSim;
use App\Model\ItemQuantity;
use App\Repository\InventoryRepository;
use App\Repository\ItemRepository;
use App\Repository\SpiceRepository;
use App\Service\PetActivity\EatingService;
use Doctrine\ORM\EntityManagerInterface;

class InventoryService
{
    private $itemRepository;
    private $em;
    private $responseService;
    private $inventoryRepository;
    private $squirrel3;
    private $spiceRepository;
    private $eatingService;
    private HouseSimService $houseSimService;
    private StatusEffectService $statusEffectService;

    public function __construct(
        ItemRepository $itemRepository, EntityManagerInterface $em, ResponseService $responseService,
        InventoryRepository $inventoryRepository, Squirrel3 $squirrel3, SpiceRepository $spiceRepository,
        EatingService $eatingService, HouseSimService $houseSimService, StatusEffectService $statusEffectService
    )
    {
        $this->itemRepository = $itemRepository;
        $this->responseService = $responseService;
        $this->em = $em;
        $this->inventoryRepository = $inventoryRepository;
        $this->squirrel3 = $squirrel3;
        $this->spiceRepository = $spiceRepository;
        $this->eatingService = $eatingService;
        $this->houseSimService = $houseSimService;
        $this->statusEffectService = $statusEffectService;
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
    public static function hasRequiredItems($requirements, $inventory): bool
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
     * @return string
     */
    public static function serializeItemList($quantities): string
    {
        if(count($quantities) === 0) return '';

        usort($quantities, fn(ItemQuantity $a, ItemQuantity $b) => $a->item->getId() <=> $b->item->getId());

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
    public function giveInventoryQuantities($quantities, User $owner, User $creator, string $comment, int $location, bool $lockedToOwner = false): array
    {
        if(!is_array($quantities)) $quantities = [ $quantities ];

        $inventory = [];

        foreach($quantities as $itemQuantity)
        {
            for($q = 0; $q < $itemQuantity->quantity; $q++)
                $inventory[] = $this->receiveItem($itemQuantity->item, $owner, $creator, $comment, $location, $lockedToOwner);
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

        if($pet->hasStatusEffect(StatusEffectEnum::HOT_TO_THE_TOUCH))
            $spice = (!$spice || $this->squirrel3->rngNextInt(1, 4) == 4) ? $this->spiceRepository->findOneByName('Spicy') : $spice;

        if($pet->getTool())
        {
            $replacementItemNames = [];
            $cancelGather = false;

            if($pet->getTool()->getSpice())
                $extraItemSpice = (!$spice || $this->squirrel3->rngNextBool()) ? $pet->getTool()->getSpice() : $spice;
            else
                $extraItemSpice = $spice;

            if($pet->getTool()->getItem()->getTool())
            {
                $toolTool = $pet->getTool()->getItem()->getTool();

                // bonus gather from equipment
                if($toolTool->getWhenGather() && $item->getName() === $toolTool->getWhenGather()->getName())
                {
                    if($toolTool->getWhenGatherApplyStatusEffect() && $toolTool->getWhenGatherApplyStatusEffectDuration())
                        $this->statusEffectService->applyStatusEffect($pet, $toolTool->getWhenGatherApplyStatusEffect(), $toolTool->getWhenGatherApplyStatusEffectDuration());

                    if($toolTool->getWhenGatherPreventGather())
                        $cancelGather = true;

                    if($toolTool->getWhenGatherAlsoGather())
                    {
                        $extraItemItem = $this->getItemWithChanceForLuckyTransformation($toolTool->getWhenGatherAlsoGather());

                        $extraItem = (new Inventory())
                            ->setOwner($pet->getOwner())
                            ->setCreatedBy($pet->getOwner())
                            ->setItem($extraItemItem)
                            ->addComment($pet->getName() . ' got this by obtaining ' . $item->getName() . ' with their ' . $pet->getTool()->getItem()->getName() . '.')
                            ->setLocation(LocationEnum::HOME)
                            ->setSpice($extraItemSpice)
                            ->setEnchantment($bonus)
                        ;

                        $this->applySeasonalSpiceToNewItem($extraItem);

                        if(!$this->houseSimService->getState()->addInventory($extraItem))
                            $this->em->persist($extraItem);

                        $this->responseService->setReloadInventory();

                        if($toolTool->getWhenGatherPreventGather())
                            $replacementItemNames[] = $extraItem->getItem()->getNameWithArticle();
                    }
                }
                else if($toolTool->getAttractsBugs() && $item->getIsBug())
                {
                    $extraItem = (new Inventory())
                        ->setOwner($pet->getOwner())
                        ->setCreatedBy($pet->getOwner())
                        ->setItem($item)
                        ->addComment($pet->getName() . ' got this by obtaining ' . $item->getName() . ' with their ' . $pet->getTool()->getItem()->getName() . '.')
                        ->setLocation(LocationEnum::HOME)
                        ->setSpice($extraItemSpice)
                        ->setEnchantment($bonus)
                    ;

                    $this->applySeasonalSpiceToNewItem($extraItem);

                    if(!$this->houseSimService->getState()->addInventory($extraItem))
                        $this->em->persist($extraItem);

                    $this->responseService->setReloadInventory();
                }
            }

            // bonus gather from equipment enchantment effects
            if($pet->getTool()->getEnchantment())
            {
                $bonusEffects = $pet->getTool()->getEnchantment()->getEffects();

                if($bonusEffects->getWhenGather() && $item->getName() === $bonusEffects->getWhenGather()->getName())
                {
                    if($bonusEffects->getWhenGatherApplyStatusEffect() && $bonusEffects->getWhenGatherApplyStatusEffectDuration())
                        $this->statusEffectService->applyStatusEffect($pet, $bonusEffects->getWhenGatherApplyStatusEffect(), $bonusEffects->getWhenGatherApplyStatusEffectDuration());

                    if($bonusEffects->getWhenGatherPreventGather())
                        $cancelGather = true;

                    if($bonusEffects->getWhenGatherAlsoGather())
                    {
                        $extraItemItem = $this->getItemWithChanceForLuckyTransformation(
                            $pet->getTool()->getEnchantment()->getEffects()->getWhenGatherAlsoGather()
                        );

                        $extraItem = (new Inventory())
                            ->setOwner($pet->getOwner())
                            ->setCreatedBy($pet->getOwner())
                            ->setItem($extraItemItem)
                            ->addComment($pet->getName() . ' got this by obtaining ' . $item->getName() . ' with their ' . $pet->getTool()->getItem()->getName() . '.')
                            ->setLocation(LocationEnum::HOME)
                            ->setSpice($extraItemSpice)
                            ->setEnchantment($bonus)
                        ;

                        $this->applySeasonalSpiceToNewItem($extraItem);

                        if(!$this->houseSimService->getState()->addInventory($extraItem))
                            $this->em->persist($extraItem);

                        $this->responseService->setReloadInventory();

                        if($bonusEffects->getWhenGatherPreventGather())
                            $replacementItemNames[] = $extraItem->getItem()->getNameWithArticle();
                    }
                }
                else if($bonusEffects->getAttractsBugs() && $item->getIsBug())
                {
                    $extraItem = (new Inventory())
                        ->setOwner($pet->getOwner())
                        ->setCreatedBy($pet->getOwner())
                        ->setItem($item)
                        ->addComment($pet->getName() . ' got this by obtaining ' . $item->getName() . ' with their ' . $pet->getTool()->getItem()->getName() . '.')
                        ->setLocation(LocationEnum::HOME)
                        ->setSpice($extraItemSpice)
                        ->setEnchantment($bonus)
                    ;

                    $this->applySeasonalSpiceToNewItem($extraItem);

                    if(!$this->houseSimService->getState()->addInventory($extraItem))
                        $this->em->persist($extraItem);

                    $this->responseService->setReloadInventory();
                }
            }

            if($pet->hasStatusEffect(StatusEffectEnum::FRUIT_CLOBBERING) && $item->hasItemGroup('Fresh Fruit'))
            {
                $pectin = $this->itemRepository->findOneByName('Pectin');

                $extraItem = (new Inventory())
                    ->setOwner($pet->getOwner())
                    ->setCreatedBy($pet->getOwner())
                    ->setItem($pectin)
                    ->addComment($pet->getName() . ' got this by obtaining ' . $item->getName() . ' while ' . StatusEffectEnum::FRUIT_CLOBBERING . '.')
                    ->setLocation(LocationEnum::HOME)
                    ->setSpice($extraItemSpice)
                    ->setEnchantment($bonus)
                ;

                $this->applySeasonalSpiceToNewItem($extraItem);

                if(!$this->houseSimService->getState()->addInventory($extraItem))
                    $this->em->persist($extraItem);

                $this->responseService->setReloadInventory();
            }

            if($pet->hasStatusEffect(StatusEffectEnum::HOPPIN) && str_ends_with($item->getName(), 'Toad Legs'))
            {
                $extraItem = (new Inventory())
                    ->setOwner($pet->getOwner())
                    ->setCreatedBy($pet->getOwner())
                    ->setItem($item)
                    ->addComment($pet->getName() . ' got this by obtaining ' . $item->getName() . ' while ' . StatusEffectEnum::HOPPIN . '.')
                    ->setLocation(LocationEnum::HOME)
                    ->setSpice($extraItemSpice)
                    ->setEnchantment($bonus)
                ;

                $this->applySeasonalSpiceToNewItem($extraItem);

                if(!$this->houseSimService->getState()->addInventory($extraItem))
                    $this->em->persist($extraItem);

                $this->responseService->setReloadInventory();
            }

            if($cancelGather)
            {
                if(count($replacementItemNames) > 0)
                    $activityLog->setEntry($activityLog->getEntry() . ' And the ' . $item->getName() . ' transformed into ' . ArrayFunctions::list_nice($replacementItemNames) . '!');
                else
                    $activityLog->setEntry($activityLog->getEntry() . ' However, the ' . $item->getName() . ' melted away instantly!');

                return null;
            }
        }

        if($item->getFood() !== null && count($pet->getLunchboxItems()) === 0 && $this->squirrel3->rngNextInt(1, 20) < 10 - $pet->getFood() - $pet->getJunk() / 2)
        {
            if($this->eatingService->doEat($pet, new FoodWithSpice($item, $spice), $activityLog))
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

        $this->applySeasonalSpiceToNewItem($i);

        if(!$this->houseSimService->getState()->addInventory($i))
            $this->em->persist($i);

        $this->responseService->setReloadInventory();

        return $i;
    }

    private function applySeasonalSpiceToNewItem(Inventory $i): Inventory
    {
        if($i->getSpice() && $this->squirrel3->rngNextBool())
            return $i;

        if($i->getItem()->getName() === 'Worms' && DateFunctions::getFullMoonName(new \DateTimeImmutable()) === 'Worm')
            return $i->setSpice($this->spiceRepository->findOneByName('with Butts'));

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
        $bugs = 1;
        $toolAttractsBugs = false;

        if($pet->getTool())
        {
            if($pet->getTool()->getItem()->getTool())
            {
                if($pet->getTool()->getItem()->getTool()->getAttractsBugs())
                {
                    $toolAttractsBugs = true;
                    $bugs++;
                }

                if($pet->getTool()->getItem()->getTool()->getPreventsBugs())
                    $bugs--;
            }

            if($pet->getTool()->getEnchantment())
            {
                if($pet->getTool()->getEnchantment()->getEffects()->getAttractsBugs())
                {
                    $toolAttractsBugs = true;
                    $bugs++;
                }

                if($pet->getTool()->getEnchantment()->getEffects()->getPreventsBugs())
                    $bugs--;
            }
        }

        if($bugs <= 0)
            return null;

        if($bugName === null)
            $bugName = $this->squirrel3->rngNextFromArray([ 'Spider', 'Centipede', 'Cockroach', 'Line of Ants', 'Fruit Fly', 'Stink Bug', 'Moth' ]);

        $bug = $this->itemRepository->findOneByName($bugName);

        $comment = $toolAttractsBugs ? $pet->getName() . ' caught this in their ' . $pet->getTool()->getItem()->getName() . '!' : 'Ah! How\'d this get inside?!';
        $inventory = null;

        for($i = 0; $i < $bugs; $i++)
        {
            $location = (!$toolAttractsBugs && $pet->getOwner()->getUnlockedBasement() && $this->squirrel3->rngNextInt(1, 4) === 1)
                ? LocationEnum::BASEMENT
                : LocationEnum::HOME
            ;

            $inventory = $this->receiveItem($bug, $pet->getOwner(), null, $comment, $location);

            if($bugName === 'Spider' && $i === 0)
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

            if($itemName === 'Butter')
                return $this->itemRepository->findOneByName('Butterknife');
            else if($itemName === 'Beans')
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

    public function getItem($item): Item
    {
        return is_string($item) ? $this->itemRepository->findOneByName($item) : $item;
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

        $this->applySeasonalSpiceToNewItem($i);

        if($location !== LocationEnum::HOME || !$this->houseSimService->getState()->addInventory($i))
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
    public static function totalFood($quantities): ItemFood
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
    public static function inventoryInSameLocation(array $inventory): bool
    {
        if(count($inventory) === 0)
            throw new \InvalidArgumentException('$inventory must contain at least 1 element.');

        if(count($inventory) === 1)
            return true;

        $locationOfFirstItem = $inventory[0]->getLocation();

        return ArrayFunctions::all($inventory, fn(Inventory $i) => $i->getLocation() === $locationOfFirstItem);
    }

    public static function getRandomItemFromItemGroup(IRandom $rng, ItemGroup $itemGroup): Item
    {
        return $rng->rngNextFromArray($itemGroup->getItems()->toArray());
    }
}
