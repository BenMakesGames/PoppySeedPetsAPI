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


namespace App\Controller\Item\Book;

use App\Controller\Item\ItemControllerHelpers;
use App\Entity\Inventory;
use App\Entity\User;
use App\Service\CookingService;
use App\Service\ResponseService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\UserAccessor;

#[Route("/item/iceCream")]
class IceCreamController
{
    #[Route("/{inventory}/upload", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function upload(
        Inventory $inventory, ResponseService $responseService, CookingService $cookingService,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $inventory, 'iceCream/#/upload');

        $message = $cookingService->showRecipeNamesToCookingBuddy($user, [
            'Blackberry Ice Cream',
            'Chocolate Ice Cream',
            'Honeydont Ice Cream',
            'Naner Ice Cream',
            'Blueberry Ice Cream',

            'Blackberry Ice Cream Sammy',
            'Chocolate Ice Cream Sammy',
            'Honeydont Ice Cream Sammy',
            'Naner Ice Cream Sammies',
            'Blueberry Ice Cream Sammy',
        ]);

        return $responseService->itemActionSuccess($message);
    }

    #[Route("/{inventory}/read", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function read(Inventory $inventory, ResponseService $responseService,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        ItemControllerHelpers::validateInventory($userAccessor->getUserOrThrow(), $inventory, 'iceCream/#/read');

        return $responseService->itemActionSuccess('# We All Scream

#### Simple Ice Cream

* Sugar
* Creamy Milk
* Egg
* Something to act as the flavor! (Fruits or Cocoa Powder are solid choices!)

#### Ice Cream Sammies

Homemade ice cream sammies are fun to make: put some ice cream on some cookies, and you\'re good to go! Here are my favorite combinations:

* Blackberry Ice Cream + World\'s Second-best Sugar Cookie
* Chocolate Ice Cream + World\'s Best Sugar Cookie
* Honeydont Ice Cream + Browser Cookie
* Naner Ice Cream + Mini Chocolate Chip Cookies
* Blueberry Ice Cream + Shortbread Cookies
');
    }
}
