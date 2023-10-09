<?php
namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CalculateDailyMarketItemAveragesCommand extends Command
{
    private EntityManagerInterface $em;
    private LoggerInterface $logger;
    private OutputInterface $output;

    public function __construct(EntityManagerInterface $em, LoggerInterface $logger)
    {
        $this->em = $em;
        $this->logger = $logger;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:calculate-daily-market-item-averages')
            ->setDescription('Calculates daily market item averages >_>')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $startTime = microtime(true);

        $this->output = $output;

        $averages = $this->em->getConnection()
            ->executeQuery('
                SELECT
                    AVG(averages.price) AS price,
                    MIN(averages.min_price) AS min_price,
                    MAX(averages.max_price) AS max_price,
                    COUNT(averages.price) AS volume,
                    averages.item_id
                FROM (
                    SELECT
                        AVG(t.price) AS price,
                        MIN(t.price) AS min_price,
                        MAX(t.price) AS max_price,
                        item_id
                    FROM daily_market_inventory_transaction AS t
                    GROUP BY t.inventory
                ) AS averages
                GROUP BY averages.item_id            
            ')
            ->fetchAllAssociative()
        ;

        $this->em->getConnection()->executeQuery('TRUNCATE daily_market_inventory_transaction');

        $sqlRows = [];
        $date = (new \DateTimeImmutable())->modify('-1 day')->format('Y-m-d');

        $output->writeln('Computed ' . count($averages) . ' averages.');

        foreach($averages as $average)
        {
            if(count($sqlRows) === 1000)
            {
                $this->insert($sqlRows);

                $sqlRows = [];
            }

            $dataPoints = [
                (int)$average['item_id'],
                '"' . $date . '"',
                (float)$average['price'],
                (float)$average['min_price'],
                (float)$average['max_price'],
                (int)$average['volume']
            ];

            $sqlRows[] = '(' . implode(',', $dataPoints) . ')';
        }

        $this->insert($sqlRows);

        $runTime = microtime(true) - $startTime;

        $this->logger->notice('Calculating market averages took ' . round($runTime, 3) . 's.');

        $output->writeln('Done!');

        return self::SUCCESS;
    }

    private function insert(array $sqlRows)
    {
        $this->output->writeln('Inserting ' . count($sqlRows) . ' records...');

        $this->em->getConnection()->executeQuery('
            INSERT INTO daily_market_item_average
            (item_id, `date`, average_price, min_price, max_price, volume)
            VALUES
            ' . implode(',', $sqlRows) . '
        ');
    }
}
