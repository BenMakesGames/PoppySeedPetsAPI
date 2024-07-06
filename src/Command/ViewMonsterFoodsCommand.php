<?php
namespace App\Command;

use App\Controller\MonsterOfTheWeek\MonsterOfTheWeekHelpers;
use App\Entity\Item;
use App\Enum\MonsterOfTheWeekEnum;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ViewMonsterFoodsCommand extends Command
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('app:view-monster-foods')
            ->setDescription('View the food items, and values, for each type of monster of the week.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $types = MonsterOfTheWeekEnum::getValues();
        $everyItem = $this->em->getRepository(Item::class)->findBy([], [ 'name' => 'ASC' ]);

        foreach($types as $type)
            $this->showInfo($output, $everyItem, $type);

        return 0;
    }

    private function showInfo(OutputInterface $output, array $everyItem, string $monsterType)
    {
        $thresholds = MonsterOfTheWeekHelpers::getBasePrizeValues($monsterType);

        $output->writeln("$monsterType - $thresholds[0], $thresholds[1], $thresholds[2]");
        $output->writeln("-------------------------------");

        foreach($everyItem as $item)
        {
            $value = MonsterOfTheWeekHelpers::getItemValue($monsterType, $item);

            if($value <= 0) continue;

            $output->writeln(mb_str_pad($item->getName(), 60, ' ') . $value);
        }

        $output->writeln('');
    }
}
