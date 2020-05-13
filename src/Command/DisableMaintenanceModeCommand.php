<?php
namespace App\Command;

use App\Repository\PetRelationshipRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DisableMaintenanceModeCommand extends Command
{
    private $em;
    private $cache;

    public function __construct(EntityManagerInterface $em, AdapterInterface $cache)
    {
        $this->em = $em;
        $this->cache = $cache;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:disable-maintenance-mode')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->cache->deleteItems([ 'MAINTENANCE_MODE' ]);

        $output->writeln('Maintenance mode has been disabled.');
    }
}
