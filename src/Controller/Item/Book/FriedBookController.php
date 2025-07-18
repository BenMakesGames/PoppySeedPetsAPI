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

#[Route("/item/friedBook")]
class FriedBookController
{
    #[Route("/{inventory}/upload", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function upload(
        Inventory $inventory, ResponseService $responseService, CookingService $cookingService,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $inventory, 'friedBook/#/upload');

        $message = $cookingService->showRecipeNamesToCookingBuddy($user, [
            'Sunflower Oil',
            'Coconut Oil',
            'Nut Oil',
            'Loaf of Plain Bread',
            'Battered, Fried Fish (without Egg)',
            'Battered, Fried Fish (with Egg)',
            'Chili Calamari with Onion',
            'Deep-fried Toad Legs (Creamy Milk)',
            'Deep-fried Toad Legs (Egg)',
            'Spicy Deep-fried Toad Legs (Creamy Milk)',
            'Spicy Deep-fried Toad Legs (Egg)',
            'Falafel',
            'Plain Donut',
            'Fried Egg (Oil)',
            'Fried Egg (Butter)',
            'Fried Tomato (with Oil)',
            'Hash Brown (with Oil)',
            'Hash Brown (with Butter)',
            'Hot Wings',
            'Laufabrauð',
            'Onion Rings',

            'Pan-fried Fish (with Butter)',
            'Pan-fried Fish (with Oil)',
            'Pan-fried Tofu (using Butter)',
            'Pan-fried Tofu (using Oil)',
        ]);

        return $responseService->itemActionSuccess($message);
    }

    #[Route("/{inventory}/read", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function read(Inventory $inventory, ResponseService $responseService,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        ItemControllerHelpers::validateInventory($userAccessor->getUserOrThrow(), $inventory, 'friedBook/#/read');

        return $responseService->itemActionSuccess('# Fried

## Table of Contents

#### Basic Ingredients

* Oil
* Plain Bread

#### Recipes

* Battered, Fried Fish
* Chili Calamari
* Deep-fried Toad Legs
* Falafel
* Fried Donut
* Fried Egg
* Fried Tomato
* Hash Brown
* Laufabrauð
* Onion Rings
* Pan-fried Fish
* Pan-fried Tofu

## Basic Ingredients

#### Oil

It should not surprise you to learn that frying involves _Oil_. And I\'m not talking about the black stuff found all over the Oil Zone! (That oil\'s poisonous to hedgehogs and humans alike! DO NOT EAT!)

To make edible Oil (not that you should eat it straight - gross), you can extract it from any of the following oil-rich foods:
1. Coconut Milk
2. Mixed Nuts
3. Sunflower (it\'s all those seeds!)

#### Plain Bread

In addition to Oil, you _may_ need bread (for crumbs!)

If you don\'t have any bread on-hand, you can make some:

* Yeast
* Butter
* Creamy Milk
* Sugar
* Wheat Flour

## Recipes

Alright! You\'ve got your Oil... you\'ve got your Plain Bread...

Let\'s fry some stuff!

#### Battered, Fried Fish

* Oil!
* Fish
* Wheat Flour
* Baking Soda
* Milk
* Egg (optional)

#### Chili Calamari

* Oil!
* Spicy Peps
* Onions
* Tentacle

#### Deep-fried Toad Legs

* Oil!
* Toad Legs
* Egg (or milk)
* Wheat Flour (no bread crumbs here!)

If you\'re feelin\' spicy, throw in some Spicy Peps to match!

#### Falafel

* Oil!
* Chickpeas (aka Garbanzo Beans)
* Onion

#### Fried Donut

No baked donuts for us! We\'re goin\' FRIED!

You won\'t need breadcrumbs for this one, either; but you _will_ need:

* Oil!
* Yeast
* Butter
* Creamy Milk
* Egg
* Sugar
* Wheat Flour

#### Fried Egg

Simple, but delicious:

* Oil! (or Butter, but, c\'mon!)
* Egg

#### Fried Tomato

Finally the bread crumbs show themselves!

* Oil!
* Tomato
* Egg
* Wheat Flour
* Plain Bread (for that extra-crunchy fried crumb goodness!)

#### Hash Brown

* Oil! (or Butter, if you must)
* Potato
* Hash Table

#### Hot Wings

* Oil!
* Butter!
* Spicy Peps
* Wings

#### Laufabrauð

After rolling the dough into thin circles, cut a pretty design into it. Then carefully place it into the hot oil to fry it!

Yep: a fried bread!

* Oil!
* Baking Powder
* Butter
* Creamy Milk
* Sugar
* Wheat Flour

#### Onion Rings

* Oil!
* Onion
* Wheat Flour
* Baking Soda (for that fast-food feel!)

#### Pan-fried Fish

* Oil!
* Fish (or Tofu, for a vegetarian recipe!)

If you insist, you can replace the Oil with Butter...
');
    }
}
