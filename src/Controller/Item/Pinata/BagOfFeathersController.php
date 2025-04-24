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
use App\Functions\ArrayFunctions;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/item/bagOfFeathers")]
class BagOfFeathersController extends AbstractController
{
    #[Route("/{inventory}/open", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function open(
        Inventory $inventory, InventoryService $inventoryService, EntityManagerInterface $em,
        ResponseService $responseService, IRandom $rng
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'bagOfFeathers/#/open');

        $loot = [
            'Feathers',
            'Feathers',
            $rng->rngNextFromArray([ 'Black Feathers', 'White Feathers' ]),
            $rng->rngNextFromArray([ 'Black Feathers', 'White Feathers', 'Wings' ]),
            'Ruby Feather',
        ];

        $location = $inventory->getLocation();
        $locked = $inventory->getLockedToOwner();

        $em->remove($inventory);

        foreach($loot as $itemName)
        {
            $inventoryService->receiveItem($itemName, $user, $user, $user->getName() . ' got this from ' . $inventory->getItem()->getNameWithArticle() . '.', $location, $locked);
        }

        $em->flush();

        return $responseService->itemActionSuccess('You open the bag, revealing ' . ArrayFunctions::list_nice($loot) . '!', [ 'itemDeleted' => true ]);
    }
}
