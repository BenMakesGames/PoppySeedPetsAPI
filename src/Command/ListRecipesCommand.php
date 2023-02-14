<?php

namespace App\Command;

use App\Entity\Recipe;
use App\Model\ItemQuantity;
use App\Repository\ItemRepository;
use App\Repository\RecipeRepository;
use App\Service\InventoryService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;

class ListRecipesCommand extends PoppySeedPetsCommand
{
    private RecipeRepository $recipeRepository;
    private InventoryService $inventoryService;
    private ItemRepository $itemRepository;

    public function __construct(
        RecipeRepository $recipeRepository, InventoryService $inventoryService,
        ItemRepository $itemRepository
    )
    {
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
            ->addOption('name', null, InputOption::VALUE_OPTIONAL, 'Only recipes containing this text will be listed.')
            ->addOption('ingredients', 'i', InputOption::VALUE_OPTIONAL, 'SEMICOLON separated list of ingredient names; only exact matches will be found.')
        ;
    }

    protected function doCommand(): int
    {
        $searchName = trim($this->input->getOption('name'));
        $searchIngredients = trim($this->input->getOption('ingredients'));

        $qb = $this->recipeRepository->createQueryBuilder('r');

        if($searchName)
        {
            $qb
                ->andWhere('r.name LIKE :search')
                ->setParameter('name', '%' . $searchName . '%')
            ;
        }

        if($searchIngredients)
        {
            $ingredientNames = explode(';', $searchIngredients);

            $itemQuantities = $this->createQuantitiesFromList($ingredientNames);

            $list = InventoryService::serializeItemList($itemQuantities);

            $qb
                ->andWhere('r.ingredients=:ingredients')
                ->setParameter('ingredients', $list)
            ;
        }

        $recipes = $qb
            ->getQuery()
            ->getResult()
        ;

        foreach($recipes as $recipe)
            $this->showRecipe($recipe);

        return Command::SUCCESS;
    }

    /**
     * @param string[] $items
     * @return ItemQuantity[]
     */
    private function createQuantitiesFromList(array $items): array
    {
        $counts = [];

        foreach($items as $item)
            $counts[$item] = array_key_exists($item, $counts) ? ($counts[$item] + 1) : 1;

        $quantities = [];

        foreach($counts as $itemName=>$quantity)
        {
            $q = new ItemQuantity();

            $q->item = $this->itemRepository->findOneByName($itemName);
            $q->quantity = $quantity;

            if($q->item === null)
                throw new \Exception('There is no item called "' . $itemName . '"');

            $quantities[] = $q;
        }

        return $quantities;
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
    private function listItems(array $items)
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
