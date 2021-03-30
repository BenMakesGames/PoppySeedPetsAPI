<?php

namespace App\Repository;

use App\Entity\Merit;
use App\Enum\EnumInvalidValueException;
use App\Enum\MeritEnum;
use App\Functions\ArrayFunctions;
use App\Service\Squirrel3;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

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
        MeritEnum::FRIEND_OF_THE_WORLD,
        MeritEnum::GOURMAND,
        MeritEnum::SPECTRAL,
        MeritEnum::PREHENSILE_TONGUE,
        MeritEnum::LOLLIGOVORE,
        MeritEnum::HYPERCHROMATIC,
        MeritEnum::DREAMWALKER,
        MeritEnum::GREGARIOUS,
        MeritEnum::SHEDS,
        MeritEnum::DARKVISION,
    ];

    public const POSSIBLE_FIRST_PET_STARTING_MERITS = [
        MeritEnum::BURPS_MOTHS,
        MeritEnum::GOURMAND,
        MeritEnum::PREHENSILE_TONGUE,
        MeritEnum::LOLLIGOVORE,
        MeritEnum::DREAMWALKER,
        MeritEnum::GREGARIOUS,
        MeritEnum::SHEDS,
        MeritEnum::DARKVISION,
    ];

    private $squirrel3;

    public function __construct(ManagerRegistry $registry, Squirrel3 $squirrel3)
    {
        $this->squirrel3 = $squirrel3;

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
        return $this->findOneBy([ 'name' => $this->squirrel3->rngNextFromArray(self::POSSIBLE_STARTING_MERITS) ]);
    }

    public function getRandomFirstPetStartingMerit(): Merit
    {
        return $this->findOneBy([ 'name' => $this->squirrel3->rngNextFromArray(self::POSSIBLE_FIRST_PET_STARTING_MERITS) ]);
    }

    public function getRandomAdoptedPetStartingMerit(): Merit
    {
        $possibleMerits = array_filter(self::POSSIBLE_STARTING_MERITS, function(string $m) {
            return $m !== MeritEnum::HYPERCHROMATIC && $m !== MeritEnum::SPECTRAL;
        });

        return $this->findOneBy([ 'name' => $this->squirrel3->rngNextFromArray($possibleMerits) ]);
    }
}
