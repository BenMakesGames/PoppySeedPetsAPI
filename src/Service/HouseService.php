<?php
namespace App\Service;

use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Entity\User;
use App\Repository\PetRepository;

class HouseService
{
    private $petService;
    private $petRepository;

    /** @var PetActivityLog[] */
    public $activityLogs = [];

    public function __construct(PetService $petService, PetRepository $petRepository)
    {
        $this->petService = $petService;
        $this->petRepository = $petRepository;
    }

    public function run(User $user)
    {
        /** @var Pet[] $petsWithTime */
        $petsWithTime = $this->petRepository->createQueryBuilder('p')
            ->andWhere('p.owner=:user')
            ->andWhere('p.time>=60')
            ->setParameter('user', $user->getId())
            ->getQuery()
            ->execute()
        ;

        while(count($petsWithTime) > 0)
        {
            \shuffle($petsWithTime);

            for($i = count($petsWithTime) - 1; $i >= 0; $i--)
            {
                if($petsWithTime[$i]->getTime() >= 60)
                {
                    $this->activityLogs = array_merge($this->activityLogs, $this->petService->runHour($petsWithTime[$i]));

                    if($petsWithTime[$i]->getTime() < 60)
                        unset($petsWithTime[$i]);
                }
            }
        }
    }
}