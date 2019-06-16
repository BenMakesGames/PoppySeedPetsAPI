<?php
namespace App\Service;

use App\Entity\Pet;
use App\Entity\User;
use App\Repository\PetRepository;

class HouseService
{
    private $petService;
    private $petRepository;

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

        foreach($petsWithTime as $pet)
        {
            if($pet->getTime() >= 60)
                $this->petService->runHour($pet);
        }
    }
}