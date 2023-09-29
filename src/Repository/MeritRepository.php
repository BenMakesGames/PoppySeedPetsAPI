<?php

namespace App\Repository;

use App\Entity\Merit;
use App\Enum\EnumInvalidValueException;
use App\Enum\MeritEnum;
use App\Exceptions\PSPNotFoundException;
use App\Model\MeritInfo;
use App\Service\IRandom;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Merit|null find($id, $lockMode = null, $lockVersion = null)
 * @method Merit|null findOneBy(array $criteria, array $orderBy = null)
 * @method Merit[]    findAll()
 * @method Merit[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @deprecated
 */
class MeritRepository extends ServiceEntityRepository
{
    private IRandom $rng;

    public function __construct(ManagerRegistry $registry, IRandom $rng)
    {
        $this->rng = $rng;

        parent::__construct($registry, Merit::class);
    }

    public static function findOneByName(EntityManagerInterface $em, string $name): Merit
    {
        if(!MeritEnum::isAValue($name))
            throw new EnumInvalidValueException(MeritEnum::class, $name);

        $merit = $em->getRepository(Merit::class)->createQueryBuilder('m')
            ->where('m.name=:name')
            ->setParameter('name', $name)
            ->getQuery()
            ->enableResultCache(24 * 60 * 60, 'MeritRepository_FindOneByName_' . $name)
            ->getOneOrNullResult();

        if(!$merit) throw new PSPNotFoundException('There is no Merit called ' . $name . '.');

        return $merit;
    }

    public function getRandomStartingMerit(): Merit
    {
        return MeritRepository::findOneByName($this->getEntityManager(), $this->rng->rngNextFromArray(MeritInfo::POSSIBLE_STARTING_MERITS));
    }

    public function getRandomFirstPetStartingMerit(): Merit
    {
        return MeritRepository::findOneByName($this->getEntityManager(), $this->rng->rngNextFromArray(MeritInfo::POSSIBLE_FIRST_PET_STARTING_MERITS));
    }

    public function getRandomAdoptedPetStartingMerit(): Merit
    {
        $possibleMerits = array_filter(MeritInfo::POSSIBLE_STARTING_MERITS, function(string $m) {
            return $m !== MeritEnum::HYPERCHROMATIC && $m !== MeritEnum::SPECTRAL;
        });

        return MeritRepository::findOneByName($this->getEntityManager(), $this->rng->rngNextFromArray($possibleMerits));
    }
}
