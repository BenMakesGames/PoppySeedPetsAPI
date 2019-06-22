<?php

namespace App\Command;

use App\Entity\Item;
use App\Model\ItemFood;
use App\Repository\ItemRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Question\Question;

class UpsertItemCommand extends PsyPetsCommand
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
        $this->food($item);
        //$this->size($item);

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

    private function food(Item $item)
    {
        $edible = $item->getFood() !== null;

        $edible = $this->confirm('Is it edible?', $edible);

        if($edible)
        {
            if($item->getFood() !== null)
                $food = clone $item->getFood();
            else
                $food = new ItemFood();

            $food->food = $this->askInt('Food hours', $food->food);
            $food->love = $this->askInt('Love hours', $food->love);
            $food->junk = $this->askInt('Junk hours', $food->junk);
            $food->whack = $this->askInt('Whack hours', $food->whack);

            $item->setFood($food);
        }
        else
        {
            $item->setFood(null);
        }
    }

    /*
    private function size(Item $item)
    {
        $size = $this->askInt('How many bits is it?', $item->getSize(), function(int $n) { return $n > 0; });

        $item->setSize($size);
    }
    */
}
