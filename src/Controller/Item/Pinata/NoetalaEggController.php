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
use App\Service\IRandom;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/item/noetalaEgg")]
class NoetalaEggController extends AbstractController
{
    #[Route("/{inventory}/open", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function open(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService,
        EntityManagerInterface $em, IRandom $squirrel3
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'noetalaEgg/#/open');
        ItemControllerHelpers::validateLocationSpace($inventory, $em);

        $location = $inventory->getLocation();

        $inventoryService->receiveItem('Quinacridone Magenta Dye', $user, $user, 'This oozed out of a Noetala Egg that ' . $user->getName() . ' opened.', $location);

        // 75% chance of more Fluff
        $includeFluff = $squirrel3->rngNextInt(1, 4) != 1;

        $loot = $squirrel3->rngNextFromArray([
            'Quintessence', 'Talon', 'Tentacle', 'Green Dye'
        ]);

        if($includeFluff)
            $inventoryService->receiveItem('Fluff', $user, $user, $user->getName() . ' peeled this off a Noetala Egg.', $location);

        $inventoryService->receiveItem($loot, $user, $user, $user->getName() . ' harvested this from a Noetala Egg.', $location);

        $em->remove($inventory);

        $em->flush();

        if($includeFluff)
            return $responseService->itemActionSuccess('You pull the clinging fibers off of the Noetala Egg, and break it open; a strange, purple liquid oozes out. Your reward? ' . $loot . '. (Well, and some Fluff from the fibers.)' , [ 'itemDeleted' => true ]);
        else
            return $responseService->itemActionSuccess('You break the egg open; a strange, purple liquid oozes out. Your reward? ' . $loot . '.' , [ 'itemDeleted' => true ]);
    }
}
