<?php
namespace App\Controller;

use App\Entity\Inventory;
use App\Entity\KnownRecipes;
use App\Entity\User;
use App\Enum\LocationEnum;
use App\Enum\SerializationGroupEnum;
use App\Enum\UserStatEnum;
use App\Exceptions\PSPInvalidOperationException;
use App\Exceptions\PSPNotFoundException;
use App\Functions\RecipeRepository;
use App\Functions\StringFunctions;
use App\Model\ItemQuantity;
use App\Repository\InventoryRepository;
use App\Service\CookingService;
use App\Service\InventoryService;
use App\Service\ResponseService;
use App\Service\UserStatsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/cookingBuddy')]
class CookingBuddyController extends AbstractController
{
    private const ALLOWED_LOCATIONS = [
        LocationEnum::HOME,
        LocationEnum::BASEMENT,
        LocationEnum::MANTLE
    ];

    #[Route("/{cookingBuddy}", methods: ["GET"])]
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function getKnownRecipes(
        Inventory $cookingBuddy, InventoryService $inventoryService, EntityManagerInterface $em,
        Request $request, ResponseService $responseService, InventoryRepository $inventoryRepository
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        if($cookingBuddy->getOwner()->getId() !== $user->getId() || ($cookingBuddy->getItem()->getName() !== 'Cooking Buddy' && $cookingBuddy->getItem()->getName() !== 'Cooking "Alien"'))
            throw new PSPNotFoundException('Cooking Buddy Not Found');

        $location = $request->query->getInt('location', $cookingBuddy->getLocation());

        if(!in_array($location, self::ALLOWED_LOCATIONS))
            throw new PSPInvalidOperationException('Cooking Buddies are only usable from the house, Basement, or Fireplace Mantle.');

        $filters = $request->query->get('filter') ?? [];

        if(is_array($filters) && array_key_exists('name', $filters))
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
        $pageCount = ceil($unfilteredTotal / 20);

        $page = max(0, min($request->query->getInt('page', 0), $pageCount));

        $quantities = $inventoryRepository->getInventoryQuantities($user, $location, 'name');

        // this feels kinda' gross, but I'm not sure how else to do it...
        $recipes = [];

        $knownRecipeNames = array_map(fn(KnownRecipes $knownRecipe) => $knownRecipe->getRecipe(), $knownRecipes);

        // build up a dictionary in advance, for fast lookup
        $knownRecipeRecipes = [];

        foreach(RecipeRepository::RECIPES as $recipe)
        {
            if(in_array($recipe['name'], $knownRecipeNames))
                $knownRecipeRecipes[$recipe['name']] = $recipe;
        }

        foreach($knownRecipes as $knownRecipe)
        {
            if(!array_key_exists($knownRecipe->getRecipe(), $knownRecipeRecipes))
                throw new \Exception('Recipe not found: ' . $knownRecipe->getRecipe() . '.');

            $recipe = $knownRecipeRecipes[$knownRecipe->getRecipe()];
            $ingredients = $inventoryService->deserializeItemList($recipe['ingredients']);
            $makes = $inventoryService->deserializeItemList($recipe['makes']);
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

    /**
     * @Route("/{cookingBuddy}/prepare/{knownRecipe}/{quantity}", methods={"POST"}, requirements={"quantity"="\d+"})
     */
    #[IsGranted("IS_AUTHENTICATED_FULLY")]
    public function prepareRecipeFromMemory(
        Inventory $cookingBuddy, KnownRecipes $knownRecipe, ResponseService $responseService, EntityManagerInterface $em,
        InventoryService $inventoryService, InventoryRepository $inventoryRepository, UserStatsService $userStatsRepository,
        CookingService $cookingService, Request $request, int $quantity = 1
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        if($cookingBuddy->getOwner()->getId() !== $user->getId() || ($cookingBuddy->getItem()->getName() !== 'Cooking Buddy' && $cookingBuddy->getItem()->getName() !== 'Cooking "Alien"'))
            throw new PSPNotFoundException('Cooking Buddy Not Found');

        if($knownRecipe->getUser()->getId() !== $user->getId())
            throw new PSPNotFoundException('Unknown recipe? Weird. Reload and try again.');

        $recipeName = $knownRecipe->getRecipe();
        $recipe = RecipeRepository::findOneByName($recipeName);

        $ingredients = $inventoryService->deserializeItemList($recipe['ingredients']);

        $location = $request->request->getInt('location', $cookingBuddy->getLocation());

        if(!in_array($location, self::ALLOWED_LOCATIONS))
            throw new PSPInvalidOperationException('Cooking Buddies are only usable from the house, Basement, or Fireplace Mantle.');

        $inventoryToUse = [];

        foreach($ingredients as $ingredient)
        {
            $inventory = $inventoryRepository->findBy(
                [
                    'owner' => $user->getId(),
                    'item' => $ingredient->item->getId(),
                    'location' => $location
                ],
                [],
                $ingredient->quantity * $quantity
            );

            if(count($inventory) !== $ingredient->quantity * $quantity)
                throw new PSPInvalidOperationException('You do not have enough ' . $ingredient->item->getName() . ' to make ' . $recipe['name'] . '.');

            $inventoryToUse = array_merge($inventoryToUse, $inventory);
        }

        $results = $cookingService->prepareRecipe($user, $inventoryToUse, false);

        $userStatsRepository->incrementStat($user, UserStatEnum::COOKED_SOMETHING);

        $em->flush();

        return $responseService->success($results->inventory, [ SerializationGroupEnum::MY_INVENTORY ]);
    }
}
