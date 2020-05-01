<?php
namespace App\Controller\Item;

use App\Controller\PoppySeedPetsController;
use App\Entity\Inventory;
use App\Enum\LocationEnum;
use App\Functions\ArrayFunctions;
use App\Service\InventoryService;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

abstract class PoppySeedPetsItemController extends PoppySeedPetsController
{
    protected function validateInventory(Inventory $inventory, string $action)
    {
        if(!$this->getUser() || $this->getUser()->getId() !== $inventory->getOwner()->getId() || !$inventory->getItem()->hasUseAction($action))
            throw new UnprocessableEntityHttpException('That item no longer exists, or cannot be used in that way.');
    }

    protected function validateHouseSpace(Inventory $inventory, InventoryService $inventoryService)
    {
        if($inventory->getLocation() !== LocationEnum::HOME)
            return;

        if($inventoryService->countTotalInventory($inventory->getOwner(), LocationEnum::HOME) > 150)
        {
            throw new UnprocessableEntityHttpException(ArrayFunctions::pick_one([
                'Whoa! You\'ve already over 150 items?! The server might LITERALLY EXPLODE if I let you open this!',
                'Waitwaitwaitwait... over 150 items? Sorry, you\'re already WAY over the limit!',
                'Whaaaat? You\'re over 150 items already? Dang! You know you\'re technically not supposed to go over 100, right??',
            ]));
        }
    }
}
