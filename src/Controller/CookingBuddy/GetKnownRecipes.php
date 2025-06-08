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

use App\Entity\KnownRecipes;
use App\Enum\LocationEnum;
use App\Enum\SerializationGroupEnum;
use App\Exceptions\PSPInvalidOperationException;
use App\Exceptions\PSPNotFoundException;
use App\Functions\RecipeRepository;
use App\Functions\StringFunctions;
use App\Model\ItemQuantity;
use App\Service\InventoryService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\UserAccessor;

#[Route('/cookingBuddy')]
class GetKnownRecipes
{
    private const array AllowedLocations = [
        LocationEnum::HOME,
        LocationEnum::BASEMENT,
        LocationEnum::MANTLE
    ];

    #[Route("/knownRecipes", methods: ["GET"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function getKnownRecipes(
        EntityManagerInterface $em, Request $request, ResponseService $responseService,
        InventoryService $inventoryService, UserAccessor $userAccessor
    ): JsonResponse
    {
        $user = $userAccessor->getUserOrThrow();

        if(!$user->getCookingBuddy())
            throw new PSPNotFoundException('Cooking Buddy Not Found');

        $location = $request->query->getInt('location');

        if(!in_array($location, self::AllowedLocations))
            throw new PSPInvalidOperationException('Cooking Buddies are only usable on the house, Basement, or Fireplace Mantle.');

        $filters = $request->query->all('filter');

        if(array_key_exists('name', $filters))
        {
            $knownRecipes = $em->getRepository(KnownRecipes::class)->createQueryBuilder('r')
                ->andWhere('r.user = :userId')
                ->andWhere('r.recipe LIKE :recipeName')
                ->setParameter('userId', $user->getId())
                ->setParameter('recipeName', '%' . StringFunctions::escapeMySqlWildcardCharacters($filters['name']) . '%')
                ->getQuery()
                ->execute();
        }
        else
        {
            $knownRecipes = $em->getRepository(KnownRecipes::class)->findBy([ 'user' => $user->getId() ]);
        }

        $unfilteredTotal = count($knownRecipes);
        $pageCount = (int)ceil($unfilteredTotal / 20);

        $page = max(0, min($request->query->getInt('page', 0), $pageCount));

        $quantities = $inventoryService->getInventoryQuantities($user, $location, 'name');

        // this feels kinda' gross, but I'm not sure how else to do it...
        $recipes = [];

        $knownRecipeNames = array_map(fn(KnownRecipes $knownRecipe) => $knownRecipe->getRecipe(), $knownRecipes);

        // build up a dictionary in advance, for fast lookup
        $knownRecipeRecipes = [];

        foreach(RecipeRepository::Recipes as $recipe)
        {
            if(in_array($recipe['name'], $knownRecipeNames))
                $knownRecipeRecipes[$recipe['name']] = $recipe;
        }

        foreach($knownRecipes as $knownRecipe)
        {
            if(!array_key_exists($knownRecipe->getRecipe(), $knownRecipeRecipes))
                throw new \Exception('Recipe not found: ' . $knownRecipe->getRecipe() . '.');

            $recipe = $knownRecipeRecipes[$knownRecipe->getRecipe()];
            $ingredients = InventoryService::deserializeItemList($em, $recipe['ingredients']);
            $makes = InventoryService::deserializeItemList($em, $recipe['makes']);
            $hasAllIngredients = true;

            $ingredients = array_map(function(ItemQuantity $itemQuantity) use($quantities, &$hasAllIngredients) {
                $itemName = $itemQuantity->item->getName();
                $available = array_key_exists($itemName, $quantities) ? $quantities[$itemName]->quantity : 0;
                $hasAllIngredients = $hasAllIngredients && $available >= $itemQuantity->quantity;

                return [
                    'item' => [
                        'name' => $itemName,
                        'image' => $itemQuantity->item->getImage(),
                    ],
                    'quantity' => $itemQuantity->quantity,
                    'available' => $available
                ];
            }, $ingredients);

            $recipes[] = [
                'id' => $knownRecipe->getId(),
                'name' => $recipe['name'],
                'ingredients' => $ingredients,
                'makes' => $makes,
                'canPrepare' => $hasAllIngredients,
            ];
        }

        usort($recipes, fn($a, $b) => $b['canPrepare'] == $a['canPrepare'] ? $a['name'] <=> $b['name'] : $b['canPrepare'] <=> $a['canPrepare']);

        $recipePage = array_slice($recipes, $page * 20, 20);

        $results = [
            'pageSize' => 20,
            'pageCount' => $pageCount,
            'page' => $page,
            'resultCount' => count($recipes),
            'unfilteredTotal' => $unfilteredTotal,
            'results' => $recipePage
        ];

        return $responseService->success(
            [
                'results' => $results,
                'location' => $location,
            ],
            [ SerializationGroupEnum::FILTER_RESULTS, SerializationGroupEnum::KNOWN_RECIPE ]
        );
    }
}
