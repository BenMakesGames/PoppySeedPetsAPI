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
use App\Service\CookingService;
use App\Service\ResponseService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\UserAccessor;

#[Route("/item/coconutCookbook")]
class CoconutCookbookController
{
    #[Route("/{inventory}/upload", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function upload(
        Inventory $inventory, ResponseService $responseService, CookingService $cookingService,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $inventory, 'coconutCookbook/#/upload');

        $message = $cookingService->showRecipeNamesToCookingBuddy($user, [
            'Coconut Milk',
            'Coconut Milk (from Coconut Half)',
            'Rice Puddin\'',
            'Mango Sticky Rice',
            'Papeete I\'a Ota',
            'Coquito',
        ]);

        return $responseService->itemActionSuccess($message);
    }

    #[Route("/{inventory}/read", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function read(Inventory $inventory, ResponseService $responseService,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        ItemControllerHelpers::validateInventory($userAccessor->getUserOrThrow(), $inventory, 'coconutCookbook/#/read');

        return $responseService->itemActionSuccess(<<<EOBOOK
# Cuckoo for Coconuts

## Table of Contents

* Coconut Milk
* Mango Sticky Rice
* Papeete l'a Ota
* Rice Puddin'
* Coquito

## Recipes

#### Coconut Milk

All you need is a Coconut! And you don't even need a whole one: a Coconut Half will also do!

#### Mango Sticky Rice

* Coconut Milk
* Mango
* Rice
* Sugar

#### Papeete l'a Ota

* Coconut Milk
* Fish
* Onion
* Yellowy Lime

It's like a chicken caesar salad, but with fish, and no cheese, and, well, there's the lime, and, uh, well... okay: I guess it's nothing like a chicken caesar salad.

### Rice Puddin'

* Coconut Milk
* Creamy Milk
* Rice
* Sugar

Is there both Coconut Milk _and_ mammal milk in this recipe? Yes. Will eating Rice Puddin' regularly make you die young? Also probably yes. On your death bed, will you look back on your life and think "I wish I'd spent more time with my kids instead of eating all that Rice Pudding?" _Heck_ no! You'll be all like "why did I spend all that money making a human instead of spending it making more tasty deserts!?! And does this IV even have pudding in it, because I'm not so sure about these hospital bills unless it does!" And then the nurse runs in all like "please, please, calm down! Your heart rate is through the roof!" while the machine is beeping like crazy and you're wildly pulling all the life-support tubes and needles out of every part of your body, including the parts you didn't even know you had...

So in summary, yes: there's two kinds of milk in this pudding, and yes: it's worth it.

### Coquito

* Coconut Milk
* Creamy Milk
* Kilju

Is there both Coconut Milk _and_ mammal milk in this recipe? _And_ alcohol?!? See Rice Puddin' (above) for more information on health considerations.
EOBOOK);
    }
}
