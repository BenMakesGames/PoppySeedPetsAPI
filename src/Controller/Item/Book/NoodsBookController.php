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
use App\Service\ResponseService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\UserAccessor;

#[Route("/item/noodsBook")]
class NoodsBookController
{
    #[Route("/{inventory}/upload", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function upload(
        Inventory $inventory, ResponseService $responseService, UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $inventory, 'noodsBook/#/upload');

        if(!$user->getCookingBuddy())
            $message = 'You need a Cooking Buddy to do this.';
        else
            $message = 'Your Cooking Buddy is too embarrassed to look at this book.';

        return $responseService->itemActionSuccess($message);
    }

    #[Route("/{inventory}/read", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function read(Inventory $inventory, ResponseService $responseService,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        ItemControllerHelpers::validateInventory($userAccessor->getUserOrThrow(), $inventory, 'noodsBook/#/read');

        return $responseService->itemActionSuccess('## Noods

* Wheat Flour
* Egg

OR

* Rice Flour
* Baking Soda (optional)

(There\'s a picture of what looks like some kind of squirming mass, but someone\'s scribbled over most of it with permanent marker.)

## Mackin Cheese

* Noods
* Cheese
* Creamy Milk
* Butter
* Flour

(There\'s a picture of what looks like someone\'s glistening knee, or elbow, poking from just off-frame, and there\'s steam everywhere... what kind of picture is this?) 

## Stroganoff

* Noods
* Fish
* Mushrooms
* Onions
* Oil (or Butter)
* Sour Cream
* Flour (any; acts as a thickener)

(There\'s a blurry picture of some kind of white goop, maybe? It\'s hard to tell.)

## Super-simple Spaghet

* Noods
* Tomato

(There\'s a picture of a shallow pool of some pale liquid that\'s clearly accumulated from dripping off of from something directly above it, but what that something is has been cut out of the book!)
');
    }
}