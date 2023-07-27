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
use App\Model\ItemQuantity;
use App\Repository\InventoryRepository;
use App\Repository\UserStatsRepository;
use App\Service\CookingService;
use App\Service\Filter\KnownRecipesFilterService;
use App\Service\InventoryService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/cookingBuddy")
 */
class CookingBuddyController extends AbstractController
{
    private const ALLOWED_LOCATIONS = [
        LocationEnum::HOME,
        LocationEnum::BASEMENT,
        LocationEnum::MANTLE
    ];

    /**
     * @Route("/{cookingBuddy}", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function getKnownRecipes(
        Inventory $cookingBuddy, KnownRecipesFilterService $knownRecipesFilterService, InventoryService $inventoryService,
        Request $request, ResponseService $responseService, InventoryRepository $inventoryRepository
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        if($cookingBuddy->getOwner()->getId() !== $user->getId() || ($cookingBuddy->getItem()->getName() !== 'Cooking Buddy' && $cookingBuddy->getItem()->getName() !== 'Cooking "Alien"'))
            throw new PSPNotFoundException('Cooking Buddy Not Found');

        $knownRecipesFilterService->addRequiredFilter('user', $user->getId());

        $results = $knownRecipesFilterService->getResults($request->query);

        $location = $request->query->getInt('location', $cookingBuddy->getLocation());

        if(!in_array($location, self::ALLOWED_LOCATIONS))
            throw new PSPInvalidOperationException('Cooking Buddies are only usable from the house, Basement, or Fireplace Mantle.');

        $quantities = $inventoryRepository->getInventoryQuantities($user, $location, 'name');

        // this feels kinda' gross, but I'm not sure how else to do it...
        $recipes = [];

        foreach($results->results as $knownRecipe)
        {
            /** @var KnownRecipes $knownRecipe */

            $ingredients = $inventoryService->deserializeItemList($knownRecipe->getRecipe()->getIngredients());
            $makes = $inventoryService->deserializeItemList($knownRecipe->getRecipe()->getMakes());
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
                'name' => $knownRecipe->getRecipe()->getName(),
                'ingredients' => $ingredients,
                'makes' => $makes,
                'canPrepare' => $hasAllIngredients,
            ];
        }

        $results->results = $recipes;

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
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function prepareRecipeFromMemory(
        Inventory $cookingBuddy, KnownRecipes $knownRecipe, ResponseService $responseService, EntityManagerInterface $em,
        InventoryService $inventoryService, InventoryRepository $inventoryRepository, UserStatsRepository $userStatsRepository,
        CookingService $cookingService, Request $request, int $quantity = 1
    )
    {
        /** @var User $user */
        $user = $this->getUser();

        if($cookingBuddy->getOwner()->getId() !== $user->getId() || ($cookingBuddy->getItem()->getName() !== 'Cooking Buddy' && $cookingBuddy->getItem()->getName() !== 'Cooking "Alien"'))
            throw new PSPNotFoundException('Cooking Buddy Not Found');

        if($knownRecipe->getUser()->getId() !== $user->getId())
            throw new PSPNotFoundException('Unknown recipe? Weird. Reload and try again.');

        $recipe = $knownRecipe->getRecipe();

        $ingredients = $inventoryService->deserializeItemList($recipe->getIngredients());

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
                throw new UnprocessableEntityHttpException('You do not have enough ' . $ingredient->item->getName() . ' to make ' . $recipe->getName() . '.');

            $inventoryToUse = array_merge($inventoryToUse, $inventory);
        }

        $results = $cookingService->prepareRecipe($user, $inventoryToUse);

        $userStatsRepository->incrementStat($user, UserStatEnum::COOKED_SOMETHING);

        $em->flush();

        return $responseService->success($results->inventory, [ SerializationGroupEnum::MY_INVENTORY ]);
    }
}
