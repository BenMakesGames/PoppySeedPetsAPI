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
use App\Enum\LocationEnum;
use App\Exceptions\PSPInvalidOperationException;
use App\Functions\InventoryHelpers;
use App\Service\CookingService;
use App\Service\ResponseService;
use App\Service\UserAccessor;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/item/yellowyLime")]
class YellowyLimeController
{
    #[Route("/{inventory}/upload", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function upload(
        Inventory $inventory, ResponseService $responseService, CookingService $cookingService,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $inventory, 'yellowyLime/#/upload');

        $message = $cookingService->showRecipeNamesToCookingBuddy($user, [
            'Yellowy Key-y Lime Pie',
            'Essence d\'Assortiment (from Blackberry Wine)',
            'Essence d\'Assortiment (from Blueberry Wine)',
            'Essence d\'Assortiment (from Dandelion Wine)',
            'Essence d\'Assortiment (from Fig Wine)',
            'Essence d\'Assortiment (from Red Wine)',
        ]);

        return $responseService->itemActionSuccess($message);
    }

    #[Route("/{inventory}/read", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function read(
        Inventory $inventory, ResponseService $responseService,
        EntityManagerInterface $em,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $inventory, 'yellowyLime/#/read');

        $magnifyingGlass = InventoryHelpers::findAnyOneFromItemGroup($em, $user, 'Magnifying Glass', [
            LocationEnum::Home,
            LocationEnum::Basement,
            LocationEnum::Mantle,
            LocationEnum::Wardrobe,
        ]);

        if(!$magnifyingGlass)
        {
            throw new PSPInvalidOperationException('Goodness! It\'s so small! You\'ll need a magnifying glass of some kind...');
        }

        return $responseService->itemActionSuccess(<<<EOBOOK
<em>(You know how on the back of a bag of chocolate chips, there's a recipe for Chocolate Chip Cookies? This is like that, but on the sticker on this Yellowy Lime. Oh, and also the recipe isn't for Chocolate Chip Cookies, because that would be weird. Oh, and ALSO-also, the print is just, like, absolutely and absurdly tiny. Thankfully your {$magnifyingGlass->getFullItemName()} is a good magnifying glass, and lets you make it all out.)</em>

**Yellowy Key-y Lime Pie**
* Yellowy Lime
* Egg
* Creamy Milk
* Sugar
* Butter
* Graham Cracker

**Essence d'Assortiment**
* Yellowy Lime
* (Almost) any wine
* Vinegar
* Chanterelle
* Onion
EOBOOK
);
    }

}