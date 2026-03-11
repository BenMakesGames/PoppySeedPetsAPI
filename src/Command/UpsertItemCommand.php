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
use App\Entity\Item;
use App\Entity\ItemFood;
use App\Entity\ItemGrammar;
use App\Entity\ItemGroup;
use App\Entity\ItemHat;
use App\Entity\ItemTool;
use App\Enum\FlavorEnum;
use App\Enum\PetSkillEnum;
use App\Functions\ArrayFunctions;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Question\Question;

class UpsertItemCommand extends PoppySeedPetsCommand
{
    use AskItemTrait;

    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('app:upsert-item')
            ->setDescription('Creates a new Item, or updates an existing one.')
            ->addArgument('item', InputArgument::REQUIRED, 'The name of the Item to upsert.')
        ;
    }

    protected function doCommand(): int
    {
        if(strtolower($_SERVER['APP_ENV']) !== 'dev')
            throw new \Exception('Can only be run in dev environments.');

        $name = $this->input->getArgument('item');
        $item = $this->em->getRepository(Item::class)->findOneBy(['name' => $name]);

        if($item)
            $this->output->writeln('Updating "' . $item->getName() . '"');
        else
        {
            if(str_starts_with($name, 'Tile:'))
                throw new \Exception('Tiles must be created with app:create-hollow-earth-tile.');

            $this->output->writeln('Creating Item: "' . $name . '"');

            $item = (new Item())
                ->setName($name)
            ;

            $this->em->persist($item);
        }

        $this->name($item, $name);
        $this->article($item);
        $this->image($item);
        $this->tool($item);
        $this->hat($item);
        $this->food($item);
        $this->fertilizer($item);
        $this->fuel($item);
        $this->recycleValueAndMuseumPoints($item);
        $this->groups($item);

        $this->em->flush();

        return self::SUCCESS;
    }

    private function askName(string $prompt, Item $item, string $name): mixed
    {
        $question = new Question($prompt . ' (' . $name . ') ', $name);
        $question->setValidator(function($answer) use($item) {
            $answer = mb_trim($answer);

            $existing = $this->em->getRepository(Item::class)->findOneBy([ 'name' => $answer ]);

            if($existing && $existing->getId() !== $item->getId())
                throw new \RuntimeException('There\'s already an Item with that name.');

            return $answer;
        });

        return $this->ask($question);
    }

    private function name(Item $item, string $name): void
    {
        $item->setName($this->askName('What is it called?', $item, $name));
    }

    private function article(Item $item): void
    {
        if(!$item->getGrammar())
        {
            $article = $this->askNullableString('Article?', 'a');

            $grammar = new ItemGrammar($item, $article);

            $this->em->persist($grammar);
        }
        else
        {
            $article = $this->askNullableString('Article?', $item->getGrammar()->getArticle());

            $item->getGrammar()->setArticle($article);
        }
    }

    private function image(Item $item): void
    {
        if($item->getImage())
            $item->setImage($this->ask(new Question('What is its image? (' . $item->getImage() . ') ', $item->getImage())));
        else
            $item->setImage($this->ask(new Question('What is its image? ', '')));
    }

    private function fertilizer(Item $item): void
    {
        $item->setFertilizer($this->askInt('Fertilizer hours', $item->getFertilizer()));
    }

    private function food(Item $item): void
    {
        $edible = $item->getFood() !== null;

        $edible = $this->confirm('Is it edible?', $edible);

        if($edible)
        {
            if($item->getFood() !== null)
                $food = $item->getFood();
            else
            {
                $food = new ItemFood();
                $this->em->persist($food);

                $item->setFood($food);
            }

            $food->setFood($this->askInt('Food hours', $food->getFood()));
            $food->setLove($this->askInt('Love hours', $food->getLove()));
            $food->setJunk($this->askInt('Junk hours', $food->getJunk()));
            $food->setAlcohol($this->askInt('Alcohol hours', $food->getAlcohol()));
            $food->setCaffeine($this->askInt('Caffeine hours', $food->getCaffeine()));
            $food->setPsychedelic($this->askInt('Psychedelic hours', $food->getPsychedelic()));

            foreach(FlavorEnum::cases() as $flavor)
                $food->{'set' . $flavor->value}($this->askInt(ucfirst($flavor->value) . ' hours', $food->{'get' . $flavor->value}()));

            $food->setContainsTentacles($this->askBool('Contains tentacles?', $food->getContainsTentacles()));

            $food->setLeftovers($this->askNullableItem('Leftovers', $food->getLeftovers()));

            $food->setChanceForBonusItem($this->askNullableInt('Chance for bonus item', $food->getChanceForBonusItem()));

            $food->setIsCandy();
        }
        else
        {
            if($item->getFood())
                $this->em->remove($item->getFood());

            $item->setFood(null);
        }
    }

    private function tool(Item $item): void
    {
        $equipable = $item->getTool() !== null;

        $equipable = $this->confirm('Is it a tool?', $equipable);

        if($equipable)
        {
            if($item->getTool() !== null)
                $tool = $item->getTool();
            else
            {
                $tool = new ItemTool();
                $this->em->persist($tool);

                $item->setTool($tool);
            }

            $tool->setGripScale($this->askFloat('Grip scale', $tool->getGripScale()));
            $tool->setGripX($this->askFloat('Grip X', $tool->getGripX()));
            $tool->setGripY($this->askFloat('Grip Y', $tool->getGripY()));
            $tool->setGripAngle($this->askInt('Grip angle', $tool->getGripAngle()));
            $tool->setGripAngleFixed($this->confirm('Grip angle fixed?', $tool->getGripAngleFixed()));

            foreach(ItemTool::ModifierFields as $modifier)
                $tool->{'set' . $modifier}($this->askInt(ucfirst($modifier), $tool->{'get' . $modifier}()));

            $tool->setProvidesLight($this->askBool('Provides light?', $tool->getProvidesLight()));
            $tool->setProtectionFromHeat($this->askBool('Protects from heat?', $tool->getProtectionFromHeat()));
            $tool->setIsRanged($this->askBool('Is ranged?', $tool->getIsRanged()));
            $tool->setLeadsToAdventure($this->askBool('Leads to adventure?', $tool->getLeadsToAdventure()));
            $tool->setFocusSkill($this->askFocusSkill($tool->getFocusSkill()));
        }
        else
        {
            if($item->getTool())
                $this->em->remove($item->getTool());

            $item->setTool(null);
        }
    }

    private function askFocusSkill(?string $default): ?string
    {
        $result = $this->askChoice('Skill to focus?', array_merge(PetSkillEnum::getValues(), [ 'NULL' ]), $default);

        return $result === 'NULL' ? null : $result;
    }

    private function hat(Item $item): void
    {
        $wearable = $item->getHat() !== null;

        $wearable = $this->confirm('Is it a hat?', $wearable);

        if($wearable)
        {
            if($item->getHat() !== null)
                $hat = $item->getHat();
            else
            {
                $hat = new ItemHat();
                $this->em->persist($hat);

                $item->setHat($hat);
            }

            $hat->setHeadScale($this->askFloat('Hat scale', $hat->getHeadScale()));
            $hat->setHeadX($this->askFloat('Head X', $hat->getHeadX()));
            $hat->setHeadY($this->askFloat('Head Y', $hat->getHeadY()));
            $hat->setHeadAngle($this->askFloat('Hat angle', $hat->getHeadAngle()));
            $hat->setHeadAngleFixed($this->confirm('Hat angle fixed?', $hat->getHeadAngleFixed()));
        }
        else
        {
            if($item->getHat())
                $this->em->remove($item->getHat());

            $item->setHat(null);
        }
    }

    private function fuel(Item $item): void
    {
        $item->setFuel($this->askInt('Fuel', $item->getFuel()));
    }

    private function recycleValueAndMuseumPoints(Item $item): void
    {
        $item->setRecycleValue($this->askInt('Recycle Value', $item->getRecycleValue()));

        // museum points must be asked for AFTER recycle value:
        $item->setMuseumPoints($this->askInt('Museum Points', $item->getMuseumPoints() === 0 ? max(1, (int)floor($item->getRecycleValue() / 5) * 10) : $item->getMuseumPoints()));
    }

    private function groups(Item $item): void
    {
        $suggestedItemGroups = [];

        if($item->getFood())
        {
            $suggestedItemGroups[] = 'Cooking';
            if($item->getFood()->getGrantedSkill())
                $suggestedItemGroups[] = 'Skill Food';
        }
        if(mb_strpos($item->getName(), 'Choco') !== false && mb_strpos($item->getImage(), 'choco') !== false) $suggestedItemGroups[] = 'Chocolate';
        if(mb_strpos($item->getName(), 'Pizza') !== false && mb_strpos($item->getImage(), 'pizza') !== false) $suggestedItemGroups[] = 'Za';
        if(mb_strpos($item->getName(), 'Chees') !== false && mb_strpos($item->getImage(), 'chees') !== false) $suggestedItemGroups[] = 'Cheese';
        if(mb_strpos($item->getName(), 'Key') !== false && mb_strpos($item->getImage(), 'key') !== false) $suggestedItemGroups[] = 'Key';
        if(mb_strpos($item->getName(), 'Blood') !== false && mb_strpos($item->getImage(), 'blood') !== false) $suggestedItemGroups[] = 'Bloody';
        if(mb_strpos($item->getImage(), 'fruit') !== false) $suggestedItemGroups[] = 'Fresh Fruit';
        if(mb_strpos($item->getName(), 'Soup') !== false && mb_strpos($item->getImage(), 'soup') !== false) $suggestedItemGroups[] = 'Soup';
        if(mb_strpos($item->getImage(), 'wand') !== false) $suggestedItemGroups[] = 'Wand';
        if(mb_strpos($item->getImage(), 'sword') !== false) $suggestedItemGroups[] = 'Sword';
        if(mb_strpos($item->getImage(), 'spear') !== false) $suggestedItemGroups[] = 'Spear';
        if(mb_strpos($item->getName(), 'Bucket') !== false && mb_strpos($item->getImage(), 'bucket') !== false) $suggestedItemGroups[] = 'Bucket';
        if(mb_strpos($item->getName(), 'Book') !== false && mb_strpos($item->getImage(), 'book') !== false) $suggestedItemGroups[] = 'Book';
        if(mb_strpos($item->getImage(), 'music') !== false || mb_strpos($item->getImage(), 'instrument') !== false) $suggestedItemGroups[] = 'Musical Instrument';

        if(count($suggestedItemGroups) > 0)
        {
            sort($suggestedItemGroups);
            $this->output->writeln('Are any of these item groups appropriate? ' . implode(', ', $suggestedItemGroups));
        }

        while(true)
        {
            $itemGroup = $this->askString('Add to an item group', null);

            if(!$itemGroup)
                break;

            $itemGroup = $this->em->getRepository(ItemGroup::class)->findOneBy([ 'name' => $itemGroup ]);

            if(!$itemGroup)
            {
                $this->output->writeln('No item group found with that name.');
                continue;
            }

            $item->addItemGroup($itemGroup);
        }

        if(ArrayFunctions::any($item->getItemGroups(), fn(ItemGroup $group) => $group->getName() === 'Chocolate'))
            $this->output->writeln('HEY, LISTEN: should an Etalocŏhc recipe be added for this item?');
    }
}
