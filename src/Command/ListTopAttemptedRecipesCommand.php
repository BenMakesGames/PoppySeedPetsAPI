<?php

namespace App\Command;

use App\Functions\ArrayFunctions;
use App\Repository\RecipeAttemptedRepository;
use App\Service\InventoryService;
use Doctrine\ORM\EntityManagerInterface;

class ListTopAttemptedRecipesCommand extends PoppySeedPetsCommand
{
    private RecipeAttemptedRepository $recipeAttemptedRepository;
    private EntityManagerInterface $em;

    public function __construct(
        RecipeAttemptedRepository $recipeAttemptedRepository,
        EntityManagerInterface $em
    )
    {
        $this->recipeAttemptedRepository = $recipeAttemptedRepository;
        $this->em = $em;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:list-top-attempted-recipes')
            ->setDescription('Lists the top attempted recipes in the game.')
        ;
    }

    protected function doCommand(): int
    {
        $topRecipes = $this->recipeAttemptedRepository->createQueryBuilder('r')
            ->select(['COUNT(r.user) AS qty', 'r.recipe'])
            ->groupBy('r.recipe')
            ->orderBy('qty', 'DESC')
            ->setMaxResults(100)
            ->getQuery()
            ->getScalarResult()
        ;

        echo ' Qty   | Recipe                 | Human-readable recipe';
        echo '-------+------------------------+-------------------------------';

        foreach($topRecipes as $recipe)
        {
            $recipeItems = InventoryService::deserializeItemList($this->em, $recipe['recipe']);

            $itemStrings = array_map(fn($item) => $item->quantity . 'x ' . $item->item->getName(), $recipeItems);

            echo
                ' ' . str_pad($recipe['qty'], 5, ' ') .
                ' | ' . str_pad($recipe['recipe'], 22, ' ') .
                ' | ' . ArrayFunctions::list_nice($itemStrings) . "\r\n";
        }

        return self::SUCCESS;
    }

}
