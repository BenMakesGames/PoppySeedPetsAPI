<?php
namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;

class PlayerStatsCommand extends PoppySeedPetsCommand
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:player-stats')
            ->setDescription('Gives some stats about players.')
        ;
    }

    protected function doCommand(): int
    {
        $now = new \DateTimeImmutable();

        $activityQuery = $this->em->createQuery('SELECT COUNT(u.id) FROM App\Entity\User AS u WHERE u.lastActivity>=?0');
        $newUsersQuery = $this->em->createQuery('SELECT COUNT(u.id) FROM App\Entity\User AS u WHERE u.registeredOn>=?0');

        $data = [
            'Activity in the Last 1 Day' => $this->getCount($activityQuery, $now->modify('-24 hours')->format('Y-m-d H:i:s')),
            'Activity in the Last 2 Days' => $this->getCount($activityQuery, $now->modify('-48 hours')->format('Y-m-d H:i:s')),
            'Activity in the Last 3 Days' => $this->getCount($activityQuery, $now->modify('-72 hours')->format('Y-m-d H:i:s')),
            'Activity in the last 5 Days' => $this->getCount($activityQuery, $now->modify('-5 days')->format('Y-m-d H:i:s')),
            'Activity in the last 1 Week' => $this->getCount($activityQuery, $now->modify('-7 days')->format('Y-m-d H:i:s')),
            'Activity in the last 2 Weeks' => $this->getCount($activityQuery, $now->modify('-14 days')->format('Y-m-d H:i:s')),

            'New Users in the Last 1 Day' => $this->getCount($newUsersQuery, $now->modify('-24 hours')->format('Y-m-d H:i:s')),
            'New Users in the Last 2 Days' => $this->getCount($newUsersQuery, $now->modify('-48 hours')->format('Y-m-d H:i:s')),
            'New Users in the Last 3 Days' => $this->getCount($newUsersQuery, $now->modify('-72 hours')->format('Y-m-d H:i:s')),
            'New Users in the last 5 Days' => $this->getCount($newUsersQuery, $now->modify('-5 days')->format('Y-m-d H:i:s')),
            'New Users in the last 1 Week' => $this->getCount($newUsersQuery, $now->modify('-7 days')->format('Y-m-d H:i:s')),
            'New Users in the last 2 Weeks' => $this->getCount($newUsersQuery, $now->modify('-14 days')->format('Y-m-d H:i:s')),
        ];

        echo json_encode($data, JSON_PRETTY_PRINT);

        return self::SUCCESS;
    }

    private function getCount(Query $query, string $argument)
    {
        return (int)$query->execute([ $argument ])[0][1];
    }
}
