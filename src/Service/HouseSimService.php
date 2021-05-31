<?php
namespace App\Service;

use App\Entity\User;
use App\Enum\LocationEnum;
use App\Model\HouseSim;
use App\Model\IHouseSim;
use App\Model\NoHouseSim;
use App\Repository\InventoryRepository;

class HouseSimService
{
    // services
    private InventoryRepository $inventoryRepository;

    // data
    private IHouseSim $houseState;

    public function __construct(InventoryRepository $inventoryRepository)
    {
        $this->houseState = new NoHouseSim();
        $this->inventoryRepository = $inventoryRepository;
    }

    public function begin(User $user)
    {
        $this->houseState = new HouseSim($this->inventoryRepository->findBy([
            'owner' => $user,
            'location' => LocationEnum::HOME
        ]));
    }

    public function end()
    {
        $this->houseState = new NoHouseSim();
    }

    public function getState()
    {
        return $this->houseState;
    }
}