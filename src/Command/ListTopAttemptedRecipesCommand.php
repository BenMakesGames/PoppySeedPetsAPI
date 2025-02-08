<?php
declare(strict_types=1);

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
            ->setDescription('Lists the top attempted recipes in the game.')
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
