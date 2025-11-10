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

#[Route("/item/SOUP")]
class SoupController
{
    #[Route("/{inventory}/UPLOAD", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function upload(
        Inventory $inventory, ResponseService $responseService, CookingService $cookingService,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventoryAllowingLibrary($user, $inventory, 'SOUP/#/UPLOAD');

        $message = $cookingService->showRecipeNamesToCookingBuddy($user, [
            '15-bean Soup',
            '"Chicken" Noodle Soup (with Fish)',
            '"Chicken" Noodle Soup (with Tofu)',
            '"Chicken" Noodle Soup (with Turkey)',
            'Cullen Skink (A)',
            'Cullen Skink (B)',
            'Dashi',
            'Fish Stew',
            'Fishkebab Stew',
            'Hobak-juk',
            'Matzah Ball Soup (A)',
            'Matzah Ball Soup (B)',
            'Minestrone',
            'Miso Soup',
            'Pumpkin Soup',
            'Tomato Soup',
            'Vichyssoise (with Milk)',
        ]);

        return $responseService->itemActionSuccess(strtoupper($message));
    }

    #[Route("/{inventory}/READ", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function read(Inventory $inventory, ResponseService $responseService,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        ItemControllerHelpers::validateInventoryAllowingLibrary($userAccessor->getUserOrThrow(), $inventory, 'SOUP/#/READ');

        return $responseService->itemActionSuccess('# SOUP

#### 15-BEAN SOUP

* FIFTEEN BEANS

IF YOU HAPPEN TO STOR-- \*HACK; CoUgh; wheeze\* to store your Beans in threes, five lots of three will do.

#### Chicken Noodle Soup

* Chicken
* Mirepoix
* Noodles

Also good with Fish, Tofu, or turkey.

#### Cullen Skink

* Fish
* Creamy Milk
* Smashed Potatoes
* Onion

A hearty, Scottish soup. The traditional recipe calls for Smashed Potatoes, but if you like chunks, you can cut up a Potato, instead.

#### Dashi

A simple stock that can be enjoyed on its own, but is also a good base for other dishes.

* Fermented Fish
* Seaweed

#### Fish Stew

* Fish
* Mirepoix
* Tomato
* Spicy Peps (optional)

#### Fishkebab Stew

This strange recipe was invented by an AI. Despite being created by something without any taste buds, it\'s quite delicious!

* Fishkebab
* Onion
* Carrot
* Oil
* Butter

(Don\'t throw out the stick! It might come in handy!)

#### Hobak-Juk

* Rice Flour
* Pumpkin
* Beans

#### Matzah Ball Soup

* Matzah Bread
* Egg
* Mirepoix (Celery + Onion + Carrot)

#### Minestrone

* Oil (or Butter)
* Carrot, Celery, Onion (or Mirepoix)
* Beans
* Tomato
* Noodles

#### Miso Soup

* Dashi
* Tofu

#### Pumpkin Soup

* Small Pumpkin
* Creamy Milk

Makes three smallish bowls!

#### Tomato Soup

* Butter
* Onion
* Tomato

#### Vichyssoise

* Creamy Milk
* Onion
* Potato

A cold soup, best served chilled. (But not frozen!)
');
    }
}
