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
use App\Functions\ArrayFunctions;
use App\Functions\ItemRepository;
use App\Model\ItemQuantity;
use App\Model\Music;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\UserAccessor;

#[Route("/item/album")]
class AlbumController
{
    public const array Genres = [
        'Salsa',
        'Meringue',
        'Rock',
        'Rock',
        'Bubblegum'
    ];

    #[Route("/single/{inventory}/listen", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function listenToSingle(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        EntityManagerInterface $em, IRandom $rng, UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $inventory, 'album/single/#/listen');

        $location = $inventory->getLocation();

        $musicNotes = new ItemQuantity(
            ItemRepository::findOneByName($em, 'Music Note'),
            $rng->rngNextInt(3, 4)
        );

        $extraItem = $rng->rngNextFromArray([ 'Pointer', 'NUL', 'Quintessence' ]);

        $inventoryService->giveInventoryQuantities($musicNotes, $user, $user, $user->getName() . ' got this by listening to a Single.', $location);
        $inventoryService->receiveItem($extraItem, $user, $user, $user->getName() . ' got this by listening to a Single.', $location);

        $em->remove($inventory);

        $em->flush();

        return $responseService->itemActionSuccess(
            'It\'s an experimental instrumental piece.' . "\n\n" .
            'You received ' . $musicNotes->quantity . ' music notes, and a ' . $extraItem . '.',
            [ 'itemDeleted' => true ]
        );
    }

    #[Route("/EP/{inventory}/listen", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function listenToEP(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        EntityManagerInterface $em, IRandom $rng, UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $inventory, 'album/EP/#/listen');

        $location = $inventory->getLocation();

        $musicNotes = new ItemQuantity(
            ItemRepository::findOneByName($em, 'Music Note'),
            $rng->rngNextInt(4, 6)
        );

        $genre = $rng->rngNextFromArray(self::Genres);
        $extraItem = $rng->rngNextFromArray([ 'Pointer', 'NUL', 'Quintessence' ]);

        $inventoryService->giveInventoryQuantities($musicNotes, $user, $user, $user->getName() . ' got this by listening to an EP.', $location);
        $inventoryService->receiveItem($genre, $user, $user, $user->getName() . ' got this by listening to a EP.', $location);
        $inventoryService->receiveItem($extraItem, $user, $user, $user->getName() . ' got this by listening to a EP.', $location);

        $em->remove($inventory);

        $em->flush();

        return $responseService->itemActionSuccess(
            "<em>♫ " . $rng->rngNextFromArray(Music::Lyrics) . " ♪</em>\n\n" .
            'What totally and completely original songs these pets have written! In the ' . mb_strtolower($genre) . ' genre, of course... so you get some ' . $genre . "! (Of course!)\n\n" .
            'You also received ' . $musicNotes->quantity . ' Music Notes, and a ' . $extraItem . '.',
            [ 'itemDeleted' => true ]
        );
    }

    #[Route("/LP/{inventory}/listen", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function listenToLP(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        EntityManagerInterface $em, IRandom $rng, UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $inventory, 'album/LP/#/listen');

        $location = $inventory->getLocation();

        $musicNotes = new ItemQuantity(
            ItemRepository::findOneByName($em, 'Music Note'),
            $rng->rngNextInt(4, 6)
        );

        $genre = $rng->rngNextFromArray(self::Genres);

        $extraItems = [ 'NUL', 'Pointer', 'Quintessence' ];

        $inventoryService->giveInventoryQuantities($musicNotes, $user, $user, $user->getName() . ' got this by listening to an LP.', $location);
        $inventoryService->receiveItem($genre, $user, $user, $user->getName() . ' got this by listening to a LP.', $location);

        foreach($extraItems as $extraItem)
            $inventoryService->receiveItem($extraItem, $user, $user, $user->getName() . ' got this by listening to a LP.', $location);

        $em->remove($inventory);

        $em->flush();

        return $responseService->itemActionSuccess(
            "<em>♫ " . $rng->rngNextFromArray(Music::Lyrics) . " ♪</em>\n\n" .
            'What totally and completely original songs these pets have written! In the ' . mb_strtolower($genre) . ' genre, of course... so you get some ' . $genre . "! (Of course!)\n\n" .
            'You also received ' . $musicNotes->quantity . ' Music Notes, ' . ArrayFunctions::list_nice($extraItems) . '.',
            [ 'itemDeleted' => true ]
        );
    }
}
