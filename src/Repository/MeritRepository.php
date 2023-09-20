<?php

namespace App\Repository;

use App\Entity\Merit;
use App\Enum\EnumInvalidValueException;
use App\Enum\MeritEnum;
use App\Model\MeritInfo;
use App\Service\IRandom;
use App\Service\Squirrel3;
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
    private IRandom $squirrel3;

    public function __construct(ManagerRegistry $registry, Squirrel3 $squirrel3)
    {
        $this->squirrel3 = $squirrel3;

        parent::__construct($registry, Merit::class);
    }

    /**
     * @deprecated
     */
    public function deprecatedFindOneByName(string $name)
    {
        if(!MeritEnum::isAValue($name))
            throw new EnumInvalidValueException(MeritEnum::class, $name);

        return $this->findOneBy([ 'name' => $name ]);
    }

    public static function findOneByName(EntityManagerInterface $em, string $name)
    {
        if(!MeritEnum::isAValue($name))
            throw new EnumInvalidValueException(MeritEnum::class, $name);

        return $em->getRepository(Merit::class)->findOneBy([ 'name' => $name ]);
    }

    public function getRandomStartingMerit(): Merit
    {
        return $this->findOneBy([ 'name' => $this->squirrel3->rngNextFromArray(MeritInfo::POSSIBLE_STARTING_MERITS) ]);
    }

    public function getRandomFirstPetStartingMerit(): Merit
    {
        return $this->findOneBy([ 'name' => $this->squirrel3->rngNextFromArray(MeritInfo::POSSIBLE_FIRST_PET_STARTING_MERITS) ]);
    }

    public function getRandomAdoptedPetStartingMerit(): Merit
    {
        $possibleMerits = array_filter(MeritInfo::POSSIBLE_STARTING_MERITS, function(string $m) {
            return $m !== MeritEnum::HYPERCHROMATIC && $m !== MeritEnum::SPECTRAL;
        });

        return $this->findOneBy([ 'name' => $this->squirrel3->rngNextFromArray($possibleMerits) ]);
    }
}
