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

#[Route("/item/megaliumRecipes")]
class MegaliumRecipesController
{
    /**
     * @return Recipe[]
     */
    private function getRecipes(RecipeRepository $recipeRepository): array
    {
        return $recipeRepository->findBy(fn(Recipe $recipe) => preg_match('/(^|,)1130:/', $recipe->ingredients) === 1);
    }

    #[Route("/{inventory}/upload", methods: ["POST"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function upload(
        Inventory $inventory, ResponseService $responseService, CookingService $cookingService,
        UserAccessor $userAccessor, RecipeRepository $recipeRepository
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        ItemControllerHelpers::validateInventoryAllowingLibrary($user, $inventory, 'megaliumRecipes/#/upload');

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
        ItemControllerHelpers::validateInventoryAllowingLibrary($userAccessor->getUserOrThrow(), $inventory, 'megaliumRecipes/#/read');

        $recipes = $this->getRecipes($recipeRepository);

        $recipeTexts = [
            '# The Science of Embiggening',
            'Many items can be embiggened through the use of Megalium. If you have too many Silica Grounds, or Smallish Pumpkins, then Megalium may become your best friend!',
            'Introduce Megalium to any of the following to embiggen them:'
        ];

        $items = [];

        foreach($recipes as $recipe)
        {
            $ingredients = InventoryService::deserializeItemList($em, $recipe->ingredients);

            $ingredients = array_values(array_filter($ingredients, fn(ItemQuantity $q) => $q->item->getName() !== 'Megalium'));

            if(count($ingredients) > 1)
                continue;

            $items[] = '* ' . $ingredients[0]->item->getName();
        }

        sort($items);

        $recipeTexts[] = implode("\r\n", $items);

        return $responseService->itemActionSuccess(implode("\r\n\r\n", $recipeTexts));
    }
}
