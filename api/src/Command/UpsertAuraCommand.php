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
use App\Entity\Aura;
use App\Entity\Enchantment;
use App\Entity\ItemFood;
use App\Entity\ItemTool;
use App\Functions\ArrayFunctions;
use App\Functions\EnchantmentRepository;
use App\Model\ItemQuantity;
use App\Model\Recipe;
use App\Service\InventoryService;
use App\Service\RecipeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Question\Question;

class UpsertAuraCommand extends PoppySeedPetsCommand
{
    use AskItemTrait;

    public function __construct(
        private readonly EntityManagerInterface $em,
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('app:upsert-aura')
            ->setDescription('Creates a new Aura (Hattier Style), or updates an existing one.')
            ->addArgument('aura', InputArgument::REQUIRED, 'The name of the Aura (as it appears at the Hattier\'s) to upsert.')
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

        $name = $this->input->getArgument('aura');
        $aura = $this->em->getRepository(Aura::class)->findOneBy([ 'name' => $name ]);

        if($aura)
        {
            $this->output->writeln('Updating "' . $name . '"');

            $enchantment = $this->em->getRepository(Enchantment::class)->findOneBy([
                'aura' => $aura,
            ]);
        }
        else
        {
            $this->output->writeln('Creating Aura: "' . $name . '"');

            $aura = new Aura($name, '');

            $this->em->persist($aura);

            $itemTool = (new ItemTool())
                ->setGripX(0)
                ->setGripY(0)
                ->setGripScale(0)
            ;

            $enchantment = (new Enchantment('', $itemTool))
                ->setAura($aura);

            $this->em->persist($enchantment);
        }

        $this->name($aura, $name);
        $this->image($aura);
        $this->positionAndScale($aura);

        $this->enchantmentName($enchantment);

        $this->em->flush();

        return self::SUCCESS;
    }

    private function askName(string $prompt, Aura $aura, string $name): string
    {
        $question = new Question($prompt . ' (' . $name . ') ', $name);
        $question->setValidator(function($answer) use($aura) {
            $answer = mb_trim($answer);

            $existing = $this->em->getRepository(Aura::class)->findOneBy([ 'name' => $answer ]);

            if($existing && $existing !== $aura)
                throw new \RuntimeException('There\'s already an Aura with that name.');

            return $answer;
        });

        return $this->ask($question);
    }

    private function name(Aura $aura, string $name): void
    {
        $aura->setName($this->askName('What is it called?', $aura, $name));
    }

    private function image(Aura $aura): void
    {
        $image = trim($this->askString('Image, without leading `images/auras/`', $aura->getImage()));

        $aura->setImage($image);
    }

    private function positionAndScale(Aura $aura): void
    {
        $size = $this->askFloat('Size', $aura->getSize(), fn(float $n) => $n >= 0);
        $centerX = $this->askFloat('X', $aura->getCenterX(), fn(float $n) => $n >= 0);
        $centerY = $this->askFloat('Y', $aura->getCenterY(), fn(float $n) => $n >= 0);

        $aura->setSize($size);
        $aura->setCenterX($centerX);
        $aura->setCenterY($centerY);
    }

    private function askEnchantmentWord(string $prompt, Enchantment $enchantment, string $word): string
    {
        $question = new Question($prompt . ' (' . $word . ') ', $word);
        $question->setValidator(function($answer) use($enchantment) {
            $answer = mb_trim($answer);

            $existing = $this->em->getRepository(Enchantment::class)->findOneBy([ 'name' => $answer ]);

            if($existing && $existing !== $enchantment)
                throw new \RuntimeException('There\'s already an Enchantment with that name.');

            return $answer;
        });

        return $this->ask($question);
    }

    private function enchantmentName(Enchantment $enchantment): void
    {
        $newName = $this->askEnchantmentWord('What is the bonus/enchantment word?', $enchantment, $enchantment->getName());

        $enchantment->setName($newName);

        $isSuffix = $this->askBool('Is the word a suffix?', $enchantment->getIsSuffix());

        $enchantment->setIsSuffix($isSuffix);
    }
}
