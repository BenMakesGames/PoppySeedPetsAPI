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

#[Route("/item/milkBook")]
class MilkBookController
{
    #[Route("/{inventory}/upload", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function upload(
        Inventory $inventory, ResponseService $responseService, CookingService $cookingService,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $inventory, 'milkBook/#/upload');

        $message = $cookingService->showRecipeNamesToCookingBuddy($user, [
            'Chocolate Cream Pie',
            'Chocomilk',
            'Creamed Corn',
            'Eggnog',
            'Blackberry Eton Mess',
            'Blueberry Eton Mess',
            'Goodberry Eton Mess',
            'Hakuna Frittata',
            'Blackberry Lassi (from scratch)',
            'Blackberry Lassi',
            'Blueberry Lassi (from scratch)',
            'Blueberry Lassi',
            'Mango Lassi (from scratch)',
            'Mango Lassi',
            'Mahalabia',
            'Melonpanbun',
            'Naner Puddin\'',
            'Pancakes',
            'Berry Pancakes (Blue)',
            'Red Pancakes',
            'Naner Pancakes',
            'Berry Pancakes (Black)',
            'Loaf of Plain Bread',
            'Qatayef',
            'Plain Yogurt',
            'Tea with Mammal Extract',
            'Tea with Mammal Extract (B)',
            'Sweet Tea with Mammal Extract',
            'Coffee Bean Tea with Mammal Extract',
            'Coffee Bean Tea with Mammal Extract (B)',
            'Sweet Coffee Bean Tea with Mammal Extract',
            'Vichyssoise (with Butter)',
            'Vichyssoise (with Milk)',
            'Vichyssoise (with both)',
        ]);

        return $responseService->itemActionSuccess($message);
    }

    #[Route("/{inventory}/read", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function read(Inventory $inventory, ResponseService $responseService,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        ItemControllerHelpers::validateInventory($userAccessor->getUserOrThrow(), $inventory, 'milkBook/#/read');

        return $responseService->itemActionSuccess('Have you ever found yourself in a situation where you just have way too much dang milk? Maybe your goat\'s just been overflowing with the stuff; maybe you have a reasonable amount, but just don\'t like the taste of it; maybe your friends\' goat was overflowing with the stuff, so they brought you some as a "gift". (I\'m on to you, Greg and Rachel!)

Whatever the circumstances that lead to your overabundance of milk, fret not! There are a lot of good uses for the stuff!

## Table o\' Contents

* Chocolate Cream Pie
* Chocomilk
* Creamed Corn
* Eggnog
* Eton Mess
* Hakuna Frittata
* Lassi
* Mahalabia
* Melonpan (Melonbun)
* Naner Puddin\'
* Pancakes
* Plain Bread
* Qatayef
* Soufflé
* Teas
* Vichyssoise
* Yogurt

## Recipes

#### Chocolate Cream Pie

* Creamy Milk!
* Sugar
* Egg
* Wheat Flour
* Butter
* Cocoa Powder
* Pie Crust

#### Chocomilk

* Creamy Milk!
* Sugar
* Cocoa Powder

#### Creamed Corn

* Creamy Milk!
* Corn

#### Eggnog

* Creamy Milk!
* Egg
* Sugar

#### Eton Mess

* Creamy Milk!
* Sugar
* Meringue
* Berries

A layered dessert: chunks of meringue, a layer of whipped cream, and berries on top.  

#### Hakuna Frittata

* Creamy Milk!
* Onion
* Egg
* Chanterelle

#### Lassi

* Creamy Milk!
* Plain Yogurt
* Fruit (Mango, Blackberry, or Blueberry)

Or, if you have Blackberry or Blueberry Yogurt, just add... Creamy Milk!

#### Mahalabia

A simple, sweet pudding! Try it with cardamom, clove, or Nutmeg!

* Creamy Milk!
* Rice Flour
* Sugar 

#### Melonpan (or is it Melonbun?)

* Creamy Milk!
* Sugar
* Egg
* Wheat Flour
* Butter
* Baking Powder
* Yeast

#### Naner Puddin\'

* Creamy Milk!
* Naner
* Sugar
* Egg
* Wheat Flour

#### Pancakes

* Creamy Milk!
* Sugar
* Egg
* Wheat Flour
* Baking Soda
* Butter (Don\'t have any? It\'s made from nothing more than Creamy Milk!)

Optionally, add fruit, such as berries, Reds, or Naners.

#### Plain Bread

* Creamy Milk!
* Sugar
* Wheat Flour
* Butter
* Yeast

#### Qatayef

A tasty Arabic dessert filled with nuts and cream! (It\'s a little involved, but worth the effort!)

* Creamy Milk!
* Mixed Nuts
* Sugar
* Yeast
* Baking Powder
* Wheat Flour
* Wheat

#### Soufflé

An airy, fluffy dish that is known for collapsing at the slightest provocation.

* Creamy Milk!
* Butter
* Egg (the fats don\'t stop! they can\'t! they _won\'t_!)
* Wheat Flour
* Cream of Tartar

#### Teas

Add Creamy Milk to any tea for a richer, creamier tea!

#### Vichyssoise

* Creamy Milk and/or Butter
* Onion
* Potato

Served cold - that\'s how you know it\'s fancy! Well: that and the French name...

#### Yogurt Begets Yogurt

* Creamy Milk!
* Plain Yogurt
* Aging Powder
');
    }
}
