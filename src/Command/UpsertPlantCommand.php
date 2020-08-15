<?php

namespace App\Command;

use App\Command\Traits\AskItemTrait;
use App\Entity\Plant;
use App\Entity\PlantYield;
use App\Entity\PlantYieldItem;
use App\Repository\ItemRepository;
use App\Repository\PlantRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Input\InputArgument;

class UpsertPlantCommand extends PoppySeedPetsCommand
{
    use AskItemTrait;

    private $em;
    private $plantRepository;

    public function __construct(EntityManagerInterface $em, PlantRepository $plantRepository, ItemRepository $itemRepository)
    {
        $this->em = $em;
        $this->plantRepository = $plantRepository;
        $this->itemRepository = $itemRepository;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:upsert-plant')
            ->setDescription('Creates a new Plant, or updates an existing one.')
            ->addArgument('plant', InputArgument::REQUIRED, 'The name of the Plant to upsert.')
        ;
    }

    protected function doCommand()
    {
        $name = $this->input->getArgument('plant');
        $plant = $this->plantRepository->findOneBy(['name' => $name]);

        if($plant)
            $this->output->writeln('Updating "' . $plant->getName() . '"');
        else
        {
            $this->output->writeln('Creating "' . $name . '"');

            $recipe = (new Plant())
                ->setName($name)
            ;

            $this->em->persist($recipe);
        }

        do
        {
            $this->output->writeln('');
            $this->output->writeln('Edit Plant');
            $this->output->writeln('----------');

            if(count($plant->getPlantYields()) > 0)
            {
                $this->output->writeln('Current yields:');

                foreach($plant->getPlantYields() as $yield)
                {
                    $length = strlen('  ' . $yield->getMin() . '-' . $yield->getMax() . ': ');
                    $this->output->write('  ' . $yield->getMin() . '-' . $yield->getMax() . ': ');

                    $first = true;
                    foreach($yield->getItems() as $item)
                    {
                        if($first)
                            $first = false;
                        else
                            $this->output->write(str_repeat(' ', $length));

                        $this->output->writeln($item->getPercentChance() . '% ' . $item->getItem()->getName());
                    }
                }
            }

            $this->output->writeln('Choose an action!');
            $this->output->writeln('1. Edit a yield');
            $this->output->writeln('2. Delete a yield');
            $this->output->writeln('3. Add a yield');
            $this->output->writeln('4. Flush changes, and quit');

            $choice = $this->askInt('', 4, function(int $n) { return $n >= 1 && $n <= 4; });

            switch($choice)
            {
                case 1:
                    $this->selectYieldToEdit($plant);
                    break;
                case 2:
                    $this->deleteYield($plant);
                    break;
                case 3:
                    $this->addYield($plant);
                    break;
            }
        }
        while($choice !== 4);

        $this->em->flush();
    }

    private function selectYieldToEdit(Plant $plant)
    {
        $yieldIndex = $this->askInt('Which yield?', 1, function($n) use($plant) { return $n >= 1 && $n <= count($plant->getPlantYields()); });
        $yield = $plant->getPlantYields()[$yieldIndex - 1];

        $this->editYield($yield);
    }

    private function editYield(PlantYield $yield)
    {
        while(true)
        {
            $this->output->writeln('');
            $this->output->writeln('Edit Yield');
            $this->output->writeln('----------');

            $index = 1;
            foreach($yield->getItems() as $item)
            {
                $this->output->writeln($index . '. ' . $item->getPercentChance() . '% ' . $item->getItem()->getName());
                $index++;
            }

            $totalPercent = array_reduce(
                $yield->getItems()->toArray(),
                function(int $carry, PlantYieldItem $item) { return $carry + $item->getPercentChance(); },
                0
            );

            $this->output->writeln($index . '. Add a new item');
            $this->output->writeln(($index + 1) . '. Current total: ' . $totalPercent . '%; auto-balance to 100%');
            $this->output->writeln(($index + 2) . '. Change yield quantity');
            $this->output->writeln(($index + 3) . '. Done');

            $yieldIndex = $this->askInt('', $index + 3, function($n) use($index) { return $n >= 1 && $n <= $index + 3; });

            if($yieldIndex === $index + 3)
            {
                if($totalPercent === 100)
                    return;

                $this->output->writeln('Can\'t leave until the item drops total to 100% exactly!');
            }
            else if($yieldIndex === $index + 2)
            {
                $this->editYieldQuantities($yield);
            }
            else if($yieldIndex === $index + 1)
            {
                $fraction = $totalPercent / 100;

                foreach($yield->getItems() as $item)
                    $item->setPercentChance(round($item->getPercentChance() / $fraction));
            }
            else if($yieldIndex === $index)
            {
                $this->addYieldItem($yield);
            }
            else
            {
                $yieldItem = $yield->getItems()[$yieldIndex - 1];

                $this->editYieldItem($yieldItem);
            }
        }
    }

    private function addYieldItem(PlantYield $yield)
    {
        $item = $this->askItem('Item');
        $percent = $this->askInt('% Chance', 100, function($n) { return $n > 0 && $n <= 100; });

        $yieldItem = (new PlantYieldItem())
            ->setItem($item)
            ->setPercentChance($percent)
        ;

        $yield->addItem($yieldItem);

        $this->em->persist($yieldItem);
    }

    private function editYieldItem(PlantYieldItem $yieldItem)
    {
        $item = $this->askItem('Item');
        $percent = $this->askInt('% Chance', $yieldItem->getPercentChance(), function($n) { return $n > 0 && $n <= 100; });

        $yieldItem
            ->setItem($item)
            ->setPercentChance($percent)
        ;
    }

    private function deleteYield(Plant $plant)
    {
        $yieldIndex = $this->askInt('Which yield?', 1, function(int $n) use($plant) { return $n >= 1 && $n <= count($plant->getPlantYields()); });

        $yieldToDelete = $plant->getPlantYields()[$yieldIndex - 1];

        $plant->removePlantYield($yieldToDelete);
        $this->em->remove($yieldToDelete);
    }

    private function editYieldQuantities(PlantYield $yield)
    {
        $min = $this->askInt('Minimum yield:', $yield->getMin(), function(int $n) { return $n >= 1; });
        $max = $this->askInt('Maximum yield:', max($min, $yield->getMax()), function(int $n) use($min) { return $n >= $min; });

        $yield
            ->setMin($min)
            ->setMax($max)
        ;
    }

    private function addYield(Plant $plant)
    {
        $newYield = (new PlantYield())
            ->setMin(1)
            ->setMax(1)
        ;

        $this->editYieldQuantities($newYield);

        $plant->addPlantYield($newYield);

        $this->em->persist($newYield);

        $this->editYield($newYield);
    }
}
