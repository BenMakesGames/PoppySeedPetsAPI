<?php
namespace App\Command;

use App\Repository\PetRelationshipRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class EnableMaintenanceModeCommand extends Command
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
            ->setName('app:enable-maintenance-mode')
            ->addArgument('message', InputArgument::REQUIRED)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $maintenanceMode = $this->cache->getItem('MAINTENANCE_MODE');

        $message = trim($input->getArgument('message'));

        if($message === '')
            $output->writeln('Message may not be blank.');
        else
            $maintenanceMode->set($message);

        $maintenanceMode->expiresAfter(\DateInterval::createFromDateString('72 hours'));

        $output->writeln('Maintenance mode has been set: "' . $maintenanceMode->get() . '"');
    }
}
