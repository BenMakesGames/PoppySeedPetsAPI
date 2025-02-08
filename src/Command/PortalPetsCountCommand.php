<?php
declare(strict_types=1);

namespace App\Command;

use App\Service\AdoptionService;
use App\Service\Clock;
use App\Service\Squirrel3;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PortalPetsCountCommand extends Command
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('app:portal-pets-count')
            ->setDescription('Check how many pets will be available in the portal in the next 365 days.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $clock = new Clock();

        $min = PHP_INT_MAX;
        $max = PHP_INT_MIN;
        $total = 0;

        for($i = 0; $i < 365; $i++)
        {
            $number = AdoptionService::getNumberOfPets($clock->now);
            $rarePetIndicies = AdoptionService::getRarePetIndicies($clock->now);

            $min = min($min, $number);
            $max = max($max, $number);
            $total += $number;

            $output->write($clock->now->format('Y-m-d') . ': ' . $number);

            if(count($rarePetIndicies) > 0)
                $output->write(' (rare pets: ' . implode(', ', $rarePetIndicies) . ')');

            $output->writeln('');

            $clock->now = $clock->now->add(\DateInterval::createFromDateString('1 day'));
        }

        $output->writeln('Min: ' . $min);
        $output->writeln('Max: ' . $max);
        $output->writeln('Avg: ' . ($total / 365));

        return self::SUCCESS;
    }
}
