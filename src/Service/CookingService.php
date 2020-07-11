<?php
namespace App\Service;

use App\Entity\Inventory;
use App\Entity\KnownRecipes;
use App\Entity\Recipe;
use App\Entity\RecipeAttempted;
use App\Entity\User;
use App\Enum\UserStatEnum;
use App\Model\ItemQuantity;
use App\Repository\InventoryRepository;
use App\Repository\KnownRecipesRepository;
use App\Repository\RecipeAttemptedRepository;
use App\Repository\RecipeRepository;
use App\Repository\UserStatsRepository;
use Doctrine\ORM\EntityManagerInterface;

class CookingService
{
    private $recipeRepository;
    private $inventoryService;
    private $em;
    private $userStatsRepository;
    private $knownRecipesRepository;
    private $inventoryRepository;
    private $recipeAttemptedRepository;

    public function __construct(
        RecipeRepository $recipeRepository, InventoryService $inventoryService, EntityManagerInterface $em,
        UserStatsRepository $userStatsRepository, KnownRecipesRepository $knownRecipesRepository,
        InventoryRepository $inventoryRepository, RecipeAttemptedRepository $recipeAttemptedRepository
    )
    {
        $this->recipeRepository = $recipeRepository;
        $this->inventoryService = $inventoryService;
        $this->em = $em;
        $this->userStatsRepository = $userStatsRepository;
        $this->knownRecipesRepository = $knownRecipesRepository;
        $this->inventoryRepository = $inventoryRepository;
        $this->recipeAttemptedRepository = $recipeAttemptedRepository;
    }

    /**
     * @param Inventory[] $inventory
     */
    public function findRecipeFromIngredients(array $inventory): ?Recipe
    {
        $quantities = $this->buildQuantitiesFromInventory($inventory);

        return $this->recipeRepository->findOneBy([
            'ingredients' => $this->inventoryService->serializeItemList($quantities)
        ]);
    }

    /**
     * @param Inventory[] $inventory
     * @return ItemQuantity[]
     */
    private function buildQuantitiesFromInventory(array $inventory): array
    {
        /** @var ItemQuantity[] $quantities */
        $quantities = [];

        foreach($inventory as $i)
        {
            $item = $i->getItem();

            if(array_key_exists($item->getId(), $quantities))
                $quantities[$item->getId()]->quantity++;
            else
            {
                $quantities[$item->getId()] = new ItemQuantity();
                $quantities[$item->getId()]->item = $item;
                $quantities[$item->getId()]->quantity = 1;
            }
        }

        return array_values($quantities);
    }

    /**
     * @param Inventory[] $inventory
     * @return Inventory[]
     */
    public function logBadRecipeAttempt(User $user, array $inventory): RecipeAttempted
    {
        $quantities = $this->buildQuantitiesFromInventory($inventory);
        $ingredientList = $this->inventoryService->serializeItemList($quantities);

        $attempt = $this->recipeAttemptedRepository->findOneBy([
            'user' => $user,
            'recipe' => $ingredientList
        ]);

        if(!$attempt)
        {
            $attempt = (new RecipeAttempted())
                ->setUser($user)
                ->setRecipe($ingredientList)
            ;

            $this->em->persist($attempt);
        }
        else
        {
            $attempt
                ->setLastAttemptedOn()
                ->incrementTimesAttempted()
            ;
        }

        return $attempt;
    }

    /**
     * @param Inventory[] $inventory
     * @return Inventory[]
     */
    public function prepareRecipe(User $user, array $inventory): ?array
    {
        $recipe = $this->findRecipeFromIngredients($inventory);

        if(!$recipe)
        {
            $this->logBadRecipeAttempt($user, $inventory);
            return null;
        }

        foreach($inventory as $i)
            $this->em->remove($i);

        $locationOfFirstItem = $inventory[0]->getLocation();

        $makes = $this->inventoryService->deserializeItemList($recipe->getMakes());

        $newInventory = $this->inventoryService->giveInventory($makes, $user, $user, $user->getName() . ' prepared this.', $locationOfFirstItem);

        $this->userStatsRepository->incrementStat($user, UserStatEnum::COOKED_SOMETHING);

        if($this->hasACookingBuddy($user))
        {
            $alreadyKnownRecipe = $this->knownRecipesRepository->findOneBy([
                'user' => $user,
                'recipe' => $recipe
            ]);

            if(!$alreadyKnownRecipe)
            {
                $knownRecipe = (new KnownRecipes())
                    ->setUser($user)
                    ->setRecipe($recipe)
                ;

                $this->em->persist($knownRecipe);

                $this->userStatsRepository->incrementStat($user, UserStatEnum::RECIPES_LEARNED_BY_COOKING_BUDDY);
            }
        }

        return $newInventory;
    }

    public function hasACookingBuddy(User $user): bool
    {
        return $this->inventoryRepository->findOneBy([
            'owner' => $user,
            'item' => [ 158, 454 ] // IDs of Cooking Buddy and Cooking "Alien"
        ]) !== null;
    }
}
