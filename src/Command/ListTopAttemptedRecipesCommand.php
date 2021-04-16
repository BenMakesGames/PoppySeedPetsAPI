<?php

namespace App\Command;

use App\Entity\Recipe;
use App\Functions\ArrayFunctions;
use App\Model\ItemQuantity;
use App\Repository\ItemRepository;
use App\Repository\RecipeAttemptedRepository;
use App\Repository\RecipeRepository;
use App\Service\InventoryService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;

class ListTopAttemptedRecipesCommand extends PoppySeedPetsCommand
{
    private $recipeRepository;
    private $recipeAttemptedRepository;
    private $inventoryService;

    public function __construct(
        RecipeRepository $recipeRepository, RecipeAttemptedRepository $recipeAttemptedRepository,
        InventoryService $inventoryService
    )
    {
        $this->recipeAttemptedRepository = $recipeAttemptedRepository;
        $this->recipeRepository = $recipeRepository;
        $this->inventoryService = $inventoryService;

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

        foreach($topRecipes as $recipe)
        {
            $recipeItems = $this->inventoryService->deserializeItemList($recipe['recipe']);

            $itemStrings = array_map(fn($item) => $item->quantity . 'x ' . $item->item->getName(), $recipeItems);

            echo $recipe['qty'] . ' attempts: ' . ArrayFunctions::list_nice($itemStrings) . "\r\n";
        }

        return Command::SUCCESS;
    }

}
