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

class UpsertRecipeCommand extends PoppySeedPetsCommand
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
            ->setName('app:upsert-recipe')
            ->setDescription('Creates a new Recipe, or updates an existing one.')
            ->addArgument('recipe', InputArgument::REQUIRED, 'The name of the Recipe to upsert.')
        ;
    }

    protected function doCommand()
    {
        $name = $this->input->getArgument('recipe');
        $recipe = $this->recipeRepository->findOneBy(['name' => $name]);

        if($recipe)
            $this->output->writeln('Updating "' . $recipe->getName() . '"');
        else
        {
            $this->output->writeln('Creating "' . $name . '"');

            $recipe = (new Recipe())
                ->setName($name)
            ;

            $this->em->persist($recipe);
        }

        $this->name($recipe, $name);
        $this->ingredients($recipe);
        $this->makes($recipe);

        $this->em->flush();

        $ingredientFood = $this->inventoryService->totalFood($this->inventoryService->deserializeItemList($recipe->getIngredients()));
        $makesFood = $this->inventoryService->totalFood($this->inventoryService->deserializeItemList($recipe->getMakes()));

        $this->output->writeln('Ingredient food value totals:');
        $this->output->writeln('  Food: ' . $ingredientFood->getFood());
        $this->output->writeln('  Love: ' . $ingredientFood->getLove());
        $this->output->writeln('  Junk: ' . $ingredientFood->getJunk());
        $this->output->writeln('  Alcohol: ' . $ingredientFood->getAlcohol());

        $this->output->writeln('Product food value totals:');
        $this->output->writeln('  Food: ' . $makesFood->getFood());
        $this->output->writeln('  Love: ' . $makesFood->getLove());
        $this->output->writeln('  Junk: ' . $makesFood->getJunk());
        $this->output->writeln('  Alcohol: ' . $makesFood->getAlcohol());
    }

    private function askName(string $prompt, Recipe $recipe, string $name)
    {
        $question = new Question($prompt . ' (' . $name . ') ', $name);
        $question->setValidator(function($answer) use($recipe) {
            $answer = trim($answer);

            $existing = $this->recipeRepository->findOneBy([ 'name' => $answer ]);

            if($existing && $existing->getId() !== $recipe->getId())
                throw new \RuntimeException('There\'s already a Recipe with that name.');

            return $answer;
        });

        return $this->ask($question);
    }

    private function name(Recipe $recipe, string $name)
    {
        $recipe->setName($this->askName('What is it called?', $recipe, $name));
    }

    private function ingredients(Recipe $recipe)
    {
        $ingredients = $this->inventoryService->deserializeItemList($recipe->getIngredients());

        $ingredients = $this->editItemList($ingredients, 'ingredients');

        $recipe->setIngredients($this->inventoryService->serializeItemList($ingredients));
    }

    private function makes(Recipe $recipe)
    {
        $makes = $this->inventoryService->deserializeItemList($recipe->getMakes());

        $makes = $this->editItemList($makes, 'products');

        $recipe->setMakes($this->inventoryService->serializeItemList($makes));
    }

    /**
     * @param ItemQuantity[] $quantities
     * @return ItemQuantity[]
     */
    private function editItemList($quantities, $describedAs)
    {
        if(count($quantities) > 0)
        {
            $this->output->writeln('Current ' . $describedAs . ':');

            foreach($quantities as $ingredient)
                $this->output->writeln('  ' . $ingredient->quantity . 'x ' . $ingredient->item->getName());

            if(!$this->confirm('Change ' . $describedAs . '?', false))
                return $quantities;
        }

        $this->output->writeln('Enter ' . $describedAs . ':');

        $quantities = [];

        while(true)
        {
            $itemQuantity = new ItemQuantity();

            $itemQuantity->item = $this->askItem('Enter an Item name to add ');
            if($itemQuantity->item === null)
                break;

            $itemQuantity->quantity = $this->askInt('Enter a quantity', 1, function(int $n) { return $n >= 0; });

            $quantities[] = $itemQuantity;
        }

        return $quantities;
    }

    private function askItem(string $prompt): ?Item
    {
        $question = new Question($prompt, null);
        $question->setValidator(function($itemName) {
            $itemName = trim($itemName);

            if($itemName === '') return null;

            $item = $this->itemRepository->findOneBy([ 'name' => $itemName ]);
            if($item === null)
                throw new \RuntimeException('There is no Item called "' . $itemName . '".');

            return $item;
        });

        return $this->ask($question);
    }

}
