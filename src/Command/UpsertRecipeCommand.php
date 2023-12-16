<?php

namespace App\Command;

use App\Command\Traits\AskItemTrait;
use App\Entity\ItemFood;
use App\Functions\RecipeRepository;
use App\Model\ItemQuantity;
use App\Service\InventoryService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Question\Question;

class UpsertRecipeCommand extends PoppySeedPetsCommand
{
    use AskItemTrait;

    private EntityManagerInterface $em;
    private InventoryService $inventoryService;

    public function __construct(
        EntityManagerInterface $em, InventoryService $inventoryService
    )
    {
        $this->em = $em;
        $this->inventoryService = $inventoryService;

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

    /**
     * @param ItemQuantity[] $quantities
     */
    public static function totalFood(array $quantities): ItemFood
    {
        $food = new ItemFood();

        foreach($quantities as $quantity)
        {
            $itemFood = $quantity->item->getFood() ?: new ItemFood();
            $food = $food->add($itemFood->multiply($quantity->quantity));
        }

        return $food;
    }

    protected function doCommand(): int
    {
        if(strtolower($_SERVER['APP_ENV']) !== 'dev')
            throw new \Exception('Can only be run in dev environments.');

        $name = $this->input->getArgument('recipe');
        $recipe = RecipeRepository::findOneByName($name);

        if($recipe)
            $this->output->writeln('Updating "' . $name . '"');
        else
        {
            $this->output->writeln('Creating "' . $name . '"');
            $recipe = [
                'name' => $name,
                'ingredients' => '',
                'makes' => ''
            ];
        }

        $this->name($recipe, $name);
        $this->ingredients($recipe);
        $this->makes($recipe);

        $this->em->flush();

        $ingredientFood = self::totalFood(InventoryService::deserializeItemList($this->em, $recipe['ingredients']));
        $makesFood = self::totalFood(InventoryService::deserializeItemList($this->em, $recipe['makes']));

        $this->output->writeln('Ingredient food value totals:');
        $this->output->writeln('  Food: ' . $ingredientFood->getFood());
        $this->output->writeln('  Love: ' . $ingredientFood->getLove());
        $this->output->writeln('  Junk: ' . $ingredientFood->getJunk());
        $this->output->writeln('  Alcohol : ' . $ingredientFood->getAlcohol());
        $this->output->writeln('  Caffeine: ' . $ingredientFood->getCaffeine());
        $this->output->writeln('  Trippy  : ' . $ingredientFood->getPsychedelic());
        $this->output->writeln('');
        $this->output->writeln('Product food value totals:');
        $this->output->writeln('  Food: ' . $makesFood->getFood());
        $this->output->writeln('  Love: ' . $makesFood->getLove());
        $this->output->writeln('  Junk: ' . $makesFood->getJunk());
        $this->output->writeln('  Alcohol : ' . $makesFood->getAlcohol());
        $this->output->writeln('  Caffeine: ' . $makesFood->getCaffeine());
        $this->output->writeln('  Trippy  : ' . $makesFood->getPsychedelic());
        $this->output->writeln('');
        $this->output->writeln('PHP:');
        $escapedName = str_replace("'", "\\'", $name);
        $this->output->writeln("[ 'name' => '$escapedName', 'ingredients' => '{$recipe['ingredients']}', 'makes' => '{$recipe['makes']}' ],");

        return self::SUCCESS;
    }

    private function askName(string $prompt, array $recipe, string $name)
    {
        $question = new Question($prompt . ' (' . $name . ') ', $name);
        $question->setValidator(function($answer) use($recipe) {
            $answer = trim($answer);

            $existing = RecipeRepository::findOneByName($answer);

            if($existing && $existing['ingredients'] !== $recipe['ingredients'])
                throw new \RuntimeException('There\'s already a Recipe with that name.');

            return $answer;
        });

        return $this->ask($question);
    }

    private function name(array &$recipe, string $name)
    {
        $recipe['name'] = $this->askName('What is it called?', $recipe, $name);
    }

    private function ingredients(array &$recipe)
    {
        $ingredients = InventoryService::deserializeItemList($this->em, $recipe['ingredients']);

        $ingredients = $this->editItemList($ingredients, 'ingredients');

        $recipe['ingredients'] = InventoryService::serializeItemList($ingredients);
    }

    private function makes(array &$recipe)
    {
        $makes = InventoryService::deserializeItemList($this->em, $recipe['makes']);

        $makes = $this->editItemList($makes, 'products');

        $recipe['makes'] = InventoryService::serializeItemList($makes);
    }

    /**
     * @param ItemQuantity[] $quantities
     * @return ItemQuantity[]
     */
    private function editItemList(array $quantities, string $describedAs): array
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

            $itemQuantity->item = $this->askNullableItem('Enter an Item name to add', null);
            if($itemQuantity->item === null)
                break;

            $itemQuantity->quantity = $this->askInt('Enter a quantity', 1, fn(int $n) => $n >= 0);

            $quantities[] = $itemQuantity;
        }

        return $quantities;
    }
}
