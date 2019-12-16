<?php

namespace App\Repository;

use App\Entity\Merit;
use App\Enum\EnumInvalidValueException;
use App\Enum\MeritEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Merit|null find($id, $lockMode = null, $lockVersion = null)
 * @method Merit|null findOneBy(array $criteria, array $orderBy = null)
 * @method Merit[]    findAll()
 * @method Merit[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MeritRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Merit::class);
    }

    public function findOneByName(string $name)
    {
        if(!MeritEnum::isAValue($name))
            throw new EnumInvalidValueException(MeritEnum::class, $name);

        return $this->findOneBy([ 'name' => $name ]);
    }
}
