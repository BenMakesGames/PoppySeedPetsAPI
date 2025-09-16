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


namespace Service;

use App\Service\RecipeRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * JUSTIFICATION: All recipes must have a unique set of ingredients. There's a lot of recipes in the game,
 * so it's easy to accidentally create a "new" recipe that actually duplicates an existing one.
 */
class DuplicateRecipeTest extends KernelTestCase
{
    /**
     * @doesNotPerformAssertions
     */
    public function testDuplicateRecipesDoNotExist(): void
    {
        $seenIngredients = [];
        $recipeRepository = new RecipeRepository();

        foreach($recipeRepository->recipes as $recipe)
        {
            if(array_key_exists($recipe->ingredients, $seenIngredients))
                $this->fail("Duplicate recipes found: \"{$recipe->name}\" and \"{$seenIngredients[$recipe->ingredients]}\".");
            else
                $seenIngredients[$recipe->ingredients] = $recipe->name;
        }
    }
}
