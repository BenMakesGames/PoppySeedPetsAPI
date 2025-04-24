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
use App\Entity\User;
use App\Service\InventoryService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/item/laserGuitar")]
class LaserGuitarController extends AbstractController
{
    #[Route("/{inventory}/overload", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function overload(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        EntityManagerInterface $em
    ): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'laserGuitar/#/overload');
        ItemControllerHelpers::validateLocationSpace($inventory, $em);

        $location = $inventory->getLocation();
        $lockedToOwner = $inventory->getLockedToOwner();

        $inventoryService->receiveItem('Plastic', $user, $user, $user->getName() . ' recovered this from an exploded Laser Guitar.', $location, $lockedToOwner);
        $inventoryService->receiveItem('Plastic', $user, $user, $user->getName() . ' recovered this from an exploded Laser Guitar.', $location, $lockedToOwner);
        $inventoryService->receiveItem('Synth Sample', $user, $user, $user->getName() . ' recovered this from an exploded Laser Guitar.', $location, $lockedToOwner);
        $inventoryService->receiveItem('Synth Sample', $user, $user, $user->getName() . ' recovered this from an exploded Laser Guitar.', $location, $lockedToOwner);
        $inventoryService->receiveItem('Synth Sample', $user, $user, $user->getName() . ' recovered this from an exploded Laser Guitar.', $location, $lockedToOwner);
        $inventoryService->receiveItem('Magic Smoke', $user, $user, $user->getName() . ' recovered this from an exploded Laser Guitar.', $location, $lockedToOwner);

        $em->remove($inventory);

        $em->flush();

        return $responseService->itemActionSuccess('You put the Laser Guitar into overload, and take a few steps back. A few seconds later it explodes into a spectacular show of light, music, and plastic shrapnel. After everything settles down, you collect the remains...', [ 'itemDeleted' => true ]);
    }
}
