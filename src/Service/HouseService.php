<?php
namespace App\Service;

use App\Entity\Pet;
use App\Entity\PetActivityLog;
use App\Entity\User;
use App\Repository\PetRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Cache\Adapter\AdapterInterface;

class HouseService
{
    private $petService;
    private $petRepository;
    private $cache;
    private $em;

    public function __construct(
        PetService $petService, PetRepository $petRepository, AdapterInterface $cache, EntityManagerInterface $em
    )
    {
        $this->petService = $petService;
        $this->petRepository = $petRepository;
        $this->cache = $cache;
        $this->em = $em;
    }

    public function run(User $user)
    {
        $item = $this->cache->getItem('User #' . $user->getId() . ' - Running House Hours');

        if(!$item->isHit())
        {
            $item->set(true)->expiresAfter(\DateInterval::createFromDateString('1 minute'));
            $this->cache->save($item);

            /** @var Pet[] $petsWithTime */
            $petsWithTime = $this->petRepository->createQueryBuilder('p')
                ->andWhere('p.owner=:user')
                ->andWhere('p.time>=60')
                ->andWhere('p.inDaycare=0')
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
                        $this->petService->runHour($petsWithTime[$i]);

                        $this->em->flush();

                        if($petsWithTime[$i]->getTime() < 60)
                            unset($petsWithTime[$i]);
                    }
                }
            }
        }
    }
}