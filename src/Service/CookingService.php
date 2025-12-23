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

namespace App\Service;

use App\Entity\Inventory;
use App\Entity\KnownRecipes;
use App\Entity\RecipeAttempted;
use App\Entity\Spice;
use App\Entity\User;
use App\Enum\EnumInvalidValueException;
use App\Enum\UserStat;
use App\Exceptions\PSPInvalidOperationException;
use App\Exceptions\PSPNotUnlockedException;
use App\Functions\ArrayFunctions;
use App\Functions\NumberFunctions;
use App\Model\ItemQuantity;
use App\Model\PrepareRecipeResults;
use App\Model\Recipe;
use Doctrine\ORM\EntityManagerInterface;

class CookingService
{
    public function __construct(
        private readonly InventoryService $inventoryService,
        private readonly EntityManagerInterface $em,
        private readonly UserStatsService $userStatsRepository,
        private readonly IRandom $rng,
        private readonly RecipeRepository $recipeRepository,
    )
    {
    }

    /**
     * @param ItemQuantity[] $quantities
     */
    public function findRecipeFromQuantities(array $quantities): ?Recipe
    {
        return $this->recipeRepository->findOneByIngredients(InventoryService::serializeItemList($quantities));
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
                $quantities[$item->getId()] = new ItemQuantity($item, 1);
            }
        }

        return array_values($quantities);
    }

    /**
     * @param Inventory[] $inventory
     */
    public function logBadRecipeAttempt(User $user, array $inventory): RecipeAttempted
    {
        $quantities = CookingService::buildQuantitiesFromInventory($inventory);
        $ingredientList = InventoryService::serializeItemList($quantities);

        $attempt = $this->em->getRepository(RecipeAttempted::class)->findOneBy([
            'user' => $user,
            'recipe' => $ingredientList
        ]);

        if(!$attempt)
        {
            $attempt = new RecipeAttempted(
                user: $user,
                recipe: $ingredientList,
            );

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
     */
    public function prepareRecipeWithCookingBuddy(User $inventoryOwner, array $inventory, Recipe $recipe, int $batchSize): ?PrepareRecipeResults
    {
        $quantities = CookingService::buildQuantitiesFromInventory($inventory);

        if(count($quantities) === 0)
            return null;

        $batchQuantities = ItemQuantity::divide($quantities, $batchSize);

        $matchedRecipe = $this->findRecipeFromQuantities($batchQuantities);

        if(!$matchedRecipe || $matchedRecipe->name != $recipe->name)
            throw new \InvalidArgumentException("Given inventory does not match recipe and batchSize");

        return $this->prepareRecipe($inventory, $recipe, $batchSize, $inventoryOwner, $inventoryOwner);
    }

    /**
     * @param Inventory[] $inventory
     * @throws EnumInvalidValueException
     */
    public function prepareRecipeByHand(User $preparer, User $inventoryOwner, array $inventory): ?PrepareRecipeResults
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

        $results = $this->prepareRecipe($inventory, $recipe, $multiple, $inventoryOwner, $preparer);

        if($inventoryOwner->getCookingBuddy())
            $this->learnRecipe($inventoryOwner, $recipe->name);

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

        if(!ArrayFunctions::any($this->recipeRepository->recipes, fn(Recipe $recipe) => $recipe->name === $recipeName))
            throw new \Exception('Cannot learn recipe "' . $recipeName . '" - it doesn\'t exist!');

        $knownRecipe = new KnownRecipes(
            user: $user,
            recipe: $recipeName
        );

        $this->em->persist($knownRecipe);

        $this->userStatsRepository->incrementStat($user, UserStat::RecipesLearnedByCookingBuddy);

        return true;
    }

    /**
     * @param string[] $recipeNames
     */
    public function showRecipeNamesToCookingBuddy(User $user, array $recipeNames): string
    {
        if(!$user->getCookingBuddy())
            return 'You need a Cooking Buddy to do this.';

        $recipeNames = array_unique($recipeNames); // prevent duplicates, in case you (Ben) made a mistake somewhere else

        $countLearnedRecipes = ArrayFunctions::sum($recipeNames, function($recipeName) use($user) {
            return $this->learnRecipe($user, $recipeName) ? 1 : 0;
        });

        if($countLearnedRecipes === 0)
            return 'Your Cooking Buddy already knows all these recipes.';

        return 'Your Cooking Buddy learned ' . $countLearnedRecipes . ' new recipe' . ($countLearnedRecipes === 1 ? '' : 's') . '.';
    }

    private function prepareRecipe(array $inventory, Recipe $recipe, int $multiple, User $inventoryOwner, User $preparer): PrepareRecipeResults
    {
        /** @var Spice[] $spices */
        $spices = [];
        $allLockedToOwner = true;

        if($recipe->requiredHeat > 0)
        {
            if(!$preparer->getFireplace())
                throw new PSPNotUnlockedException('Fireplace');

            if(!$preparer->getFireplace()->getHasForge())
                throw new PSPInvalidOperationException('You need HEAT to prepare this recipe; you don\'t seem to have a suitable source...');

            $requiredHeat = $recipe->requiredHeat * $multiple;

            if($preparer->getFireplace()->getHeat() < $requiredHeat)
                throw new PSPInvalidOperationException('You need more HEAT in your Fireplace - get burnin\'!');

            $preparer->getFireplace()->removeHeat($requiredHeat);
        }

        foreach($inventory as $i)
        {
            if($i->getSpice())
                $spices[] = $i->getSpice();

            $this->em->remove($i);

            $allLockedToOwner = $allLockedToOwner && $i->getLockedToOwner();
        }

        $locationOfFirstItem = $inventory[0]->getLocation();

        $makes = InventoryService::deserializeItemList($this->em, $recipe->makes);

        foreach($makes as $m)
            $m->quantity *= $multiple;

        $newInventory = $this->inventoryService->giveInventoryQuantities($makes, $inventoryOwner, $inventoryOwner, $inventoryOwner->getName() . ' prepared this.', $locationOfFirstItem, $allLockedToOwner);

        if(count($spices) > 0)
        {
            // shuffle the spices
            $this->rng->rngNextShuffle($spices);

            // then add ~1/3 duplicate spices, but always at the END of the initial list
            $originalSpicesCount = count($spices);

            for($i = 0; $i < $originalSpicesCount; $i++)
            {
                if($this->rng->rngNextInt(1, 3) === 1 && !$spices[$i]->getEffects()->getChanceForBonusItem())
                    $spices[] = $spices[$i];
            }

            // apply the spices to the new inventory in a random order
            $this->rng->rngNextShuffle($newInventory);

            for($i = 0; $i < count($newInventory) && count($spices) > 0; $i++)
            {
                // if this item is a spice item (like Spicy Spice), DON'T try to apply a spice to it!
                if($newInventory[$i]->getItem()->getSpice())
                    continue;

                $spice = array_shift($spices);
                $newInventory[$i]->setSpice($spice);
            }
        }

        $this->userStatsRepository->incrementStat($preparer, UserStat::CookedSomething, $multiple);

        $results = new PrepareRecipeResults();
        $results->inventory = $newInventory;
        $results->quantities = $makes;
        $results->recipe = $recipe;
        $results->location = $locationOfFirstItem;

        return $results;
    }
}
