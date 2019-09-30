<?php
namespace App\Command;

use App\Enum\UserStatEnum;
use App\Repository\UserRepository;
use App\Repository\UserStatsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MergePetsAdoptedStatsCommand extends Command
{
    private $em;
    private $userStatsRepository;
    private $userRepository;

    public function __construct(
        EntityManagerInterface $em, UserStatsRepository $userStatsRepository, UserRepository $userRepository
    )
    {
        $this->em = $em;
        $this->userStatsRepository = $userStatsRepository;
        $this->userRepository = $userRepository;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('app:merge-pets-adopted-stats')
            ->setDescription('Merge "Pets Adopted" stat.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $users = $this->userRepository->findAll();

        foreach($users as $user)
        {
            $petsAdopted = $this->userStatsRepository->findBy([
                'stat' => UserStatEnum::PETS_ADOPTED,
                'user' => $user
            ]);

            if(count($petsAdopted) > 1)
            {
                $output->writeln('user #' . $user->getId() . ' has ' . count($petsAdopted));

                $totalAdopted = 0;
                $earliestAdopted = null;
                $latestAdopted = null;

                foreach($petsAdopted as $adopted)
                {
                    $totalAdopted += $adopted->getValue();

                    if($earliestAdopted === null || $adopted->getFirstTime() < $earliestAdopted)
                        $earliestAdopted = $adopted->getFirstTime();

                    if($latestAdopted === null || $adopted->getLastTime() > $latestAdopted)
                        $latestAdopted = $adopted->getLastTime();

                    $this->em->remove($adopted);
                }

                $this->em->getConnection()->executeQuery('
                    INSERT INTO user_stats (user_id, stat, value, first_time, last_time) VALUES (
                      ' . $user->getId() . ',
                      "' . UserStatEnum::PETS_ADOPTED . '",
                      ' . $totalAdopted . ',
                      "' . $earliestAdopted->format('Y-m-d H:i:s') . '",
                      "' . $latestAdopted->format('Y-m-d H:i:s') . '"
                    )
                ');

                $this->em->flush();
            }
        }
    }
}
