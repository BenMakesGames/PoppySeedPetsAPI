<?php
namespace App\Service;

use App\Entity\Inventory;
use App\Entity\Item;
use App\Entity\ItemFood;
use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Entity\User;
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
    private $petService;

    public function __construct(
        ItemRepository $itemRepository, EntityManagerInterface $em, ResponseService $responseService,
        PetService $petService
    )
    {
        $this->itemRepository = $itemRepository;
        $this->responseService = $responseService;
        $this->em = $em;
        $this->petService = $petService;
    }

    /**
     * @param User $user
     * @param Item|string|integer $item
     */
    public function countInventory(User $user, $item): int
    {
        if(is_string($item))
            $item = $this->itemRepository->findOneByName($item);

        if($item instanceof Item)
            $item = $item->getId();

        if(!is_integer($item))
            throw new \InvalidArgumentException('item must be an Item, string, or integer.');

        return (int)$this->em->createQueryBuilder()
            ->select('COUNT(i.id)')
            ->from(Inventory::class, 'i')
            ->andWhere('i.owner=:owner')
            ->andWhere('i.item=:item')
            ->setParameter('owner', $user->getId())
            ->setParameter('item', $item)
            ->getQuery()
            ->getSingleScalarResult()
        ;
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
        if(\count($quantities) === 0) return '';

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
    public function giveInventory($quantities, User $owner, User $creator, string $comment)
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

        if($item->getFood() !== null && \mt_rand(1, 20) < 10 - $pet->getFood())
        {
            if($this->petService->doEat($pet, $item, $activityLog))
                return null;
        }

        $i = (new Inventory())
            ->setOwner($pet->getOwner())
            ->setCreatedBy($pet->getOwner())
            ->setItem($item)
            ->addComment($comment)
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

        $this->em->persist($i);

        return $i;
    }

    /**
     * @param Item|string $item
     */
    public function receiveItem($item, User $owner, ?User $creator, string $comment): Inventory
    {
        if(is_string($item))
        {
            if($item === 'Beans' && mt_rand(1, 100) === 1)
                $item = 'Magic Beans';

            $item = $this->itemRepository->findOneByName($item);
        }

        $i = (new Inventory())
            ->setOwner($owner)
            ->setCreatedBy($creator)
            ->setItem($item)
            ->addComment($comment)
        ;

        $this->em->persist($i);

        return $i;
    }

    public function loseItem($item, User $owner, int $quantity = 1): int
    {
        if(is_string($item)) $item = $this->itemRepository->findOneByName($item);

        $statement = $this->em->getConnection()->prepare('DELETE FROM inventory WHERE owner_id=:user AND item_id=:item LIMIT ' . $quantity);
        $statement->execute([
            'user' => $owner->getId(),
            'item' => $item->getId()
        ]);

        return $statement->rowCount();
    }

    public function generateColorFromRange(string $range): string
    {
        $hsl = explode(',', $range);
        $hRange = explode('-', $hsl[0]);
        $sRange = explode('-', $hsl[1]);
        $lRange = explode('-', $hsl[2]);

        $rgb = ColorFunctions::HSL2RGB(
            mt_rand($hRange[0], $hRange[1]) / 360,
            mt_rand($sRange[0], $sRange[1]) / 100,
            mt_rand($lRange[0], $lRange[1]) / 100
        );

        return ColorFunctions::RGB2Hex((int)$rgb['r'], (int)$rgb['g'], (int)$rgb['b']);
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