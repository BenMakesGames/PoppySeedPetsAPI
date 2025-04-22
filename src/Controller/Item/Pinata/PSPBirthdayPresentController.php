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
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;

#[Route("/item/pspBirthdayPresent")]
class PSPBirthdayPresentController extends AbstractController
{
    #[Route("/{inventory}/open", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function open(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService, IRandom $rng,
        EntityManagerInterface $em
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'pspBirthdayPresent/#/open');

        $location = $inventory->getLocation();

        $loot = [
            'Slice of Poppy Seed* Pie',
            $rng->rngNextFromArray([
                'Ruby Feather', 'Secret Seashell', 'Candle', 'Gold Ring', 'Mysterious Seed',
                'Behatting Scroll', 'Magic Bean Milk', 'Magic Brush'
            ])
        ];

        $possibleLoot = [
            'Sweet Ginger Tea',
            'Coffee Jelly',
            'Caramel-covered Popcorn',
            'Cheese Ravioli',
            'Egg Salad',
            'Konpeitō',
            'Potato-mushroom Stuffed Onion',
        ];

        for($x = 0; $x < 2; $x++)
            $loot[] = $rng->rngNextFromArray($possibleLoot);

        foreach($loot as $itemName)
        {
            $inventoryService->receiveItem(
                $itemName,
                $user,
                $user,
                $user->getName() . ' found this in ' . $inventory->getItem()->getNameWithArticle() . '!',
                $location,
                $inventory->getLockedToOwner()
            );
        }

        $em->remove($inventory);

        $em->flush();

        return $responseService->itemActionSuccess(
            'Inside the present, you found ' . ArrayFunctions::list_nice($loot) . '!',
            [ 'itemDeleted' => true ]
        );
    }
}
