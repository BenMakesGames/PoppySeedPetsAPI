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
use App\Enum\UserStat;
use App\Service\InventoryService;
use App\Service\IRandom;
use App\Service\ResponseService;
use App\Service\TransactionService;
use App\Service\UserStatsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\UserAccessor;

#[Route("/item/plasticBottle")]
class PlasticBottleController
{
    #[Route("/{inventory}/open", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function open(
        Inventory $inventory, ResponseService $responseService, InventoryService $inventoryService, IRandom $rng,
        EntityManagerInterface $em, UserStatsService $userStatsRepository, TransactionService $transactionService,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $inventory, 'plasticBottle/#/open');

        $location = $inventory->getLocation();
        $lockedToOwner = $inventory->getLockedToOwner();

        $bottlesOpened = $userStatsRepository->incrementStat($user, UserStat::PlasticBottlesOpened);

        if($bottlesOpened->getValue() > 3 && $rng->rngNextInt(1, 50) === 1)
        {
            $inventoryService->receiveItem('Lightning in a Bottle', $user, $user, $user->getName() . ' found this in a Plastic Bottle.', $location, $lockedToOwner);

            $message = 'You open the can - OMG! THERE\'S LIGHTNING IN THIS BOTTLE! (Despite this, you do also recycle the bottle, and get 1♺. Woo?)';
        }
        else
        {
            // my, a lot of things sure are bottleable!
            $item = $rng->rngNextFromArray([
                'Blackberry Lassi',
                'Blackberry Wine',
                'Blueberry Lassi',
                'Blueberry Wine',

                'Caramel',
                'Carrot Juice',
                'Carrot Wine',
                'Chicha Morada',
                'Chocolate Syrup',
                'Chocolate Wine',
                'Coconut Milk',
                'Coffee Bean Tea',
                'Coffee Bean Tea with Mammal Extract',
                'Corn Syrup',
                'Coquito',
                'Creamy Milk',
                'Cucumber Water',

                'Dandelion Wine',
                'Dashi',
                'Dreamwalker\'s Tea',

                'Eggnog',

                'Fig Wine',
                'Freshly-squeezed Fish Oil',

                'Ginger Beer',
                'Ginger Tea',
                'Glue',
                'Gravy',
                'Green Dye',

                'Horchata',

                'Invisibility Juice',

                'Jellyfish Juice',

                'Kilju',
                'Kombucha',
                'Kumis',

                'Mango Lassi',
                'Merchant Fish',
                'Missing Mail',

                'Naner Ketchup',

                'Oil',
                'Orange Juice',

                'Paint Stripper',
                'Pamplemousse Juice',
                'Petrichor',

                'Quinacridone Magenta Dye',

                'Red Juice',
                'Red Wine',

                'Short Glass of Greenade',
                'Soy Sauce',
                'Sweet Black Tea',
                'Sweet Coffee Bean Tea',
                'Sweet Coffee Bean Tea with Mammal Extract',
                'Sweet Ginger Tea',
                'Sweet Tea with Mammal Extract',

                'Tall Glass of Yellownade',
                'Tepache',
                'Tiny Tea',
                'Totally Tea',
                'Tremendous Tea',

                'Useless Fizz',

                'Vinegar',

                'Werebane',

                'Yellow Dye',
            ]);

            $inventoryService->receiveItem($item, $user, $user, $user->getName() . ' found this in a bottle. A Plastic Bottle.', $location, $lockedToOwner)
                ->setSpice($inventory->getSpice())
            ;

            $message = 'You open the bottle; it has ' . $item . ' inside! (You also recycle the bottle, and get 1♺. Woo.)';
        }

        $transactionService->getRecyclingPoints($user, 1, 'You recycled a Plastic Bottle after emptying it.');

        $em->remove($inventory);

        $em->flush();

        return $responseService->itemActionSuccess($message, [ 'itemDeleted' => true ]);
    }
}
