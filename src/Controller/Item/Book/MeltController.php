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
use App\Model\ItemQuantity;
use App\Model\Recipe;
use App\Service\CookingService;
use App\Service\InventoryService;
use App\Service\RecipeRepository;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\UserAccessor;

#[Route("/item/melt")]
class MeltController
{
    /**
     * @return Recipe[]
     */
    private function getRecipes(RecipeRepository $recipeRepository): array
    {
        return $recipeRepository->findBy(fn(Recipe $recipe) => str_starts_with($recipe->name, 'Melt'));
    }

    #[Route("/{inventory}/upload", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function upload(
        Inventory $inventory, ResponseService $responseService, CookingService $cookingService,
        UserAccessor $userAccessor, RecipeRepository $recipeRepository
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventory($user, $inventory, 'melt/#/upload');

        $recipeNames = array_map(fn(Recipe $recipe) => $recipe->name, $this->getRecipes($recipeRepository));

        $message = $cookingService->showRecipeNamesToCookingBuddy($user, $recipeNames);

        return $responseService->itemActionSuccess($message);
    }

    #[Route("/{inventory}/read", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function read(
        Inventory $inventory, ResponseService $responseService, EntityManagerInterface $em,
        UserAccessor $userAccessor, RecipeRepository $recipeRepository
    ): JsonResponse
    {
        ItemControllerHelpers::validateInventory($userAccessor->getUserOrThrow(), $inventory, 'melt/#/read');

        $recipes = $this->getRecipes($recipeRepository);

        $recipeTexts = [
            '# Melt',
            'Many items can be melted down through the application of HEAT. If you have too many Pokers, or Silver Keys, get yourself a Forge - it may become your best friend!',
            'Using the heat provided by your forge, you can melt these down:'
        ];

        $items = [];

        foreach($recipes as $recipe)
        {
            $ingredients = InventoryService::deserializeItemList($em, $recipe->ingredients);

            if(count($ingredients) > 1)
                continue;

            $items[] = '* ' . $ingredients[0]->item->getName();
        }

        sort($items);

        $recipeTexts[] = implode("\r\n", $items);

        return $responseService->itemActionSuccess(implode("\r\n\r\n", $recipeTexts));
    }
}
