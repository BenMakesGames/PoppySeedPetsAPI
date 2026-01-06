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

use App\Command\Traits\AskItemTrait;
use App\Entity\ItemFood;
use App\Functions\ArrayFunctions;
use App\Model\ItemQuantity;
use App\Model\Recipe;
use App\Service\InventoryService;
use App\Service\RecipeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Question\Question;

class UpsertRecipeCommand extends PoppySeedPetsCommand
{
    use AskItemTrait;

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly RecipeRepository $recipeRepository
    )
    {
        parent::__construct();
    }

    protected function configure(): void
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

    /**
     * @param ItemQuantity[] $quantities
     */
    public static function totalFertilizer(array $quantities): int
    {
        return ArrayFunctions::sum($quantities, fn(ItemQuantity $quantity) => $quantity->item->getFertilizer() * $quantity->quantity);
    }

    protected function doCommand(): int
    {
        if(strtolower($_SERVER['APP_ENV']) !== 'dev')
            throw new \Exception('Can only be run in dev environments.');

        $name = $this->input->getArgument('recipe');
        $recipe = $this->recipeRepository->findOneByName($name);

        if($recipe)
            $this->output->writeln('Updating "' . $name . '"');
        else
        {
            $this->output->writeln('Creating "' . $name . '"');
            $recipe = new Recipe(
                $name,
                '',
                '',
                0
            );
        }

        $recipe = $this->name($recipe, $name);
        $recipe = $this->ingredients($recipe);
        $recipe = $this->makes($recipe);
        $recipe = $this->requiredHeat($recipe);

        $this->em->flush();

        $ingredientFood = self::totalFood(InventoryService::deserializeItemList($this->em, $recipe->ingredients));
        $ingredientFertilizer = self::totalFertilizer(InventoryService::deserializeItemList($this->em, $recipe->ingredients));
        $makesFood = self::totalFood(InventoryService::deserializeItemList($this->em, $recipe->makes));
        $makesFertilizer = self::totalFertilizer(InventoryService::deserializeItemList($this->em, $recipe->makes));

        $this->output->writeln('Ingredient food value totals:');
        $this->output->writeln('  Fertilizer: ' . $ingredientFertilizer);
        $this->output->writeln('  Food      : ' . $ingredientFood->getFood());
        $this->output->writeln('  Love      : ' . $ingredientFood->getLove());
        $this->output->writeln('  Junk      : ' . $ingredientFood->getJunk());
        $this->output->writeln('  Alcohol   : ' . $ingredientFood->getAlcohol());
        $this->output->writeln('  Caffeine  : ' . $ingredientFood->getCaffeine());
        $this->output->writeln('  Trippy    : ' . $ingredientFood->getPsychedelic());
        $this->output->writeln('');
        $this->output->writeln('Product food value totals:');
        $this->output->writeln('  Fertilizer: ' . $makesFertilizer);
        $this->output->writeln('  Food      : ' . $makesFood->getFood());
        $this->output->writeln('  Love      : ' . $makesFood->getLove());
        $this->output->writeln('  Junk      : ' . $makesFood->getJunk());
        $this->output->writeln('  Alcohol   : ' . $makesFood->getAlcohol());
        $this->output->writeln('  Caffeine  : ' . $makesFood->getCaffeine());
        $this->output->writeln('  Trippy    : ' . $makesFood->getPsychedelic());
        $this->output->writeln('');
        $this->output->writeln('PHP:');
        $escapedName = str_replace("'", "\\'", $name);
        $this->output->writeln("new Recipe('$escapedName', '$recipe->ingredients', '$recipe->makes', $recipe->requiredHeat),");

        return self::SUCCESS;
    }

    private function askName(string $prompt, Recipe $recipe, string $name): string
    {
        $question = new Question($prompt . ' (' . $name . ') ', $name);
        $question->setValidator(function($answer) use($recipe) {
            $answer = mb_trim($answer);

            $existing = $this->recipeRepository->findOneByName($answer);

            if($existing && $existing->ingredients !== $recipe->ingredients)
                throw new \RuntimeException('There\'s already a Recipe with that name.');

            return $answer;
        });

        return $this->ask($question);
    }

    private function name(Recipe $recipe, string $name): Recipe
    {
        return new Recipe(
            $this->askName('What is it called?', $recipe, $name),
            $recipe->ingredients,
            $recipe->makes,
            $recipe->requiredHeat
        );
    }

    private function ingredients(Recipe $recipe): Recipe
    {
        $ingredients = InventoryService::deserializeItemList($this->em, $recipe->ingredients);

        $ingredients = $this->editItemList($ingredients, 'ingredients');

        return new Recipe(
            $recipe->name,
            InventoryService::serializeItemList($ingredients),
            $recipe->makes,
            $recipe->requiredHeat
        );
    }

    private function makes(Recipe $recipe): Recipe
    {
        $makes = InventoryService::deserializeItemList($this->em, $recipe->makes);

        $makes = $this->editItemList($makes, 'products');

        return new Recipe(
            $recipe->name,
            $recipe->ingredients,
            InventoryService::serializeItemList($makes),
            $recipe->requiredHeat
        );
    }

    private function requiredHeat(Recipe $recipe): Recipe
    {
        return new Recipe(
            $recipe->name,
            $recipe->ingredients,
            $recipe->makes,
            $this->askInt('How much heat? (360 for melt; 300 for crafts)', $recipe->requiredHeat),
        );
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
                $this->output->writeln('  ' . $ingredient->quantity . 'x ' . $ingredient->item->getName() . ' (#' . $ingredient->item->getId() . ')');

            if(!$this->confirm('Change ' . $describedAs . '?', false))
                return $quantities;
        }

        $this->output->writeln('Enter ' . $describedAs . ':');

        $quantities = [];

        while(true)
        {
            $item = $this->askNullableItem('Enter an Item name to add', null);
            if($item === null)
                break;

            $quantity = $this->askInt('Enter a quantity', 1, fn(int $n) => $n >= 0);

            $quantities[] = new ItemQuantity($item, $quantity);
        }

        return $quantities;
    }
}
