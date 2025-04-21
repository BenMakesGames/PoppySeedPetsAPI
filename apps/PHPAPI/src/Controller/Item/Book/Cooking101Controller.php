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
use App\Functions\RecipeRepository;
use App\Service\CookingService;
use App\Service\ResponseService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/item/cooking101")]
class Cooking101Controller extends AbstractController
{
    #[Route("/{inventory}/upload", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function upload(
        Inventory $inventory, ResponseService $responseService, CookingService $cookingService
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'cooking101/#/upload');

        $message = $cookingService->showRecipeNamesToCookingBuddy($user, [
            'Matzah Bread',
            'Matzah Bread (with Oil)',
            'Red Juice, and Pectin',
            'Orange Juice, and Pectin',
            'Carrot Juice, and Pectin',
            'Pamplemousse Juice, and Pectin',
            'Orange Hard Candy',
            'Red Hard Candy',
            'Blue Hard Candy',
            'Purple Hard Candy',
            'Yellow Hard Candy',
            'Sugar (from Sweet Beet)',
            'Butter',
            'Pan-fried Fish (with Butter)',
            'Pan-fried Fish (with Oil)',
        ]);

        return $responseService->itemActionSuccess($message);
    }

    #[Route("/{inventory}/read", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function read(
        Inventory $inventory, ResponseService $responseService
    )
    {
        ItemControllerHelpers::validateInventory($this->getUser(), $inventory, 'cooking101/#/read');

        $recipeCount = count(RecipeRepository::RECIPES);

        return $responseService->itemActionSuccess('# Cooking 101

## Cooking Basics

Your house comes with everything you need: stove, pots and pans, water, and basic spices.

To get started:
1. Click "Cook or Combine"
2. Select the item or items you want to use
3. Click "Cook or Combine" again!

There are ' . $recipeCount . ' recipes to discover, so feel free to experiment! Nothing bad will happen if you try to prepare a "wrong" recipe; the items will simply not be used.

## Recipes

Here are a few simple recipes to get you started:

#### Matzah Bread

Prepare Wheat Flour alone (or with Oil, if you have any).

If you have raw Wheat, it can be prepared alone to get Wheat Flour!

(No need to worry about how many cups of flour! If a recipe needs Wheat Flour, it only ever needs one!)

#### Fruit Juice (and Pectin)

Prepare either a Red, Orange, Carrot, or other fruit or veggie high in Pectin, to create juice (and get a bit of Pectin on the side!)

#### Candy

Combine Sugar and most fruits to make hard candy.

Sugar can be extracted from Sweet Beets by preparing Sweet Beet on its own.

#### Butter

Prepare Milk on its own.

#### Pan-fried Fish

Combine Fish and Butter (or Oil, if you happen to find any of that).

## Learn More

There are other cook books to find, but some item descriptions contain recipes, as well, and again: don\'t be afraid to experiment a little! 
');
    }
}
