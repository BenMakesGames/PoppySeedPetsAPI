<?php
namespace App\Service\PetActivity;

use App\Entity\Pet;
use App\Functions\ArrayFunctions;
use App\Service\InventoryService;

// yep. this game has a class called "PoopingService". you're welcome.
class PoopingService
{
    private $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    public function poopDarkMatter(Pet $pet)
    {
        if(mt_rand(1, 20) === 1)
        {
            $this->inventoryService->receiveItem('Dark Matter', $pet->getOwner(), $pet->getOwner(), $pet->getName() . ' ' . ArrayFunctions::pick_one([
                'pooped this. Yay?',
                'pooped this. Neat?',
                'pooped this. Yep.',
                'pooped this. Hooray. Poop.'
            ]));
        }
        else
        {
            $this->inventoryService->receiveItem('Dark Matter', $pet->getOwner(), $pet->getOwner(), $pet->getName() . ' pooped this.');
        }
    }
}