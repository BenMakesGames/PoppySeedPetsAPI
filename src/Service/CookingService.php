<?php
namespace App\Service;

use App\Entity\Inventory;
use App\Entity\KnownRecipes;
use App\Entity\Recipe;
use App\Entity\RecipeAttempted;
use App\Entity\Spice;
use App\Entity\User;
use App\Enum\UserStatEnum;
use App\Functions\ArrayFunctions;
use App\Functions\NumberFunctions;
use App\Model\ItemQuantity;
use App\Model\PrepareRecipeResults;
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
     * @param ItemQuantity[] $quantities
     */
    public function findRecipeFromQuantities(array $quantities): ?Recipe
    {
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
    public function prepareRecipe(User $user, array $inventory): ?PrepareRecipeResults
    {
        $quantities = $this->buildQuantitiesFromInventory($inventory);

        if(count($quantities) === 0)
            return null;

        $recipe = $this->findRecipeFromQuantities($quantities);
        $multiple = 1;

        // if we didn't find a recipe, check if this is a repeated recipe
        if(!$recipe)
        {
            $smallestQuantity = ArrayFunctions::min($quantities, function(ItemQuantity $q) { return $q->quantity; })->quantity;

            if($smallestQuantity === 1)
            {
                $this->logBadRecipeAttempt($user, $inventory);
                return null;
            }

            $divisors = NumberFunctions::findDivisors($smallestQuantity);
            sort($divisors);
            array_shift($divisors); // we don't want the "1" batch size (which is now at the beginning of the array, thanks to the previous sort(...))

            $foundAny = false;

            foreach($divisors as $batchSize)
            {
                // if we cannot evenly divide all of the ingredient quantities by this batch size, then it's not a
                // valid batch size; try the next one!
                if(
                    ArrayFunctions::any($quantities, function(ItemQuantity $q) use($batchSize) {
                        return $q->quantity % $batchSize !== 0;
                    })
                )
                {
                    continue;
                }

                $batchQuantities = ItemQuantity::divide($quantities, $batchSize);

                $recipe = $this->findRecipeFromQuantities($batchQuantities);

                if($recipe)
                {
                    $multiple = $batchSize;
                    $foundAny = true;
                    break;
                }
            }

            if(!$foundAny)
            {
                $this->logBadRecipeAttempt($user, $inventory);
                return null;
            }
        }

        /** @var Spice[] $spices */
        $spices = [];

        foreach($inventory as $i)
        {
            if($i->getSpice())
                $spices[] = $i->getSpice();

            $this->em->remove($i);
        }

        $locationOfFirstItem = $inventory[0]->getLocation();

        $makes = $this->inventoryService->deserializeItemList($recipe->getMakes());

        foreach($makes as $m)
            $m->quantity *= $multiple;

        $newInventory = $this->inventoryService->giveInventory($makes, $user, $user, $user->getName() . ' prepared this.', $locationOfFirstItem);

        if(count($spices) > 0)
        {
            // shuffle the spices
            shuffle($spices);

            // then add ~1/3 duplicate spices, but always at the END of the initial list
            $originalSpicesCount = count($spices);

            for($i = 0; $i < $originalSpicesCount; $i++)
            {
                if(mt_rand(1, 3) === 1 && !$spices[$i]->getEffects()->getBringsLuck())
                    $spices[] = $spices[$i];
            }

            // apply the spices to the new inventory in a random order
            shuffle($newInventory);

            for($i = 0; $i < count($newInventory) && count($spices) > 0; $i++)
            {
                $spice = array_shift($spices);
                $newInventory[$i]->setSpice($spice);
            }
        }

        $this->userStatsRepository->incrementStat($user, UserStatEnum::COOKED_SOMETHING, $multiple);

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

        $results = new PrepareRecipeResults();
        $results->inventory = $newInventory;
        $results->quantities = $makes;
        $results->recipe = $recipe;
        $results->location = $locationOfFirstItem;

        return $results;
    }

    public function hasACookingBuddy(User $user): bool
    {
        return $this->inventoryRepository->findOneBy([
            'owner' => $user,
            'item' => [ 158, 454 ] // IDs of Cooking Buddy and Cooking "Alien"
        ]) !== null;
    }
}
