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
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route("/item/candyMakerCookbook")]
class CandyMakersCookbookController extends AbstractController
{
    #[Route("/{inventory}/upload", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function upload(
        Inventory $inventory, ResponseService $responseService, CookingService $cookingService
    ): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        ItemControllerHelpers::validateInventory($user, $inventory, 'candyMakerCookbook/#/upload');

        $message = $cookingService->showRecipeNamesToCookingBuddy($user, [
            'Agar-agar',
            'Corn Syrup',
            'Red Juice, and Pectin',
            'Orange Juice, and Pectin',
            'Carrot Juice, and Pectin',
            'Pamplemousse Juice, and Pectin',
            'Sugar (from Sweet Beet)',
            'Sugar (from Honeycomb)',
            'Candied Lotus Petals',
            'Chocolate Bar',
            'Spicy Chocolate Bar',
            'Orange Chocolate Bar',
            'Everybeans (yellow)',
            'Everybeans (green)',
            'Everybeans (magenta)',
            'Orange Hard Candy',
            'Red Hard Candy',
            'Blue Hard Candy',
            'Purple Hard Candy',
            'Yellow Hard Candy',
            'Honeycomb (candy)',
            'Konpeitō',
            'Mixed Nut Brittle',
            'Rock Candy',
            'Orange Gummies (with Corn Syrup)',
            'Red Gummies (with Corn Syrup)',
            'Blue Gummies (with Corn Syrup)',
            'Purple Gummies (with Corn Syrup)',
            'Yellow Gummies (with Corn Syrup)',
            'Green Gummies (Melowatern)',
            'Green Gummies (Honeydont)',
            'Orange Gummies (with Agar-agar)',
            'Red Gummies (with Agar-agar)',
            'Blue Gummies (with Agar-agar)',
            'Purple Gummies (with Agar-agar)',
            'Yellow Gummies (with Agar-agar)',
            'Green Gummies (Melowatern & Agar-agar)',
            'Green Gummies (Honeydont & Agar-agar)',
            'Apricot Gummies (using Corn Syrup)',
            'Apricot Gummies (using Agar-Agar)',
            'Dandelion Syrup',
        ]);

        return $responseService->itemActionSuccess($message);
    }

    #[Route("/{inventory}/read", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function read(Inventory $inventory, ResponseService $responseService): JsonResponse
    {
        ItemControllerHelpers::validateInventory($this->getUser(), $inventory, 'candyMakerCookbook/#/read');

        return $responseService->itemActionSuccess('# Candy Maker\'s Cookbook

## Table of Contents

#### Basic Ingredients

* Agar-agar
* Corn Syrup
* Pectin
* Sugar

#### Recipes

* Candied Lotus Petals
* Chocolates
* Dandelion Syrup
* Everybeans
* Hard Fruit Candies
* Honeycomb
* Konpeitō
* Mixed Nut Brittle
* Rock Candy
* Soft Fruit Gummies

## Basic Ingredients

#### Agar-agar

Boiling Algae breaks up the cell walls. As it all cools, the fibrous chains we call Agar-agar are formed!

Let it dry, grind it up, and you\'ve got yourself some Agar-agar powder!

<small>(For those of you trying these recipes in real life, it\'s probably actually more complicated than that... sorry...)</small>

#### Corn Syrup

Combine Sugar, Corn, and Cream of Tartar in a saucepan and bring to a simmer; strain out solids, then continue simmering until thick.

#### Pectin

Many fruits (and even some vegetables) contain Pectin. Fruits with piths and rinds (such as Oranges and Pamplemousses) are especially rich with the stuff.

Cook any of the following, on its own, to receive Pectin:
* Red
* Orange
* Carrot
* Pamplemousse

#### Sugar

Sweet Beets are _brimming_ with Sugar, just waiting to be extracted!

1. Slice beets into 1/4" thick slices, and rinse.
2. Microwave until steaming.
3. Shred in food processor, then transfer to a large pot.
4. Cover with water, and simmer for about 45 minutes.
5. Strain liquid, and place on a shallow baking dish.
6. Bake at 250 until a sugar sludge remains.
7. Take out of oven, transfer to cool baking sheet, and allow remaining water to evaporate (be prepared to wait a day).

Quick method:

1. Click "Cook or Combine"
2. Select one Sweet Beet
3. Click "Cook or Combine" again
4. Dries instantly, because reasons; don\'t worry about it

Pro tip: Sugar can also be extracted from Honeycomb!

## Recipes

#### Candied Lotus Petals

A floral treat for your pets!

Combine:
* Egg
* Sugar
* Lotus Flower

#### Chocolates

Combine:
* Cocoa Beans (*not* powder!)
* Sugar
* Optionally, add Orange, or Spicy Peps.

#### Dandelion Syrup

Combine:
* Dandelion
* Sugar
* Yellowy Lime

Makes a sweet syrup that\'s delicious to put on just about anything!

#### Everybeans

Somehow, all you have to do is dye and sweeten some Beans, and they\'ll gain unexpected flavors!

Even mundane Beans are a little magic, it seems...

Combine:
* Beans
* Sugar
* Any dye (yellow, green, magenta...)

#### Hard Fruit Candies

Combine the following:
* Fruit (berries, melon, orange, etc)
* Sugar

#### Honeycomb

The candy, of course. You will need non-candy Honeycomb to make it, though!

* Sugar
* Corn Syrup
* Honeycomb
* Baking Soda

Optionally cover in chocolate for a delicious & crunchy candy bar!

#### Konpeitō

These colorful candies are painstaking to make by hand! But the ingredients are simple:
* Sugar
* A variety of dyes (at least three!)

#### Mixed Nut Brittle

* Sugar
* Corn Syrup
* Baking Soda
* Butter
* Mixed Nuts

#### Rock Candy

Dangle a String in a solution of Sugar-water.

It\'s the easiest thing!

#### Soft Fruit Gummies

Combine the following:
* Fruit (berries, melon, orange, etc)
* Sugar
* Corn Syrup, or Agar-agar
');
    }
}
