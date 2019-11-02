<?php

namespace App\Command;

use App\Entity\Item;
use App\Entity\Recipe;
use App\Model\ItemQuantity;
use App\Repository\ItemRepository;
use App\Repository\RecipeRepository;
use App\Service\InventoryService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Question\Question;

class ListRecipesCommand extends PoppySeedPetsCommand
{
    private $em;
    private $recipeRepository;
    private $inventoryService;
    private $itemRepository;

    public function __construct(
        EntityManagerInterface $em, RecipeRepository $recipeRepository, InventoryService $inventoryService,
        ItemRepository $itemRepository
    )
    {
        $this->em = $em;
        $this->recipeRepository = $recipeRepository;
        $this->inventoryService = $inventoryService;
        $this->itemRepository = $itemRepository;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:list-recipes')
            ->setDescription('Lists all the recipes in the game.')
            ->addArgument('search', InputArgument::OPTIONAL, 'Only recipes containing this text will be listed.')
        ;
    }

    protected function doCommand()
    {
        $search = trim($this->input->getArgument('search'));

        if($search)
        {
            $recipes = $this->recipeRepository->createQueryBuilder('r')
                ->andWhere('r.name LIKE :search')
                ->setParameter('search', '%' . $search . '%')
                ->getQuery()
                ->getResult()
            ;
        }
        else
            $recipes = $this->recipeRepository->findAll();

        foreach($recipes as $recipe)
            $this->showRecipe($recipe);
    }

    private function showRecipe(Recipe $recipe)
    {
        $ingredients = $this->inventoryService->deserializeItemList($recipe->getIngredients());
        $products = $this->inventoryService->deserializeItemList($recipe->getMakes());

        $this->output->writeln($recipe->getName());

        $this->output->writeln('  Ingredients');
        $this->listItems($ingredients);


        $this->output->writeln('  Products');
        $this->listItems($products);

        $this->output->writeln('');
    }

    /**
     *
     * @param ItemQuantity[] $items
     */
    private function listItems($items)
    {
        foreach($items as $ingredient)
            $this->output->writeln('    ' . $ingredient->quantity . 'x ' . $ingredient->item->getName());

        /*
        $food = $this->inventoryService->totalFood($items);

        $this->output->writeln('  Food : ' . $food->getFood());
        $this->output->writeln('  Love : ' . $food->getLove());
        $this->output->writeln('  Junk : ' . $food->getJunk());
        $this->output->writeln('  A/C/P: ' . $food->getAlcohol() . '/' . $food->getCaffeine() . '/' . $food->getPsychedelic());
        */
    }

}
