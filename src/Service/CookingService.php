<?php
namespace App\Service;

use App\Entity\Inventory;
use App\Entity\KnownRecipes;
use App\Entity\RecipeAttempted;
use App\Entity\Spice;
use App\Entity\User;
use App\Enum\EnumInvalidValueException;
use App\Enum\UserStatEnum;
use App\Functions\ArrayFunctions;
use App\Functions\NumberFunctions;
use App\Functions\RecipeRepository;
use App\Model\ItemQuantity;
use App\Model\PrepareRecipeResults;
use App\Repository\InventoryRepository;
use App\Repository\RecipeAttemptedRepository;
use Doctrine\ORM\EntityManagerInterface;

class CookingService
{
    public function __construct(
        private readonly InventoryService $inventoryService,
        private readonly EntityManagerInterface $em,
        private readonly UserStatsService $userStatsRepository,
        private readonly InventoryRepository $inventoryRepository,
        private readonly RecipeAttemptedRepository $recipeAttemptedRepository,
        private readonly IRandom $squirrel3
    )
    {
    }

    /**
     * @param ItemQuantity[] $quantities
     */
    public function findRecipeFromQuantities(array $quantities): ?array
    {
        return RecipeRepository::findOneByIngredients(InventoryService::serializeItemList($quantities));
    }

    /**
     * @param Inventory[] $inventory
     * @return ItemQuantity[]
     */
    private static function buildQuantitiesFromInventory(array $inventory): array
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
        $quantities = CookingService::buildQuantitiesFromInventory($inventory);
        $ingredientList = InventoryService::serializeItemList($quantities);

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
     * @throws EnumInvalidValueException
     */
    public function prepareRecipe(User $preparer, User $inventoryOwner, array $inventory, bool $teachCookingBuddies): ?PrepareRecipeResults
    {
        $quantities = CookingService::buildQuantitiesFromInventory($inventory);

        if(count($quantities) === 0)
            return null;

        $recipe = $this->findRecipeFromQuantities($quantities);
        $multiple = 1;

        // if we didn't find a recipe, check if this is a repeated recipe
        if(!$recipe)
        {
            $smallestQuantity = ArrayFunctions::min($quantities, fn(ItemQuantity $q) => $q->quantity)->quantity;

            if($smallestQuantity === 1)
            {
                $this->logBadRecipeAttempt($preparer, $inventory);
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
                if(ArrayFunctions::any($quantities, fn(ItemQuantity $q) => $q->quantity % $batchSize !== 0))
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
                $this->logBadRecipeAttempt($preparer, $inventory);
                return null;
            }
        }

        /** @var Spice[] $spices */
        $spices = [];
        $allLockedToOwner = true;

        foreach($inventory as $i)
        {
            if($i->getSpice())
                $spices[] = $i->getSpice();

            $this->em->remove($i);

            $allLockedToOwner = $allLockedToOwner && $i->getLockedToOwner();
        }

        $locationOfFirstItem = $inventory[0]->getLocation();

        $makes = InventoryService::deserializeItemList($this->em, $recipe['makes']);

        foreach($makes as $m)
            $m->quantity *= $multiple;

        $newInventory = $this->inventoryService->giveInventoryQuantities($makes, $inventoryOwner, $inventoryOwner, $inventoryOwner->getName() . ' prepared this.', $locationOfFirstItem, $allLockedToOwner);

        if(count($spices) > 0)
        {
            // $this->squirrel3->rngNextShuffle the spices
            $this->squirrel3->rngNextShuffle($spices);

            // then add ~1/3 duplicate spices, but always at the END of the initial list
            $originalSpicesCount = count($spices);

            for($i = 0; $i < $originalSpicesCount; $i++)
            {
                if($this->squirrel3->rngNextInt(1, 3) === 1 && !$spices[$i]->getEffects()->getChanceForBonusItem())
                    $spices[] = $spices[$i];
            }

            // apply the spices to the new inventory in a random order
            $this->squirrel3->rngNextShuffle($newInventory);

            for($i = 0; $i < count($newInventory) && count($spices) > 0; $i++)
            {
                // if this item is a spice item (like Spicy Spice), DON'T try to apply a spice to it!
                if($newInventory[$i]->getItem()->getSpice())
                    continue;

                $spice = array_shift($spices);
                $newInventory[$i]->setSpice($spice);
            }
        }

        $this->userStatsRepository->incrementStat($preparer, UserStatEnum::COOKED_SOMETHING, $multiple);

        if($teachCookingBuddies && $this->hasACookingBuddy($inventoryOwner))
        {
            $this->learnRecipe($inventoryOwner, $recipe['name']);
        }

        $results = new PrepareRecipeResults();
        $results->inventory = $newInventory;
        $results->quantities = $makes;
        $results->recipe = $recipe;
        $results->location = $locationOfFirstItem;

        return $results;
    }

    public function learnRecipe(User $user, string $recipeName): bool
    {
        $alreadyKnownRecipe = $this->em->getRepository(KnownRecipes::class)->count([
            'user' => $user,
            'recipe' => $recipeName
        ]) > 0;

        if($alreadyKnownRecipe)
            return false;

        if(!ArrayFunctions::any(RecipeRepository::RECIPES, fn($recipe) => $recipe['name'] === $recipeName))
            throw new \Exception('Cannot learn recipe "' . $recipeName . '" - it doesn\'t exist!');

        $knownRecipe = (new KnownRecipes())
            ->setUser($user)
            ->setRecipe($recipeName)
        ;

        $this->em->persist($knownRecipe);

        $this->userStatsRepository->incrementStat($user, UserStatEnum::RECIPES_LEARNED_BY_COOKING_BUDDY);

        return true;
    }

    public function hasACookingBuddy(User $user): bool
    {
        return $this->inventoryRepository->count([
            'owner' => $user,
            'item' => [ 158, 454 ] // IDs of Cooking Buddy and Cooking "Alien"
        ]) > 0;
    }

    public function showRecipeNamesToCookingBuddy(User $user, array $recipeNames): string
    {
        if(!$this->hasACookingBuddy($user))
            return 'You need a Cooking Buddy to do this.';

        $countLearnedRecipes = ArrayFunctions::sum($recipeNames, function($recipeName) use($user) {
            return $this->learnRecipe($user, $recipeName) ? 1 : 0;
        });

        if($countLearnedRecipes === 0)
            return 'Your Cooking Buddy already knows all these recipes.';

        return 'Your Cooking Buddy learned ' . $countLearnedRecipes . ' new recipe' . ($countLearnedRecipes === 1 ? '' : 's') . '.';
    }
}
