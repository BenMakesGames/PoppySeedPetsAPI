<?php

namespace App\Repository;

use App\Entity\Merit;
use App\Enum\EnumInvalidValueException;
use App\Enum\MeritEnum;
use App\Functions\ArrayFunctions;
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
    public const POSSIBLE_STARTING_MERITS = [
        MeritEnum::BURPS_MOTHS,
        MeritEnum::NAIVE,
        MeritEnum::GOURMAND,
        MeritEnum::SPECTRAL,
        MeritEnum::PREHENSILE_TONGUE,
        MeritEnum::LOLLIGOVORE,
        MeritEnum::HYPERCHROMATIC,
        MeritEnum::DREAMWALKER,
        MeritEnum::EXTROVERTED,
        MeritEnum::SHEDS,
        MeritEnum::DARKVISION,
    ];

    public const POSSIBLE_FIRST_PET_STARTING_MERITS = [
        MeritEnum::BURPS_MOTHS,
        MeritEnum::GOURMAND,
        MeritEnum::PREHENSILE_TONGUE,
        MeritEnum::LOLLIGOVORE,
        MeritEnum::DREAMWALKER,
        MeritEnum::EXTROVERTED,
        MeritEnum::SHEDS,
        MeritEnum::DARKVISION,
    ];

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

    public function getRandomStartingMerit(): Merit
    {
        return $this->findOneBy([ 'name' => ArrayFunctions::pick_one(self::POSSIBLE_STARTING_MERITS) ]);
    }

    public function getRandomFirstPetStartingMerit(): Merit
    {
        return $this->findOneBy([ 'name' => ArrayFunctions::pick_one(self::POSSIBLE_FIRST_PET_STARTING_MERITS) ]);
    }
}
