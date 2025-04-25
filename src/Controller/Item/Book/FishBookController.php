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

#[Route("/item/fishBook")]
class FishBookController
{
    #[Route("/{inventory}/upload", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function upload(
        Inventory $inventory, ResponseService $responseService, CookingService $cookingService,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $inventory, 'fishBook/#/upload');

        $message = $cookingService->showRecipeNamesToCookingBuddy($user, [
            'Baked Fish Fingers',
            'Basic Fish Taco',
            'Battered, Fried Fish (with Egg)',
            'Battered, Fried Fish (without Egg)',
            'Cullen Skink (A)',
            'Cullen Skink (B)',
            'Fermented Fish',
            'Fisherman\'s Pie (Carrot)',
            'Fisherman\'s Pie (Corn)',
            'Fish Onigiri',
            'Gefilte Fish',
            'Gefilte Fish (with Celery)',
            'Grilled Fish',
            'Nigiri',
            'Orange Fish',
            'Pan-fried Fish (with Butter)',
            'Pan-fried Fish (with Oil)',
            'Papeete I\'a Ota',
            'Peruvian Ceviche',
            'Red Red',
            'Soy-ginger Fish',
            'Stargazy Pie',
            'Simple Sushi',
            'Takoyaki',
            'Zongzi',
        ]);

        return $responseService->itemActionSuccess($message);
    }

    #[Route("/{inventory}/read", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function read(Inventory $inventory, ResponseService $responseService,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        ItemControllerHelpers::validateInventory($userAccessor->getUserOrThrow(), $inventory, 'fishBook/#/read');

        return $responseService->itemActionSuccess('# Fish Book

## Table of Contents

#### Crafts

* Fishing Rod
* Painted Fishing Rod
* Plastic Fishing Rod

#### Recipes

* Baked Fish Fingers
* Basic Fish Taco
* Battered, Fried Fish
* Cullen Skink
* Fermented Fish
* Fisherman\'s Pie
* Fish Onigiri
* Gefilte Fish
* Grilled Fish
* Nigiri
* Orange Fish
* Pan-fried Fish
* Papeete I\'a Ota
* Peruvian Ceviche
* Red Red
* Soy-ginger Fish
* Stargazy Pie
* Sushi
* Takoyaki
* Zongzi

## Crafts

Your pets can craft these things, if the materials are available.

For some reason, only pets can make these?

Life as a human truly isn\'t fair.

#### Fishing Rod

1. Attach a bit of String to a Crooked Stick
1. Somehow, that\'s all it takes! (Where does the hook come from?)

#### Painted Fishing Rod

1. Prepare a palette of Yellow and Green Dye
1. Alternate painting splotchy patches (technical term!) of green and yellow on the rod

#### Plastic Fishing Rod

1. Get a 3D Printer
1. Print it!

## Recipes

Your can cook these things, if the ingredients are available.

For some reason, only humans can make these?

Life as a pet truly isn\'t fair.

#### Baked Fish Fingers

1. Cut up some Fish into finger sizes
1. Brush Fish with Egg
1. Roll Fish in Wheat Flour and crumbs from a Slice of Bread
1. Bake!

#### Basic Fish Taco

* Tortilla
* Fish
* Salsa

#### Battered, Fried Fish

* Wheat Flour
* Baking Soda
* Milk
* Egg (optional)
* Fish
* Oil (for frying)

#### Cullen Skink

* Fish
* Creamy Milk
* Smashed Potatoes
* Onion

A hearty, Scottish soup. The traditional recipe calls for Smashed Potatoes, but if you like chunks, you can cut up a Potato, instead. 

#### Fermented Fish

Just age some Fish with Aging Powder!

An acquired taste!

#### Fisherman\'s Pie

* Smashed Potatoes
* Wheat Flour
* Butter
* Creamy Milk
* Fish
* Onion
* Carrot (or Corn)

#### Fish Onigiri

* Rice
* Seaweed
* Fish

You can substitute Fish with other things, too: Tentacle, Melowatern... experiment!

#### Gefilte Fish

* Carrot
* Onion
* Egg
* Fish
* Matzah Bread
* Celery (optional)

#### Grilled Fish

Just grill some Fish with Charcoal!

#### Nigiri

* Rice
* Fish

(Psst, hey! I know this is supposed to be a book about Fish, BUT: if you marinade a slice of Tomato in Soy Sauce and Oil, and use that instead of Fish, you could make yourself a nice Tomato "Sushi"! Just sayin\'! It\'s an option!)

#### Orange Fish

* Orange
* Fish
* Sugar (not optional!)

#### Pan-fried Fish

* Fish
* Butter, or Oil

That\'s it!

#### Papeete I\'a Ota

* Fish
* Coconut Milk
* Onion
* Yellowy Lime

#### Peruvian Ceviche

* Fish
* Yellowy Lime
* Onion
* Spicy Peps

#### Red Red

* Fish
* Beans
* Onion
* Spicy Peps
* Oil
* Naner
* Tomato

It gets its name from the red palm oil and red plantains used to make it; not from the inclusion of Red. Which is good, because it doesn\'t have any Red in it!

(Just... just pretend the Oil comes from red palm, and that the Naners are red plantains... \'kay? M\'kay.)

#### Soy-ginger Fish

* Fish
* Soy Sauce
* Ginger

#### Stargazy Pie

* Fish
* Egg
* Potato
* Pie Crust

This pie is said to have saved a town from a devil, because when the devil saw that the people of the town made this pie, it worried they\'d make a pie of anything... maybe even a devil.

#### Sushi

1. Lay out a sheet of Seaweed
1. Place a bed of sushi rice on the seaweed (Rice, Sugar, Vinegar)
1. Place pieces of Fish on the sushi rice, in a line about 1-2 inches away from one edge 
1. Roll!

#### Takoyaki

1. Make a tasty batter of Egg, Dashi, Wheat Flour, and Soy Sauce
2. Mash together Tentacles, Onion, and Ginger into balls
3. Cover balls in batter, and cook
4. Put on a stick. For fun.
   * This step is not optional.
     * You are _required_ to have fun.

#### Zongzi

1. Lay Rice on a Really Big Leaf
1. Place Fish, Chanterelle, Beans, and Mixed nuts on the rice
1. Fold/wrap into a triangular shape
1. Bind with String
');
    }
}
