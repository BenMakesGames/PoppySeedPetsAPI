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

#[Route("/item/artOfTofu")]
class ArtOfTofuController
{
    #[Route("/{inventory}/upload", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function upload(
        Inventory $inventory, ResponseService $responseService, CookingService $cookingService,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventoryAllowingLibrary($user, $inventory, 'artOfTofu/#/upload');

        $message = $cookingService->showRecipeNamesToCookingBuddy($user, [
            'Tofu',
            '"Chicken" Noodle Soup (with Tofu)',
            'Miso Soup',
            'Pan-fried Tofu (using Butter)',
            'Pan-fried Tofu (using Oil)',
            'Tofu Fried Rice (A)',
            'Tofu Fried Rice (B)',
        ]);

        return $responseService->itemActionSuccess($message);
    }

    #[Route("/{inventory}/read", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function read(Inventory $inventory, ResponseService $responseService,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        ItemControllerHelpers::validateInventoryAllowingLibrary($userAccessor->getUserOrThrow(), $inventory, 'artOfTofu/#/read');

        return $responseService->itemActionSuccess('# The Art of Tofu

#### Making Tofu

1. Press Beans into Bean Milk
2. Combine Bean Milk with Gypsum

#### "Chicken" Noodle Soup

* Tofu
* Mirepoix
* Noodles

#### Miso Soup

* Tofu
* Dashi

#### Pan-seared Tofu

* Tofu
* Oil (or Butter)
* Soy Sauce

#### Tofu Fried Rice

* Tofu
* Rice
* Oil
* Soy Sauce
* Onion (or Mirepoix!)
* Egg
');
    }
}
