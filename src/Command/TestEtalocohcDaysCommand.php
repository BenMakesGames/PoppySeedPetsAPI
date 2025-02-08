<?php
declare(strict_types=1);

namespace App\Command;

use App\Service\TraderService;
use Symfony\Component\Console\Input\InputArgument;

class TestEtalocohcDaysCommand extends PoppySeedPetsCommand
{
    protected function configure(): void
    {
        $this
            ->setName('app:test-etalocohc-days')
            ->setDescription('Shows upcoming Etalocŏhc days.')
        ;
    }

    protected function doCommand(): int
    {
        $day = new \DateTimeImmutable();

        for($i = 0; $i < 365; $i++)
        {
            if(TraderService::isEtalocohcDay($day))
            {
                $cost = TraderService::getEtalocohcCost($day);
                $this->output->writeln("√ {$day->format('Y-m-d')} - costs $cost");
            }
            else
                $this->output->writeln("x {$day->format('Y-m-d')}");

            $day = $day->modify('+1 day');
        }

        return self::SUCCESS;
    }
}
