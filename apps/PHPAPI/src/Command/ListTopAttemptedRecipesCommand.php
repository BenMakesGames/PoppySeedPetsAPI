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


namespace App\Command;

use App\Entity\RecipeAttempted;
use App\Functions\ArrayFunctions;
use App\Service\InventoryService;
use Doctrine\ORM\EntityManagerInterface;

class ListTopAttemptedRecipesCommand extends PoppySeedPetsCommand
{
    private EntityManagerInterface $em;

    public function __construct(
        EntityManagerInterface $em
    )
    {
        $this->em = $em;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('app:list-top-attempted-recipes')
            ->setDescription('Lists the top attempted recipes in the game, in markdown format.')
        ;
    }

    protected function doCommand(): int
    {
        $topRecipes = $this->em->getRepository(RecipeAttempted::class)->createQueryBuilder('r')
            ->select(['COUNT(r.user) AS qty', 'r.recipe'])
            ->groupBy('r.recipe')
            ->orderBy('qty', 'DESC')
            ->setMaxResults(100)
            ->getQuery()
            ->getScalarResult()
        ;

        echo "| Qty | Recipe | Human-readable recipe |\n";
        echo "| --- | --- | --- |\n";

        foreach($topRecipes as $recipe)
        {
            $recipeItems = InventoryService::deserializeItemList($this->em, $recipe['recipe']);

            $itemStrings = array_map(fn($item) => $item->quantity . 'x ' . $item->item->getName(), $recipeItems);

            echo
                '| ' . $recipe['qty'] .
                ' | ' . $recipe['recipe'] .
                ' | ' . ArrayFunctions::list_nice($itemStrings) . " |\n";
        }

        return self::SUCCESS;
    }

}
