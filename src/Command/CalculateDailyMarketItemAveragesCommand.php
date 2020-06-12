<?php
namespace App\Command;

use Doctrine\DBAL\FetchMode;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CalculateDailyMarketItemAveragesCommand extends Command
{
    private $em;
    /** @var OutputInterface */ private $output;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:calculate-daily-market-item-averages')
            ->setDescription('Calculates daily market item averages >_>')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;

        $averages = $this->em->getConnection()
            ->executeQuery('
                SELECT
                    AVG(averages.price) AS price,
                    MIN(averages.min_price) AS min_price,
                    MAX(averages.max_price) AS max_price,
                    averages.item_id
                FROM (
                    SELECT
                        AVG(t.price) AS price,
                        MIN(t.price) AS min_price,
                        MAX(t.price) AS max_price,
                        item.id AS item_id
                    FROM `daily_market_inventory_transaction` AS t
                    INNER JOIN inventory AS i ON i.id=t.inventory_id
                    INNER JOIN item ON item.id=i.item_id
                    GROUP BY t.inventory_id
                ) AS averages
                GROUP BY averages.item_id            
            ')
            ->fetchAll(FetchMode::ASSOCIATIVE)
        ;

        $sqlRows = [];
        $date = date('Y-m-d');

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
                (float)$average['max_price']
            ];

            $sqlRows[] = '(' . implode(',', $dataPoints) . ')';
        }

        $this->insert($sqlRows);

        $output->writeln('Done!');
    }

    private function insert(array $sqlRows)
    {
        $this->output->writeln('Inserting ' . count($sqlRows) . ' records...');

        $this->em->getConnection()->executeQuery('
            INSERT INTO daily_market_item_average
            (item_id, `date`, average_price, min_price, max_price)
            VALUES
            ' . implode(',', $sqlRows) . '
        ');
    }
}
