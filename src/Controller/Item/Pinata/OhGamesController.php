<?php
declare(strict_types=1);

/**
 * This file is part of the Poppy Seed Pets API.
 *
 * The Poppy Seed Pets API is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 *
 * The Poppy Seed Pets API is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with The Poppy Seed Pets API. If not, see <https://www.gnu.org/licenses/>.
 */


namespace App\Controller\Item\Pinata;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Inventory;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\UserAccessor;

#[Route("/item/ohGames")]
class OhGamesController
{
    #[Route("/{inventory}/rockPaintingKit", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function rockPaintingKit(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        EntityManagerInterface $em, IRandom $rng,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $inventory, 'ohGames/#/rockPaintingKit');
        ItemControllerHelpers::validateLocationSpace($inventory, $em);

        $location = $inventory->getLocation();
        $lockedToOwner = $inventory->getLockedToOwner();

        $dyes = [
            'Green Dye', 'Yellow Dye', 'Quinacridone Magenta Dye',
        ];

        $lucky = $rng->rngNextInt(1, 100) === 1;

        $loot = [
            'Rock', 'Rock', $lucky ? 'Meteorite' : 'Rock',
            $rng->rngNextFromArray($dyes),
            $rng->rngNextFromArray($dyes),
            $rng->rngNextFromArray($dyes)
        ];

        foreach($loot as $itemName)
            $inventoryService->receiveItem($itemName, $user, $user, $user->getName() . ' got this from ' . $inventory->getItem()->getNameWithArticle() . '!', $location, $lockedToOwner);

        $em->remove($inventory);
        $em->flush();

        $responseService->setReloadInventory();

        $message = 'You open the box, revealing three rocks, and three dyes!';

        if($lucky)
            $message .= ' (One of the rocks seems a little different, though...)';

        return $responseService->itemActionSuccess($message, [ 'itemDeleted' => true ]);
    }

    #[Route("/{inventory}/sneqosAndLadders", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function sneqosAndLadders(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        EntityManagerInterface $em, IRandom $rng,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $inventory, 'ohGames/#/sneqosAndLadders');
        ItemControllerHelpers::validateLocationSpace($inventory, $em);

        $location = $inventory->getLocation();
        $lockedToOwner = $inventory->getLockedToOwner();

        $lucky = $rng->rngNextInt(1, 50) === 1;

        $loot = [
            'Scales', 'Talon', 'Talon',
            'Crooked Stick', 'Crooked Stick', 'Crooked Stick', $lucky ? 'Stick Insect' : 'Crooked Stick',
            $rng->rngNextFromArray([ 'Glowing Four-sided Die', 'Glowing Six-sided Die', 'Glowing Eight-sided Die' ])
        ];

        foreach($loot as $itemName)
            $inventoryService->receiveItem($itemName, $user, $user, $user->getName() . ' got this from ' . $inventory->getItem()->getNameWithArticle() . '!', $location, $lockedToOwner);

        $em->remove($inventory);
        $em->flush();

        $responseService->setReloadInventory();

        $message = 'You open the box, revealing scales, a couple fangs, some sticks, and a die! (They weren\'t lying when they said "some assembly required"!)';

        if($lucky)
            $message .= "\n\n" . $rng->rngNextFromArray([ 'Whoa, hey!', 'Whoamygoodness!', 'Hey, whoa!', 'Holy poop!', 'Eek!' ]) . ' Did one of the sticks just move?!';

        return $responseService->itemActionSuccess($message, [ 'itemDeleted' => true ]);
    }
}
