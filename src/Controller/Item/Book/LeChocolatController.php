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
use App\Functions\RecipeRepository;
use App\Model\ItemQuantity;
use App\Service\CookingService;
use App\Service\InventoryService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\UserAccessor;

#[Route("/item/leChocolat")]
class LeChocolatController
{
    private function getRecipes(): array
    {
        return RecipeRepository::findBy(fn($recipe) => mb_strpos(mb_strtolower($recipe['name']), 'chocolat') !== false);
    }

    #[Route("/{inventory}/upload", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function upload(
        Inventory $inventory, ResponseService $responseService, CookingService $cookingService,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $inventory, 'leChocolat/#/upload');

        $recipeNames = array_map(fn(array $recipe) => $recipe['name'], $this->getRecipes());

        $message = $cookingService->showRecipeNamesToCookingBuddy($user, $recipeNames);

        return $responseService->itemActionSuccess($message);
    }

    #[Route("/{inventory}/read", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function read(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em,
        UserAccessor $userAccessor
    ): JsonResponse
    {
        ItemControllerHelpers::validateInventory($userAccessor->getUserOrThrow(), $inventory, 'leChocolat/#/read');

        $recipes = $this->getRecipes();

        $recipeTexts = [
            '# Chocolate',
            '### Table of Contents'
        ];

        $recipeTexts[] = implode("\r\n", array_map(function($recipe) {
            return '* ' . $recipe['name'];
        }, $recipes));

        foreach($recipes as $recipe)
        {
            $ingredients = InventoryService::deserializeItemList($em, $recipe['ingredients']);

            usort($ingredients, fn($a, $b) => $a->item->getName() <=> $b->item->getName());

            $items = array_map(function(ItemQuantity $q) {
                if($q->quantity > 1)
                    return '* ' . $q->quantity . '× ' . $q->item->getName();
                else
                    return '* ' . $q->item->getName();
            }, $ingredients);

            $recipeTexts[] = '### ' . $recipe['name'] . "\r\n" . implode("\r\n", $items);
        }

        return $responseService->itemActionSuccess(implode("\r\n\r\n", $recipeTexts));
    }
}
