<?php

namespace App\Command;

use App\Entity\Item;
use App\Entity\ItemFood;
use App\Entity\ItemHat;
use App\Entity\ItemTool;
use App\Enum\FlavorEnum;
use App\Repository\ItemRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Question\Question;

class UpsertItemCommand extends PoppySeedPetsCommand
{
    private $em;
    private $itemRepository;

    public function __construct(EntityManagerInterface $em, ItemRepository $itemRepository)
    {
        $this->em = $em;
        $this->itemRepository = $itemRepository;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:upsert-item')
            ->setDescription('Creates a new Item, or updates an existing one.')
            ->addArgument('item', InputArgument::REQUIRED, 'The name of the Item to upsert.')
        ;
    }

    protected function doCommand()
    {
        $name = $this->input->getArgument('item');
        $item = $this->itemRepository->findOneBy(['name' => $name]);

        if($item)
            $this->output->writeln('Updating "' . $item->getName() . '"');
        else
        {
            $this->output->writeln('Creating "' . $name . '"');

            $item = (new Item())
                ->setName($name)
            ;

            $this->em->persist($item);
        }

        $this->name($item, $name);
        $this->image($item);
        $this->elements($item);
        $this->tool($item);
        $this->hat($item);
        $this->food($item);
        $this->fertilizer($item);

        $this->em->flush();
    }

    private function askName(string $prompt, Item $item, string $name)
    {
        $question = new Question($prompt . ' (' . $name . ') ', $name);
        $question->setValidator(function($answer) use($item) {
            $answer = trim($answer);

            $existing = $this->itemRepository->findOneBy([ 'name' => $answer ]);

            if($existing && $existing->getId() !== $item->getId())
                throw new \RuntimeException('There\'s already an Item with that name.');

            return $answer;
        });

        return $this->ask($question);
    }

    private function name(Item $item, string $name)
    {
        $item->setName($this->askName('What is it called?', $item, $name));
    }

    private function image(Item $item)
    {
        if($item->getImage())
            $item->setImage($this->ask(new Question('What is its image? (' . $item->getImage() . ') ', $item->getImage())));
        else
            $item->setImage($this->ask(new Question('What is its image? ', '')));
    }

    private function fertilizer(Item $item)
    {
        $item->setFertilizer($this->askInt('Fertilizer hours', $item->getFertilizer()));
    }

    private function food(Item $item)
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

            foreach(FlavorEnum::getValues() as $flavor)
                $food->{'set' . $flavor}($this->askInt(ucfirst($flavor) . ' hours', $food->{'get' . $flavor}()));
        }
        else
        {
            if($item->getFood())
                $this->em->remove($item->getFood());

            $item->setFood(null);
        }
    }

    private function tool(Item $item)
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

            foreach(ItemTool::MODIFIER_FIELDS as $modifier)
                $tool->{'set' . $modifier}($this->askInt(ucfirst($modifier) . '', $tool->{'get' . $modifier}()));
        }
        else
        {
            if($item->getTool())
                $this->em->remove($item->getTool());

            $item->setTool(null);
        }
    }

    private function hat(Item $item)
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
            $hat->setHeadAngle($this->askInt('Hat angle', $hat->getHeadAngle()));
            $hat->setHeadAngleFixed($this->confirm('Hat angle fixed?', $hat->getHeadAngleFixed()));
        }
        else
        {
            if($item->getHat())
                $this->em->remove($item->getHat());

            $item->setHat(null);
        }
    }

    private function elements(Item $item)
    {
        /*
        $item->setEarth($this->askInt('Earth hours', $item->getEarth()));
        $item->setFire($this->askInt('Fire hours', $item->getFire()));
        $item->setWater($this->askInt('Water hours', $item->getWater()));
        $item->setWind($this->askInt('Wind hours', $item->getWind()));
        $item->setSpirit($this->askInt('Spirit hours', $item->getSpirit()));
        */
    }
}
