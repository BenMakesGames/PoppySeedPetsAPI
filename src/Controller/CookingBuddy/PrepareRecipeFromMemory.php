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


namespace App\Controller\CookingBuddy;

use App\Entity\Inventory;
use App\Entity\KnownRecipes;
use App\Enum\LocationEnum;
use App\Enum\SerializationGroupEnum;
use App\Enum\UserStat;
use App\Exceptions\PSPInvalidOperationException;
use App\Exceptions\PSPNotFoundException;
use App\Service\CookingService;
use App\Service\InventoryService;
use App\Service\RecipeRepository;
use App\Service\ResponseService;
use App\Service\UserAccessor;
use App\Service\UserStatsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/cookingBuddy')]
class PrepareRecipeFromMemory
{
    private const array AllowedLocations = [
        LocationEnum::Home,
        LocationEnum::Basement,
        LocationEnum::Mantle
    ];

    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    #[Route("/prepare/{knownRecipe}/{quantity}", methods: ["POST"], requirements: ["quantity" => "\d+"])]
    public function prepareRecipeFromMemory(
        KnownRecipes $knownRecipe, ResponseService $responseService, EntityManagerInterface $em,
        RecipeRepository $recipeRepository,
        UserStatsService $userStatsRepository, CookingService $cookingService, Request $request,
        UserAccessor $userAccessor, int $quantity = 1,
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        if(!$user->getCookingBuddy())
            throw new PSPNotFoundException('Cooking Buddy Not Found');

        if($knownRecipe->getUser()->getId() !== $user->getId())
            throw new PSPNotFoundException('Unknown recipe? Weird. Reload and try again.');

        $recipeName = $knownRecipe->getRecipe();
        $recipe = $recipeRepository->findOneByName($recipeName);

        $ingredients = InventoryService::deserializeItemList($em, $recipe->ingredients);

        $location = $request->request->getInt('location');

        if(!in_array($location, self::AllowedLocations))
            throw new PSPInvalidOperationException('Cooking Buddies are only usable on the house, Basement, or Fireplace Mantle.');

        $inventoryToUse = [];

        foreach($ingredients as $ingredient)
        {
            $inventory = $em->getRepository(Inventory::class)->findBy(
                [
                    'owner' => $user->getId(),
                    'item' => $ingredient->item->getId(),
                    'location' => $location
                ],
                [],
                $ingredient->quantity * $quantity
            );

            if(count($inventory) !== $ingredient->quantity * $quantity)
                throw new PSPInvalidOperationException('You do not have enough ' . $ingredient->item->getName() . ' to make ' . $recipe->name . '.');

            $inventoryToUse = array_merge($inventoryToUse, $inventory);
        }

        $results = $cookingService->prepareRecipeWithCookingBuddy($user, $inventoryToUse, $recipe, $quantity);

        $userStatsRepository->incrementStat($user, UserStat::CookedSomething, $quantity);

        $em->flush();

        return $responseService->success($results->inventory, [ SerializationGroupEnum::MY_INVENTORY ]);
    }
}
