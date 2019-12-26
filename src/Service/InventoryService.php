<?php
namespace App\Service;

use App\Entity\Inventory;
use App\Entity\Item;
use App\Entity\ItemFood;
use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Entity\User;
use App\Enum\EnumInvalidValueException;
use App\Enum\LocationEnum;
use App\Functions\ArrayFunctions;
use App\Functions\ColorFunctions;
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

    public function __construct(
        ItemRepository $itemRepository, EntityManagerInterface $em, ResponseService $responseService,
        PetExperienceService $petExperienceService
    )
    {
        $this->itemRepository = $itemRepository;
        $this->responseService = $responseService;
        $this->em = $em;
        $this->petExperienceService = $petExperienceService;
    }

    /**
     * @param User $user
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
     * @param User $user
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
            list($itemId, $quantity) = \explode(':', $item);
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
     * @param Inventory[] $inventory
     * @return ItemQuantity[]
     */
    public function buildQuantitiesFromInventory($inventory)
    {
        /** @var ItemQuantity[] $quantities */
        $quantities = [];

        foreach($inventory as $i)
        {
            $item = $i->getItem();

            if(array_key_exists($item->getId(), $quantities))
                $quantities[$item->getId()]->quantity++;
            else
            {
                $quantities[$item->getId()] = new ItemQuantity();
                $quantities[$item->getId()]->item = $item;
                $quantities[$item->getId()]->quantity = 1;
            }
        }

        return array_values($quantities);
    }

    /**
     * @param ItemQuantity|ItemQuantity[] $quantities
     * @return Inventory[]
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

    /**
     * @param Item|string $item
     */
    public function petCollectsItem($item, Pet $pet, string $comment, ?PetActivityLog $activityLog): ?Inventory
    {
        if(is_string($item)) $item = $this->itemRepository->findOneByName($item);

        if($item->getFood() !== null && mt_rand(1, 20) < 10 - $pet->getFood())
        {
            if($this->petExperienceService->doEat($pet, $item, $activityLog))
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

    public function petAttractsRandomBug(Pet $pet): Inventory
    {
        $bugName  = ArrayFunctions::pick_one([ 'Spider', 'Centipede', 'Cockroach', 'Line of Ants', 'Fruit Fly', 'Stink Bug', 'Moth' ]);

        $bug = $this->itemRepository->findOneByName($bugName);

        $i = (new Inventory())
            ->setOwner($pet->getOwner())
            ->setCreatedBy(null)
            ->setItem($bug)
            ->addComment('Ah! How\'d this get inside?!')
        ;

        if($pet->getOwner()->getUnlockedBasement() && mt_rand(1, 4) === 1)
            $i->setLocation(LocationEnum::BASEMENT);

        $this->em->persist($i);

        return $i;
    }

    /**
     * @param Item|string $item
     */
    public function receiveItem($item, User $owner, ?User $creator, string $comment, int $location, bool $lockedToOwner = false): Inventory
    {
        if(is_string($item))
        {
            if(mt_rand(1, 100) === 1)
            {
                if($item === 'Beans')
                    $item = 'Magic Beans';
                else if($item === 'Feathers')
                    $item = 'Ruby Feather';
            }

            $item = $this->itemRepository->findOneByName($item);
        }

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
     */
    public function loseItem($item, User $owner, int $location, int $quantity = 1): int
    {
        if(is_string($item)) $item = $this->itemRepository->findOneByName($item);

        $statement = $this->em->getConnection()->prepare('DELETE FROM inventory WHERE owner_id=:user AND item_id=:item AND location=:location LIMIT ' . $quantity);
        $statement->execute([
            'user' => $owner->getId(),
            'item' => $item->getId(),
            'location' => $location
        ]);

        return $statement->rowCount();
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
}