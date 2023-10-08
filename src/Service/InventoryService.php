<?php
namespace App\Service;

use App\Entity\Enchantment;
use App\Entity\Inventory;
use App\Entity\Item;
use App\Entity\ItemGroup;
use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Entity\Spice;
use App\Entity\User;
use App\Enum\EnumInvalidValueException;
use App\Enum\LocationEnum;
use App\Enum\MeritEnum;
use App\Enum\PetActivityLogInterestingnessEnum;
use App\Enum\StatusEffectEnum;
use App\Enum\UnlockableFeatureEnum;
use App\Functions\ArrayFunctions;
use App\Functions\DateFunctions;
use App\Functions\ItemRepository;
use App\Functions\StatusEffectHelpers;
use App\Model\FoodWithSpice;
use App\Model\ItemQuantity;
use App\Repository\SpiceRepository;
use App\Service\PetActivity\EatingService;
use Doctrine\ORM\EntityManagerInterface;

class InventoryService
{
    private EntityManagerInterface $em;
    private ResponseService $responseService;
    private IRandom $squirrel3;
    private EatingService $eatingService;
    private HouseSimService $houseSimService;

    public function __construct(
        EntityManagerInterface $em, ResponseService $responseService, IRandom $squirrel3,
        EatingService $eatingService, HouseSimService $houseSimService
    )
    {
        $this->responseService = $responseService;
        $this->em = $em;
        $this->squirrel3 = $squirrel3;
        $this->eatingService = $eatingService;
        $this->houseSimService = $houseSimService;
    }

    /**
     * @throws EnumInvalidValueException
     */
    public static function countInventory(EntityManagerInterface $em, int $userId, int $itemId, int $location): int
    {
        if(!LocationEnum::isAValue($location))
            throw new EnumInvalidValueException(LocationEnum::class, $location);

        return (int)$em->createQueryBuilder()
            ->select('COUNT(i.id)')
            ->from(Inventory::class, 'i')
            ->andWhere('i.owner=:owner')
            ->andWhere('i.item=:item')
            ->andWhere('i.location=:location')
            ->setParameter('owner', $userId)
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
    public static function hasRequiredItems(array $requirements, array $inventory): bool
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

            $itemQuantity->item = ItemRepository::findOneById($this->em, $itemId);
            $itemQuantity->quantity = (int)$quantity;

            $quantities[] = $itemQuantity;
        }

        return $quantities;
    }

    /**
     * @param ItemQuantity[] $quantities
     */
    public static function serializeItemList(array $quantities): string
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

    public function petCollectsEnhancedItem($item, ?Enchantment $bonus, ?Spice $spice, Pet $pet, string $comment, ?PetActivityLog $activityLog): ?Inventory
    {
        $item = $this->getItemWithChanceForLuckyTransformation($item);

        if($pet->hasStatusEffect(StatusEffectEnum::HOT_TO_THE_TOUCH))
            $spice = (!$spice || $this->squirrel3->rngNextInt(1, 4) == 4) ? SpiceRepository::findOneByName($this->em, 'Spicy') : $spice;

        $cancelGather = false;
        $replacementItemNames = [];
        $extraItemSpice = null;

        if($pet->hasMerit(MeritEnum::RUMPELSTILTSKINS_CURSE))
        {
            if($item->getName() === 'Gold Bar' || $item->getName() === 'Gold Ore')
            {
                $activityLog
                    ->setEntry($activityLog->getEntry() . ' The ' . $item->getName() . ' was transformed into Wheat by their curse!')
                    ->addInterestingness(PetActivityLogInterestingnessEnum::UNCOMMON_ACTIVITY)
                ;

                $item = ItemRepository::findOneByName($this->em, 'Wheat');
            }
            else if($item->getName() === 'Wheat' || $item->getName() === 'Wheat Flower')
            {
                $activityLog
                    ->setEntry($activityLog->getEntry() . ' The ' . $item->getName() . ' was transformed into a Gold Bar by their curse!')
                    ->addInterestingness(PetActivityLogInterestingnessEnum::UNCOMMON_ACTIVITY)
                ;

                $item = ItemRepository::findOneByName($this->em, 'Gold Bar');
            }
        }

        if($pet->getTool())
        {
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
                        StatusEffectHelpers::applyStatusEffect($this->em, $pet, $toolTool->getWhenGatherApplyStatusEffect(), $toolTool->getWhenGatherApplyStatusEffectDuration());

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
                        StatusEffectHelpers::applyStatusEffect($this->em, $pet, $bonusEffects->getWhenGatherApplyStatusEffect(), $bonusEffects->getWhenGatherApplyStatusEffectDuration());

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
        }

        if($pet->hasMerit(MeritEnum::CELESTIAL_CHORUSER) && $item->hasItemGroup('Outer Space'))
        {
            $musicNote = ItemRepository::findOneByName($this->em, 'Music Note');

            $extraItem = (new Inventory())
                ->setOwner($pet->getOwner())
                ->setCreatedBy($pet->getOwner())
                ->setItem($musicNote)
                ->addComment($pet->getName() . ' got this by obtaining ' . $item->getName() . ' as a Celestial Choruser.')
                ->setLocation(LocationEnum::HOME)
                ->setSpice($extraItemSpice)
                ->setEnchantment($bonus)
            ;

            $activityLog->addInterestingness(PetActivityLogInterestingnessEnum::ACTIVITY_USING_MERIT);

            $this->applySeasonalSpiceToNewItem($extraItem);

            if(!$this->houseSimService->getState()->addInventory($extraItem))
                $this->em->persist($extraItem);

            $this->responseService->setReloadInventory();
        }

        if($pet->hasStatusEffect(StatusEffectEnum::FRUIT_CLOBBERING) && $item->hasItemGroup('Fresh Fruit'))
        {
            $pectin = ItemRepository::findOneByName($this->em, 'Pectin');

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

        if($pet->hasStatusEffect(StatusEffectEnum::SPICED) && $item->getSpice())
        {
            $extraItem = (new Inventory())
                ->setOwner($pet->getOwner())
                ->setCreatedBy($pet->getOwner())
                ->setItem($item)
                ->addComment($pet->getName() . ' got this extra ' . $item->getName() . ' thanks to being ' . StatusEffectEnum::SPICED . '.')
                ->setLocation(LocationEnum::HOME)
                ->setEnchantment($bonus)
            ;

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
                ->addComment($pet->getName() . ' got this extra ' . $item->getName() . ' thanks to being ' . StatusEffectEnum::HOPPIN . '.')
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
            return $i->setSpice(SpiceRepository::findOneByName($this->em, 'with Butts'));

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

        $bug = ItemRepository::findOneByName($this->em, $bugName);

        $comment = $toolAttractsBugs ? $pet->getName() . ' caught this in their ' . $pet->getTool()->getItem()->getName() . '!' : 'Ah! How\'d this get inside?!';
        $inventory = null;

        for($i = 0; $i < $bugs; $i++)
        {
            $location = (!$toolAttractsBugs && $pet->getOwner()->hasUnlockedFeature(UnlockableFeatureEnum::Basement) && $this->squirrel3->rngNextInt(1, 4) === 1)
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
                return ItemRepository::findOneByName($this->em, 'Butterknife');
            else if($itemName === 'Beans')
                return ItemRepository::findOneByName($this->em, 'Magic Beans');
            else if($itemName === 'Feathers')
                return ItemRepository::findOneByName($this->em, 'Ruby Feather');
            else if($itemName === 'Toad Legs')
                return ItemRepository::findOneByName($this->em, 'Rainbow Toad Legs');
            else if($itemName === 'Stink Bug')
                return ItemRepository::findOneByName($this->em, 'Stinkier Bug');
            else if($itemName === 'Naner')
                return ItemRepository::findOneByName($this->em, 'Bunch of Naners');
            else
                return $itemIsString ? ItemRepository::findOneByName($this->em, $item) : $item;
        }
        else
            return $itemIsString ? ItemRepository::findOneByName($this->em, $item) : $item;
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
     * @param int|int[] $location
     */
    public function loseItem(User $owner, int $itemId, $location, int $quantity = 1): int
    {
        $inventory = $this->em->getRepository(Inventory::class)->findBy(
            [
                'owner' => $owner->getId(),
                'item' => $itemId,
                'location' => $location
            ],
            null,
            $quantity
        );

        if(count($inventory) < $quantity)
            return 0;

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
