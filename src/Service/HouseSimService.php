<?php
declare(strict_types=1);

namespace App\Service;

use App\Entity\Inventory;
use App\Entity\Pet;
use App\Entity\User;
use App\Enum\LocationEnum;
use App\Functions\ItemRepository;
use App\Model\HouseSim;
use App\Model\HouseSimRecipe;
use App\Model\IHouseSim;
use App\Model\ItemQuantity;
use App\Model\NoHouseSim;
use Doctrine\ORM\EntityManagerInterface;

class HouseSimService
{
    // data
    private IHouseSim $houseState;
    private array $petIdsThatRanSocialTime = [];

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly PerformanceProfiler $performanceProfiler
    )
    {
        $this->houseState = new NoHouseSim();
    }

    public function begin(EntityManagerInterface $em, User $user)
    {
        $time = microtime(true);

        $inventory = $em->getRepository(Inventory::class)->findBy([
            'owner' => $user,
            'location' => LocationEnum::HOME
        ]);

        $this->performanceProfiler->logExecutionTime(__METHOD__ . ' - Fetch Inventory', microtime(true) - $time);

        $this->houseState = new HouseSim($inventory);
        $this->petIdsThatRanSocialTime = [];
    }

    public function end(EntityManagerInterface $em)
    {
        $toRemove = $this->houseState->getInventoryToRemove();
        $toPersist = $this->houseState->getInventoryToPersist();

        foreach($toRemove as $i)
            $em->remove($i);

        foreach($toPersist as $i)
            $em->persist($i);

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
            $ingredient = ItemRepository::findOneByName($this->em, $itemName);
        }
        else
        {
            $ingredient = new ItemQuantity();
            $ingredient->item = ItemRepository::findOneByName($this->em, $itemName);
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