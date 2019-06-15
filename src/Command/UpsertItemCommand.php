<?php

namespace App\Command;

use App\Entity\Item;
use App\Model\ItemFood;
use App\Repository\ItemRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

class UpsertItemCommand extends Command
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
            ->setDescription('Creates a new item, or updates an existing one.')
            ->addArgument('item', InputArgument::REQUIRED, 'The name of the item to upsert.')
        ;
    }

    /** @var InputInterface */ private $input;
    /** @var OutputInterface */ private $output;
    /** @var QuestionHelper */ private $questionHelper;

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;

        $name = $input->getArgument('item');
        $item = $this->itemRepository->findOneBy(['name' => $name]);

        $this->questionHelper = $this->getHelper('question');

        if($item)
        {
            $output->writeln('Updating "' . $item->getName() . '"');
        } else
        {
            $output->writeln('Creating "' . $name . '"');

            $item = (new Item())
                ->setName($name)
            ;

            $this->em->persist($item);
        }

        $this->name($item, $name);
        $this->food($item);
        $this->size($item);

        $this->em->flush();
    }

    private function confirm(string $prompt, bool $defaultValue): bool
    {
        if($defaultValue)
            $prompt .= ' (Yes) ';
        else
            $prompt .= ' (No) ';

        return $this->questionHelper->ask($this->input, $this->output, new ConfirmationQuestion($prompt, $defaultValue));
    }

    private function askInt(string $prompt, int $defaultValue): int
    {
        $question = new Question($prompt . ' (' . $defaultValue . ') ', $defaultValue);

        $question->setValidator(function($answer) {
            if((int)$answer != $answer)
                throw new \RuntimeException('Must be a number.');

            return (int)$answer;
        });

        return $this->ask($question);
    }

    private function askPositiveInt(string $prompt, int $defaultValue): int
    {
        $question = new Question($prompt . ' (' . $defaultValue . ') ', $defaultValue);

        $question->setValidator(function($answer) {
            if((int)$answer != $answer)
                throw new \RuntimeException('Must be a number.');
            if($answer < 0)
                throw new \RuntimeException('Must not be less than 0.');

            return (int)$answer;
        });

        return $this->ask($question);
    }

    private function ask(Question $q)
    {
        return $this->questionHelper->ask($this->input, $this->output, $q);
    }

    private function name(Item $item, string $name)
    {
        if($item->getName() !== $name)
        {
            if($this->confirm('Rename "' . $item->getName() . '" to "' . $name . '"?', false))
                $item->setName($name);
        }
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

            $item->setFood($food);
        }
        else
        {
            $item->setFood(null);
        }
    }

    private function size(Item $item)
    {
        $size = $this->askPositiveInt('How many bits is it?', $item->getSize());

        $item->setSize($size);
    }
}
