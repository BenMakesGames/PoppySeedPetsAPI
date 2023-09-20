<?php

namespace App\Repository;

use App\Entity\Spice;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Spice|null find($id, $lockMode = null, $lockVersion = null)
 * @method Spice|null findOneBy(array $criteria, array $orderBy = null)
 * @method Spice[]    findAll()
 * @method Spice[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @deprecated
 */
class SpiceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Spice::class);
    }

    /**
     * @deprecated
     */
    public function deprecatedFindOneByName(string $name): ?Spice
    {
        return $this->findOneBy([ 'name' => $name ]);
    }

    public static function findOneByName(EntityManagerInterface $em, string $name): ?Spice
    {
        return $em->getRepository(Spice::class)->findOneBy([ 'name' => $name ]);
    }
}
