<?php
namespace App\Service;

use App\Entity\Item;
use App\Entity\Pet;
use App\Entity\User;
use App\Enum\LocationEnum;
use App\Model\HouseSim;
use App\Model\HouseSimRecipe;
use App\Model\IHouseSim;
use App\Model\ItemQuantity;
use App\Model\NoHouseSim;
use App\Repository\InventoryRepository;
use App\Repository\ItemRepository;
use Doctrine\ORM\EntityManagerInterface;

class HouseSimService
{
    // services
    private InventoryRepository $inventoryRepository;
    private ItemRepository $itemRepository;
    private EntityManagerInterface $em;
    private IRandom $squirrel3;

    // data
    private IHouseSim $houseState;
    private array $petIdsThatRanSocialTime = [];

    public function __construct(
        InventoryRepository $inventoryRepository, ItemRepository $itemRepository, EntityManagerInterface $em,
        Squirrel3 $squirrel3
    )
    {
        $this->inventoryRepository = $inventoryRepository;
        $this->itemRepository = $itemRepository;
        $this->em = $em;
        $this->squirrel3 = $squirrel3;

        $this->houseState = new NoHouseSim();
    }

    public function begin(User $user)
    {
        $inventory = $this->inventoryRepository->findBy([
            'owner' => $user,
            'location' => LocationEnum::HOME
        ]);

        $this->houseState = new HouseSim($this->squirrel3, $inventory);
        $this->petIdsThatRanSocialTime = [];
    }

    public function end()
    {
        $toRemove = $this->houseState->getInventoryToRemove();
        $toPersist = $this->houseState->getInventoryToPersist();

        foreach($toRemove as $i)
            $this->em->remove($i);

        foreach($toPersist as $i)
            $this->em->persist($i);

        $this->houseState = new NoHouseSim();
    }

    public function getState()
    {
        return $this->houseState;
    }

    public function hasInventory(string $itemName, int $quantity = 1): bool
    {
        if($quantity === 1)
        {
            $ingredient = $this->itemRepository->findOneByName($itemName);
        }
        else
        {
            $ingredient = new ItemQuantity();
            $ingredient->item = $this->itemRepository->findOneByName($itemName);
            $ingredient->quantity = $quantity;
        }

        return $this->getState()->hasInventory(new HouseSimRecipe([ $ingredient ]));
    }

    public function setPetHasRunSocialTime(Pet $pet)
    {
        $this->petIdsThatRanSocialTime[] = $pet->getId();
    }

    public function getPetHasRunSocialTime(Pet $pet): bool
    {
        return in_array($pet->getId(), $this->petIdsThatRanSocialTime);
    }
}