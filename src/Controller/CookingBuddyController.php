<?php
namespace App\Controller;

use App\Entity\Inventory;
use App\Entity\KnownRecipes;
use App\Enum\SerializationGroupEnum;
use App\Enum\UserStatEnum;
use App\Model\ItemQuantity;
use App\Repository\InventoryRepository;
use App\Repository\ItemRepository;
use App\Repository\UserStatsRepository;
use App\Service\Filter\KnownRecipesFilterService;
use App\Service\InventoryService;
use App\Service\ResponseService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/cookingBuddy")
 */
class CookingBuddyController extends PoppySeedPetsController
{
    /**
     * @Route("/{cookingBuddy}", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function getKnownRecipes(
        Inventory $cookingBuddy, KnownRecipesFilterService $knownRecipesFilterService, InventoryService $inventoryService,
        Request $request, ResponseService $responseService, ItemRepository $itemRepository
    )
    {
        $user = $this->getUser();

        if($cookingBuddy->getOwner()->getId() !== $user->getId() || ($cookingBuddy->getItem()->getName() !== 'Cooking Buddy' && $cookingBuddy->getItem()->getName() !== 'Cooking "Alien"'))
            throw new NotFoundHttpException('404 Cooking Buddy Not Found');

        $knownRecipesFilterService->addRequiredFilter('user', $user->getId());

        $results = $knownRecipesFilterService->getResults($request->query);

        $quantities = $itemRepository->getInventoryQuantities($user, $cookingBuddy->getLocation(), 'name');

        // this feels kinda' gross, but I'm not sure how else to do it...
        $recipes = [];

        foreach($results->results as $knownRecipe)
        {
            /** @var KnownRecipes $knownRecipe */

            $ingredients = $inventoryService->deserializeItemList($knownRecipe->getRecipe()->getIngredients());
            $makes = $inventoryService->deserializeItemList($knownRecipe->getRecipe()->getMakes());

            $recipes[] = [
                'id' => $knownRecipe->getId(),
                'name' => $knownRecipe->getRecipe()->getName(),
                'ingredients' => $ingredients,
                'makes' => $makes,
                'canPrepare' => $inventoryService->hasRequiredItems($ingredients, $quantities)
            ];
        }

        $results->results = $recipes;

        return $responseService->success($results, [ SerializationGroupEnum::FILTER_RESULTS, SerializationGroupEnum::KNOWN_RECIPE ]);
    }

    /**
     * @Route("/{cookingBuddy}/prepare/{knownRecipe}", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    public function prepareRecipeFromMemory(
        Inventory $cookingBuddy, KnownRecipes $knownRecipe, ResponseService $responseService, EntityManagerInterface $em,
        InventoryService $inventoryService, InventoryRepository $inventoryRepository, UserStatsRepository $userStatsRepository
    )
    {
        $user = $this->getUser();

        if($cookingBuddy->getOwner()->getId() !== $user->getId() || ($cookingBuddy->getItem()->getName() !== 'Cooking Buddy' && $cookingBuddy->getItem()->getName() !== 'Cooking "Alien"'))
            throw new NotFoundHttpException('404 Cooking Buddy Not Found');

        if($knownRecipe->getUser()->getId() !== $user->getId())
            throw new NotFoundHttpException('Unknown recipe? Weird. Reload and try again.');

        $recipe = $knownRecipe->getRecipe();

        $ingredients = $inventoryService->deserializeItemList($recipe->getIngredients());

        $inventoryToDelete = [];

        foreach($ingredients as $ingredient)
        {
            $inventory = $inventoryRepository->findBy(
                [
                    'owner' => $user->getId(),
                    'item' => $ingredient->item->getId(),
                    'location' => $cookingBuddy->getLocation()
                ],
                [],
                $ingredient->quantity
            );

            if(count($inventory) !== $ingredient->quantity)
                throw new UnprocessableEntityHttpException('You do not have enough ' . $ingredient->item->getName() . ' to make ' . $recipe->getName() . '.');

            $inventoryToDelete = array_merge($inventoryToDelete, $inventory);
        }

        foreach($inventoryToDelete as $i)
            $em->remove($i);

        $makes = $inventoryService->deserializeItemList($recipe->getMakes());

        $newInventory = $inventoryService->giveInventory($makes, $user, $user, $user->getName() . ' prepared this.', $cookingBuddy->getLocation());

        $userStatsRepository->incrementStat($user, UserStatEnum::COOKED_SOMETHING);

        $em->flush();

        return $responseService->success($newInventory, SerializationGroupEnum::MY_INVENTORY);
    }
}
