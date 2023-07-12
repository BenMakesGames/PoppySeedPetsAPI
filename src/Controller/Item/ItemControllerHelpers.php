<?php
namespace App\Controller\Item;

use App\Entity\Inventory;
use App\Entity\User;
use App\Enum\LocationEnum;
use App\Exceptions\PSPInvalidOperationException;
use App\Exceptions\PSPNotFoundException;
use App\Service\InventoryService;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class ItemControllerHelpers
{
    public static function validateInventory(?User $user, Inventory $inventory, string $action)
    {
        if(!$user || $user->getId() !== $inventory->getOwner()->getId())
            throw new PSPNotFoundException('That item does not exist.');

        if(!$inventory->getItem()->hasUseAction($action))
            throw new PSPInvalidOperationException('That item cannot be used in that way!');
    }

    public static function validateHouseSpace(Inventory $inventory, InventoryService $inventoryService)
    {
        if($inventory->getLocation() !== LocationEnum::HOME)
            return;

        $itemsInHouse = $inventoryService->countTotalInventory($inventory->getOwner(), LocationEnum::HOME);

        if($itemsInHouse > 150)
        {
            $index = $itemsInHouse + $inventory->getOwner()->getId();

            $message = [
                'Whoa! You\'ve already over 150 items?! The server might LITERALLY EXPLODE if I let you open this!',
                'Waitwaitwaitwait... over 150 items? Sorry, you\'re already WAY over the limit!',
                'Whaaaat? You\'re over 150 items already? Dang! You know you\'re technically not supposed to go over 100, right??',
            ][$index % 3];

            throw new UnprocessableEntityHttpException($message);
        }
    }
}
